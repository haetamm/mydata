<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\AgamaService;
use App\Services\KelasService;
use App\Services\PekerjaanService;
use App\Services\PermissionService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$agamaService    = new AgamaService($pdo);
$pekerjaanService = new PekerjaanService($pdo);
$kelasService    = new KelasService($pdo);

session_start();

$jenjang = strtolower(trim($_GET['jenjang'] ?? ''));
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    header('Location: /');
    exit;
}

$jenjangUp = strtoupper($jenjang);

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'siswa-' . $jenjang);

if (!PermissionService::can($permMap, 'siswa-' . $jenjang, 'import'))
{
    denyAccess('Anda tidak diizinkan mengunduh template.');
}

$agama     = $agamaService->getAll();
$pekerjaan = $pekerjaanService->getAll();
$kelas     = $kelasService->getByJenjang($jenjang);

// ─── Colour palette ───────────────────────────────────────────────────────────
const C_BRAND      = '4D58EF';
const C_BRAND_BG   = 'EEF0FF';
const C_WHITE      = 'FFFFFF';
const C_AMBER      = 'F59E0B';
const C_BORDER     = 'BFBFBF';
const C_REF_BORDER = 'C7D2FE';
const C_REF_BG     = 'EEF2FF';
const C_REF_HDR    = '6366F1';
const C_NOTE       = '6B7280';

// ─── Style helpers ────────────────────────────────────────────────────────────

/**
 * Returns a PhpSpreadsheet style array for a bordered data cell.
 * $textFormat = true → force "@" (text) number format.
 */
function cellStyle(bool $center = false, bool $textFormat = false): array
{
    $style = [
        'font'      => ['name' => 'Arial', 'size' => 10],
        'borders'   => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF' . C_BORDER],
            ],
        ],
    ];
    if ($center)
    {
        $style['alignment'] = ['horizontal' => Alignment::HORIZONTAL_CENTER];
    }
    if ($textFormat)
    {
        $style['numberFormat'] = ['formatCode' => NumberFormat::FORMAT_TEXT];
    }
    return $style;
}

function refCellStyle(bool $center = false, bool $altRow = false, bool $boldBlue = false): array
{
    $style = [
        'font'    => [
            'name' => 'Arial',
            'size' => 10,
            'bold'  => $boldBlue,
            'color' => ['argb' => $boldBlue ? 'FF' . C_BRAND : 'FF000000'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF' . C_REF_BORDER],
            ],
        ],
    ];
    if ($altRow)
    {
        $style['fill'] = [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF' . C_REF_BG],
        ];
    }
    if ($center)
    {
        $style['alignment'] = ['horizontal' => Alignment::HORIZONTAL_CENTER];
    }
    return $style;
}

// ─── Column definitions ───────────────────────────────────────────────────────
$headers = [
    'No',
    'Nama *',
    'NIS',
    'NISN',
    'NIK',
    'Tempat Lahir',
    'Tanggal Lahir (YYYY-MM-DD)',
    'ID Agama *',
    'ID Kelas *',
    'Ruang *',
    'Tahun Masuk',
    'Alamat',
    'RT/RW',
    'Dusun',
    'Kelurahan',
    'Kecamatan',
    'Kode Pos',
    'Nama Ayah',
    'Thn Lahir Ayah',
    'Pendidikan Ayah',
    'ID Pekerjaan Ayah',
    'Penghasilan Ayah',
    'NIK Ayah',
    'Nama Ibu',
    'Thn Lahir Ibu',
    'Pendidikan Ibu',
    'ID Pekerjaan Ibu',
    'Penghasilan Ibu',
    'NIK Ibu',
    'Nama Wali',
    'Thn Lahir Wali',
    'Pendidikan Wali',
    'ID Pekerjaan Wali',
    'Penghasilan Wali',
    'NIK Wali',
];

// Column widths in characters (approximate Excel unit)
$colWidths = [
    4,   // No
    22,  // Nama
    12,  // NIS
    14,  // NISN
    19,  // NIK
    16,  // Tempat Lahir
    18,  // Tanggal Lahir
    10,  // ID Agama
    10,  // ID Kelas
    7,   // Ruang
    11,  // Tahun Masuk
    22,  // Alamat
    10,  // RT/RW
    12,  // Dusun
    13,  // Kelurahan
    13,  // Kecamatan
    10,  // Kode Pos
    22,  // Nama Ayah
    14,  // Thn Lahir Ayah
    16,  // Pendidikan Ayah
    16,  // ID Pekerjaan Ayah
    16,  // Penghasilan Ayah
    19,  // NIK Ayah
    22,  // Nama Ibu
    14,  // Thn Lahir Ibu
    16,  // Pendidikan Ibu
    16,  // ID Pekerjaan Ibu
    16,  // Penghasilan Ibu
    19,  // NIK Ibu
    22,  // Nama Wali
    14,  // Thn Lahir Wali
    16,  // Pendidikan Wali
    16,  // ID Pekerjaan Wali
    16,  // Penghasilan Wali
    19,  // NIK Wali
];

// Kolom yang harus diformat sebagai Text (0-indexed)
// NIS(2), NISN(3), NIK(4), RT/RW(12), Kode Pos(16), NIK Ayah(22), NIK Ibu(28), NIK Wali(34)
$textColIndexes = [2, 3, 4, 12, 16, 22, 28, 34];

// Required columns (0-indexed): No(0), Nama*(1), ID Agama*(7), ID Kelas*(8), Ruang*(9)
$requiredColIndexes = [0, 1, 7, 8, 9];

$totalCols = count($headers);

// ─── Build Spreadsheet ────────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Sistem Akademik')
    ->setTitle('Template Upload Siswa ' . $jenjangUp);

// ═════════════════════════════════════════════════════════════════════════════
// SHEET 1 — Upload
// ═════════════════════════════════════════════════════════════════════════════
$ws = $spreadsheet->getActiveSheet();
$ws->setTitle('Upload');

// Set column widths
foreach ($colWidths as $i => $w)
{
    $ws->getColumnDimensionByColumn($i + 1)->setWidth($w);
}

// ── Row 1: Title ──────────────────────────────────────────────────────────────
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

$ws->mergeCells('A1:' . $lastCol . '1');
$ws->setCellValue('A1', 'TEMPLATE UPLOAD SISWA ' . $jenjangUp . ' — ' . date('d/m/Y'));
$ws->getRowDimension(1)->setRowHeight(24);
$ws->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── Row 2: Note ───────────────────────────────────────────────────────────────
$ws->mergeCells('A2:' . $lastCol . '2');
$ws->setCellValue('A2', '* Kolom bertanda bintang wajib diisi. Gunakan ID dari sheet "Daftar ID" untuk kolom ID Agama, ID Kelas, dan ID Pekerjaan.');
$ws->getRowDimension(2)->setRowHeight(16);
$ws->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_NOTE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

// ── Row 3: Spacer ─────────────────────────────────────────────────────────────
$ws->getRowDimension(3)->setRowHeight(8);

// ── Row 4: Headers ────────────────────────────────────────────────────────────
$ws->getRowDimension(4)->setRowHeight(30);
foreach ($headers as $i => $label)
{
    $col   = $i + 1;
    $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '4';
    $ws->setCellValue($coord, $label);

    $isReq = in_array($i, $requiredColIndexes, true);
    $ws->getStyle($coord)->applyFromArray([
        'font'      => [
            'name'  => 'Arial',
            'size'  => 10,
            'bold'  => true,
            'color' => ['argb' => 'FF' . C_WHITE],
        ],
        'fill'      => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF' . ($isReq ? C_AMBER : C_BRAND)],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER,
            'wrapText'   => true,
        ],
        'borders'   => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF' . C_WHITE],
            ],
        ],
    ]);
}

// ── Rows 5–104: Data rows (100 rows) ─────────────────────────────────────────
for ($r = 1; $r <= 100; $r++)
{
    $excelRow = $r + 4; // offset: title(1) + note(2) + spacer(3) + header(4)
    $ws->getRowDimension($excelRow)->setRowHeight(18);

    // Column A: row number (centered)
    $ws->setCellValue('A' . $excelRow, $r);
    $ws->getStyle('A' . $excelRow)->applyFromArray(cellStyle(true));

    // Remaining columns
    for ($col = 1; $col < $totalCols; $col++)
    {
        $isText  = in_array($col, $textColIndexes, true);
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
        $coord   = $colLetter . $excelRow;

        // Force cell type to string so leading zeros are preserved
        $ws->getCell($coord)->setValueExplicit('', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $ws->getStyle($coord)->applyFromArray(cellStyle(false, $isText));
    }
}

// ═════════════════════════════════════════════════════════════════════════════
// SHEET 2 — Daftar ID
// ═════════════════════════════════════════════════════════════════════════════
$ws2 = $spreadsheet->createSheet();
$ws2->setTitle('Daftar ID');

// Column widths: ID | Nama | gap | ID | Nama | gap | ID | Nama
$refWidths = [8, 25, 4, 8, 25, 4, 8, 25];
foreach ($refWidths as $i => $w)
{
    $ws2->getColumnDimensionByColumn($i + 1)->setWidth($w);
}

$refAgama = array_map(fn($row) => ['id' => $row['id_agama'],     'nama' => $row['nama_agama']],     $agama);
$refKelas = array_map(fn($row) => ['id' => $row['id_kelas'],     'nama' => $row['nama_kelas']],     $kelas);
$refPkrj  = array_map(fn($row) => ['id' => $row['id_pekerjaan'], 'nama' => $row['nama_pekerjaan']], $pekerjaan);
$maxRows  = max(count($refAgama), count($refKelas), count($refPkrj));

// ── Row 1: Title ──────────────────────────────────────────────────────────────
$ws2->mergeCells('A1:H1');
$ws2->setCellValue('A1', 'DAFTAR ID REFERENSI — Jenjang ' . $jenjangUp);
$ws2->getRowDimension(1)->setRowHeight(22);
$ws2->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── Row 2: Note ───────────────────────────────────────────────────────────────
$ws2->mergeCells('A2:H2');
$ws2->setCellValue('A2', 'Salin nilai kolom ID (angka) ke kolom yang sesuai di sheet Upload.');
$ws2->getRowDimension(2)->setRowHeight(14);
$ws2->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_NOTE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
]);

// ── Row 3: Spacer ─────────────────────────────────────────────────────────────
$ws2->getRowDimension(3)->setRowHeight(8);

// ── Row 4: Group headers ──────────────────────────────────────────────────────
$ws2->getRowDimension(4)->setRowHeight(22);
$groupHeaderStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_REF_HDR]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
$ws2->mergeCells('A4:B4');
$ws2->setCellValue('A4', 'AGAMA');
$ws2->getStyle('A4:B4')->applyFromArray($groupHeaderStyle);

$ws2->mergeCells('D4:E4');
$ws2->setCellValue('D4', 'KELAS ' . $jenjangUp);
$ws2->getStyle('D4:E4')->applyFromArray($groupHeaderStyle);

$ws2->mergeCells('G4:H4');
$ws2->setCellValue('G4', 'PEKERJAAN ORANG TUA / WALI');
$ws2->getStyle('G4:H4')->applyFromArray($groupHeaderStyle);

// ── Row 5: Sub headers ────────────────────────────────────────────────────────
$ws2->getRowDimension(5)->setRowHeight(18);
$subStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_REF_HDR]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
];
foreach (['A5', 'D5', 'G5'] as $cell)
{
    $ws2->setCellValue($cell, 'ID');
    $ws2->getStyle($cell)->applyFromArray($subStyle);
}
foreach (['B5', 'E5', 'H5'] as $cell)
{
    $ws2->setCellValue($cell, 'Nama');
    $ws2->getStyle($cell)->applyFromArray($subStyle);
}

// ── Data rows ─────────────────────────────────────────────────────────────────
for ($i = 0; $i < $maxRows; $i++)
{
    $excelRow = $i + 6; // offset: 5 header rows + 1
    $ws2->getRowDimension($excelRow)->setRowHeight(18);
    $isAlt = ($i % 2 === 1);

    // Agama
    if (isset($refAgama[$i]))
    {
        $ws2->setCellValue('A' . $excelRow, $refAgama[$i]['id']);
        $ws2->setCellValue('B' . $excelRow, $refAgama[$i]['nama']);
        $ws2->getStyle('A' . $excelRow)->applyFromArray(refCellStyle(true, $isAlt, true));
        $ws2->getStyle('B' . $excelRow)->applyFromArray(refCellStyle(false, $isAlt));
    }

    // Kelas
    if (isset($refKelas[$i]))
    {
        $ws2->setCellValue('D' . $excelRow, $refKelas[$i]['id']);
        $ws2->setCellValue('E' . $excelRow, $refKelas[$i]['nama']);
        $ws2->getStyle('D' . $excelRow)->applyFromArray(refCellStyle(true, $isAlt, true));
        $ws2->getStyle('E' . $excelRow)->applyFromArray(refCellStyle(false, $isAlt));
    }

    // Pekerjaan
    if (isset($refPkrj[$i]))
    {
        $ws2->setCellValue('G' . $excelRow, $refPkrj[$i]['id']);
        $ws2->setCellValue('H' . $excelRow, $refPkrj[$i]['nama']);
        $ws2->getStyle('G' . $excelRow)->applyFromArray(refCellStyle(true, $isAlt, true));
        $ws2->getStyle('H' . $excelRow)->applyFromArray(refCellStyle(false, $isAlt));
    }
}

// ─── Output ───────────────────────────────────────────────────────────────────
$spreadsheet->setActiveSheetIndex(0); // Focus Sheet Upload saat dibuka

$filename = 'Template_Siswa_' . $jenjangUp . '_' . date('Y-m-d') . '.xlsx';

// Pastikan tidak ada output sebelumnya
if (ob_get_length())
{
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
