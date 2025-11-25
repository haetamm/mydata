<?php
session_start();
include("connection.php");

allowSuperAdminOnly();

$jenjang = strtolower($_GET['jenjang'] ?? '');

if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    die("Jenjang tidak valid!");
}

$jenjangUp = strtoupper($jenjang);
$pdo = $link;

// Ambil data referensi
$agama      = $pdo->query("SELECT id_agama, nama_agama FROM master_agama WHERE deleted_at IS NULL ORDER BY id_agama")->fetchAll(PDO::FETCH_ASSOC);
$semester   = $pdo->query("SELECT id_semester, nama_semester FROM master_semester WHERE deleted_at IS NULL ORDER BY id_semester")->fetchAll(PDO::FETCH_ASSOC);
$tahun      = $pdo->query("SELECT id_tahun, tahun FROM master_tahun_pelajaran WHERE deleted_at IS NULL ORDER BY id_tahun DESC")->fetchAll(PDO::FETCH_ASSOC);
$pekerjaan  = $pdo->query("SELECT id_pekerjaan, nama_pekerjaan FROM master_pekerjaan WHERE deleted_at IS NULL ORDER BY id_pekerjaan")->fetchAll(PDO::FETCH_ASSOC);
$kelas      = $pdo->query("SELECT k.id_kelas, k.nama_kelas FROM master_kelas k JOIN master_level_kelas l ON k.level_id = l.id_level WHERE l.jenjang = '$jenjangUp' AND k.deleted_at IS NULL ORDER BY k.nama_kelas")->fetchAll(PDO::FETCH_ASSOC);

// Buat direktori temporary
$tempDir = sys_get_temp_dir() . '/excel_' . uniqid();
mkdir($tempDir, 0777, true);

// Struktur folder Excel
$xlDir = $tempDir . '/xl';
$worksheetsDir = $xlDir . '/worksheets';
$relsDir = $tempDir . '/_rels';
$xlRelsDir = $xlDir . '/_rels';

mkdir($xlDir, 0777, true);
mkdir($worksheetsDir, 0777, true);
mkdir($relsDir, 0777, true);
mkdir($xlRelsDir, 0777, true);

// ==================== FILE UTAMA ====================

// 1. [Content_Types].xml
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';
file_put_contents($tempDir . '/[Content_Types].xml', $contentTypes);

// 2. _rels/.rels
$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
file_put_contents($relsDir . '/.rels', $rels);

// 3. xl/workbook.xml
$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Upload" sheetId="1" r:id="rId1"/>
        <sheet name="Daftar ID" sheetId="2" r:id="rId2"/>
    </sheets>
</workbook>';
file_put_contents($xlDir . '/workbook.xml', $workbook);

// 4. xl/_rels/workbook.xml.rels
$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
file_put_contents($xlRelsDir . '/workbook.xml.rels', $workbookRels);

// 5. xl/sharedStrings.xml - Kumpulkan semua teks
$sharedStrings = [];
$stringIndex = 0;

// Header untuk sheet Upload
$headers = [
    "No",
    "Nama",
    "NIS",
    "NISN",
    "NIK",
    "Tempat Lahir",
    "Tanggal Lahir (YYYY-MM-DD)",
    "ID Agama",
    "ID Kelas",
    "Ruang",
    "ID Semester",
    "ID Tahun Pelajaran",
    "Alamat",
    "RT/RW",
    "Dusun",
    "Kelurahan",
    "Kecamatan",
    "Kode Pos",
    "Nama Ayah",
    "Thn Lahir Ayah",
    "Pendidikan Ayah",
    "ID Pekerjaan Ayah",
    "Penghasilan Ayah",
    "NIK Ayah",
    "Nama Ibu",
    "Thn Lahir Ibu",
    "Pendidikan Ibu",
    "ID Pekerjaan Ibu",
    "Penghasilan Ibu",
    "NIK Ibu",
    "Nama Wali",
    "Thn Lahir Wali",
    "Pendidikan Wali",
    "ID Pekerjaan Wali",
    "Penghasilan Wali",
    "NIK Wali"
];

foreach ($headers as $header)
{
    $sharedStrings[$stringIndex++] = $header;
}

// Data referensi untuk sheet Daftar ID - BUAT JUDUL YANG UNIK
$sharedStrings[$stringIndex++] = "DAFTAR REFERENSI UNTUK JENJANG " . $jenjangUp;
$sharedStrings[$stringIndex++] = "ID";
$sharedStrings[$stringIndex++] = "Nama";

// JUDUL UNIK UNTUK SETIAP TABEL
$sharedStrings[$stringIndex++] = "AGAMA";
$sharedStrings[$stringIndex++] = "KELAS " . $jenjangUp;
$sharedStrings[$stringIndex++] = "SEMESTER";
$sharedStrings[$stringIndex++] = "TAHUN PELAJARAN";
$sharedStrings[$stringIndex++] = "PEKERJAAN ORANG TUA / WALI";

// Agama
foreach ($agama as $a)
{
    $sharedStrings[$stringIndex++] = $a['nama_agama'];
}

// Kelas
foreach ($kelas as $k)
{
    $sharedStrings[$stringIndex++] = $k['nama_kelas'];
}

// Semester
foreach ($semester as $s)
{
    $sharedStrings[$stringIndex++] = $s['nama_semester'];
}

// Tahun
foreach ($tahun as $t)
{
    $sharedStrings[$stringIndex++] = $t['tahun'];
}

// Pekerjaan
foreach ($pekerjaan as $p)
{
    $sharedStrings[$stringIndex++] = $p['nama_pekerjaan'];
}

// Build sharedStrings.xml
$sharedStringsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
foreach ($sharedStrings as $string)
{
    $sharedStringsXml .= '<si><t>' . htmlspecialchars($string) . '</t></si>';
}
$sharedStringsXml .= '</sst>';
file_put_contents($xlDir . '/sharedStrings.xml', $sharedStringsXml);

// Helper function untuk konversi kolom
function numToAlpha($n)
{
    $r = '';
    for ($i = $n; $i >= 0; $i = (int)($i / 26) - 1)
    {
        $r = chr($i % 26 + 65) . $r;
    }
    return $r;
}

// 6. SHEET 1: Upload
$sheet1 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetData>';

// Header row
$sheet1 .= '<row r="1">';
foreach ($headers as $col => $header)
{
    $cellRef = numToAlpha($col) . '1';
    $sheet1 .= '<c r="' . $cellRef . '" t="s"><v>' . $col . '</v></c>';
}
$sheet1 .= '</row>';

// 10 baris kosong
for ($row = 2; $row <= 11; $row++)
{
    $sheet1 .= '<row r="' . $row . '">';
    for ($col = 0; $col < count($headers); $col++)
    {
        $cellRef = numToAlpha($col) . $row;
        $sheet1 .= '<c r="' . $cellRef . '"><v></v></c>';
    }
    $sheet1 .= '</row>';
}

$sheet1 .= '</sheetData></worksheet>';
file_put_contents($worksheetsDir . '/sheet1.xml', $sheet1);

// 7. SHEET 2: Daftar ID
$sheet2 = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetData>';

$currentRow = 1;
$stringIndexOffset = count($headers); // Offset untuk shared strings

// Judul besar
$sheet2 .= '<row r="' . $currentRow . '">';
$sheet2 .= '<c r="A' . $currentRow . '" t="s"><v>' . $stringIndexOffset . '</v></c>';
$sheet2 .= '</row>';
$currentRow += 2;

// Function untuk print tabel referensi
function addReferenceTable(&$sheet2, &$currentRow, &$stringIndex, $titleIndex, $data, $idCol, $namaCol, $stringIndexOffset)
{
    // Judul tabel
    $sheet2 .= '<row r="' . $currentRow . '">';
    $sheet2 .= '<c r="A' . $currentRow . '" t="s"><v>' . $titleIndex . '</v></c>';
    $sheet2 .= '</row>';
    $currentRow++;

    // Header ID & Nama
    $sheet2 .= '<row r="' . $currentRow . '">';
    $sheet2 .= '<c r="A' . $currentRow . '" t="s"><v>' . ($stringIndexOffset + 1) . '</v></c>';
    $sheet2 .= '<c r="B' . $currentRow . '" t="s"><v>' . ($stringIndexOffset + 2) . '</v></c>';
    $sheet2 .= '</row>';
    $currentRow++;

    // Data
    foreach ($data as $item)
    {
        $sheet2 .= '<row r="' . $currentRow . '">';
        $sheet2 .= '<c r="A' . $currentRow . '"><v>' . $item[$idCol] . '</v></c>';
        $sheet2 .= '<c r="B' . $currentRow . '" t="s"><v>' . $stringIndex . '</v></c>';
        $sheet2 .= '</row>';
        $stringIndex++;
        $currentRow++;
    }

    $currentRow++; // Spasi
    return $stringIndex;
}

// Hitung posisi awal untuk data referensi
$refStringIndex = $stringIndexOffset + 8; // Mulai setelah semua judul

// Agama - JUDUL UNIK
$refStringIndex = addReferenceTable($sheet2, $currentRow, $refStringIndex, $stringIndexOffset + 3, $agama, "id_agama", "nama_agama", $stringIndexOffset);

// Kelas - JUDUL UNIK
$refStringIndex = addReferenceTable($sheet2, $currentRow, $refStringIndex, $stringIndexOffset + 4, $kelas, "id_kelas", "nama_kelas", $stringIndexOffset);

// Semester - JUDUL UNIK
$refStringIndex = addReferenceTable($sheet2, $currentRow, $refStringIndex, $stringIndexOffset + 5, $semester, "id_semester", "nama_semester", $stringIndexOffset);

// Tahun Pelajaran - JUDUL UNIK
$refStringIndex = addReferenceTable($sheet2, $currentRow, $refStringIndex, $stringIndexOffset + 6, $tahun, "id_tahun", "tahun", $stringIndexOffset);

// Pekerjaan - JUDUL UNIK
$refStringIndex = addReferenceTable($sheet2, $currentRow, $refStringIndex, $stringIndexOffset + 7, $pekerjaan, "id_pekerjaan", "nama_pekerjaan", $stringIndexOffset);

$sheet2 .= '</sheetData></worksheet>';
file_put_contents($worksheetsDir . '/sheet2.xml', $sheet2);

// ==================== BUAT ZIP ====================
$zip = new ZipArchive();
$filename = $tempDir . '/template.xlsx';

if ($zip->open($filename, ZipArchive::CREATE) === TRUE)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file)
    {
        if (!$file->isDir())
        {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($tempDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();

    // Kirim ke browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Template_Siswa_' . $jenjangUp . '_' . date('Y-m-d') . '.xlsx"');
    header('Content-Length: ' . filesize($filename));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    readfile($filename);

    // Cleanup
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo)
    {
        $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
    }
    rmdir($tempDir);

    exit;
}
else
{
    die('Gagal membuat file Excel');
}
