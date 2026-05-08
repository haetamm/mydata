<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class AgamaService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT id_agama, nama_agama
            FROM master_agama
            WHERE deleted_at IS NULL
            ORDER BY nama_agama
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAgama(string $nama_agama): array
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_agama
                WHERE nama_agama = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$nama_agama]);
            if ((int) $check->fetchColumn() > 0)
            {
                return ['success' => false, 'message' => 'Agama sudah ada.'];
            }

            $stmt   = $this->pdo->prepare("INSERT INTO master_agama (nama_agama) VALUES (?)");
            $result = $stmt->execute([$nama_agama]);

            return [
                'success' => $result,
                'message' => $result ? 'Agama berhasil ditambahkan.' : 'Gagal menambahkan agama.',
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menambahkan agama.'
            ];
        }
    }

    public function deleteAgama(int $id): array
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_agama
                WHERE id_agama   = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$id]);
            if ((int) $check->fetchColumn() === 0)
            {
                return ['success' => false, 'message' => 'Agama tidak ditemukan.'];
            }

            $stmt   = $this->pdo->prepare("UPDATE master_agama SET deleted_at = NOW() WHERE id_agama = ?");
            $result = $stmt->execute([$id]);

            return [
                'success' => $result,
                'message' => $result ? 'Agama berhasil dihapus.' : 'Gagal menghapus agama.',
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menghapus agama.'
            ];
        }
    }
}
