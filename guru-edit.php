<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\GuruService;
use App\Validation\GuruValidation;

session_start();

$jenjang = strtolower(trim($_GET['jenjang'] ?? ''));

if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

$slug  = "guru-{$jenjang}";
$tabel = "guru_{$jenjang}";

[$menus, $permMap] = PermissionMiddleware::handle($pdo, $slug);

$canEdit   = PermissionService::can($permMap, $slug, 'edit');
$canCreate = PermissionService::can($permMap, $slug, 'create');
$canDelete = PermissionService::can($permMap, $slug, 'delete');

$service = new GuruService($pdo);

$page     = max(1, (int) ($_GET['page'] ?? 1));
$param    = $_GET['id_guru'] ?? null;
$formMode = ($param === 'new') ? 'create' : 'edit';
$back     = "guru-{$jenjang}.php?" . buildUrl($_GET);
$pageTitle = $formMode === 'create'
    ? 'Tambah Guru ' . strtoupper($jenjang)
    : 'Edit Guru '   . strtoupper($jenjang);

$errors = [];

// Guard create
if ($formMode === 'create' && !$canCreate)
{
    header("Location: guru-{$jenjang}.php");
    exit;
}

$defaultForm = [
    'nama'              => '',
    'nik'               => '',
    'nuptk'             => '',
    'jenis_kelamin'     => '',
    'tempat_lahir'      => '',
    'tgl_lahir'         => '',
    'nama_ibu'          => '',
    'status_pegawai'    => '',
    'jenis_gtk'         => '',
    'jabatan'           => '',
    'alamat'            => '',
    'tahun_masuk'       => date('Y'),
    'tahun_keluar'      => '',
    'status'            => 'aktif',
    'status_keterangan' => '',
];

try
{
    // Load data edit
    if ($formMode === 'edit')
    {
        $result = $service->getById($tabel, (int)$param);
        if (!$result)
        {
            header("Location: {$back}");
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

//  POST: toggle status
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'toggle_status' &&
    $canDelete
)
{
    $res = $service->updateStatus(
        $tabel,
        (int) $param,
        trim($_POST['status'] ?? 'aktif'),
        trim($_POST['status_keterangan'] ?? '') ?: null
    );

    $_SESSION['swal'] = [
        'icon'  => $res['success'] ? 'success' : 'error',
        'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
        'html'  => $res['message'],
    ];

    header("Location: guru-edit.php?" . buildUrl($_GET));
    exit;
}

//  POST: simpan data
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

    $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    $errors = GuruValidation::save($post);

    if (empty($errors))
    {
        $data = [
            'nama'           => $post['nama'],
            'nik'            => $post['nik']           ?: null,
            'nuptk'          => $post['nuptk']         ?: null,
            'jenis_kelamin'  => $post['jenis_kelamin'],
            'tempat_lahir'   => $post['tempat_lahir']  ?: null,
            'tgl_lahir'      => $post['tgl_lahir']     ?: null,
            'nama_ibu'       => $post['nama_ibu']      ?: null,
            'status_pegawai' => $post['status_pegawai'] ?: null,
            'jenis_gtk'      => $post['jenis_gtk']     ?: null,
            'jabatan'        => $post['jabatan']        ?: null,
            'alamat'         => $post['alamat']         ?: null,
            'tahun_masuk'    => !empty($post['tahun_masuk']) ? (int) $post['tahun_masuk'] : null,
        ];

        $res = $formMode === 'create'
            ? $service->create($tabel, $data)
            : $service->update($tabel, (int) $param, $data);

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

    // Kembalikan nilai POST ke form jika gagal
    $defaultForm = array_merge($defaultForm, $post);
}

$post = $defaultForm;


$disabled = ($formMode === 'edit' && !$canEdit) ? 'disabled' : '';

// Page vars untuk header component
$icon     = 'fa-chalkboard-teacher';
$title    = $pageTitle;
$subtitle = $canEdit || $formMode === 'create'
    ? 'Isi data guru dengan lengkap dan benar'
    : 'Anda hanya memiliki akses lihat data';

?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] p-3 sm:p-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                    <!-- Header (komponen sama dengan siswa) -->
                    <?php include 'components/form_header.php' ?>

                    <?php if ($formMode === 'edit'): ?>
                        <?php include 'components/form_status_guru.php' ?>
                    <?php endif; ?>

                    <?php include 'components/form_guru.php' ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
