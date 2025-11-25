<?php
session_start();
include("connection.php");
include("services/master.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$pesan = $_GET["pesan"] ?? "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_GET['action'])
    && $_GET['action'] === 'add_level_kelas'
)
{
    // Validasi
    if (
        !empty(trim($_POST['jenjang'])) &&
        !empty(trim($_POST['level_min'])) &&
        !empty(trim($_POST['level_max']))
    )
    {
        $jenjang = trim($_POST['jenjang']);
        $level_min = trim($_POST['level_min']);
        $level_max = trim($_POST['level_max']);

        $result = addLevelKelas($jenjang, $level_min, $level_max, $link);

        if ($result['success'])
        {
            header("Location: level-kelas.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'level-kelas.php';</script>";
        }
        exit;
    }
    else
    {
        echo "<script>alert('Form tidak boleh kosong'); window.location.href = 'level-kelas.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']))
{
    try
    {
        $result = deleteLevelKelas($_POST['delete_id'], $link);

        if ($result['success'])
        {
            header("Location: level-kelas.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'level-kelas.php';</script>";
        }
        exit;
    }
    catch (Exception $e)
    {
        echo "<script>alert('Error sistem: " . addslashes($e->getMessage()) . "'); window.location.href = 'level-kelas.php';</script>";
        exit;
    }
}

// GET DATA
$result = getLevelKelas($link);
$dataAgama   = $result["data"];
$dataMaster = array_map(function ($item)
{
    return [
        "id_master"   => $item["id_level"],
        "nama_master" => $item["jenjang"],
        "level_min" => $item["level_min"],
        "level_max" => $item["level_max"]
    ];
}, $dataAgama);

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
                        <!-- Konten halaman akan ditaruh di sini -->
                        <div class=" flex-1 flex flex-col">

                            <!-- Pesan -->
                            <?php include("components/flash_message.php") ?>

                            <!-- Form Tambah -->
                            <div class="bg-white px-4 py-3 rounded-lg border border-gray-200 mb-3">
                                <h2 class="text-lg font-semibold mb-3">Tambah Kelas Baru</h2>
                                <form method="POST" action="level-kelas.php?action=add_level_kelas" class="space-y-3 xs:space-y-0 xs:flex xs:flex-col sm:flex-row gap-3">
                                    <!-- Input Nama Kelas -->
                                    <div class="flex-1">
                                        <input type="text" name="jenjang" placeholder="Masukkan jenjang" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                    </div>

                                    <!-- Select Level -->
                                    <div class="flex-1">
                                        <input type="text" name="level_min" placeholder="Masukkan min level" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                    </div>

                                    <div class="flex-1">
                                        <input type="text" name="level_max" placeholder="Masukkan max level" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                    </div>

                                    <!-- Button -->
                                    <div class="xs:w-full sm:w-auto">
                                        <button type="submit"
                                            class="w-full sm:w-auto px-4 py-2 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 text-sm sm:text-base font-medium">
                                            Tambah
                                        </button>
                                    </div>
                                </form>
                            </div>


                            <!-- Table Data -->
                            <div class="flex-1 min-h-0">
                                <?php $action = 'level-kelas.php';
                                include("components/table_master.php") ?>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>
