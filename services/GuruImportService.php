<?php
// App/Services/GuruImportService.php
declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

class GuruImportService
{
    /**
     * Peta kolom Excel → index array (0-based).
     *
     * Struktur template Upload:
     *   0  = No (dilewati)
     *   1  = Nama *
     *   2  = Jenis Kelamin * (L/P)
     *   3  = NIK
     *   4  = NUPTK
     *   5  = Tempat Lahir
     *   6  = Tanggal Lahir
     *   7  = Nama Ibu
     *   8  = Status Pegawai
     *   9  = Jenis GTK
     *   10 = Jabatan
     *   11 = Alamat
     *   12 = Tahun Masuk
     */
    private const COL = [
        'nama'           => 1,
        'jenis_kelamin'  => 2,
        'nik'            => 3,
        'nuptk'          => 4,
        'tempat_lahir'   => 5,
        'tgl_lahir'      => 6,
        'nama_ibu'       => 7,
        'status_pegawai' => 8,
        'jenis_gtk'      => 9,
        'jabatan'        => 10,
        'alamat'         => 11,
        'tahun_masuk'    => 12,
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

    private const VALID_JK = ['L', 'P'];

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Entry point — dipanggil dari controller.
     *
     * Semua baris divalidasi dulu sebelum insert.
     * Jika ada 1 baris saja yang gagal, seluruh import dibatalkan (rollback).
     *
     * @return array{sukses: int, gagal: int, errors: list<string>}
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

        // ── FASE 1: Validasi semua baris sebelum menyentuh DB ─────────────
        $validationErrors = [];
        foreach ($rows as ['excelRow' => $excelRow, 'data' => $row])
        {
            $err = $this->validate($row, $excelRow);
            if ($err !== null)
            {
                $validationErrors[] = $err;
            }
        }

        if (!empty($validationErrors))
        {
            $result['gagal']    = count($rows);
            $result['errors']   = $validationErrors;
            $result['errors'][] = '<strong>Tidak ada data yang disimpan</strong> karena terdapat baris yang tidak valid.';
            return $result;
        }

        // ── FASE 2: Insert semua dalam 1 transaksi ────────────────────────
        $sql = $this->buildInsertSql($tabel);
        $this->pdo->beginTransaction();

        try
        {
            foreach ($rows as ['excelRow' => $excelRow, 'data' => $row])
            {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($this->mapRow($row));
                $result['sukses']++;
            }
            $this->pdo->commit();
        }
        catch (PDOException $e)
        {
            $this->pdo->rollBack();

            $failedIndex   = $result['sukses'];
            $failedExcelRow = $rows[$failedIndex]['excelRow'] ?? '?';
            $failedRowData  = $rows[$failedIndex]['data']     ?? [];

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
     * @return list<array{excelRow: int, data: list<string>}>
     */
    private function parseXlsx(string $filePath): array
    {
        $tempDir = sys_get_temp_dir() . '/guru_import_' . uniqid('', true);
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
                    $excelRowNum = (int) ($xmlRow['r'] ?? 0);

                    // Skip baris judul / petunjuk / spacer / header kolom
                    if ($excelRowNum < self::DATA_START_ROW)
                    {
                        continue;
                    }

                    $rowData = [];
                    foreach ($xmlRow->c as $cell)
                    {
                        $colRef   = preg_replace('/[0-9]/', '', (string) $cell['r']);
                        $colIndex = $this->colRefToIndex($colRef);
                        $type     = (string) ($cell['t'] ?? '');
                        $value    = (string) ($cell->v ?? '');

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
                        $rows[] = ['excelRow' => $excelRowNum, 'data' => $rowData];
                    }
                }
            }

            return $rows;
        }
        finally
        {
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
        $nama  = $row[self::COL['nama']]          ?? '';
        $jk    = strtoupper($row[self::COL['jenis_kelamin']] ?? '');

        if ($nama === '')
        {
            return "Baris {$excelRow}: Kolom <strong>Nama</strong> wajib diisi.";
        }
        if (!in_array($jk, self::VALID_JK, true))
        {
            return "Baris {$excelRow}: Kolom <strong>Jenis Kelamin</strong> harus diisi <strong>L</strong> atau <strong>P</strong>.";
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
            'nama'           => $g($c['nama']),
            'jenis_kelamin'  => strtoupper($g($c['jenis_kelamin']) ?? ''),
            'nik'            => $g($c['nik']),
            'nuptk'          => $g($c['nuptk']),
            'tempat_lahir'   => $g($c['tempat_lahir']),
            'tgl_lahir'      => $this->parseDate($row[$c['tgl_lahir']] ?? ''),
            'nama_ibu'       => $g($c['nama_ibu']),
            'status_pegawai' => $g($c['status_pegawai']),
            'jenis_gtk'      => $g($c['jenis_gtk']),
            'jabatan'        => $g($c['jabatan']),
            'alamat'         => $g($c['alamat']),
            'tahun_masuk'    => $this->toYear($g($c['tahun_masuk'])),
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
            $nik = $row[self::COL['nik']] ?? '-';
            if (stripos($msg, 'nik') !== false)
            {
                return "Baris {$excelRow}: <strong>NIK {$nik}</strong> sudah digunakan di database.";
            }
            return "Baris {$excelRow}: Data duplikat — NIK sudah ada di database.";
        }

        if (stripos($msg, 'foreign key') !== false || stripos($msg, 'Cannot add') !== false)
        {
            return "Baris {$excelRow}: Referensi data tidak ditemukan di master data.";
        }

        if (stripos($msg, "Data truncated") !== false && stripos($msg, 'jenis_kelamin') !== false)
        {
            return "Baris {$excelRow}: Kolom <strong>Jenis Kelamin</strong> harus diisi <strong>L</strong> atau <strong>P</strong>.";
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
        return $result - 1;
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

        // Serial number Excel
        if (is_numeric($val) && strlen($val) <= 5)
        {
            return date('Y-m-d', ((int) $val - 25569) * 86400);
        }

        $ts = strtotime($val);
        return $ts !== false ? date('Y-m-d', $ts) : null;
    }

    private function toYear(?string $val): ?int
    {
        if ($val === null || $val === '') return null;
        if (preg_match('/^\d{4}$/', $val)) return (int) $val;
        $date = $this->parseDate($val);
        return $date !== null ? (int) substr($date, 0, 4) : null;
    }

    private function assertValidTabel(string $tabel): void
    {
        if (!in_array($tabel, ['guru_sd', 'guru_smp', 'guru_sma'], true))
        {
            throw new \InvalidArgumentException("Tabel tidak valid: {$tabel}");
        }
    }
}
