<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\PermissionService;
use PDO;

class PermissionMiddleware
{
    private const DASHBOARD_FALLBACK = [
        [
            'id'          => null,
            'slug'        => 'dashboard',
            'name'        => 'Dashboard',
            'route'       => 'dashboard.php',
            'icon'        => 'home',
            'parent_id'   => null,
            'sort_order'  => 0,
            'permissions' => ['view' => true],
            'children'    => [],
        ],
    ];

    public static function handle(
        PDO     $pdo,
        ?string $menuSlug   = null,
        string  $permission = 'view',
        string  $loginPage  = 'index.php',
        string  $forbidden  = 'dashboard.php'
    ): array
    {
        if (empty($_SESSION['id_user']))
        {
            header("Location: $loginPage");
            exit;
        }

        // ✅ Ambil role_id fresh dari DB, bukan dari session
        $userService = new \App\Services\UserService($pdo);
        $freshRoleId = $userService->getFreshRoleId($_SESSION['id_user']);

        // User dihapus / dinonaktifkan → force logout
        if ($freshRoleId === null)
        {
            session_destroy();
            header("Location: $loginPage");
            exit;
        }

        // ✅ Sync session jika role berubah
        if ($_SESSION['role_id'] !== $freshRoleId)
        {
            $_SESSION['role_id'] = $freshRoleId;
        }

        $service = new PermissionService($pdo);
        $permMap = $service->getPermissionMap($freshRoleId);
        $menus   = $service->getMenusWithPermissions($freshRoleId);

        if (empty($menus))
        {
            $permMap = [];
            $menus   = self::DASHBOARD_FALLBACK;

            if ($menuSlug !== null && $menuSlug !== 'dashboard')
            {
                header("Location: $forbidden");
                exit;
            }

            return [$menus, $permMap];
        }

        if ($menuSlug !== null)
        {
            if (!PermissionService::can($permMap, $menuSlug, $permission))
            {
                header("Location: $forbidden");
                exit;
            }
        }

        return [$menus, $permMap];
    }
}
