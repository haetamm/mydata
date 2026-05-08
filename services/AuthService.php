<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class AuthService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function login(string $username): array|false
    {
        $sql = "SELECT
                    u.id,
                    u.nama_lengkap,
                    u.username,
                    u.password,
                    u.role_id,
                    u.level_id,
                    m.jenjang,
                    r.name AS role_name
                FROM users u
                LEFT JOIN master_level_kelas m ON u.level_id = m.id_level
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.username = ?
                  AND u.deleted_at IS NULL
                  AND r.deleted_at IS NULL
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
