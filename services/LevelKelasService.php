<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class LevelKelasService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id_level, jenjang, level_min, level_max
            FROM master_level_kelas
            WHERE deleted_at IS NULL
            ORDER BY jenjang
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addLevelKelas(string $jenjang, int $level_min, int $level_max): array
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_level_kelas
                WHERE jenjang    = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$jenjang]);
            if ((int) $check->fetchColumn() > 0)
            {
                return [
                    'success' => false,
                    'message' => 'Jenjang ' . $jenjang . ' sudah memiliki konfigurasi level kelas.'
                ];
            }

            $stmt   = $this->pdo->prepare("
                INSERT INTO master_level_kelas (jenjang, level_min, level_max)
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$jenjang, $level_min, $level_max]);

            return [
                'success' => $result,
                'message' => $result ? 'Level kelas berhasil ditambahkan.' : 'Gagal menambahkan level kelas.',
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Gagal menambahkan level kelas.'];
        }
    }

    public function deleteLevelKelas(int $id): array
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_level_kelas
                WHERE id_level   = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$id]);
            if ((int) $check->fetchColumn() === 0)
            {
                return ['success' => false, 'message' => 'Level kelas tidak ditemukan.'];
            }

            $stmt   = $this->pdo->prepare("UPDATE master_level_kelas SET deleted_at = NOW() WHERE id_level = ?");
            $result = $stmt->execute([$id]);

            return [
                'success' => $result,
                'message' => $result ? 'Level kelas berhasil dihapus.' : 'Gagal menghapus level kelas.',
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menghapus level kelas.'
            ];
        }
    }
}
