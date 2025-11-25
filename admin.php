<?php
session_start();
include("connection.php");
include("services/user.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['nama'] ?? "");

// Pesan tetap ada
$pesan = $_GET["pesan"] ?? "";

// HAPUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']))
{
    allowSuperAdminOnly();
    deleteUser($_POST['delete_id'], $link);
    header("Location: admin.php?page=$page&pesan=User berhasil dihapus");
    exit;
}

// GET DATA
$result = getUsersData($page, $limit, $search, $link);
$dataUser   = $result["data"];
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
            <div class="lg:pt-5 pb-[120px] px-3 xs:px-2 lg:px-3 xs:pb-20 md:pb-10 lg:pb-0">

                <!-- Header -->
                <?php
                $title = "Daftar User";
                include("components/header_page.php")
                ?>

                <div class="bg-white">
                    <!-- Tombol + Cari -->
                    <div class="flex flex-col xs:flex-row mt-6 md:gap-4 md:mb-6 items-center justify-between">
                        <div class="flex gap-3 w-full xs:w-auto">
                            <!-- Tambah -->
                            <?php
                            $btnLink = "admin-add.php?page=$page";
                            $btnLabel = "Tambah User";
                            include("components/button_add.php");
                            ?>
                        </div>

                        <div class="w-full xs:w-auto">
                            <?php
                            $action = "admin.php";
                            $placholder = "Cari nama";
                            include("components/searchbar.php");
                            ?>
                        </div>
                    </div>

                    <!-- Flash Message -->
                    <?php include("components/flash_message.php") ?>

                    <!-- Tabel -->
                    <?php $action = 'admin.php';
                    include("components/table_user.php") ?>

                    <?php $action = 'admin.php';
                    include("components/card_user.php") ?>

                    <!-- Pagination -->
                    <?php
                    $current_page = $page;
                    $total_pages = $totalPages;
                    $url = "guru-sd.php";
                    include("components/pagination.php");
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>
