<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\SiswaService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

session_start();

// Validasi jenjang
$jenjangRaw = strtolower(trim($_GET['jenjang'] ?? 'sd'));
$jenjangMap = [
    'sd'  => ['tabel' => 'siswa_sd',  'slug' => 'siswa-sd',  'label' => 'SD'],
    'smp' => ['tabel' => 'siswa_smp', 'slug' => 'siswa-smp', 'label' => 'SMP'],
    'sma' => ['tabel' => 'siswa_sma', 'slug' => 'siswa-sma', 'label' => 'SMA'],
];

if (!isset($jenjangMap[$jenjangRaw]))
{
    http_response_code(400);
    exit('Jenjang tidak valid.');
}

$jenjang = $jenjangMap[$jenjangRaw];

// Guard─
[$menus, $permMap] = PermissionMiddleware::handle($pdo, $jenjang['slug'], 'export');

// Filter
$filter = [
    'nama'     => trim($_GET['nama']       ?? ''),
    'id_kelas' => (int) ($_GET['id_kelas'] ?? 0),
    'status'   => trim($_GET['status']     ?? ''),
];

// Ambil data
$service = new SiswaService($pdo);
$rows    = $service->getForExport($jenjang['tabel'], $filter);
$total   = count($rows);

// Label status
$statusLabel = [
    'aktif'       => 'Aktif',
    'lulus'       => 'Lulus',
    'pindah'      => 'Pindah',
    'keluar'      => 'Keluar',
    'dikeluarkan' => 'Dikeluarkan',
];

// Sub-judul─
$filterInfo = ['Dicetak: ' . date('d/m/Y H:i')];
if ($filter['nama']    !== '') $filterInfo[] = 'Pencarian: ' . $filter['nama'];
if ($filter['id_kelas'] >  0)  $filterInfo[] = 'Kelas ID: '  . $filter['id_kelas'];
if ($filter['status']  !== '') $filterInfo[] = 'Status: '    . ucfirst($filter['status']);
$subJudul = implode('   |   ', $filterInfo);

// Nama file─
$suffix  = ($filter['status']   !== '') ? '_' . $filter['status']          : '';
$suffix .= ($filter['id_kelas']  >  0)  ? '_kelas' . $filter['id_kelas'] : '';
$filename = 'Data_Siswa_' . $jenjang['label'] . $suffix . '_' . date('Ymd_His') . '.xlsx';

// Helpers─
function rupiah(mixed $v): string
{
    if ($v === null || $v === '') return '-';
    return 'Rp ' . number_format((float) $v, 0, ',', '.');
}

//─ Warna─
const C_BRAND     = '4D58EF';
const C_BRAND_BG  = 'EEF0FF';
const C_WHITE     = 'FFFFFF';
const C_BORDER    = 'BFBFBF';
const C_ALT       = 'EEF0FF';
const C_SUBTITLE  = '666666';

//─ Style builders─

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

function dataStyle(bool $center = false, bool $alt = false, bool $text = false): array
{
    $style = array_merge_recursive(
        [
            'font' => ['name' => 'Arial', 'size' => 10],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF' . ($alt ? C_ALT : C_WHITE)],
            ],
        ],
        borderAll()
    );

    if ($center)
    {
        $style['alignment'] = ['horizontal' => Alignment::HORIZONTAL_CENTER];
    }
    if ($text)
    {
        $style['numberFormat'] = ['formatCode' => NumberFormat::FORMAT_TEXT];
    }

    return $style;
}

function statusStyle(string $status): array
{
    $color = match ($status)
    {
        'aktif'                          => '166534',
        'lulus'                          => '1D4ED8',
        'pindah', 'keluar', 'dikeluarkan' => '991B1B',
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

//─ Header kolom─
$headers = [
    'No',
    'Nama Siswa',
    'NIS',
    'NISN',
    'NIK',
    'Tempat Lahir',
    'Tgl Lahir',
    'Agama',
    'Kelas',
    'Ruang',
    'Thn Masuk',
    'Thn Keluar',
    'Status',
    'Keterangan Status',
    'Alamat',
    'RT/RW',
    'Dusun',
    'Kelurahan',
    'Kecamatan',
    'Kode Pos',
    'Nama Ayah',
    'Thn Lahir Ayah',
    'Pendidikan Ayah',
    'Pekerjaan Ayah',
    'Penghasilan Ayah',
    'NIK Ayah',
    'Nama Ibu',
    'Thn Lahir Ibu',
    'Pendidikan Ibu',
    'Pekerjaan Ibu',
    'Penghasilan Ibu',
    'NIK Ibu',
    'Nama Wali',
    'Thn Lahir Wali',
    'Pendidikan Wali',
    'Pekerjaan Wali',
    'Penghasilan Wali',
    'NIK Wali',
];

// Lebar kolom (karakter)
$colWidths = [
    4,   // No
    22,  // Nama
    11,  // NIS
    13,  // NISN
    18,  // NIK
    14,  // Tempat Lahir
    11,  // Tgl Lahir
    10,  // Agama
    8,   // Kelas
    6,   // Ruang
    8,   // Thn Masuk
    8,   // Thn Keluar
    11,  // Status
    21,  // Ket Status
    22,  // Alamat
    8,   // RT/RW
    11,  // Dusun
    13,  // Kelurahan
    13,  // Kecamatan
    9,   // Kode Pos
    20,  // Ayah Nama
    10,  // Ayah Thn Lahir
    13,  // Ayah Pendidikan
    14,  // Ayah Pekerjaan
    16,  // Ayah Penghasilan
    18,  // Ayah NIK
    20,  // Ibu Nama
    10,  // Ibu Thn Lahir
    13,  // Ibu Pendidikan
    14,  // Ibu Pekerjaan
    16,  // Ibu Penghasilan
    18,  // Ibu NIK
    20,  // Wali Nama
    10,  // Wali Thn Lahir
    13,  // Wali Pendidikan
    14,  // Wali Pekerjaan
    16,  // Wali Penghasilan
    18,  // Wali NIK
];

$totalCols = count($headers); // 38
$lastCol   = Coordinate::stringFromColumnIndex($totalCols);

//─ Build Spreadsheet
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Sistem Akademik')
    ->setTitle('Data Siswa ' . $jenjang['label']);

$ws = $spreadsheet->getActiveSheet();
$ws->setTitle('Data Siswa ' . $jenjang['label']);

// Set column widths
foreach ($colWidths as $i => $w)
{
    $ws->getColumnDimensionByColumn($i + 1)->setWidth($w);
}

// Baris 1: Judul
$ws->mergeCells('A1:' . $lastCol . '1');
$ws->setCellValue('A1', 'DATA SISWA ' . strtoupper($jenjang['label']));
$ws->getRowDimension(1)->setRowHeight(30);
$ws->getStyle('A1')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => ['argb' => 'FF' . C_BRAND]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// Baris 2: Sub-judul
$ws->mergeCells('A2:' . $lastCol . '2');
$ws->setCellValue('A2', $subJudul);
$ws->getRowDimension(2)->setRowHeight(16);
$ws->getStyle('A2')->applyFromArray([
    'font'      => ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => ['argb' => 'FF' . C_SUBTITLE]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Baris 3: Spasi
$ws->getRowDimension(3)->setRowHeight(6);

// Baris 4: Header─
$ws->getRowDimension(4)->setRowHeight(28);
$headerStyle = [
    'font'      => ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => ['argb' => 'FF' . C_WHITE]],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . C_BRAND]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
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

// Baris 5+: Data─
// Kolom yang pakai format Text (0-indexed): NIS(2), NISN(3), NIK(4), NIK Ayah(25), NIK Ibu(31), NIK Wali(37)
$textColIndexes = [2, 3, 4, 25, 31, 37];

// Kolom yang center (0-indexed)
$centerColIndexes = [0, 6, 7, 8, 9, 10, 11, 12, 15, 19, 21, 27, 33];

foreach ($rows as $i => $row)
{
    $excelRow = $i + 5; // offset: 3 baris header + 1 spasi + 1 header kolom
    $ws->getRowDimension($excelRow)->setRowHeight(18);
    $isAlt = ($i % 2 === 1);

    $tglLahir   = ($row['tgl_lahir'] ?? '') !== '' ? date('d/m/Y', strtotime($row['tgl_lahir'])) : '';
    $statusVal  = $row['status'] ?? '';
    $statusTeks = $statusLabel[$statusVal] ?? ucfirst($statusVal);

    $rowValues = [
        $i + 1,                                       // No
        $row['nama']               ?? '',             // Nama
        $row['nis']                ?? '',             // NIS
        $row['nisn']               ?? '',             // NISN
        $row['nik']                ?? '',             // NIK
        $row['tempat_lahir']       ?? '',             // Tempat Lahir
        $tglLahir,                                    // Tgl Lahir
        $row['nama_agama']         ?? '',             // Agama
        $row['nama_kelas']         ?? '',             // Kelas
        $row['ruang']              ?? '',             // Ruang
        $row['tahun_masuk']        ?? '',             // Thn Masuk
        $row['tahun_keluar']       ?? '',             // Thn Keluar
        $statusTeks,                                  // Status
        $row['status_keterangan']  ?? '',             // Ket Status
        $row['alamat']             ?? '',             // Alamat
        $row['rt_rw']              ?? '',             // RT/RW
        $row['dusun']              ?? '',             // Dusun
        $row['kelurahan']          ?? '',             // Kelurahan
        $row['kecamatan']          ?? '',             // Kecamatan
        $row['kode_pos']           ?? '',             // Kode Pos
        $row['ayah_nama']          ?? '',             // Nama Ayah
        $row['ayah_tahun_lahir']   ?? '',             // Thn Lahir Ayah
        $row['ayah_pendidikan']    ?? '',             // Pendidikan Ayah
        $row['ayah_nama_pekerjaan'] ?? '',            // Pekerjaan Ayah
        rupiah($row['ayah_penghasilan'] ?? ''),       // Penghasilan Ayah
        $row['ayah_nik']           ?? '',             // NIK Ayah
        $row['ibu_nama']           ?? '',             // Nama Ibu
        $row['ibu_tahun_lahir']    ?? '',             // Thn Lahir Ibu
        $row['ibu_pendidikan']     ?? '',             // Pendidikan Ibu
        $row['ibu_nama_pekerjaan'] ?? '',             // Pekerjaan Ibu
        rupiah($row['ibu_penghasilan'] ?? ''),        // Penghasilan Ibu
        $row['ibu_nik']            ?? '',             // NIK Ibu
        $row['wali_nama']          ?? '',             // Nama Wali
        $row['wali_tahun_lahir']   ?? '',             // Thn Lahir Wali
        $row['wali_pendidikan']    ?? '',             // Pendidikan Wali
        $row['wali_nama_pekerjaan'] ?? '',            // Pekerjaan Wali
        rupiah($row['wali_penghasilan'] ?? ''),       // Penghasilan Wali
        $row['wali_nik']           ?? '',             // NIK Wali
    ];

    foreach ($rowValues as $colIdx => $value)
    {
        $colNum = $colIdx + 1;
        $coord  = Coordinate::stringFromColumnIndex($colNum) . $excelRow;
        $isText   = in_array($colIdx, $textColIndexes, true);
        $isCenter = in_array($colIdx, $centerColIndexes, true);

        // Kolom status punya style warna khusus
        if ($colIdx === 12)
        {
            $ws->setCellValue($coord, $value);
            $ws->getStyle($coord)->applyFromArray(statusStyle($statusVal));
            continue;
        }

        // Kolom text (NIK dll) — force string agar leading zero tidak hilang
        if ($isText)
        {
            $ws->getCell($coord)->setValueExplicit((string) $value, DataType::TYPE_STRING);
        }
        else
        {
            $ws->setCellValue($coord, $value);
        }

        $ws->getStyle($coord)->applyFromArray(dataStyle($isCenter, $isAlt, $isText));
    }
}

// Baris footer: Total─
$footerRow = $total + 5;
$ws->getRowDimension($footerRow)->setRowHeight(20);

// Merge A sampai kolom ke-37 (satu sebelum terakhir)
$mergeEnd = Coordinate::stringFromColumnIndex($totalCols - 1);
$ws->mergeCells('A' . $footerRow . ':' . $mergeEnd . $footerRow);
$ws->setCellValue('A' . $footerRow, 'Total Siswa');

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

// Freeze pane di baris data─
$ws->freezePane('A5');

// Output
if (ob_get_length()) ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
