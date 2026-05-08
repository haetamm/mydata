<?php
// App/Services/SiswaImportService.php
declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

class SiswaImportService
{
    private const COL = [
        'nama'             =>  1,
        'nis'              =>  2,
        'nisn'             =>  3,
        'nik'              =>  4,
        'tempat_lahir'     =>  5,
        'tgl_lahir'        =>  6,
        'id_agama'         =>  7,
        'id_kelas'         =>  8,
        'ruang'            =>  9,
        'tahun_masuk'      => 10,
        'alamat'           => 11,
        'rt_rw'            => 12,
        'dusun'            => 13,
        'kelurahan'        => 14,
        'kecamatan'        => 15,
        'kode_pos'         => 16,
        'ayah_nama'        => 17,
        'ayah_tahun_lahir' => 18,
        'ayah_pendidikan'  => 19,
        'ayah_pekerjaan'   => 20,
        'ayah_penghasilan' => 21,
        'ayah_nik'         => 22,
        'ibu_nama'         => 23,
        'ibu_tahun_lahir'  => 24,
        'ibu_pendidikan'   => 25,
        'ibu_pekerjaan'    => 26,
        'ibu_penghasilan'  => 27,
        'ibu_nik'          => 28,
        'wali_nama'        => 29,
        'wali_tahun_lahir' => 30,
        'wali_pendidikan'  => 31,
        'wali_pekerjaan'   => 32,
        'wali_penghasilan' => 33,
        'wali_nik'         => 34,
    ];

    /**
     * Baris Excel tempat data mulai (1-based).
     * Struktur template:
     *   1 = Judul
     *   2 = Petunjuk
     *   3 = Kosong
     *   4 = Header kolom
     *   5+ = Data
     */
    private const DATA_START_ROW = 5;

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Entry point — dipanggil dari controller.
     *
     * Semua baris divalidasi dulu sebelum insert.
     * Jika ada 1 baris saja yang gagal (validasi atau DB), seluruh import dibatalkan (rollback).
     *
     * Mengembalikan ['sukses' => int, 'gagal' => int, 'errors' => array]
     */
    public function importFromFile(string $tabel, string $filePath): array
    {
        $this->assertValidTabel($tabel);

        $rows   = $this->parseXlsx($filePath);
        $result = ['sukses' => 0, 'gagal' => 0, 'errors' => []];

        if (empty($rows))
        {
            $result['errors'][] = 'File kosong atau tidak ada data yang dapat dibaca.';
            return $result;
        }

        // ── FASE 1: Validasi semua baris dulu, SEBELUM menyentuh DB ──────────
        $validationErrors = [];
        foreach ($rows as ['excelRow' => $excelRow, 'data' => $row])
        {
            $err = $this->validate($row, $excelRow);
            if ($err !== null)
            {
                $validationErrors[] = $err;
            }
        }

        // Jika ada error validasi, tolak semua — tidak perlu buka transaksi
        if (!empty($validationErrors))
        {
            $result['gagal']  = count($rows);
            $result['errors'] = $validationErrors;
            $result['errors'][] = '<strong>Tidak ada data yang disimpan</strong> karena terdapat baris yang tidak valid.';
            return $result;
        }

        // ── FASE 2: Insert semua dalam 1 transaksi ────────────────────────────
        $sql = $this->buildInsertSql($tabel);

        $this->pdo->beginTransaction();

        try
        {
            foreach ($rows as ['excelRow' => $excelRow, 'data' => $row])
            {
                $data = $this->mapRow($row);
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($data);
                $result['sukses']++;
            }

            $this->pdo->commit();
        }
        catch (PDOException $e)
        {
            $this->pdo->rollBack();

            // Cari baris mana yang error (sukses sudah terhitung sebelum exception)
            $failedExcelRow = $rows[$result['sukses']]['excelRow'] ?? '?';
            $failedRowData  = $rows[$result['sukses']]['data']     ?? [];

            $result['sukses']   = 0;
            $result['gagal']    = count($rows);
            $result['errors'][] = $this->friendlyError($e, $failedRowData, $failedExcelRow);
            $result['errors'][] = '<strong>Semua data dibatalkan (rollback)</strong> karena terjadi error pada baris di atas.';
        }

        return $result;
    }

    // ─── Parse XLSX ───────────────────────────────────────────────────────────

    /**
     * Membaca sheet pertama (Upload) dari file .xlsx.
     * Hanya membaca baris mulai DATA_START_ROW ke bawah.
     *
     * Mengembalikan array of: ['excelRow' => int, 'data' => array]
     */
    private function parseXlsx(string $filePath): array
    {
        $tempDir = sys_get_temp_dir() . '/siswa_import_' . uniqid('', true);
        mkdir($tempDir, 0777, true);

        try
        {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true)
            {
                throw new \RuntimeException(
                    'File Excel tidak dapat dibuka. Pastikan format .xlsx dan tidak rusak.'
                );
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // Shared strings
            $sharedStrings = [];
            $ssPath        = $tempDir . '/xl/sharedStrings.xml';
            if (file_exists($ssPath))
            {
                $ssXml = simplexml_load_file($ssPath);
                if ($ssXml)
                {
                    foreach ($ssXml->si as $si)
                    {
                        $parts = [];
                        foreach ($si->r as $r) $parts[] = (string) $r->t;
                        if (empty($parts))      $parts[] = (string) $si->t;
                        $sharedStrings[] = implode('', $parts);
                    }
                }
            }

            // Sheet 1
            $sheetPath = $tempDir . '/xl/worksheets/sheet1.xml';
            if (!file_exists($sheetPath))
            {
                throw new \RuntimeException(
                    'Struktur file Excel tidak valid. Pastikan menggunakan template resmi.'
                );
            }

            $xml  = simplexml_load_file($sheetPath);
            $rows = [];

            if ($xml && $xml->sheetData)
            {
                foreach ($xml->sheetData->row as $xmlRow)
                {
                    // Nomor baris dari atribut r (1-based, sesuai nomor baris Excel)
                    $excelRowNum = (int) ($xmlRow['r'] ?? 0);

                    // Skip baris judul / petunjuk / kosong / header kolom
                    if ($excelRowNum < self::DATA_START_ROW)
                    {
                        continue;
                    }

                    $rowData = [];

                    foreach ($xmlRow->c as $cell)
                    {
                        $colRef   = preg_replace('/[0-9]/', '', (string) $cell['r']);
                        $colIndex = $this->colRefToIndex($colRef);

                        $type  = (string) ($cell['t'] ?? '');
                        $value = (string) ($cell->v ?? '');

                        if ($type === 's')
                        {
                            $value = $sharedStrings[(int) $value] ?? '';
                        }

                        while (count($rowData) < $colIndex)
                        {
                            $rowData[] = '';
                        }

                        $rowData[$colIndex] = trim($value);
                    }

                    // Skip baris kosong (semua kolom selain "No" kosong)
                    $meaningful = array_filter(
                        array_slice($rowData, 1),
                        fn($v) => $v !== ''
                    );

                    if (!empty($meaningful))
                    {
                        $rows[] = [
                            'excelRow' => $excelRowNum,
                            'data'     => $rowData,
                        ];
                    }
                }
            }

            return $rows;
        }
        finally
        {
            // Cleanup temp dir
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iter as $f)
            {
                $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
            }
            if (is_dir($tempDir)) rmdir($tempDir);
        }
    }

    // ─── Validasi ─────────────────────────────────────────────────────────────

    private function validate(array $row, int $excelRow): ?string
    {
        $nama    = $row[self::COL['nama']]     ?? '';
        $idAgama = $row[self::COL['id_agama']] ?? '';
        $idKelas = $row[self::COL['id_kelas']] ?? '';
        $ruang   = $row[self::COL['ruang']]    ?? '';

        if ($nama === '')
        {
            return "Baris {$excelRow}: Kolom <strong>Nama</strong> wajib diisi.";
        }
        if ($idAgama === '' || !is_numeric($idAgama))
        {
            return "Baris {$excelRow}: Kolom <strong>ID Agama</strong> wajib diisi dengan angka.";
        }
        if ($idKelas === '' || !is_numeric($idKelas))
        {
            return "Baris {$excelRow}: Kolom <strong>ID Kelas</strong> wajib diisi dengan angka.";
        }
        if ($ruang === '')
        {
            return "Baris {$excelRow}: Kolom <strong>Ruang</strong> wajib diisi.";
        }

        $tglLahir = $row[self::COL['tgl_lahir']] ?? '';
        if ($tglLahir !== '' && $this->parseDate($tglLahir) === null)
        {
            return "Baris {$excelRow}: Format <strong>Tanggal Lahir</strong> tidak valid ({$tglLahir}). Gunakan YYYY-MM-DD atau DD/MM/YYYY.";
        }

        return null;
    }

    // ─── Map row → PDO data ───────────────────────────────────────────────────

    private function mapRow(array $row): array
    {
        $c = self::COL;
        $g = fn(int $i) => isset($row[$i]) && $row[$i] !== '' ? $row[$i] : null;

        return [
            'nama'             => $g($c['nama']),
            'nis'              => $g($c['nis']),
            'nisn'             => $g($c['nisn']),
            'nik'              => $g($c['nik']),
            'tempat_lahir'     => $g($c['tempat_lahir']),
            'tgl_lahir'        => $this->parseDate($row[$c['tgl_lahir']] ?? ''),
            'id_agama'         => $this->toInt($g($c['id_agama'])),
            'id_kelas'         => $this->toInt($g($c['id_kelas'])),
            'ruang'            => $g($c['ruang']),
            'tahun_masuk'      => $this->toYear($g($c['tahun_masuk'])),
            'alamat'           => $g($c['alamat']),
            'rt_rw'            => $g($c['rt_rw']),
            'dusun'            => $g($c['dusun']),
            'kelurahan'        => $g($c['kelurahan']),
            'kecamatan'        => $g($c['kecamatan']),
            'kode_pos'         => $g($c['kode_pos']),
            'ayah_nama'        => $g($c['ayah_nama']),
            'ayah_tahun_lahir' => $this->toYear($g($c['ayah_tahun_lahir'])),
            'ayah_pendidikan'  => $g($c['ayah_pendidikan']),
            'ayah_pekerjaan'   => $this->toInt($g($c['ayah_pekerjaan'])),
            'ayah_penghasilan' => $this->toDecimal($g($c['ayah_penghasilan'])),
            'ayah_nik'         => $g($c['ayah_nik']),
            'ibu_nama'         => $g($c['ibu_nama']),
            'ibu_tahun_lahir'  => $this->toYear($g($c['ibu_tahun_lahir'])),
            'ibu_pendidikan'   => $g($c['ibu_pendidikan']),
            'ibu_pekerjaan'    => $this->toInt($g($c['ibu_pekerjaan'])),
            'ibu_penghasilan'  => $this->toDecimal($g($c['ibu_penghasilan'])),
            'ibu_nik'          => $g($c['ibu_nik']),
            'wali_nama'        => $g($c['wali_nama']),
            'wali_tahun_lahir' => $this->toYear($g($c['wali_tahun_lahir'])),
            'wali_pendidikan'  => $g($c['wali_pendidikan']),
            'wali_pekerjaan'   => $this->toInt($g($c['wali_pekerjaan'])),
            'wali_penghasilan' => $this->toDecimal($g($c['wali_penghasilan'])),
            'wali_nik'         => $g($c['wali_nik']),
        ];
    }

    // ─── Build SQL ────────────────────────────────────────────────────────────

    private function buildInsertSql(string $tabel): string
    {
        $cols = implode(', ', array_keys(self::COL));
        $phs  = implode(', ', array_map(fn($k) => ':' . $k, array_keys(self::COL)));
        return "INSERT INTO {$tabel} ({$cols}) VALUES ({$phs})";
    }

    // ─── Error message ────────────────────────────────────────────────────────

    private function friendlyError(PDOException $e, array $row, int $excelRow): string
    {
        $msg = $e->getMessage();

        if (stripos($msg, 'Duplicate') !== false)
        {
            $nis  = $row[self::COL['nis']]  ?? '-';
            $nisn = $row[self::COL['nisn']] ?? '-';

            if (stripos($msg, 'nisn') !== false)
                return "Baris {$excelRow}: <strong>NISN {$nisn}</strong> sudah digunakan di database.";
            if (stripos($msg, 'nis') !== false)
                return "Baris {$excelRow}: <strong>NIS {$nis}</strong> sudah digunakan di database.";

            return "Baris {$excelRow}: Data duplikat — NIS/NISN sudah ada di database.";
        }

        if (stripos($msg, 'foreign key') !== false || stripos($msg, 'Cannot add') !== false)
        {
            return "Baris {$excelRow}: ID Agama, ID Kelas, atau ID Pekerjaan tidak ditemukan di master data.";
        }

        return "Baris {$excelRow}: Gagal menyimpan — " . $msg;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** "C" → 2, "A" → 0, "AA" → 26 */
    private function colRefToIndex(string $ref): int
    {
        $ref    = strtoupper($ref);
        $result = 0;
        $len    = strlen($ref);
        for ($i = 0; $i < $len; $i++)
        {
            $result = $result * 26 + (ord($ref[$i]) - ord('A') + 1);
        }
        return $result - 1; // 0-based
    }

    /** Parse tanggal dari berbagai format ke YYYY-MM-DD */
    private function parseDate(?string $val): ?string
    {
        if ($val === null || $val === '') return null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return $val;

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m))
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $val, $m))
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);

        // Serial number Excel (misal 45123)
        if (is_numeric($val) && strlen($val) <= 5)
        {
            $unix = ((int) $val - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        $ts = strtotime($val);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function toInt(?string $val): ?int
    {
        if ($val === null || $val === '') return null;
        $clean = preg_replace('/[^0-9]/', '', $val);
        return $clean !== '' ? (int) $clean : null;
    }

    private function toYear(?string $val): ?int
    {
        if ($val === null || $val === '') return null;
        if (preg_match('/^\d{4}$/', $val)) return (int) $val;
        $date = $this->parseDate($val);
        return $date !== null ? (int) substr($date, 0, 4) : null;
    }

    private function toDecimal(?string $val): ?float
    {
        if ($val === null || $val === '') return null;
        $clean = preg_replace('/[^0-9,.]/', '', $val);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function assertValidTabel(string $tabel): void
    {
        $valid = ['siswa_sd', 'siswa_smp', 'siswa_sma'];
        if (!in_array($tabel, $valid, true))
        {
            throw new \InvalidArgumentException("Tabel tidak valid: {$tabel}");
        }
    }
}
