<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\SiswaImportService;

session_start();

// Validasi jenjang
$jenjang = strtolower(trim($_GET['jenjang'] ?? ''));
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    header('Location: /');
    exit;
}

$jenjangUp  = strtoupper($jenjang);
$tabel      = 'siswa_' . $jenjang;
$slugMenu   = 'siswa-' . $jenjang;
$backUrl    = 'siswa-' . $jenjang . '.php';

// Guard
[$menus, $permMap] = PermissionMiddleware::handle($pdo, $slugMenu);
if (!PermissionService::can($permMap, $slugMenu, 'import'))
{
    header('Location: ' . $backUrl);
    exit;
}

// Proses upload
$flashHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload']))
{
    // 1. Validasi file
    $uploadError = validateExcelUpload($_FILES['file_excel'] ?? []);

    if ($uploadError !== null)
    {
        $flashHtml = flashMsg('error', $uploadError);
    }
    else
    {
        $tmpPath = $_FILES['file_excel']['tmp_name'];

        // 2. Proses import via service
        $service = new SiswaImportService($pdo);

        try
        {
            $result  = $service->importFromFile($tabel, $tmpPath);
            $sukses  = $result['sukses'];
            $gagal   = $result['gagal'];
            $errors  = $result['errors'];

            if ($gagal === 0 && $sukses > 0)
            {
                // Semua berhasil
                $flashHtml = flashMsg(
                    'success',
                    "Berhasil mengimpor <strong>{$sukses}</strong> data siswa {$jenjangUp}."
                );
            }
            elseif ($sukses > 0 && $gagal > 0)
            {
                // Sebagian berhasil
                $errorList = implode('<br>', array_slice($errors, 0, 10));
                $more      = count($errors) > 10 ? '<br>… dan ' . (count($errors) - 10) . ' error lainnya.' : '';

                $flashHtml = flashMsg(
                    'warning',
                    "<strong>{$sukses}</strong> baris berhasil, <strong>{$gagal}</strong> baris gagal.<br><br>"
                        . "<span class='font-semibold'>Detail error:</span><br>{$errorList}{$more}"
                );
            }
            else
            {
                // Semua gagal
                $errorList = implode('<br>', array_slice($errors, 0, 10));
                $more      = count($errors) > 10 ? '<br>… dan ' . (count($errors) - 10) . ' error lainnya.' : '';

                $flashHtml = flashMsg(
                    'error',
                    "Semua <strong>{$gagal}</strong> baris gagal diimpor.<br><br>"
                        . "<span class='font-semibold'>Detail error:</span><br>{$errorList}{$more}"
                );
            }
        }
        catch (\Throwable $e)
        {
            error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
            $flashHtml = flashMsg('error', 'Terjadi kesalahan sistem: ' . htmlspecialchars($e->getMessage()));
        }
    }
}

// Petunjuk pengisian─
$instructions = [
    'Gunakan template resmi yang diunduh dari tombol <strong>Download Template</strong>.',
    'Jangan mengubah urutan atau nama kolom pada template.',
    'Kolom bertanda <strong>*</strong> (Nama, ID Agama, ID Kelas, Ruang) wajib diisi.',
    'Gunakan nilai dari kolom <strong>ID</strong> di sheet <em>Daftar ID</em> untuk kolom ID Agama, ID Kelas, dan ID Pekerjaan.',
    'Format tanggal lahir: <strong>YYYY-MM-DD</strong> atau <strong>DD/MM/YYYY</strong>.',
    'Kolom Tahun Masuk cukup diisi <strong>4 digit tahun</strong>, contoh: <strong>2022</strong>.',
    '<strong>NIS dan NISN harus unik</strong> — tidak boleh ada yang sama di database.',
    'Baris yang gagal tidak membatalkan baris lain yang sudah berhasil.',
    'Ukuran file maksimal <strong>10 MB</strong>.',
];

$title = 'Upload Siswa ' . $jenjangUp;
?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr] w-full">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] px-3 sm:px-4 lg:px-6 xs:pb-20 md:pb-10 lg:pb-0">

                <?php include 'components/header_page.php' ?>

                <div class="w-full">

                    <!-- Flash message -->
                    <?= $flashHtml ?>

                    <!-- Card utama -->
                    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        <!-- Step 1: Download template -->
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-start gap-4">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    1
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 mb-1">Download Template</p>
                                    <p class="text-sm text-gray-500 mb-3">
                                        Unduh template Excel, isi data siswa, lalu upload kembali di langkah 2.
                                    </p>
                                    <a href="download-template-siswa.php?jenjang=<?= $jenjang ?>"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                                        <i class="fa-solid fa-file-excel"></i>
                                        Download Template <?= $jenjangUp ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Upload -->
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm shrink-0">
                                    2
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 mb-1">Upload File yang Sudah Diisi</p>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Hanya file <strong>.xlsx</strong> (Excel 2007+). Maksimal 10 MB.
                                    </p>

                                    <form method="POST" enctype="multipart/form-data" class="space-y-6">

                                        <!-- Drop zone - TINGGI BESAR -->
                                        <label for="file_excel"
                                            class="flex flex-col items-center justify-center gap-4 w-full
                                                    border-2 border-dashed border-gray-300 rounded-xl
                                                    bg-gray-50 hover:bg-indigo-50 hover:border-indigo-400
                                                    cursor-pointer transition text-center"
                                            style="padding: 30px 20px; min-height: 100px;">

                                            <i class="fa-solid fa-cloud-arrow-up text-6xl text-gray-400"></i>

                                            <span class="text-lg text-gray-500">
                                                Klik untuk memilih file <strong class="text-indigo-600">.xlsx</strong>
                                            </span>

                                            <span class="text-base text-gray-400">atau seret file ke area ini</span>

                                            <span id="file-name-display" class="text-sm text-indigo-600 font-medium hidden mt-3"></span>

                                        </label>

                                        <!-- Input file tetap hidden, tapi sekarang label yang memicu -->
                                        <input id="file_excel" type="file" name="file_excel"
                                            accept=".xlsx" required class="hidden"
                                            onchange="document.getElementById('file-name-display').textContent = this.files[0]?.name ?? '';
                                            document.getElementById('file-name-display').classList.toggle('hidden', !this.files[0]);">

                                        <div class="space-y-3 xs:space-y-0 xs:flex gap-3">
                                            <button type="submit" name="upload"
                                                class="w-full xs:w-auto flex-1 sm:flex-none px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700
                                                        text-white text-sm font-semibold rounded-lg transition
                                                        flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-upload"></i>
                                                Upload & Simpan
                                            </button>

                                            <a href="<?= $backUrl ?>"
                                                class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700
                                                        text-sm font-medium rounded-lg transition
                                                        flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-arrow-left"></i>
                                                Kembali
                                            </a>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Petunjuk -->
                    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <p class="text-sm font-semibold text-amber-800 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Petunjuk Pengisian
                        </p>
                        <ul class="space-y-1.5 list-none">
                            <?php foreach ($instructions as $i => $item): ?>
                                <li class="flex items-start gap-2 text-sm text-amber-700">
                                    <span class="shrink-0 w-4 h-4 rounded-full bg-amber-200 text-amber-800
                                                 text-[10px] font-bold flex items-center justify-center mt-0.5">
                                        <?= $i + 1 ?>
                                    </span>
                                    <?= $item ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
