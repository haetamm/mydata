<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use PDO;

class PekerjaanService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT id_pekerjaan, nama_pekerjaan
            FROM master_pekerjaan
            WHERE deleted_at IS NULL
            ORDER BY nama_pekerjaan
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function addPekerjaan(string $nama_pekerjaan)
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_pekerjaan
                WHERE nama_pekerjaan   = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$nama_pekerjaan]);
            if ((int) $check->fetchColumn() > 0)
            {
                return ['success' => false, 'message' => 'Pekerjaan sudah ada.'];
            }

            // Insert baru
            $stmt = $this->pdo->prepare("INSERT INTO master_pekerjaan (nama_pekerjaan) VALUES (?)");
            $result = $stmt->execute([$nama_pekerjaan]);

            return [
                'success' => $result,
                'message' => $result ? 'Pekerjaan berhasil ditambahkan' : 'Gagal menambahkan Pekerjaan'
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menambahkan pekerjaan.'
            ];
        }
    }

    function deletePekerjaan(int $id)
    {
        try
        {
            $check = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM master_pekerjaan
                WHERE id_pekerjaan   = ?
                  AND deleted_at IS NULL
            ");
            $check->execute([$id]);
            if ((int) $check->fetchColumn() === 0)
            {
                return ['success' => false, 'message' => 'Pekerjaan tidak ditemukan.'];
            }

            $stmt = $this->pdo->prepare("UPDATE master_pekerjaan SET deleted_at = NOW() WHERE id_pekerjaan = ?");
            $result = $stmt->execute([$id]);

            return [
                'success' => $result,
                'message' => $result ? 'pekerjaan berhasil dihapus' : 'Gagal menghapus pekerjaan'
            ];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return [
                'success' => false,
                'message' => 'Gagal menghapus pekerjaan.'
            ];
        }
    }
}
