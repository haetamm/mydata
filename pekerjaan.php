<?php
session_start();
include("connection.php");
include("services/master.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$pesan = $_GET["pesan"] ?? "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add_pekerjaan')
{
    if (isset($_POST['nama_pekerjaan']) && !empty(trim($_POST['nama_pekerjaan'])))
    {
        $nama_pekerjaan = trim($_POST['nama_pekerjaan']);
        $result = addPekerjaan($nama_pekerjaan, $link);

        if ($result['success'])
        {
            header("Location: pekerjaan.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'pekerjaan.php';</script>";
        }
        exit;
    }
    else
    {
        echo "<script>alert('Nama pekerjaan tidak boleh kosong'); window.location.href = 'pekerjaan.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']))
{
    try
    {
        $result = deletePekerjaan($_POST['delete_id'], $link);

        if ($result['success'])
        {
            header("Location: pekerjaan.php?pesan=" . urlencode($result['message']));
        }
        else
        {
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href = 'pekerjaan.php';</script>";
        }
        exit;
    }
    catch (Exception $e)
    {
        echo "<script>alert('Error sistem: " . addslashes($e->getMessage()) . "'); window.location.href = 'pekerjaan.php';</script>";
        exit;
    }
}

// GET DATA
$result = getPekerjaan($link);
$dataPekerjaan   = $result["data"];
$dataMaster = array_map(function ($item)
{
    return [
        "id_master"   => $item["id_pekerjaan"],
        "nama_master" => $item["nama_pekerjaan"],
    ];
}, $dataPekerjaan);

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
