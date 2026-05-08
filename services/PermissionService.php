<?php

declare(strict_types=1);

namespace App\Services;

use PDO;


class PermissionService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getPermissionMap(string $roleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.slug, p.code AS perm
            FROM   role_menu_permission rmp
            JOIN   roles       r ON rmp.role_id       = r.id  AND r.deleted_at IS NULL
            JOIN   menus       m ON rmp.menu_id       = m.id
            JOIN   permissions p ON rmp.permission_id = p.id
            WHERE  rmp.role_id  = ?
            AND  m.deleted_at IS NULL
            AND  m.is_visible = 1
        ");
        $stmt->execute([$roleId]);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
        {
            $map[$row['slug']][$row['perm']] = true;
        }
        return $map;
    }

    public function getMenusWithPermissions(string $roleId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                m.id,
                m.slug,
                m.name,
                m.route,
                m.icon,
                m.parent_id,
                m.sort_order,
                GROUP_CONCAT(p.code ORDER BY p.code SEPARATOR ',') AS perms
            FROM role_menu_permission rmp
            JOIN roles       r ON rmp.role_id       = r.id  AND r.deleted_at IS NULL
            JOIN menus       m ON rmp.menu_id       = m.id
            JOIN permissions p ON rmp.permission_id = p.id
            WHERE rmp.role_id  = ?
            AND m.deleted_at IS NULL
            AND m.is_visible = 1
            GROUP BY m.id, m.slug, m.name, m.route, m.icon, m.parent_id, m.sort_order
            HAVING SUM(p.code = 'view') > 0
            ORDER BY m.parent_id ASC, m.sort_order ASC
        ");
        $stmt->execute([$roleId]);
        $childRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $parentIds = array_values(array_unique(array_filter(
            array_column($childRows, 'parent_id'),
            fn($v) => $v !== null
        )));

        $parentMap = [];
        if ($parentIds)
        {
            $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
            $stmt2 = $this->pdo->prepare("
                SELECT id, slug, name, route, icon, parent_id, sort_order
                FROM   menus
                WHERE  id IN ($placeholders)
                AND  deleted_at IS NULL
                ORDER BY sort_order ASC
            ");
            $stmt2->execute($parentIds);
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $p)
            {
                $parentMap[(int)$p['id']] = $p;
            }
        }

        $parents  = [];
        $children = [];

        foreach ($childRows as $row)
        {
            $perms = [];
            foreach (explode(',', $row['perms'] ?? '') as $code)
            {
                if ($code !== '') $perms[$code] = true;
            }
            $row['permissions'] = $perms;
            unset($row['perms']);

            $pid = $row['parent_id'] !== null ? (int)$row['parent_id'] : null;

            if ($pid === null)
            {
                $id = (int)$row['id'];
                $parents[$id] ??= array_merge($row, ['children' => []]);
            }
            else
            {
                $children[$pid][] = $row;

                if (!isset($parents[$pid]) && isset($parentMap[$pid]))
                {
                    $parents[$pid] = array_merge($parentMap[$pid], [
                        'permissions' => [],
                        'children'    => [],
                    ]);
                }
            }
        }

        foreach ($children as $pid => $items)
        {
            if (isset($parents[$pid]))
            {
                $parents[$pid]['children'] = $items;
            }
        }

        $result = array_values(array_filter(
            $parents,
            fn($p) => !empty($p['children']) || $p['parent_id'] === null
        ));
        usort($result, fn($a, $b) => (int)$a['sort_order'] <=> (int)$b['sort_order']);

        return $result;
    }

    public static function can(array $permMap, string $menuSlug, string $permission): bool
    {
        return isset($permMap[$menuSlug][$permission]);
    }

    public static function canView(array $permMap, string $menuSlug): bool
    {
        return self::can($permMap, $menuSlug, 'view');
    }
}
