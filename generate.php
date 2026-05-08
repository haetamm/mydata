<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "dataku";

$link = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$link)
{
    die("Koneksi gagal: " . mysqli_connect_errno() . " - " . mysqli_connect_error());
}

mysqli_query($link, "DROP DATABASE IF EXISTS `$dbname`");
mysqli_query($link, "CREATE DATABASE `$dbname`");
mysqli_select_db($link, $dbname);
mysqli_set_charset($link, 'utf8mb4');

mysqli_query($link, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($link, "SET SESSION default_storage_engine = 'InnoDB'");

echo "<h2>GENERATE MULAI</h2>";

// DROP redundant karena database sudah di-recreate di atas,
// tapi tetap dipertahankan sebagai fallback jika DROP DATABASE di-comment.
$tables_to_drop = [
    // Siswa & Guru (child dari master_* dan users)
    'siswa_sma',
    'siswa_smp',
    'siswa_sd',
    'guru_sma',
    'guru_smp',
    'guru_sd',
    // Auth & RBAC (child dulu)
    'role_menu_permission',
    'users',
    'menus',
    'permissions',
    'roles',
    // Master data (child dulu)
    'master_kelas',
    'master_semester',
    'master_tahun_pelajaran',
    'master_pekerjaan',
    'master_agama',
    'master_level_kelas',
];

foreach ($tables_to_drop as $t)
{
    mysqli_query($link, "DROP TABLE IF EXISTS `$t`");
    echo "Hapus tabel: <b>$t</b><br>";
}

echo "<hr>";

// =====================================================================
// MIGRATION
// =====================================================================
echo "<h3>1. Migration (CREATE TABLE)</h3>";
include 'migration/index.php';

// =====================================================================
// SEEDER
// =====================================================================
echo "<h3>2. Seeder (INSERT DATA)</h3>";
include 'seeder/index.php';

// =====================================================================
// SELESAI
// =====================================================================
mysqli_query($link, "SET FOREIGN_KEY_CHECKS = 1");
mysqli_close($link);

echo "<hr>";
echo "<h3 style='color:green'>✔ SELESAI! SEMUA TABEL & DATA BERHASIL DIBUAT.</h3>";
echo "<p>Total kelas: <b>36</b> (SD: 1A–6B &nbsp;|&nbsp; SMP: 7A–9B &nbsp;|&nbsp; SMA: 10A–12B)</p>";
