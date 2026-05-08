<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\SiswaService;

session_start();

// Guard + load menu & permission dari DB
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'siswa-sma');

$service = new SiswaService($pdo);
$servicePermission = new PermissionService($pdo);

$canCreate = $servicePermission->can($permMap, 'siswa-sma', 'create');
$canExport = $servicePermission->can($permMap, 'siswa-sma', 'export');
$canImport = $servicePermission->can($permMap, 'siswa-sma', 'import');
$canEdit = $servicePermission->can($permMap, 'siswa-sma', 'edit');
$canView = $servicePermission->can($permMap, 'siswa-sma', 'view');

// Parameter filter
$limit  = 10;
$page   = max(1, (int) ($_GET['page'] ?? 1));
$jenjang = "sma";

$filter = [
    'nama'     => trim($_GET['nama']      ?? ''),
    'id_kelas' => (int) ($_GET['id_kelas'] ?? 0),
    'status'   => trim($_GET['status']    ?? ''),
];

// Actions via GET (dalam satu form)

if (isset($_GET['add']) && $canCreate)
{
    $params = array_merge($filter, [
        'id_siswa'  => 'new',
        'jenjang'   => $jenjang,
        'page'      => $page
    ]);

    header('Location: siswa-edit.php?' . http_build_query($params));
    exit;
}

if (isset($_GET['download']) && $canExport)
{
    $q = http_build_query(array_filter($filter));
    header("Location: siswa-export.php?jenjang=sma&{$q}");
    exit;
}

if (isset($_GET['upload']) && $canImport)
{
    header("Location: siswa-import.php?jenjang=sma");
    exit;
}

$pesan = $_GET['pesan'] ?? '';

// Ambil data via service
$result     = null;
$fetchError = null;
$listKelas  = [];

try
{
    $result    = $service->getSiswa('siswa_sma', $filter, $page, $limit);
    $listKelas = $service->getKelasByJenjang('SMA');
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

// Unpack hasil service
$dataSiswa  = $result['data']        ?? [];
$totalPages = $result['total_pages'] ?? 1;
$totalData  = $result['total']       ?? 0;
$page       = $result['page']        ?? $page;
$offset     = $result['offset']      ?? 0;

// Base query string untuk pagination (pertahankan filter aktif)
$baseQuery = http_build_query(array_filter([
    'nama'     => $filter['nama'],
    'id_kelas' => $filter['id_kelas'] ?: null,
    'status'   => $filter['status'],
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
                <?php $title = 'Daftar Siswa SMA';
                include 'components/header_page.php' ?>

                <div class="bg-white">

                    <!-- filterbar -->
                    <?php include("components/filter_siswa.php") ?>

                    <!-- Error -->
                    <?php include("components/feedback_error.php") ?>

                    <!-- table siswa -->
                    <?php include("components/table_siswa.php") ?>


                    <!-- card siswa -->
                    <?php include("components/card_siswa.php") ?>

                    <!-- pagination -->
                    <?php include("components/pagination.php") ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include("layout/footer.php") ?>
