<?php

declare(strict_types=1);

namespace App\Services;

use PDO;


class SiswaService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getSiswa(string $tabel, array $filter, int $page = 1, int $limit = 10): array
    {
        $this->assertValidTabel($tabel);

        [$where, $params] = $this->buildWhere($filter);

        // Count
        $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM {$tabel} s {$where}");
        $stmtCount->execute($params);
        $total      = (int) $stmtCount->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $limit;

        // Data
        $stmtData = $this->pdo->prepare("
            SELECT
                s.id_siswa,
                s.nama,
                s.nis,
                s.nisn,
                s.tempat_lahir,
                s.tgl_lahir,
                s.ruang,
                s.status,
                a.nama_agama,
                mk.nama_kelas
            FROM {$tabel} s
            LEFT JOIN master_agama  a  ON s.id_agama = a.id_agama
            LEFT JOIN master_kelas  mk ON s.id_kelas = mk.id_kelas
            {$where}
            ORDER BY mk.nama_kelas ASC, s.nama ASC
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmtData->execute($params);

        return [
            'data'        => $stmtData->fetchAll(PDO::FETCH_ASSOC),
            'total'       => $total,
            'total_pages' => $totalPages,
            'page'        => $page,
            'offset'      => $offset,
        ];
    }

    public function nonaktifkan(string $tabel, int $idSiswa, string $keterangan = 'Dinonaktifkan via sistem'): bool
    {
        $this->assertValidTabel($tabel);

        $stmt = $this->pdo->prepare("
            UPDATE {$tabel}
            SET
                status            = 'keluar',
                status_keterangan = ?,
                status_updated_at = NOW(),
                tahun_keluar      = YEAR(NOW())
            WHERE id_siswa = ?
              AND deleted_at IS NULL
        ");

        $stmt->execute([$keterangan, $idSiswa]);
        return $stmt->rowCount() > 0;
    }

    public function getKelasByJenjang(string $jenjang): array
    {
        $stmt = $this->pdo->prepare("
            SELECT mk.id_kelas, mk.nama_kelas
            FROM master_kelas mk
            JOIN master_level_kelas mlk ON mk.level_id = mlk.id_level
            WHERE mlk.jenjang    = ?
              AND mk.deleted_at IS NULL
            ORDER BY mk.nama_kelas ASC
        ");
        $stmt->execute([strtoupper($jenjang)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildWhere(array $filter): array
    {
        $conditions = ['s.deleted_at IS NULL'];
        $params     = [];

        // Cari nama, NIS, atau NISN (satu input, tiga kolom)
        $nama = trim($filter['nama'] ?? '');
        if ($nama !== '')
        {
            $conditions[] = '(s.nama LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)';
            $like         = "%{$nama}%";
            array_push($params, $like, $like, $like);
        }

        // Filter kelas
        $idKelas = (int) ($filter['id_kelas'] ?? 0);
        if ($idKelas > 0)
        {
            $conditions[] = 's.id_kelas = ?';
            $params[]     = $idKelas;
        }

        // Filter status
        $status = trim($filter['status'] ?? '');
        $validStatus = ['aktif', 'lulus', 'pindah', 'keluar', 'dikeluarkan'];
        if ($status !== '' && in_array($status, $validStatus, true))
        {
            $conditions[] = 's.status = ?';
            $params[]     = $status;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    private function assertValidTabel(string $tabel): void
    {
        $valid = ['siswa_sd', 'siswa_smp', 'siswa_sma'];
        if (!in_array($tabel, $valid, true))
        {
            throw new \InvalidArgumentException("Tabel tidak valid: {$tabel}");
        }
    }

    public function getById(string $tabel, int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*,
                a.nama_agama,
                k.nama_kelas,
                pa.nama_pekerjaan AS ayah_nama_pekerjaan,
                pi.nama_pekerjaan AS ibu_nama_pekerjaan,
                pw.nama_pekerjaan AS wali_nama_pekerjaan
            FROM {$tabel} s
            LEFT JOIN master_agama      a  ON s.id_agama       = a.id_agama
            LEFT JOIN master_kelas      k  ON s.id_kelas        = k.id_kelas
            LEFT JOIN master_pekerjaan  pa ON s.ayah_pekerjaan  = pa.id_pekerjaan
            LEFT JOIN master_pekerjaan  pi ON s.ibu_pekerjaan   = pi.id_pekerjaan
            LEFT JOIN master_pekerjaan  pw ON s.wali_pekerjaan  = pw.id_pekerjaan
            WHERE s.id_siswa = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $tabel, array $data): array
    {
        try
        {
            $cols = implode(', ', array_keys($data));
            $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));

            $stmt = $this->pdo->prepare("INSERT INTO {$tabel} ($cols) VALUES ($phs)");
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Data siswa berhasil ditambahkan.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');

            // Duplicate entry
            if (str_contains($e->getMessage(), 'uq_siswa') || str_contains($e->getMessage(), 'Duplicate'))
            {
                if (str_contains($e->getMessage(), '_nisn')) return ['success' => false, 'message' => 'NISN sudah digunakan.'];
                if (str_contains($e->getMessage(), '_nis'))  return ['success' => false, 'message' => 'NIS sudah digunakan.'];
            }

            return ['success' => false, 'message' => 'Gagal menyimpan data siswa.'];
        }
    }

    public function update(string $tabel, int $id, array $data): array
    {
        try
        {
            $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $data['__id'] = $id;

            $stmt = $this->pdo->prepare("UPDATE {$tabel} SET $sets WHERE id_siswa = :__id");
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Data siswa berhasil diperbarui.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../../logs/error.log');

            if (str_contains($e->getMessage(), 'Duplicate'))
            {
                if (str_contains($e->getMessage(), 'nisn')) return ['success' => false, 'message' => 'NISN sudah digunakan.'];
                if (str_contains($e->getMessage(), 'nis'))  return ['success' => false, 'message' => 'NIS sudah digunakan.'];
            }

            return ['success' => false, 'message' => 'Gagal memperbarui data siswa.'];
        }
    }

    public function updateStatus(string $tabel, int $id, string $status, ?string $keterangan = null): array
    {
        try
        {
            $stmt = $this->pdo->prepare("
            UPDATE {$tabel}
            SET status            = :status,
                status_keterangan = :keterangan,
                status_updated_at = NOW(),
                tahun_keluar      = CASE
                    WHEN :status2 != 'aktif' THEN YEAR(NOW())
                    ELSE NULL
                END
            WHERE id_siswa = :id
        ");
            $stmt->execute([
                ':status'     => $status,
                ':keterangan' => $keterangan,
                ':status2'    => $status,
                ':id'         => $id,
            ]);

            return ['success' => true, 'message' => "Status siswa berhasil diubah ke '$status'."];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../../logs/error.log');
            return ['success' => false, 'message' => 'Gagal mengubah status siswa.'];
        }
    }

    public function getForExport(string $tabel, array $filter): array
    {
        $this->assertValidTabel($tabel);

        [$where, $params] = $this->buildWhere($filter);

        $stmt = $this->pdo->prepare("
            SELECT
                s.nama,
                s.nis,
                s.nisn,
                s.nik,
                s.tempat_lahir,
                s.tgl_lahir,
                ma.nama_agama,
                mk.nama_kelas,
                s.ruang,
                s.tahun_masuk,
                s.tahun_keluar,
                s.status,
                s.status_keterangan,
                -- Alamat
                s.alamat,
                s.rt_rw,
                s.dusun,
                s.kelurahan,
                s.kecamatan,
                s.kode_pos,
                -- Ayah
                s.ayah_nama,
                s.ayah_tahun_lahir,
                s.ayah_pendidikan,
                pa.nama_pekerjaan   AS ayah_nama_pekerjaan,
                s.ayah_penghasilan,
                s.ayah_nik,
                -- Ibu
                s.ibu_nama,
                s.ibu_tahun_lahir,
                s.ibu_pendidikan,
                pi.nama_pekerjaan   AS ibu_nama_pekerjaan,
                s.ibu_penghasilan,
                s.ibu_nik,
                -- Wali
                s.wali_nama,
                s.wali_tahun_lahir,
                s.wali_pendidikan,
                pw.nama_pekerjaan   AS wali_nama_pekerjaan,
                s.wali_penghasilan,
                s.wali_nik
            FROM {$tabel} s
            LEFT JOIN master_agama     ma ON s.id_agama       = ma.id_agama
            LEFT JOIN master_kelas     mk ON s.id_kelas       = mk.id_kelas
            LEFT JOIN master_pekerjaan pa ON s.ayah_pekerjaan = pa.id_pekerjaan
            LEFT JOIN master_pekerjaan pi ON s.ibu_pekerjaan  = pi.id_pekerjaan
            LEFT JOIN master_pekerjaan pw ON s.wali_pekerjaan = pw.id_pekerjaan
            {$where}
            ORDER BY mk.nama_kelas ASC, s.nama ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
