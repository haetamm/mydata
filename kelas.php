<?php
session_start();
include("connection.php");
include("services/master.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$pesan = $_GET["pesan"] ?? "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_kelas')
{
    if (isset($_POST['nama_kelas']) && isset($_POST['level_id']))
    {
        $nama_kelas = trim($_POST['nama_kelas']);
        $level_id   = intval($_POST['level_id']);

        $result = addKelas($nama_kelas, $level_id, $link);

        if ($result['success'])
        {
            header("Location: kelas.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'kelas.php';</script>";
        }
        exit;
    }
    else
    {
        echo "<script>alert('Nama kelas atau jenjang tidak boleh kosong'); window.location.href = 'kelas.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']))
{
    try
    {
        $result = deleteKelas($_POST['delete_id'], $link);

        if ($result['success'])
        {
            header("Location: kelas.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'kelas.php';</script>";
        }
        exit;
    }
    catch (Exception $e)
    {
        echo "<script>alert('Error sistem: " . addslashes($e->getMessage()) . "'); window.location.href = 'kelas.php';</script>";
        exit;
    }
}

$levels = $link->query("SELECT id_level, jenjang, level_min, level_max
                        FROM master_level_kelas
                        WHERE deleted_at IS NULL
                        ORDER BY id_level")->fetchAll(PDO::FETCH_ASSOC);

// GET DATA
$result = getKelas($link);
$dataKelas   = $result["data"];
$dataMaster = array_map(function ($item)
{
    return [
        "id_master"   => $item["id_kelas"],
        "nama_master" => $item["nama_kelas"],
        "jenjang" => $item["jenjang"],
    ];
}, $dataKelas);

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
                                        <input type="text" name="nama_kelas" placeholder="Masukkan nama kelas" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                                    </div>

                                    <!-- Select Level -->
                                    <div class="flex-1">
                                        <select name="level_id" required
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
                                        <button type="submit"
                                            class="w-full sm:w-auto px-4 py-2 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 text-sm sm:text-base font-medium">
                                            Tambah
                                        </button>
                                    </div>
                                </form>
                            </div>


                            <!-- Table Data -->
                            <div class="flex-1 min-h-0">
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
