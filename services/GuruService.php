<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class GuruService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getGuru(string $tabel, array $filter, int $page = 1, int $limit = 10): array
    {
        $this->assertValidTabel($tabel);

        [$where, $params] = $this->buildWhere($filter);

        // Count
        $stmtCount = $this->pdo->prepare("SELECT COUNT(*) FROM {$tabel} g {$where}");
        $stmtCount->execute($params);
        $total      = (int) $stmtCount->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $limit;

        // Data
        $stmtData = $this->pdo->prepare("
            SELECT
                g.id_guru,
                g.nik,
                g.nuptk,
                g.nama,
                g.jenis_kelamin,
                g.tempat_lahir,
                g.tgl_lahir,
                g.status_pegawai,
                g.jenis_gtk,
                g.jabatan,
                g.tahun_masuk,
                g.status
            FROM {$tabel} g
            {$where}
            ORDER BY g.nama ASC
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

    public function getById(string $tabel, int $id): ?array
    {
        $this->assertValidTabel($tabel);

        $stmt = $this->pdo->prepare("
            SELECT g.*
            FROM {$tabel} g
            WHERE g.id_guru    = ?
              AND g.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(string $tabel, array $data): array
    {
        $this->assertValidTabel($tabel);

        try
        {
            $cols = implode(', ', array_keys($data));
            $phs  = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));

            $stmt = $this->pdo->prepare("INSERT INTO {$tabel} ($cols) VALUES ($phs)");
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Data guru berhasil ditambahkan.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');

            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'uq_guru'))
            {
                if (str_contains($e->getMessage(), '_nik'))   return ['success' => false, 'message' => 'NIK sudah terdaftar.'];
                if (str_contains($e->getMessage(), '_nuptk')) return ['success' => false, 'message' => 'NUPTK sudah terdaftar.'];
            }

            return ['success' => false, 'message' => 'Gagal menyimpan data guru.'];
        }
    }

    public function update(string $tabel, int $id, array $data): array
    {
        $this->assertValidTabel($tabel);

        try
        {
            $sets        = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $data['__id'] = $id;

            $stmt = $this->pdo->prepare("UPDATE {$tabel} SET $sets WHERE id_guru = :__id AND deleted_at IS NULL");
            $stmt->execute($data);

            return ['success' => true, 'message' => 'Data guru berhasil diperbarui.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');

            if (str_contains($e->getMessage(), 'Duplicate'))
            {
                if (str_contains($e->getMessage(), 'nik'))   return ['success' => false, 'message' => 'NIK sudah terdaftar.'];
                if (str_contains($e->getMessage(), 'nuptk')) return ['success' => false, 'message' => 'NUPTK sudah terdaftar.'];
            }

            return ['success' => false, 'message' => 'Gagal memperbarui data guru.'];
        }
    }

    public function updateStatus(string $tabel, int $id, string $status, ?string $keterangan = null): array
    {
        $this->assertValidTabel($tabel);

        $validStatus = ['aktif', 'pensiun', 'pindah', 'keluar', 'meninggal'];
        if (!in_array($status, $validStatus, true))
        {
            return ['success' => false, 'message' => 'Status tidak valid.'];
        }

        try
        {
            $stmt = $this->pdo->prepare("
                UPDATE {$tabel}
                SET
                    status            = :status,
                    status_keterangan = :keterangan,
                    status_updated_at = NOW(),
                    tahun_keluar      = CASE
                        WHEN :status2 != 'aktif' THEN YEAR(NOW())
                        ELSE NULL
                    END
                WHERE id_guru    = :id
                  AND deleted_at IS NULL
            ");

            $stmt->execute([
                ':status'     => $status,
                ':keterangan' => $keterangan,
                ':status2'    => $status,
                ':id'         => $id,
            ]);

            return ['success' => true, 'message' => "Status guru berhasil diubah ke '{$status}'."];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Gagal mengubah status guru.'];
        }
    }

    public function getDistinctJenisGtk(string $tabel): array
    {
        $this->assertValidTabel($tabel);

        $stmt = $this->pdo->prepare("
            SELECT DISTINCT jenis_gtk
            FROM {$tabel}
            WHERE jenis_gtk IS NOT NULL
              AND jenis_gtk != ''
              AND deleted_at IS NULL
            ORDER BY jenis_gtk ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDistinctStatusPegawai(string $tabel): array
    {
        $this->assertValidTabel($tabel);

        $stmt = $this->pdo->prepare("
            SELECT DISTINCT status_pegawai
            FROM {$tabel}
            WHERE status_pegawai IS NOT NULL
              AND status_pegawai != ''
              AND deleted_at IS NULL
            ORDER BY status_pegawai ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getForExport(string $tabel, array $filter): array
    {
        $this->assertValidTabel($tabel);

        [$where, $params] = $this->buildWhere($filter);

        $stmt = $this->pdo->prepare("
            SELECT
                g.nama,
                g.nik,
                g.nuptk,
                g.jenis_kelamin,
                g.tempat_lahir,
                g.tgl_lahir,
                g.nama_ibu,
                g.status_pegawai,
                g.jenis_gtk,
                g.jabatan,
                g.alamat,
                g.tahun_masuk,
                g.tahun_keluar,
                g.status,
                g.status_keterangan
            FROM {$tabel} g
            {$where}
            ORDER BY g.nama ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function buildWhere(array $filter): array
    {
        $conditions = ['g.deleted_at IS NULL'];
        $params     = [];

        // Cari nama, NIK, atau NUPTK
        $nama = trim($filter['nama'] ?? '');
        if ($nama !== '')
        {
            $conditions[] = '(g.nama LIKE ? OR g.nik LIKE ? OR g.nuptk LIKE ?)';
            $like         = "%{$nama}%";
            array_push($params, $like, $like, $like);
        }

        // Filter jenis GTK (Guru Kelas, Guru Mapel, dll)
        $jenisGtk = trim($filter['jenis_gtk'] ?? '');
        if ($jenisGtk !== '')
        {
            $conditions[] = 'g.jenis_gtk = ?';
            $params[]     = $jenisGtk;
        }

        // Filter status kepegawaian (PNS, Honorer, dll)
        $statusPegawai = trim($filter['status_pegawai'] ?? '');
        if ($statusPegawai !== '')
        {
            $conditions[] = 'g.status_pegawai = ?';
            $params[]     = $statusPegawai;
        }

        // Filter status keaktifan (aktif, pensiun, pindah, keluar, meninggal)
        $status      = trim($filter['status'] ?? '');
        $validStatus = ['aktif', 'pensiun', 'pindah', 'keluar', 'meninggal'];
        if ($status !== '' && in_array($status, $validStatus, true))
        {
            $conditions[] = 'g.status = ?';
            $params[]     = $status;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    private function assertValidTabel(string $tabel): void
    {
        $valid = ['guru_sd', 'guru_smp', 'guru_sma'];
        if (!in_array($tabel, $valid, true))
        {
            throw new \InvalidArgumentException("Tabel tidak valid: {$tabel}");
        }
    }
}
