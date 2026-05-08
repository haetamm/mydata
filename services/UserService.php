<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class UserService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getUsers(array $filter, int $page = 1, int $limit = 10): array
    {
        [$where, $params] = $this->buildWhere($filter);

        $stmtCount = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            {$where}
        ");
        $stmtCount->execute($params);
        $total      = (int) $stmtCount->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $limit;

        $stmtData = $this->pdo->prepare("
            SELECT
                u.id,
                u.nama_lengkap,
                u.username,
                u.role_id,
                r.name     AS role_name,
                u.created_at,
                u.deleted_at
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            {$where}
            ORDER BY u.nama_lengkap ASC
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

    public function getById(string $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                u.id,
                u.nama_lengkap,
                u.username,
                u.role_id,
                u.level_id,
                u.deleted_at
            FROM users u
            WHERE u.id = ?
            AND u.role_id != 'superadmin'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): array
    {
        try
        {
            // Block assign role superadmin
            if (($data['role_id'] ?? '') === 'superadmin')
            {
                return ['success' => false, 'message' => 'Role tidak diizinkan.'];
            }

            // Cek username duplikat
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? AND deleted_at IS NULL");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch())
            {
                return ['success' => false, 'message' => 'Username sudah digunakan.'];
            }

            $id = uuidv4();
            $stmt = $this->pdo->prepare("
                INSERT INTO users (id, nama_lengkap, username, password, role_id, level_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $data['nama_lengkap'],
                $data['username'],
                password_hash($data['password'], PASSWORD_BCRYPT),
                $data['role_id']  ?: null,
                $data['level_id'] ?: null,
            ]);

            return ['success' => true, 'message' => 'User berhasil ditambahkan.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
    }

    public function update(string $id, array $data): array
    {
        try
        {
            // Block edit user superadmin
            $stmt = $this->pdo->prepare("SELECT role_id FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing && $existing['role_id'] === 'superadmin')
            {
                return ['success' => false, 'message' => 'User ini tidak dapat diubah.'];
            }

            // Block assign role superadmin
            if (($data['role_id'] ?? '') === 'superadmin')
            {
                return ['success' => false, 'message' => 'Role tidak diizinkan.'];
            }

            // Cek username duplikat (exclude diri sendiri)
            $stmt = $this->pdo->prepare("
                SELECT id FROM users WHERE username = ? AND id != ? AND deleted_at IS NULL
            ");
            $stmt->execute([$data['username'], $id]);
            if ($stmt->fetch())
            {
                return ['success' => false, 'message' => 'Username sudah digunakan.'];
            }

            // Jika password diisi, update sekalian
            if (!empty($data['password']))
            {
                $stmt = $this->pdo->prepare("
                    UPDATE users
                    SET nama_lengkap = ?, username = ?, password = ?, role_id = ?, level_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['nama_lengkap'],
                    $data['username'],
                    password_hash($data['password'], PASSWORD_BCRYPT),
                    $data['role_id']  ?: null,
                    $data['level_id'] ?: null,
                    $id,
                ]);
            }
            else
            {
                $stmt = $this->pdo->prepare("
                    UPDATE users
                    SET nama_lengkap = ?, username = ?, role_id = ?, level_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['nama_lengkap'],
                    $data['username'],
                    $data['role_id']  ?: null,
                    $data['level_id'] ?: null,
                    $id,
                ]);
            }

            return ['success' => true, 'message' => 'User berhasil diperbarui.'];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
    }

    public function toggleAktif(string $id, bool $aktif): array
    {
        try
        {
            // Block toggle superadmin
            $stmt = $this->pdo->prepare("SELECT role_id FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing && $existing['role_id'] === 'superadmin')
            {
                return ['success' => false, 'message' => 'User ini tidak dapat diubah.'];
            }

            $stmt = $this->pdo->prepare("
            UPDATE users
            SET deleted_at = ?
            WHERE id = ?
        ");
            $stmt->execute([
                $aktif ? null : date('Y-m-d H:i:s'),
                $id,
            ]);
            $label = $aktif ? 'diaktifkan' : 'dinonaktifkan';
            return ['success' => true, 'message' => "User berhasil {$label}."];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan.'];
        }
    }

    private function buildWhere(array $filter): array
    {
        $conditions = ['u.role_id != ?']; // exclude superadmin
        $params     = ['superadmin'];

        $nama = trim($filter['nama'] ?? '');
        if ($nama !== '')
        {
            $conditions[] = '(u.nama_lengkap LIKE ? OR u.username LIKE ?)';
            $like         = "%{$nama}%";
            array_push($params, $like, $like);
        }

        $roleId = trim($filter['role_id'] ?? '');
        if ($roleId !== '')
        {
            $conditions[] = 'u.role_id = ?';
            $params[]     = $roleId;
        }

        $status = trim($filter['status'] ?? '');
        if ($status === 'aktif')
        {
            $conditions[] = 'u.deleted_at IS NULL';
        }
        elseif ($status === 'nonaktif')
        {
            $conditions[] = 'u.deleted_at IS NOT NULL';
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }
}
