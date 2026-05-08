<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class KelasService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->prepare("
             SELECT
                k.id_kelas,
                k.nama_kelas,
                l.jenjang,
                l.level_min,
                l.level_max
            FROM master_kelas k
            JOIN master_level_kelas l ON k.level_id = l.id_level
            WHERE k.deleted_at IS NULL
            ORDER BY l.level_min, k.nama_kelas
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getByJenjang(string $jenjang): array
    {
        $stmt = $this->pdo->prepare("
            SELECT k.id_kelas, k.nama_kelas
            FROM master_kelas k
            JOIN master_level_kelas l ON k.level_id = l.id_level
            WHERE l.jenjang   = ?
              AND k.deleted_at IS NULL
            ORDER BY k.nama_kelas
        ");
        $stmt->execute([strtoupper($jenjang)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function addKelas(string $nama_kelas, int $level_id)
    {
        try
        {
            // pastikan level ada
            $check_level = $this->pdo->prepare("SELECT COUNT(*) FROM master_level_kelas WHERE id_level = ?");
            $check_level->execute([$level_id]);
            if ($check_level->fetchColumn() == 0)
            {
                return ['success' => false, 'message' => 'Level kelas tidak valid'];
            }

            // cek duplikat kelas di level tersebut
            $check_stmt = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_kelas
                WHERE nama_kelas = ? AND level_id = ? AND deleted_at IS NULL
            ");
            $check_stmt->execute([$nama_kelas, $level_id]);
            $exists = $check_stmt->fetchColumn();

            if ($exists > 0)
            {
                return ['success' => false, 'message' => 'Nama kelas sudah ada di level ini'];
            }

            // insert
            $stmt = $this->pdo->prepare("INSERT INTO master_kelas (nama_kelas, level_id) VALUES (?, ?)");
            $result = $stmt->execute([$nama_kelas, $level_id]);

            return [
                'success' => $result,
                'message' => $result ? 'Kelas berhasil ditambahkan' : 'Gagal menambahkan kelas'
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menambahkan kelas.'
            ];
        }
    }

    function deleteKelas(int $id)
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_kelas
                WHERE id_kelas   = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$id]);
            if ((int) $check->fetchColumn() === 0)
            {
                return ['success' => false, 'message' => 'Kelas tidak ditemukan.'];
            }

            $stmt = $this->pdo->prepare("UPDATE master_kelas SET deleted_at = NOW() WHERE id_kelas = ?");
            $result = $stmt->execute([$id]);

            return [
                'success' => $result,
                'message' => $result ? 'kelas berhasil dihapus' : 'Gagal menghapus kelas'
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menghapus kelas.'
            ];
        }
    }
}
