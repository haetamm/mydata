<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\GuruService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

session_start();

// ── Validasi jenjang ──────────────────────────────────────────────
$jenjangRaw = strtolower(trim($_GET['jenjang'] ?? 'sd'));
$jenjangMap = [
    'sd'  => ['tabel' => 'guru_sd',  'slug' => 'guru-sd',  'label' => 'SD'],
    'smp' => ['tabel' => 'guru_smp', 'slug' => 'guru-smp', 'label' => 'SMP'],
    'sma' => ['tabel' => 'guru_sma', 'slug' => 'guru-sma', 'label' => 'SMA'],
];

if (!isset($jenjangMap[$jenjangRaw]))
{
    http_response_code(400);
    exit('Jenjang tidak valid.');
}

$jenjang = $jenjangMap[$jenjangRaw];

// ── Guard ─────────────────────────────────────────────────────────
[$menus, $permMap] = PermissionMiddleware::handle($pdo, $jenjang['slug'], 'export');

// ── Filter ────────────────────────────────────────────────────────
$filter = [
    'nama'           => trim($_GET['nama']           ?? ''),
    'jenis_gtk'      => trim($_GET['jenis_gtk']      ?? ''),
    'status_pegawai' => trim($_GET['status_pegawai'] ?? ''),
    'status'         => trim($_GET['status']         ?? ''),
];

// ── Ambil data ────────────────────────────────────────────────────
$service = new GuruService($pdo);
$rows    = $service->getForExport($jenjang['tabel'], $filter);
$total   = count($rows);

// ── Label status ──────────────────────────────────────────────────
$statusLabel = [
    'aktif'     => 'Aktif',
    'pensiun'   => 'Pensiun',
    'pindah'    => 'Pindah',
    'keluar'    => 'Keluar',
    'meninggal' => 'Meninggal',
];

// ── Sub-judul filter ──────────────────────────────────────────────
$filterInfo = ['Dicetak: ' . date('d/m/Y H:i')];
if ($filter['nama']           !== '') $filterInfo[] = 'Pencarian: '      . $filter['nama'];
if ($filter['jenis_gtk']      !== '') $filterInfo[] = 'Jenis GTK: '      . $filter['jenis_gtk'];
if ($filter['status_pegawai'] !== '') $filterInfo[] = 'Status Pegawai: ' . $filter['status_pegawai'];
if ($filter['status']         !== '') $filterInfo[] = 'Status: '         . ucfirst($filter['status']);
$subJudul = implode('   |   ', $filterInfo);

// ── Nama file ─────────────────────────────────────────────────────
$suffix  = ($filter['status']         !== '') ? '_' . $filter['status']                          : '';
$suffix .= ($filter['status_pegawai'] !== '') ? '_' . $filter['status_pegawai']                  : '';
$suffix .= ($filter['jenis_gtk']      !== '') ? '_' . str_replace(' ', '-', $filter['jenis_gtk']) : '';
$filename = 'Data_Guru_' . $jenjang['label'] . $suffix . '_' . date('Ymd_His') . '.xlsx';

// ── Konstanta warna ───────────────────────────────────────────────
const C_BRAND    = '4D58EF';
const C_BRAND_BG = 'EEF0FF';
const C_WHITE    = 'FFFFFF';
const C_BORDER   = 'BFBFBF';
const C_SUBTITLE = '666666';

// ── Helper: border semua sisi ──────────────────────────────────────
function borderAll(string $color = C_BORDER): array
{
    return [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FF' . $color],
            ],
        ],
    ];
}

// ── Helper: style sel data biasa ──────────────────────────────────
function dataStyle(bool $center = false, bool $alt = false): array
{
    $style = array_merge_recursive(
        [
            'font' => ['name' => 'Arial', 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF' . ($alt ? C_BRAND_BG : C_WHITE)],
            ],
        ],
        borderAll()
    );

    if ($center)
    {
        $style['alignment'] = ['horizontal' => Alignment::HORIZONTAL_CENTER];
    }

    return $style;
}

// ── Helper: style sel status ──────────────────────────────────────
function statusStyle(string $status): array
{
    $color = match ($status)
    {
        'aktif'                          => '166534',
        'pensiun'                        => '1D4ED8',
        'pindah', 'keluar', 'meninggal'  => '991B1B',
        default                          => '000000',
    };

    return array_merge_recursive(
        [
            'font' => [
                'name'  => 'Arial',
                'size'  => 10,
                'bold'  => true,
                'color' => ['argb' => 'FF' . $color],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF' . C_WHITE],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ],
        borderAll()
    );
}

// ── Header kolom (16 kolom) ───────────────────────────────────────
$headers = [
    'No',
    'Nama',
    'NIK',
    'NUPTK',
    'L/P',
    'Tempat Lahir',
    'Tgl Lahir',
    'Nama Ibu',
    'Status Pegawai',
    'Jenis GTK',
    'Jabatan',
    'Alamat',
    'Thn Masuk',
    'Thn Keluar',
    'Status',
    'Keterangan Status',
];

// Lebar kolom (karakter)
$colWidths = [
    4,    // No
    22,   // Nama
    17,   // NIK
    17,   // NUPTK
    5,    // L/P
    14,   // Tempat Lahir
    11,   // Tgl Lahir
    20,   // Nama Ibu
    13,   // Status Pegawai
    17,   // Jenis GTK
    22,   // Jabatan
    28,   // Alamat
    9,    // Thn Masuk
    9,    // Thn Keluar
    11,   // Status
    22,   // Keterangan Status
];

// Kolom yang force string agar leading zero tidak hilang (0-indexed): NIK(2), NUPTK(3)
$textColIndexes = [2, 3];

// Kolom yang center (0-indexed)
$centerColIndexes = [0, 4, 6, 8, 10, 12, 13, 14];

$totalCols = count($headers); // 16
$lastCol   = Coordinate::stringFromColumnIndex($totalCols);

// ── Build Spreadsheet ─────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Sistem Akademik')
    ->setTitle('Data Guru ' . $jenjang['label']);

$ws = $spreadsheet->getActiveSheet();
$ws->setTitle('Data Guru ' . $jenjang['label']);

// Set lebar kolom
foreach ($colWidths as $i => $w)
{
    $ws->getColumnDimensionByColumn($i + 1)->setWidth($w);
}

// ── Baris 1: Judul ────────────────────────────────────────────────
$ws->mergeCells('A1:' . $lastCol . '1');
$ws->setCellValue('A1', 'DATA GURU ' . strtoupper($jenjang['label']));
$ws->getRowDimension(1)->setRowHeight(30);
$ws->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// ── Baris 2: Sub-judul ────────────────────────────────────────────
$ws->mergeCells('A2:' . $lastCol . '2');
$ws->setCellValue('A2', $subJudul);
$ws->getRowDimension(2)->setRowHeight(16);
$ws->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_SUBTITLE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// ── Baris 3: Spasi ────────────────────────────────────────────────
$ws->getRowDimension(3)->setRowHeight(6);

// ── Baris 4: Header kolom ─────────────────────────────────────────
$ws->getRowDimension(4)->setRowHeight(28);
$headerStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND]],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true,
    ],
    'borders'   => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . C_WHITE]],
    ],
];

foreach ($headers as $i => $label)
{
    $coord = Coordinate::stringFromColumnIndex($i + 1) . '4';
    $ws->setCellValue($coord, $label);
    $ws->getStyle($coord)->applyFromArray($headerStyle);
}

// ── Baris 5+: Data ───────────────────────────────────────────────
foreach ($rows as $i => $row)
{
    $excelRow = $i + 5;
    $ws->getRowDimension($excelRow)->setRowHeight(18);
    $isAlt = ($i % 2 === 1);

    $tglLahir   = ($row['tgl_lahir'] ?? '') !== ''
        ? date('d/m/Y', strtotime($row['tgl_lahir']))
        : '';
    $statusVal  = $row['status'] ?? '';
    $statusTeks = $statusLabel[$statusVal] ?? ucfirst($statusVal);

    $rowValues = [
        $i + 1,                               // No
        $row['nama']               ?? '',     // Nama
        $row['nik']                ?? '',     // NIK
        $row['nuptk']              ?? '',     // NUPTK
        $row['jenis_kelamin']      ?? '',     // L/P
        $row['tempat_lahir']       ?? '',     // Tempat Lahir
        $tglLahir,                            // Tgl Lahir
        $row['nama_ibu']           ?? '',     // Nama Ibu
        $row['status_pegawai']     ?? '',     // Status Pegawai
        $row['jenis_gtk']          ?? '',     // Jenis GTK
        $row['jabatan']            ?? '',     // Jabatan
        $row['alamat']             ?? '',     // Alamat
        $row['tahun_masuk']        ?? '',     // Thn Masuk
        $row['tahun_keluar']       ?? '',     // Thn Keluar
        $statusTeks,                          // Status
        $row['status_keterangan']  ?? '',     // Keterangan Status
    ];

    foreach ($rowValues as $colIdx => $value)
    {
        $colNum = $colIdx + 1;
        $coord  = Coordinate::stringFromColumnIndex($colNum) . $excelRow;
        $isText   = in_array($colIdx, $textColIndexes, true);
        $isCenter = in_array($colIdx, $centerColIndexes, true);

        // Kolom status: warna khusus
        if ($colIdx === 14)
        {
            $ws->setCellValue($coord, $value);
            $ws->getStyle($coord)->applyFromArray(statusStyle($statusVal));
            continue;
        }

        // Kolom NIK / NUPTK: force string agar leading zero tidak hilang
        if ($isText)
        {
            $ws->getCell($coord)->setValueExplicit((string) $value, DataType::TYPE_STRING);
        }
        else
        {
            $ws->setCellValue($coord, $value);
        }

        $ws->getStyle($coord)->applyFromArray(dataStyle($isCenter, $isAlt));
    }
}

// ── Baris footer: Total ───────────────────────────────────────────
$footerRow = $total + 5;
$ws->getRowDimension($footerRow)->setRowHeight(20);

$mergeEnd = Coordinate::stringFromColumnIndex($totalCols - 1);
$ws->mergeCells('A' . $footerRow . ':' . $mergeEnd . $footerRow);
$ws->setCellValue('A' . $footerRow, 'Total Guru');

$footerStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND_BG]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
    'borders'   => [
        'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF' . C_BRAND]],
        'bottom' => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF' . C_BORDER]],
        'left'   => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF' . C_BORDER]],
        'right'  => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF' . C_BORDER]],
    ],
];
$footerNumStyle = array_merge($footerStyle, [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$ws->getStyle('A' . $footerRow . ':' . $mergeEnd . $footerRow)->applyFromArray($footerStyle);

$totalCoord = $lastCol . $footerRow;
$ws->setCellValue($totalCoord, $total);
$ws->getStyle($totalCoord)->applyFromArray($footerNumStyle);

// ── Freeze pane ───────────────────────────────────────────────────
$ws->freezePane('A5');

// ── Output ────────────────────────────────────────────────────────
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
