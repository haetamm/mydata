<?php
session_start();
include("connection.php");

$jenjangUp = strtoupper($_GET['jenjang'] ?? '');
authorizeStudentData($jenjangUp);

$jenjang = strtolower($_GET['jenjang'] ?? '');

if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    echo "<script>alert('Parameter jenjang tidak valid!'); window.history.back();</script>";
    exit;
}

$jenjangUpper = strtoupper($jenjang);
$tabel = "guru_" . $jenjang;

$query = "
    SELECT
        g.*
    FROM $tabel g
    ORDER BY g.nama ASC
";

// === JANGAN PAKAI query() KALAU BISA PAKAI prepare() (LEBIH AMAN & KONSISTEN) ===
$stmt = $link->prepare($query);
$stmt->execute();                       // WAJIB ADA INI!
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper function biar ga capek nulis ?? ''
function h($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Header kolom
$headers = [
    "No",
    "Nama",
    "NIK",
    "NUPTK",
    "Jenis Kelamin",
    "Tempat Lahir",
    "Tanggal Lahir",
    "Nama Ibu",
    "Status Pegawai",
    "Jenis GTK",
    "Jabatan",
    "Alamat"
];
$filename = "DATA_GURU_" . strtoupper($jenjang) . "_" . date("d-m-Y") . ".xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: no-cache");

// Judul laporan
$judul = "DATA GURU " . strtoupper($jenjang);
$tanggal_export = "Dicetak pada: " . date("d F Y, H:i");

// Mulai tabel HTML (Excel bisa baca)
echo "<table border='0' cellpadding='6' cellspacing='0' width='100%'>";

// === BARIS JUDUL UTAMA ===
echo "<tr>";
echo "<td colspan='" . count($headers) . "' align='center' style='background:#4d58ef; color:white; font-size:18pt; font-weight:bold; padding:20px;'>
        $judul
      </td>";
echo "</tr>";

// === TANGGAL CETAK ===
echo "<tr>";
echo "<td colspan='" . count($headers) . "' align='center' style='font-size:10pt; color:#555; padding:8px;'>
        $tanggal_export
      </td>";
echo "</tr>";

// === SPASI KOSONG ===
echo "<tr><td colspan='" . count($headers) . "' style='height:20px;'></td></tr>";

// === HEADER KOLOM ===
echo "<tr style='background:#4d58ef; color:white; font-weight:bold; text-align:center;'>";
foreach ($headers as $header)
{
    echo "<th style='padding:10px; border:1px solid #ddd;'>" . h($header) . "</th>";
}
echo "</tr>";

// === ISI DATA ===
$no = 1;
foreach ($data as $row)
{
    echo "<tr style='text-align:center;'>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . $no++ . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; text-align:left;'>" . h($row['nama']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['nik']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['nuptk']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['jenis_kelamin']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['tempat_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['tgl_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['nama_ibu']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['status_pegawai']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['jenis_gtk']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['jabatan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['alamat']) . "</td>";
    echo "</tr>";
}

// === TOTAL DATA ===
echo "<tr style='background:#f0f0f0; font-weight:bold;'>";
echo "<td colspan='" . count($headers) . "' align='right' style='padding:15px; font-size:12pt;'>
        Total Guru: " . count($data) . " orang
      </td>";
echo "</tr>";

echo "</table>";
exit;
