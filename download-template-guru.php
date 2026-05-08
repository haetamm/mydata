<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

session_start();

$jenjang = strtolower(trim($_GET['jenjang'] ?? ''));
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    header('Location: /');
    exit;
}

$jenjangUp = strtoupper($jenjang);

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'guru-' . $jenjang);

if (!PermissionService::can($permMap, 'guru-' . $jenjang, 'import'))
{
    denyAccess('Anda tidak diizinkan mengunduh template.');
}

// ─── Warna ────────────────────────────────────────────────────────
const C_BRAND    = '4D58EF';
const C_BRAND_BG = 'EEF0FF';
const C_WHITE    = 'FFFFFF';
const C_AMBER    = 'F59E0B';
const C_BORDER   = 'BFBFBF';
const C_REF_BG   = 'EEF2FF';
const C_REF_HDR  = '6366F1';
const C_REF_BORD = 'C7D2FE';
const C_NOTE     = '6B7280';

// ─── Kolom template ───────────────────────────────────────────────
// Kolom wajib (*): No, Nama, Jenis Kelamin
$headers = [
    'No',
    'Nama *',
    'Jenis Kelamin * (L/P)',
    'NIK',
    'NUPTK',
    'Tempat Lahir',
    'Tanggal Lahir (YYYY-MM-DD)',
    'Nama Ibu',
    'Status Pegawai',
    'Jenis GTK',
    'Jabatan',
    'Alamat',
    'Tahun Masuk',
];

$colWidths        = [4, 22, 16, 19, 19, 16, 18, 22, 16, 18, 18, 28, 12];
$requiredColIdx   = [0, 1, 2];   // No, Nama, Jenis Kelamin
$textColIdx       = [3, 4];      // NIK, NUPTK
$totalCols        = count($headers); // 13

// ─── Daftar referensi ─────────────────────────────────────────────
$statusPegawaiList = ['PNS', 'PPPK', 'GTT / Honorer', 'GTY', 'Lainnya'];
$jenisGtkList      = [
    'Guru Kelas',
    'Guru Mata Pelajaran',
    'Guru BK',
    'Kepala Sekolah',
    'Wakil Kepala Sekolah',
    'Tenaga Administrasi',
    'Tenaga Perpustakaan',
    'Tenaga Laboratorium',
    'Lainnya',
];
$jabatanList = [
    'Guru',
    'Kepala Sekolah',
    'Wakil Kepala Sekolah',
    'Wali Kelas',
    'Koordinator BK',
    'Bendahara',
    'Operator Sekolah',
    'Lainnya',
];

// ─── Build Spreadsheet ────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Sistem Akademik')
    ->setTitle('Template Upload Guru ' . $jenjangUp);

// ═══════════════════════════════════════════════════════════════════
// SHEET 1 — Upload
// ═══════════════════════════════════════════════════════════════════
$ws      = $spreadsheet->getActiveSheet();
$ws->setTitle('Upload');
$lastCol = Coordinate::stringFromColumnIndex($totalCols);

foreach ($colWidths as $i => $w)
{
    $ws->getColumnDimensionByColumn($i + 1)->setWidth($w);
}

// Baris 1: Judul
$ws->mergeCells('A1:' . $lastCol . '1');
$ws->setCellValue('A1', 'TEMPLATE UPLOAD GURU ' . $jenjangUp . ' — ' . date('d/m/Y'));
$ws->getRowDimension(1)->setRowHeight(24);
$ws->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// Baris 2: Petunjuk
$ws->mergeCells('A2:' . $lastCol . '2');
$ws->setCellValue('A2', '* Kolom bertanda bintang wajib diisi. Jenis Kelamin: L (Laki-laki) atau P (Perempuan).');
$ws->getRowDimension(2)->setRowHeight(16);
$ws->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_NOTE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

// Baris 3: Spacer
$ws->getRowDimension(3)->setRowHeight(8);

// Baris 4: Header
$ws->getRowDimension(4)->setRowHeight(30);
$hBase = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . C_WHITE]]],
];
$hReq = array_replace_recursive($hBase, ['fill' => ['startColor' => ['argb' => 'FF' . C_AMBER]]]);

foreach ($headers as $i => $label)
{
    $coord = Coordinate::stringFromColumnIndex($i + 1) . '4';
    $ws->setCellValue($coord, $label);
    $ws->getStyle($coord)->applyFromArray(in_array($i, $requiredColIdx, true) ? $hReq : $hBase);
}

// Baris 5–104: 100 baris data kosong
$cellBase = [
    'font'    => ['name' => 'Arial', 'size' => 10],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . C_BORDER]]],
];
$cellText = array_replace_recursive($cellBase, ['numberFormat' => ['formatCode' => NumberFormat::FORMAT_TEXT]]);
$cellCtr  = array_replace_recursive($cellBase, ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

for ($r = 1; $r <= 100; $r++)
{
    $excelRow = $r + 4;
    $ws->getRowDimension($excelRow)->setRowHeight(18);

    $ws->setCellValue('A' . $excelRow, $r);
    $ws->getStyle('A' . $excelRow)->applyFromArray($cellCtr);

    for ($col = 1; $col < $totalCols; $col++)
    {
        $isText = in_array($col, $textColIdx, true);
        $coord  = Coordinate::stringFromColumnIndex($col + 1) . $excelRow;
        $ws->getCell($coord)->setValueExplicit('', DataType::TYPE_STRING);
        $ws->getStyle($coord)->applyFromArray($isText ? $cellText : $cellBase);
    }
}

// ═══════════════════════════════════════════════════════════════════
// SHEET 2 — Referensi
// ═══════════════════════════════════════════════════════════════════
$ws2 = $spreadsheet->createSheet();
$ws2->setTitle('Referensi');

$ws2->getColumnDimensionByColumn(1)->setWidth(28);
$ws2->getColumnDimensionByColumn(2)->setWidth(4);
$ws2->getColumnDimensionByColumn(3)->setWidth(28);
$ws2->getColumnDimensionByColumn(4)->setWidth(4);
$ws2->getColumnDimensionByColumn(5)->setWidth(22);

// Judul
$ws2->mergeCells('A1:E1');
$ws2->setCellValue('A1', 'DAFTAR REFERENSI — Jenjang ' . $jenjangUp);
$ws2->getRowDimension(1)->setRowHeight(22);
$ws2->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// Note
$ws2->mergeCells('A2:E2');
$ws2->setCellValue('A2', 'Salin nilai persis (termasuk huruf besar/kecil) ke kolom yang sesuai di sheet Upload.');
$ws2->getRowDimension(2)->setRowHeight(14);
$ws2->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_NOTE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

$ws2->getRowDimension(3)->setRowHeight(8);

// Group headers
$ws2->getRowDimension(4)->setRowHeight(22);
$grpStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_REF_HDR]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
foreach (['A4' => 'STATUS PEGAWAI', 'C4' => 'JENIS GTK', 'E4' => 'JABATAN'] as $cell => $label)
{
    $ws2->setCellValue($cell, $label);
    $ws2->getStyle($cell)->applyFromArray($grpStyle);
}

$refBase = [
    'font'    => ['name' => 'Arial', 'size' => 10],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . C_REF_BORD]]],
];
$refAlt = array_replace_recursive($refBase, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_REF_BG]],
]);

$maxRows = max(count($statusPegawaiList), count($jenisGtkList), count($jabatanList));
for ($i = 0; $i < $maxRows; $i++)
{
    $excelRow = $i + 5;
    $ws2->getRowDimension($excelRow)->setRowHeight(18);
    $style = ($i % 2 === 1) ? $refAlt : $refBase;

    if (isset($statusPegawaiList[$i]))
    {
        $ws2->setCellValue('A' . $excelRow, $statusPegawaiList[$i]);
        $ws2->getStyle('A' . $excelRow)->applyFromArray($style);
    }
    if (isset($jenisGtkList[$i]))
    {
        $ws2->setCellValue('C' . $excelRow, $jenisGtkList[$i]);
        $ws2->getStyle('C' . $excelRow)->applyFromArray($style);
    }
    if (isset($jabatanList[$i]))
    {
        $ws2->setCellValue('E' . $excelRow, $jabatanList[$i]);
        $ws2->getStyle('E' . $excelRow)->applyFromArray($style);
    }
}

// ─── Output ───────────────────────────────────────────────────────
$spreadsheet->setActiveSheetIndex(0);
$filename = 'Template_Guru_' . $jenjangUp . '_' . date('Y-m-d') . '.xlsx';

if (ob_get_length()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

(new Xlsx($spreadsheet))->save('php://output');
exit;
