<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\KelasService;
use App\Services\LevelKelasService;
use App\Services\PermissionService;
use App\Validation\KelasValidation;

session_start();

// Guard
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-kelas');

$kelasService       = new KelasService($pdo);
$levelKelasService  = new LevelKelasService($pdo);
$servicePermission = new PermissionService($pdo);

$canCreate = $servicePermission->can($permMap, 'master-kelas', 'create');
$canDelete = $servicePermission->can($permMap, 'master-kelas', 'delete');

$pesan              = $_GET['pesan'] ?? '';
$errors             = [];

// POST: Tambah pekerjaan
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['action'])
    && $_GET['action'] === 'add_kelas'
    && $canCreate
)
{
    $data = [
        'nama_kelas'    => trim($_POST['nama_kelas']   ?? ''),
        'level_id'      => trim($_POST['level_id'] ?? '')
    ];
    $errors = KelasValidation::save($data);

    if (empty($errors))
    {
        try
        {
            $res = $kelasService->addKelas($data['nama_kelas'], (int) $data['level_id']);
            $_SESSION['swal'] = [
                'icon'  => $res['success'] ? 'success' : 'error',
                'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
                'html'  => $res['message'],
            ];

            header('Location: kelas.php');
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
            header('Location: kelas.php?pesan=' . urlencode('Terjadi kesalahan sistem.'));
        }
        exit;
    }
}

// POST: Hapus kelas
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_id'])
    && $canDelete
)
{
    $deleteId = (int) $_POST['delete_id'];
    try
    {
        $res = $kelasService->deleteKelas($deleteId);
        $_SESSION['swal'] = [
            'icon'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
            'html'  => $res['message'],
        ];

        header('Location: kelas.php');
    }
    catch (\Throwable $e)
    {
        error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
        header('Location: kelas.php?pesan=' . urlencode('Terjadi kesalahan sistem.'));
    }
    exit;
}

$dataKelas = [];
$levels = [];
$fetchError     = null;

try
{
    $dataKelas = $kelasService->getAll();
    $levels = $levelKelasService->getAll();
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

$dataMaster = array_map(fn($item) => [
    "id_master"   => $item["id_kelas"],
    "nama_master" => $item["nama_kelas"],
    "jenjang" => $item["jenjang"],
], $dataKelas);

$err = fn(string $field) => !empty($errors[$field])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$field]) . '</p>'
    : '';

$disabled = (!$canCreate) ? 'disabled' : '';

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

                            <!-- Pesan -->
                            <?php include("components/flash_message.php") ?>

                            <!-- Form Tambah -->
                            <div class="bg-white px-4 py-3 rounded-lg border border-gray-200 mb-3">
                                <h2 class="text-lg font-semibold mb-3">Tambah Kelas Baru</h2>
                                <form method="POST" action="kelas.php?action=add_kelas" class="space-y-3 xs:space-y-0 xs:flex xs:flex-col sm:flex-row gap-3">
                                    <!-- Input Nama Kelas -->
                                    <div class="flex-1">
                                        <input type="text" name="nama_kelas" placeholder="Masukkan nama kelas" required <?= $disabled ?>
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                    </div>

                                    <!-- Select Level -->
                                    <div class="flex-1">
                                        <select name="level_id" required <?= $disabled ?>
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                            <option value="">-- Pilih Jenjang --</option>
                                            <?php foreach ($levels as $lvl): ?>
                                                <option value="<?= $lvl['id_level'] ?>">
                                                    <?= $lvl['jenjang'] ?> (Tingkat <?= $lvl['level_min'] ?> - <?= $lvl['level_max'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Button -->
                                    <div class="xs:w-full sm:w-auto">
                                        <button type="submit" <?= btnDisabled($canCreate) ?>
                                            class="px-4 py-2 text-white rounded-md
                                                <?= btnClass($canCreate, 'bg-blue-600 hover:bg-blue-700') ?>" ">
                                            Tambah
                                        </button>
                                    </div>
                                </form>
                            </div>


                            <!-- Table Data -->
                            <div class=" flex-1 min-h-0">
                                            <?php $action = 'kelas.php';
                                            include("components/table_master.php") ?>
                                    </div>
                            </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'layout/footer.php' ?>
