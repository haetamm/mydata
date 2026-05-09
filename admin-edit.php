<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\LevelKelasService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Validation\UserValidation;

session_start();

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-admin');

$canEdit   = PermissionService::can($permMap, 'master-admin', 'edit');
$canCreate = PermissionService::can($permMap, 'master-admin', 'create');
$canDelete = PermissionService::can($permMap, 'master-admin', 'delete');

$service           = new UserService($pdo);
$roleService       = new RoleService($pdo);
$levelKelasService = new LevelKelasService($pdo);

$page      = max(1, (int) ($_GET['page'] ?? 1));
$param     = $_GET['id'] ?? null;
$formMode  = ($param === 'new') ? 'create' : 'edit';
$back      = 'admin.php?' . buildUrl($_GET);
$pageTitle = $formMode === 'create' ? 'Tambah User' : 'Edit User';

$errors = [];
$roles  = [];
$levels = [];

$defaultForm = [
    'nama_lengkap'        => '',
    'username'            => '',
    'password'            => '',
    'password_konfirmasi' => '',
    'role_id'             => '',
    'level_id'            => '',
    'deleted_at'          => null,
];

if ($formMode === 'create' && !$canCreate)
{
    header('Location: admin.php');
    exit;
}

// Load data awal
try
{
    $roles  = $roleService->getAll();
    $levels = $levelKelasService->getAll();

    if ($formMode === 'edit')
    {
        $result = $service->getById($param);
        if (!$result)
        {
            header("Location: admin.php?page={$page}");
            exit;
        }
        $defaultForm = array_merge($defaultForm, $result);
    }
}
catch (\Throwable $e)
{
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Gagal',
        'html'  => 'Terjadi kesalahan saat memuat data.',
    ];
    header("Location: {$back}");
    exit;
}

// POST: toggle aktif
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'toggle_aktif' &&
    $canDelete
)
{
    $aktif = ($_POST['aktif'] ?? '0') === '1';
    $res   = $service->toggleAktif($param, $aktif);

    $_SESSION['swal'] = [
        'icon'  => $res['success'] ? 'success' : 'error',
        'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
        'html'  => $res['message'],
    ];

    header('Location: admin-edit.php?' . buildUrl($_GET));
    exit;
}

// POST: simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save')
{
    if ($formMode === 'create' && !$canCreate)
    {
        header("Location: {$back}");
        exit;
    }
    if ($formMode === 'edit' && !$canEdit)
    {
        header("Location: {$back}");
        exit;
    }

    $post   = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
    $errors = UserValidation::save($post, $formMode === 'create');

    if (empty($errors))
    {
        $data = [
            'nama_lengkap' => $post['nama_lengkap'],
            'username'     => $post['username'],
            'password'     => $post['password'],
            'role_id'      => $post['role_id']  ?: null,
            'level_id'     => $post['level_id'] ?: null,
        ];

        $res = $formMode === 'create'
            ? $service->create($data)
            : $service->update($param, $data);

        $_SESSION['swal'] = [
            'icon'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
            'html'  => $res['message'],
        ];

        if ($res['success'])
        {
            header("Location: {$back}");
            exit;
        }
    }

    $defaultForm = array_merge($defaultForm, $post);
}

$post     = $defaultForm;
$disabled = ($formMode === 'edit' && !$canEdit) ? 'disabled' : '';
$isAktif  = empty($post['deleted_at']);

$title    = $pageTitle;
$icon     = 'fa-user-gear';
$subtitle = $canEdit || $formMode === 'create'
    ? 'Isi data user dengan lengkap dan benar'
    : 'Anda hanya memiliki akses lihat data';
?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-full lg:h-screen overflow-auto no-scrollbar">
            <div class="lg:min-h-screen lg:bg-slate-100 md:p-2 lg:py-4">
                <div class="grid grid-cols-6 gap-2 lg:gap-3 h-full lg:h-[calc(100vh-32px)]">

                    <?php include 'layout/nav-master.php' ?>
                    <div class="col-span-6 lg:col-span-5 bg-white h-screen overflow-auto no-scrollbar">
                        <div class="lg:pt-5 pb-[120px] p-3 sm:p-4 lg:px-3 xs:pb-20 md:pb-5">
                            <div class="bg-white rounded-2xl shadow-xl overflow-hidden w-full">

                                <?php include 'components/form_header.php' ?>

                                <?php if ($formMode === 'edit'): ?>
                                    <?php include 'components/form_status_user.php' ?>
                                <?php endif; ?>

                                <?php include 'components/form_user.php' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'layout/footer.php' ?>
