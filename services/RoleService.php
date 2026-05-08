<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class RoleService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, name
            FROM roles
            WHERE deleted_at IS NULL
            AND id != 'superadmin'
            ORDER BY name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoles(array $filter, int $page = 1, int $limit = 10): array
    {
        [$where, $params] = $this->buildWhere($filter);

        $stmtCount = $this->pdo->prepare("
            SELECT COUNT(*) FROM roles {$where}
        ");
        $stmtCount->execute($params);
        $total      = (int) $stmtCount->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $limit;

        $stmtData = $this->pdo->prepare("
            SELECT id, name, deleted_at, created_at
            FROM roles
            {$where}
            ORDER BY name ASC
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
            SELECT id, name, deleted_at
            FROM roles
            WHERE id = ?
              AND id != 'superadmin'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllMenus(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, name, slug, parent_id, sort_order
            FROM menus
            WHERE deleted_at IS NULL
            ORDER BY parent_id ASC, sort_order ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPermissions(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, code, name
            FROM permissions
            ORDER BY id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPermissionsByRoleId(string $roleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT menu_id, permission_id
            FROM role_menu_permission
            WHERE role_id = ?
        ");
        $stmt->execute([$roleId]);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
        {
            $map[(int)$row['menu_id']][] = (int)$row['permission_id'];
        }
        return $map;
    }

    public function create(array $data): array
    {
        try
        {
            if (empty($data['id']) || empty($data['name']))
            {
                return ['success' => false, 'message' => 'ID dan nama role wajib diisi.'];
            }

            // Cek duplikat id
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE id = ?");
            $stmt->execute([$data['id']]);
            if ($stmt->fetch())
            {
                return ['success' => false, 'message' => 'ID role sudah digunakan.'];
            }

            // Cek duplikat name
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = ?");
            $stmt->execute([$data['name']]);
            if ($stmt->fetch())
            {
                return ['success' => false, 'message' => 'Nama role sudah digunakan.'];
            }

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO roles (id, name) VALUES (?, ?)");
            $stmt->execute([$data['id'], $data['name']]);

            $this->savePermissions($data['id'], $data['permissions'] ?? []);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Role berhasil ditambahkan.'];
        }
        catch (\Throwable $e)
        {
            $this->pdo->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
    }

    public function update(string $id, array $data): array
    {
        try
        {
            if (empty($data['name']))
            {
                return ['success' => false, 'message' => 'Nama role wajib diisi.'];
            }

            // Cek duplikat name (exclude diri sendiri)
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = ? AND id != ?");
            $stmt->execute([$data['name'], $id]);
            if ($stmt->fetch())
            {
                return ['success' => false, 'message' => 'Nama role sudah digunakan.'];
            }

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("UPDATE roles SET name = ? WHERE id = ?");
            $stmt->execute([$data['name'], $id]);

            $this->savePermissions($id, $data['permissions'] ?? []);

            $this->pdo->commit();
            return ['success' => true, 'message' => 'Role berhasil diperbarui.'];
        }
        catch (\Throwable $e)
        {
            $this->pdo->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data.'];
        }
    }

    public function toggleAktif(string $id, bool $aktif): array
    {
        try
        {
            $stmt = $this->pdo->prepare("UPDATE roles SET deleted_at = ? WHERE id = ?");
            $stmt->execute([
                $aktif ? null : date('Y-m-d H:i:s'),
                $id,
            ]);
            $label = $aktif ? 'diaktifkan' : 'dinonaktifkan';
            return ['success' => true, 'message' => "Role berhasil {$label}."];
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
            return ['success' => false, 'message' => 'Terjadi kesalahan.'];
        }
    }

    private function savePermissions(string $roleId, array $permissions): void
    {
        // Hapus semua permission lama, KECUALI dashboard
        $stmt = $this->pdo->prepare("
            DELETE rmp FROM role_menu_permission rmp
            JOIN menus m ON m.id = rmp.menu_id
            WHERE rmp.role_id = ?
            AND m.slug != 'dashboard'
        ");
        $stmt->execute([$roleId]);

        // Pastikan dashboard view selalu ada
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO role_menu_permission (role_id, menu_id, permission_id)
            SELECT ?, m.id, p.id
            FROM menus m, permissions p
            WHERE m.slug = 'dashboard'
            AND p.code = 'view'
        ");
        $stmt->execute([$roleId]);

        if (empty($permissions)) return;

        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO role_menu_permission (role_id, menu_id, permission_id)
            VALUES (?, ?, ?)
        ");

        foreach ($permissions as $menuId => $permIds)
        {
            foreach ($permIds as $permId)
            {
                $stmt->execute([$roleId, (int)$menuId, (int)$permId]);
            }
        }
    }

    private function buildWhere(array $filter): array
    {
        $conditions = ["id != 'superadmin'"];
        $params     = [];

        $nama = trim($filter['nama'] ?? '');
        if ($nama !== '')
        {
            $conditions[] = 'name LIKE ?';
            $params[]     = "%{$nama}%";
        }

        $status = trim($filter['status'] ?? '');
        if ($status === 'aktif')
        {
            $conditions[] = 'deleted_at IS NULL';
        }
        elseif ($status === 'nonaktif')
        {
            $conditions[] = 'deleted_at IS NOT NULL';
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }
}
