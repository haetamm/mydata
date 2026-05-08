<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\GuruService;

session_start();

// Guard + load menu & permission dari DB
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'guru-smp');

$service           = new GuruService($pdo);
$servicePermission = new PermissionService($pdo);

$canCreate = $servicePermission->can($permMap, 'guru-smp', 'create');
$canExport = $servicePermission->can($permMap, 'guru-smp', 'export');
$canImport = $servicePermission->can($permMap, 'guru-smp', 'import');
$canEdit   = $servicePermission->can($permMap, 'guru-smp', 'edit');
$canView   = $servicePermission->can($permMap, 'guru-smp', 'view');

// Parameter
$limit   = 10;
$page    = max(1, (int) ($_GET['page'] ?? 1));
$jenjang = 'smp';

$filter = [
    'nama'          => trim($_GET['nama']          ?? ''),
    'status'        => trim($_GET['status']        ?? ''),
    'status_pegawai' => trim($_GET['status_pegawai'] ?? ''),
    'jabatan'       => trim($_GET['jabatan']        ?? ''),
];

// Actions via GET
if (isset($_GET['add']) && $canCreate)
{
    $params = array_merge($filter, [
        'id_guru' => 'new',
        'jenjang' => $jenjang,
        'page'    => $page,
    ]);
    header('Location: guru-edit.php?' . http_build_query($params));
    exit;
}

if (isset($_GET['download']) && $canExport)
{
    $q = http_build_query(array_filter($filter));
    header("Location: guru-export.php?jenjang=smp&{$q}");
    exit;
}

if (isset($_GET['upload']) && $canImport)
{
    header("Location: guru-import.php?jenjang=smp");
    exit;
}

// Action: ubah status via POST
$pesan = $_GET['pesan'] ?? '';

// Ambil data
$result     = null;
$fetchError = null;

try
{
    $result = $service->getGuru('guru_smp', $filter, $page, $limit);
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

// var_dump(json_encode($result));
$dataGuru   = $result['data']        ?? [];
$totalPages = $result['total_pages'] ?? 1;
$totalData  = $result['total']       ?? 0;
$page       = $result['page']        ?? $page;
$offset     = $result['offset']      ?? 0;

$baseQuery = http_build_query(array_filter([
    'nama'           => $filter['nama'],
    'status'         => $filter['status'],
    'status_pegawai' => $filter['status_pegawai'],
    'jabatan'        => $filter['jabatan'],
]));

?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php' ?>

        <!-- Content -->
        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-10 lg:pb-0">

                <!-- Header -->
                <?php $title = 'Daftar Guru SMP';
                include 'components/header_page.php' ?>

                <div class="bg-white">

                    <!-- Filterbar -->
                    <?php include 'components/filter_guru.php' ?>

                    <!-- Error -->
                    <?php include("components/feedback_error.php") ?>

                    <!-- Table guru (desktop) -->
                    <?php include 'components/table_guru.php' ?>

                    <!-- Card guru (mobile) -->
                    <?php include 'components/card_guru.php' ?>


                    <!-- Pagination -->
                    <?php include 'components/pagination.php' ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
