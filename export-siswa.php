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
$tabel = "siswa_" . $jenjang;

// Query tetap sama
$query = "
    SELECT
        s.*,
        a.nama_agama,
        CONCAT(k.nama_kelas, ' ') AS kelas,
        mp_ayah.nama_pekerjaan AS pekerjaan_ayah,
        mp_ibu.nama_pekerjaan  AS pekerjaan_ibu,
        mp_wali.nama_pekerjaan AS pekerjaan_wali,
        ms.nama_semester AS semester,
        tp.tahun AS tahun_pelajaran
    FROM $tabel s
    LEFT JOIN master_agama a        ON s.id_agama = a.id_agama
    LEFT JOIN master_kelas k        ON s.id_kelas = k.id_kelas
    LEFT JOIN master_semester ms           ON s.id_semester = ms.id_semester
    LEFT JOIN master_tahun_pelajaran tp    ON s.id_tahun_pelajaran = tp.id_tahun
    LEFT JOIN master_pekerjaan mp_ayah ON s.ayah_pekerjaan = mp_ayah.id_pekerjaan
    LEFT JOIN master_pekerjaan mp_ibu  ON s.ibu_pekerjaan  = mp_ibu.id_pekerjaan
    LEFT JOIN master_pekerjaan mp_wali ON s.wali_pekerjaan = mp_wali.id_pekerjaan
    ORDER BY nama
";

$stmt = $link->query($query);
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
    "NIS",
    "NISN",
    "NIK",
    "Tempat Lahir",
    "Tanggal Lahir",
    "Agama",
    "Kelas",
    "Ruang",
    "Semester",
    "Tahun Pelajaran",
    "Alamat",
    "RT/RW",
    "Dusun",
    "Kelurahan",
    "Kecamatan",
    "Kode Pos",
    "Nama Ayah",
    "Tahun Lahir Ayah",
    "Pendidikan Ayah",
    "Pekerjaan Ayah",
    "Penghasilan Ayah",
    "NIK Ayah",
    "Nama Ibu",
    "Tahun Lahir Ibu",
    "Pendidikan Ibu",
    "Pekerjaan Ibu",
    "Penghasilan Ibu",
    "NIK Ibu",
    "Nama Wali",
    "Tahun Lahir Wali",
    "Pendidikan Wali",
    "Pekerjaan Wali",
    "Penghasilan Wali",
    "NIK Wali"
];
$filename = "DATA_SISWA_" . strtoupper($jenjang) . "_" . date("d-m-Y") . ".xlsx";
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: no-cache");

// Judul laporan
$judul = "DATA SISWA " . strtoupper($jenjang);
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
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['nis']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['nisn']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['nik']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['tempat_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['tgl_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['nama_agama']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['kelas']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ruang']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['semester']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['tahun_pelajaran']) . "</td>";

    echo "<td style='border:1px solid #ddd; padding:8px; text-align:left;'>" . h($row['alamat']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['rt_rw']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['dusun']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['kelurahan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['kecamatan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['kode_pos']) . "</td>";

    echo "<td style='border:1px solid #ddd; padding:8px; text-align:left;'>" . h($row['ayah_nama']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ayah_tahun_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ayah_pendidikan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['pekerjaan_ayah']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ayah_penghasilan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['ayah_nik']) . "</td>";

    echo "<td style='border:1px solid #ddd; padding:8px; text-align:left;'>" . h($row['ibu_nama']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ibu_tahun_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ibu_pendidikan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['pekerjaan_ibu']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['ibu_penghasilan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['ibu_nik']) . "</td>";

    echo "<td style='border:1px solid #ddd; padding:8px; text-align:left;'>" . h($row['wali_nama']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['wali_tahun_lahir']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['wali_pendidikan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['pekerjaan_wali']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px;'>" . h($row['wali_penghasilan']) . "</td>";
    echo "<td style='border:1px solid #ddd; padding:8px; mso-number-format:\"@\";'>" . h($row['wali_nik']) . "</td>";

    echo "</tr>";
}

// === TOTAL DATA ===
echo "<tr style='background:#f0f0f0; font-weight:bold;'>";
echo "<td colspan='" . count($headers) . "' align='right' style='padding:15px; font-size:12pt;'>
        Total Siswa: " . count($data) . " orang
    </td>";
echo "</tr>";

echo "</table>";
exit;
