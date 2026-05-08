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
        // Cek login
        if (empty($_SESSION['id_user']))
        {
            header("Location: $loginPage");
            exit;
        }

        $roleId  = $_SESSION['role_id'] ?? '';
        $service = new PermissionService($pdo);

        $permMap = $service->getPermissionMap($roleId);
        $menus   = $service->getMenusWithPermissions($roleId);

        // Role dinonaktifkan / dihapus → paksa ke dashboard saja
        if (empty($menus))
        {
            $permMap = [];
            $menus   = self::DASHBOARD_FALLBACK;

            // Kalau lagi akses halaman selain dashboard, redirect
            if ($menuSlug !== null && $menuSlug !== 'dashboard')
            {
                header("Location: $forbidden");
                exit;
            }

            return [$menus, $permMap];
        }

        // Guard halaman normal
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
