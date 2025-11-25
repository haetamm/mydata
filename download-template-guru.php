<?php
session_start();
include("connection.php");

$jenjang = strtolower($_GET['jenjang'] ?? '');
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    die("Jenjang tidak valid!");
}

$jenjangUp = strtoupper($jenjang);
$pdo = $link;

// Buat direktori temporary
$tempDir = sys_get_temp_dir() . '/excel_guru_' . uniqid();
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
        <sheet name="Upload Guru" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>';
file_put_contents($xlDir . '/workbook.xml', $workbook);

// 4. xl/_rels/workbook.xml.rels
$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
file_put_contents($xlRelsDir . '/workbook.xml.rels', $workbookRels);

// 5. xl/sharedStrings.xml - Kumpulkan semua teks
$sharedStrings = [];
$stringIndex = 0;

// Header untuk sheet Upload Guru
$headers = [
    "No",
    "NIK",
    "NUPTK",
    "Nama",
    "Jenis Kelamin (L/P)",
    "Tempat Lahir",
    "Tanggal Lahir (YYYY-MM-DD)",
    "Nama Ibu",
    "Status Pegawai",
    "Jenis GTK",
    "Jabatan",
    "Alamat"
];

foreach ($headers as $header)
{
    $sharedStrings[$stringIndex++] = $header;
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

// 6. SHEET 1: Upload Guru
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
    header('Content-Disposition: attachment; filename="Template_Guru_' . $jenjangUp . '_' . date('Y-m-d') . '.xlsx"');
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
