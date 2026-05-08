<?php

declare(strict_types=1);
require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\UserService;
use App\Validation\ProfileValidation;

session_start();

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'setting-profil');
$canEdit = PermissionService::can($permMap, 'setting-profil', 'edit');
$service = new UserService($pdo);
$id_user = $_SESSION['id_user'];
$errors  = [];
$post    = [];

if (!$canEdit)
{
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

try
{
    $user = $service->getById($id_user);
    if (!$user)
    {
        session_destroy();
        header('Location: /');
        exit;
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
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    $errors = ProfileValidation::save($post, false);

    if (empty($errors))
    {
        $data = [
            'nama_lengkap' => $post['nama_lengkap'],
            'username'     => $post['username'],
            'password'     => $post['password'] ?? '',
            'role_id'      => $user['role_id'],
            'level_id'     => $user['level_id'],
        ];

        $res = $service->update($id_user, $data);

        $_SESSION['swal'] = [
            'icon'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
            'html'  => $res['message'],
        ];

        if ($res['success'])
        {
            // Update session nama jika berhasil
            $_SESSION['nama'] = $post['nama_lengkap'];
            header('Location: profile.php');
            exit;
        }
    }
}

$post = !empty($post) ? $post : [
    'nama_lengkap' => $user['nama_lengkap'],
    'username'     => $user['username'],
    'password'     => '',
];

$disabled = !$canEdit ? 'disabled' : '';
?>
<?php include('layout/head.php') ?>
<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <?php include('layout/sidebar.php') ?>
        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class="mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php
                        $title    = 'Profil Pengguna';
                        $back     = '';
                        $icon     = 'fa-user-pen';
                        $subtitle = 'Perbarui data Anda';
                        include('components/form_header.php');
                        ?>

                        <?php include('components/form_profile.php') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('layout/footer.php') ?>
