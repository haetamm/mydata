<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PekerjaanService;
use App\Services\PermissionService;
use App\Validation\MasterValidation;

session_start();

// Guard
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-pkrj');

$pekerjaanService = new PekerjaanService($pdo);
$servicePermission = new PermissionService($pdo);

$canCreate = $servicePermission->can($permMap, 'master-pkrj', 'create');
$canDelete = $servicePermission->can($permMap, 'master-pkrj', 'delete');

$pesan            = $_GET['pesan'] ?? '';
$errors           = [];

// POST: Tambah pekerjaan
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['action'])
    && $_GET['action'] === 'add_pekerjaan'
    && $canCreate
)
{
    $data   = ['nama' => trim($_POST['nama_pekerjaan'] ?? '')];
    $errors = MasterValidation::save($data);

    if (empty($errors))
    {
        try
        {
            $res = $pekerjaanService->addPekerjaan($data['nama']);
            $_SESSION['swal'] = [
                'icon'  => $res['success'] ? 'success' : 'error',
                'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
                'html'  => $res['message'],
            ];

            header('Location: pekerjaan.php');
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
            header('Location: pekerjaan.php?pesan=' . urlencode('Terjadi kesalahan sistem.'));
        }
        exit;
    }
}

// POST: Hapus pekerjaan
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_id'])
    && $canDelete
)
{
    $deleteId = (int) $_POST['delete_id'];
    try
    {
        $res = $pekerjaanService->deletePekerjaan($deleteId);
        $_SESSION['swal'] = [
            'icon'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
            'html'  => $res['message'],
        ];

        header('Location: pekerjaan.php');
    }
    catch (\Throwable $e)
    {
        error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
        header('Location: pekerjaan.php?pesan=' . urlencode('Terjadi kesalahan sistem.'));
    }
    exit;
}

// GET data
$dataPekerjaan  = [];
$fetchError = null;

try
{
    $dataPekerjaan = $pekerjaanService->getAll();
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

$dataMaster = array_map(fn($item) => [
    'id_master'   => $item['id_pekerjaan'],
    'nama_master' => $item['nama_pekerjaan'],
], $dataPekerjaan);

?>

<?php include("layout/head.php") ?>
<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <!-- Sidebar -->
        <?php include("layout/sidebar.php") ?>

        <!-- Content -->
        <div class="h-full lg:h-screen overflow-auto no-scrollbar">
            <div class="lg:min-h-screen lg:bg-slate-100 md:p-2 lg:py-4">

                <div class="grid grid-cols-6 gap-2 lg:gap-3 h-full lg:h-[calc(100vh-32px)]">

                    <!-- Sidebar / Navigation -->
                    <?php include("layout/nav-master.php") ?>

                    <!-- Main Content -->
                    <main id="mainContent"
                        class="col-span-6 p-3 pb-[120px] xs:pb-10 lg:pb-4 lg:col-span-5 bg-white md:rounded-sm xl:rounded-lg overflow-hidden flex flex-col">
                        <div class=" flex-1 flex flex-col">

                            <!-- Error fetch -->
                            <?php include('components/feedback_error.php') ?>

                            <!-- Form Tambah -->
                            <?php
                            $title_form = "Pekerjaan";
                            $input_value = "nama_pekerjaan";
                            $action_form = "pekerjaan.php?action=add_pekerjaan";
                            $placeholder = "Masukkan nama pekerjaan";
                            include("components/form_master_add.php") ?>


                            <!-- Table Data -->
                            <div class="flex-1 min-h-0">
                                <?php $action = 'pekerjaan.php';
                                include("components/table_master.php") ?>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('layout/footer.php') ?>
