<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\SiswaService;
use App\Services\AgamaService;
use App\Services\KelasService;
use App\Services\PekerjaanService;
use App\Validation\SiswaValidation;

session_start();

// Validasi jenjang
$jenjang = strtolower(trim($_GET['jenjang'] ?? ''));
$filter_nama       = $_GET['nama'] ?? '';
$filter_status     = $_GET['status'] ?? '';
$filter_kelas      = $_GET['id_kelas'] ?? '';
$page              = max(1, (int)($_GET['page'] ?? 1));


if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

$slug  = "siswa-$jenjang";
$tabel = "siswa_$jenjang";

[$menus, $permMap] = PermissionMiddleware::handle($pdo, $slug);

$canEdit   = PermissionService::can($permMap, $slug, 'edit');
$canCreate = PermissionService::can($permMap, $slug, 'create');
$canDelete = PermissionService::can($permMap, $slug, 'delete');

$service         = new SiswaService($pdo);
$agamaService    = new AgamaService($pdo);
$kelasService    = new KelasService($pdo);
$pekerjaanService = new PekerjaanService($pdo);

$page    = max(1, (int)($_GET['page'] ?? 1));
$param   = $_GET['id_siswa'] ?? null;
$back = "siswa-$jenjang.php?" . buildUrl($_GET);
$formMode = ($param === 'new') ? 'create' : 'edit';
$pageTitle = $formMode === 'create'
    ? 'Tambah Siswa ' . strtoupper($jenjang)
    : 'Edit Siswa '   . strtoupper($jenjang);


$errors = [];
$listAgama = [];
$listKelas = [];
$listPekerjaan = [];

// Guard create
if ($formMode === 'create' && !$canCreate)
{
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

$defaultForm = [
    'nama' => '',
    'nis' => '',
    'nisn' => '',
    'nik' => '',
    'tempat_lahir' => '',
    'tgl_lahir' => '',
    'id_agama' => '',
    'id_kelas' => '',
    'ruang' => '',
    'tahun_masuk' => date('Y'),
    'status' => 'aktif',
    'status_keterangan' => '',
    'ayah_nama' => '',
    'ayah_tahun_lahir' => '',
    'ayah_pendidikan' => '',
    'ayah_pekerjaan' => '',
    'ayah_penghasilan' => '',
    'ayah_nik' => '',
    'ibu_nama' => '',
    'ibu_tahun_lahir' => '',
    'ibu_pendidikan' => '',
    'ibu_pekerjaan' => '',
    'ibu_penghasilan' => '',
    'ibu_nik' => '',
    'wali_nama' => '',
    'wali_tahun_lahir' => '',
    'wali_pendidikan' => '',
    'wali_pekerjaan' => '',
    'wali_penghasilan' => '',
    'wali_nik' => '',
    'alamat' => '',
    'rt_rw' => '',
    'dusun' => '',
    'kelurahan' => '',
    'kecamatan' => '',
    'kode_pos' => '',
];

try
{
    // Load master dropdown
    $listAgama     = $agamaService->getAll();
    $listKelas     = $kelasService->getByJenjang($jenjang);
    $listPekerjaan = $pekerjaanService->getAll();

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

// POST: toggle status
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'toggle_status' &&
    $canDelete
)
{
    $res = $service->updateStatus(
        $tabel,
        (int)$param,
        trim($_POST['status'] ?? 'aktif'),
        trim($_POST['status_keterangan'] ?? '') ?: null
    );
    $_SESSION['swal'] = [
        'icon'  => $res['success'] ? 'success' : 'error',
        'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
        'html'  => $res['message'],
    ];
    header("Location: siswa-edit.php?" . buildUrl($_GET));
    exit;
}

// POST: simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save')
{

    if ($formMode === 'create' && !$canCreate)
    {
        header("Location: {$back}");
        exit;
    }
    if ($formMode === 'edit'   && !$canEdit)
    {
        header("Location: {$back}");
        exit;
    }

    $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    // Validasi pakai Rakit
    $errors = SiswaValidation::save($post);


    if (empty($errors))
    {
        $data = [
            'nama'             => $post['nama'],
            'nis'              => $post['nis'],
            'nisn'             => $post['nisn']             ?: null,
            'nik'              => $post['nik']              ?: null,
            'tempat_lahir'     => $post['tempat_lahir'],
            'tgl_lahir'        => $post['tgl_lahir'],
            'id_agama'         => (int)$post['id_agama'],
            'id_kelas'         => (int)$post['id_kelas'],
            'ruang'            => $post['ruang'],
            'tahun_masuk'      => !empty($post['tahun_masuk'])      ? (int)$post['tahun_masuk']      : null,
            'ayah_nama'        => $post['ayah_nama']        ?: null,
            'ayah_tahun_lahir' => !empty($post['ayah_tahun_lahir']) ? (int)$post['ayah_tahun_lahir'] : null,
            'ayah_pendidikan'  => $post['ayah_pendidikan']  ?: null,
            'ayah_pekerjaan'   => !empty($post['ayah_pekerjaan'])   ? (int)$post['ayah_pekerjaan']   : null,
            'ayah_penghasilan' => $post['ayah_penghasilan'] ?: null,
            'ayah_nik'         => $post['ayah_nik']         ?: null,
            'ibu_nama'         => $post['ibu_nama']         ?: null,
            'ibu_tahun_lahir'  => !empty($post['ibu_tahun_lahir'])  ? (int)$post['ibu_tahun_lahir']  : null,
            'ibu_pendidikan'   => $post['ibu_pendidikan']   ?: null,
            'ibu_pekerjaan'    => !empty($post['ibu_pekerjaan'])    ? (int)$post['ibu_pekerjaan']    : null,
            'ibu_penghasilan'  => $post['ibu_penghasilan']  ?: null,
            'ibu_nik'          => $post['ibu_nik']          ?: null,
            'wali_nama'        => $post['wali_nama']        ?: null,
            'wali_tahun_lahir' => !empty($post['wali_tahun_lahir']) ? (int)$post['wali_tahun_lahir'] : null,
            'wali_pendidikan'  => $post['wali_pendidikan']  ?: null,
            'wali_pekerjaan'   => !empty($post['wali_pekerjaan'])   ? (int)$post['wali_pekerjaan']   : null,
            'wali_penghasilan' => $post['wali_penghasilan'] ?: null,
            'wali_nik'         => $post['wali_nik']         ?: null,
            'alamat'           => $post['alamat'],
            'rt_rw'            => $post['rt_rw'],
            'dusun'            => $post['dusun'],
            'kelurahan'        => $post['kelurahan'],
            'kecamatan'        => $post['kecamatan'],
            'kode_pos'         => $post['kode_pos']         ?: null,
        ];

        $res = $formMode === 'create'
            ? $service->create($tabel, $data)
            : $service->update($tabel, (int)$param, $data);

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

$post = $defaultForm;



// Disabled logic
$disabled = ($formMode === 'edit' && !$canEdit) ? 'disabled' : '';

$icon     = 'fa-user-graduate';
$title    = $pageTitle;
$subtitle = $canEdit || $formMode === 'create'
    ? 'Isi data siswa dengan lengkap dan benar'
    : 'Anda hanya memiliki akses lihat data';
?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] p-3 sm:p-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                    <?php include 'components/form_header.php' ?>

                    <?php if ($formMode === 'edit'): ?>
                        <?php include 'components/form_status_siswa.php' ?>
                    <?php endif; ?>

                    <?php include 'components/form_siswa.php' ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
