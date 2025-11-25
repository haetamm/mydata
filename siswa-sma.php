<?php
session_start();
include("connection.php");
include("services/siswa.php");

// === 1. CEK LOGIN & HAK AKSES ===
authorizeStudentData('SMA');

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['nama'] ?? "");
$id_kelas = $_GET['id_kelas'] ?? "";


// Pesan tetap ada
$pesan = $_GET["pesan"] ?? "";

// HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']))
{
    allowSuperAdminOnly();
    deleteSiswa("siswa_sma", $_POST['delete_id'], $link);
    header("Location: siswa-sma.php?page=$page&pesan=Siswa berhasil dihapus");
    exit;
}

// GET DATA
$result = getSiswaData("siswa_sma", $page, $limit, $search, $id_kelas, $link);
$dataSiswa   = $result["data"];
$totalPages  = $result["totalPages"];
$pesan_cari  = $result["pesan_cari"];
$offset = ($page - 1) * $limit;

?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <!-- Sidebar -->
        <?php include("layout/sidebar.php") ?>

        <!-- Content -->
        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-10 lg:pb-0">

                <!-- Header -->
                <?php
                $title = "Daftar Siswa SMA";
                include("components/header_page.php")
                ?>

                <div class="bg-white">
                    <!-- Table Bar -->
                    <div class="flex flex-col lg:flex-row mt-6 md:gap-4 md:mb-6 items-center justify-between">
                        <div class="order-2 lg:order-1 flex  gap-3 mt-4 md:mt-0 w-full lg:w-auto">

                            <div class="md:flex md:justify-between w-full md:space-x-4">
                                <!-- Tambah -->
                                <?php
                                $btnLink = "siswa-add.php?jenjang=sma&page=$page";
                                $btnLabel = "Siswa";
                                include("components/button_add.php");
                                ?>

                                <div class="flex space-x-4">
                                    <?php $icon = 'fa-file-excel';
                                    $label = 'Download';
                                    $linkExport = "export-siswa.php?jenjang=sma";
                                    include("components/button_export.php") ?>

                                    <?php if (isAdmin()): ?>
                                        <?php $icon = 'fa-cloud-arrow-up';
                                        $label = 'Upload';
                                        $linkExport = "siswa-upload.php?jenjang=sma";
                                        include("components/button_export.php") ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="order-1 lg:order-2 w-full">
                            <?php
                            $action = "siswa-sma.php";
                            $placholder = "Cari nama, NIS, atau NISN";
                            $jenjang = "SMA";
                            include("components/searchbar.php");
                            ?>
                        </div>
                    </div>

                    <!-- Flash Message -->
                    <?php include("components/flash_message.php") ?>

                    <!-- Tabel Siswa-->
                    <?php $action = 'siswa-sma.php';
                    $jenjang = 'sma';
                    include("components/table_siswa.php") ?>

                    <?php $action = 'siswa-sma.php';
                    $jenjang = 'sma';
                    include("components/card_siswa.php") ?>

                    <!-- Pagination -->
                    <?php
                    $current_page = $page;
                    $total_pages = $totalPages;
                    $url = "siswa-sma.php";
                    include("components/pagination.php");
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
