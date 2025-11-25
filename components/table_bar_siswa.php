<div class="flex flex-col xs:flex-row mt-6 md:gap-4 md:mb-6 items-center justify-between">
    <div class="order-2 xs:order-1 flex  gap-3 mt-4 xs:mt-0 w-full xs:w-auto">
        <!-- Tambah -->
        <?php
        $btnLink = "siswa-add.php?jenjang=sd&page=$page";
        $btnLabel = "Siswa";
        include("components/button_add.php");
        ?>

        <!-- Export -->
        <?php $linkExport = "export-siswa.php?jenjang=sd";
        include("components/button_export.php") ?>
    </div>

    <div class="order-1 xs:order-2 w-full">
        <?php
        $action = "siswa-sd.php";
        $placholder = "Cari nama, NIS, atau NISN";
        include("components/searchbar.php");
        ?>
    </div>
</div>
