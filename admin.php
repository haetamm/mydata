<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\UserService;

session_start();

// Guard + load menu & permission dari DB
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-admin');

$service           = new UserService($pdo);
$servicePermission = new PermissionService($pdo);

$canCreate = $servicePermission->can($permMap, 'master-admin', 'create');
$canEdit   = $servicePermission->can($permMap, 'master-admin', 'edit');
$canView   = $servicePermission->can($permMap, 'master-admin', 'view');

// Parameter
$limit = 10;
$page  = max(1, (int) ($_GET['page'] ?? 1));

$filter = [
    'nama'    => trim($_GET['nama']    ?? ''),
    'role_id' => trim($_GET['role_id'] ?? ''),
    'status'  => trim($_GET['status']  ?? ''),
];

// Actions via GET
if (isset($_GET['add']) && $canCreate)
{
    $params = array_merge($filter, [
        'id'        => 'new',
        'page'      => $page,
    ]);
    header('Location: admin-edit.php?' . http_build_query($params));
    exit;
}

$pesan = $_GET['pesan'] ?? '';

// Ambil data
$result     = null;
$fetchError = null;

try
{
    $result = $service->getUsers($filter, $page, $limit);
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

$dataUser   = $result['data']        ?? [];
$totalPages = $result['total_pages'] ?? 1;
$totalData  = $result['total']       ?? 0;
$page       = $result['page']        ?? $page;
$offset     = $result['offset']      ?? 0;

$baseQuery = http_build_query(array_filter([
    'nama'    => $filter['nama'],
    'role_id' => $filter['role_id'],
]));

?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php' ?>

        <!-- Content -->
        <div class="h-full lg:h-screen overflow-auto no-scrollbar">
            <div class="lg:min-h-screen lg:bg-slate-100 md:p-2 lg:py-4">

                <div class="grid grid-cols-6 gap-2 lg:gap-3 h-full lg:h-[calc(100vh-32px)]">

                    <!-- Sidebar / Navigation -->
                    <?php include("layout/nav-master.php") ?>

                    <!-- Main Content -->
                    <main id="mainContent"
                        class="col-span-6 p-3 xs:pb-10 lg:pb-4 lg:col-span-5 bg-white md:rounded-sm xl:rounded-lg overflow-hidden flex flex-col h-full">

                        <!-- Header Page -->
                        <?php $title = 'Daftar User';
                        include 'components/header_page.php' ?>

                        <!-- Filter User -->
                        <?php include 'components/filter_user.php' ?>

                        <!-- Error Feedback -->
                        <?php include 'components/feedback_error.php' ?>

                        <!-- Scrollable Table Area -->
                        <div class="flex-1 overflow-hidden flex flex-col min-h-0">
                            <!-- Table user (desktop) -->
                            <div class="overflow-auto flex-1">
                                <?php include 'components/table_user.php' ?>
                            </div>

                            <!-- Card user (mobile) -->
                            <?php include 'components/card_user.php' ?>

                            <!-- Pagination - Sticky at bottom -->
                            <div class="mt-4 pt-4 border-t border-gray-200 bg-white sticky bottom-0">
                                <?php include 'components/pagination.php' ?>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
