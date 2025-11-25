<?php
session_start();
include("connection.php");

$jenjangUp = strtoupper($_GET['jenjang'] ?? '');
allowSuperAdminOnly();

$jenjang = strtolower($_GET['jenjang'] ?? '');
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    echo "<script>alert('Jenjang tidak valid!'); history.back();</script>";
    exit;
}

$tabel = "siswa_" . $jenjang;
$pesan = '';

// Fungsi konversi tanggal Excel
function convertExcelDate($excelDate)
{
    if (empty($excelDate)) return null;

    // Jika sudah format YYYY-MM-DD, langsung return
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $excelDate))
    {
        return $excelDate;
    }

    // Jika berupa angka serial Excel (seperti 42259)
    if (is_numeric($excelDate))
    {
        $excelBaseDate = 25569; // Excel base date (1900-01-01) to Unix timestamp
        $unixTimestamp = ($excelDate - $excelBaseDate) * 86400; // Convert to seconds
        return date('Y-m-d', $unixTimestamp);
    }

    // Coba format tanggal Indonesia (dd/mm/yyyy)
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $excelDate, $matches))
    {
        return $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
    }

    // Coba format tanggal dengan berbagai separator
    $formats = [
        'd-m-Y',
        'd/m/Y',
        'd.m.Y',
        'Y-m-d',
        'Y/m/d',
        'Y.m.d',
        'm-d-Y',
        'm/d/Y',
        'm.d.Y'
    ];

    foreach ($formats as $format)
    {
        $date = DateTime::createFromFormat($format, $excelDate);
        if ($date !== false)
        {
            return $date->format('Y-m-d');
        }
    }

    return null; // Tidak bisa dikonversi
}

// Fungsi untuk membersihkan nilai integer
function cleanIntegerValue($value)
{
    if ($value === '' || $value === null || $value === ' ')
    {
        return null;
    }
    if (is_numeric($value))
    {
        return (int)$value;
    }
    return null;
}

// ====================== PROSES UPLOAD ======================
if (isset($_POST['upload']))
{
    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] != 0)
    {
        $error_code = $_FILES['file_excel']['error'] ?? 'unknown';
        $error_messages = [
            0 => 'Tidak ada error',
            1 => 'File terlalu besar (upload_max_filesize)',
            2 => 'File terlalu besar (MAX_FILE_SIZE)',
            3 => 'File hanya terupload sebagian',
            4 => 'Tidak ada file yang diupload',
            6 => 'Missing temporary folder',
            7 => 'Failed to write to disk',
            8 => 'PHP extension stopped the upload'
        ];
        $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Error upload: ' . ($error_messages[$error_code] ?? 'Unknown error') . '</div>';
    }
    else
    {
        $file = $_FILES['file_excel']['tmp_name'];
        $nama_asli = $_FILES['file_excel']['name'];
        $file_size = $_FILES['file_excel']['size'];

        // Validasi ekstensi
        $ext = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        if ($ext !== 'xlsx')
        {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Hanya file .xlsx yang diizinkan! File Anda: .' . $ext . '</div>';
        }
        // Validasi file exists dan size
        elseif (!file_exists($file) || $file_size == 0)
        {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">File upload tidak valid atau kosong!</div>';
        }
        // Validasi size maksimal (10MB)
        elseif ($file_size > 10 * 1024 * 1024)
        {
            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">File terlalu besar! Maksimal 10MB.</div>';
        }
        else
        {
            // Cek signature file
            $file_handle = fopen($file, 'rb');
            $file_signature = bin2hex(fread($file_handle, 4));
            fclose($file_handle);

            $valid_signatures = ['504b0304', '504b0506', '504b0708'];

            if (!in_array($file_signature, $valid_signatures))
            {
                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">File bukan format Excel yang valid! Signature: ' . $file_signature . '</div>';
            }
            else
            {
                // Folder sementara
                $temp_dir = sys_get_temp_dir() . '/excel_upload_' . uniqid();
                if (!mkdir($temp_dir, 0777, true))
                {
                    $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Gagal membuat direktori sementara!</div>';
                }
                else
                {
                    $zip_path = $temp_dir . '/file.zip';

                    if (!copy($file, $zip_path))
                    {
                        $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Gagal menyalin file!</div>';
                    }
                    else
                    {
                        $zip = new ZipArchive();
                        $zip_result = $zip->open($zip_path);

                        if ($zip_result !== TRUE)
                        {
                            $zip_errors = [
                                ZipArchive::ER_EXISTS => 'File already exists',
                                ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                                ZipArchive::ER_INVAL => 'Invalid argument',
                                ZipArchive::ER_MEMORY => 'Malloc failure',
                                ZipArchive::ER_NOENT => 'No such file',
                                ZipArchive::ER_NOZIP => 'Not a zip archive',
                                ZipArchive::ER_OPEN => "Can't open file",
                                ZipArchive::ER_READ => 'Read error',
                                ZipArchive::ER_SEEK => 'Seek error'
                            ];

                            $error_msg = $zip_errors[$zip_result] ?? 'Unknown error (' . $zip_result . ')';
                            $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Gagal membuka file Excel: ' . $error_msg . '</div>';
                        }
                        else
                        {
                            // Ekstrak file
                            if (!$zip->extractTo($temp_dir))
                            {
                                $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Gagal mengekstrak file Excel!</div>';
                                $zip->close();
                            }
                            else
                            {
                                $zip->close();

                                $xmlPath = $temp_dir . '/xl/worksheets/sheet1.xml';
                                if (!file_exists($xmlPath))
                                {
                                    $pesan = '<div class="bg-red-100 text-red-700 p-4 rounded mb-4">Struktur file Excel tidak valid! Pastikan file adalah Excel 2007+ (.xlsx)</div>';
                                }
                                else
                                {
                                    // Process sharedStrings dan data Excel
                                    $sharedStrings = [];
                                    $ssPath = $temp_dir . '/xl/sharedStrings.xml';
                                    if (file_exists($ssPath))
                                    {
                                        $ssXml = simplexml_load_file($ssPath);
                                        if ($ssXml)
                                        {
                                            foreach ($ssXml->si as $si)
                                            {
                                                $sharedStrings[] = (string)$si->t;
                                            }
                                        }
                                    }

                                    // Baca data sheet
                                    $xml = simplexml_load_file($xmlPath);
                                    $data_excel = [];

                                    if ($xml && $xml->sheetData)
                                    {
                                        foreach ($xml->sheetData->row as $row)
                                        {
                                            $cellData = [];
                                            foreach ($row->c as $cell)
                                            {
                                                $val = '';
                                                $v = (string)$cell->v;
                                                $t = (string)($cell['t'] ?? '');

                                                if ($t === 's')
                                                {
                                                    $val = $sharedStrings[(int)$v] ?? '';
                                                }
                                                else
                                                {
                                                    $val = $v;
                                                }
                                                $cellData[] = $val;
                                            }
                                            if (!empty(array_filter($cellData)))
                                            {
                                                $data_excel[] = $cellData;
                                            }
                                        }
                                    }

                                    // Hapus header
                                    if (!empty($data_excel)) array_shift($data_excel);

                                    // Proses Insert ke Database
                                    $sukses = $gagal = 0;
                                    $error_log = [];

                                    $pdo = $link;
                                    $pdo->beginTransaction();

                                    // Sesuaikan dengan struktur database yang benar
                                    $sql = "INSERT INTO $tabel (
                                        nama, nis, nisn, nik, tempat_lahir, tgl_lahir,
                                        id_agama, id_kelas, ruang, id_tahun_pelajaran, id_semester,
                                        alamat, rt_rw, dusun, kelurahan, kecamatan, kode_pos,
                                        ayah_nama, ayah_tahun_lahir, ayah_pendidikan, ayah_pekerjaan, ayah_penghasilan, ayah_nik,
                                        ibu_nama, ibu_tahun_lahir, ibu_pendidikan, ibu_pekerjaan, ibu_penghasilan, ibu_nik,
                                        wali_nama, wali_tahun_lahir, wali_pendidikan, wali_pekerjaan, wali_penghasilan, wali_nik
                                    ) VALUES (
                                        ?, ?, ?, ?, ?, ?,
                                        ?, ?, ?, ?, ?,
                                        ?, ?, ?, ?, ?, ?,
                                        ?, ?, ?, ?, ?, ?,
                                        ?, ?, ?, ?, ?, ?,
                                        ?, ?, ?, ?, ?, ?
                                    )";

                                    $stmt = $pdo->prepare($sql);

                                    if (!$stmt)
                                    {
                                        $error_log[] = "Gagal mempersiapkan statement SQL: " . implode(", ", $pdo->errorInfo());
                                    }
                                    else
                                    {
                                        foreach ($data_excel as $idx => $row)
                                        {
                                            $row_number = $idx + 2;

                                            try
                                            {
                                                if (empty($row[1]) || empty($row[2]))
                                                {
                                                    $error_log[] = "Baris $row_number: Nama atau NIS tidak boleh kosong";
                                                    $gagal++;
                                                    continue;
                                                }

                                                // Konversi tanggal lahir (kolom 6)
                                                $tgl_lahir = convertExcelDate($row[6] ?? '');
                                                if (!empty($row[6]) && $tgl_lahir === null)
                                                {
                                                    $error_log[] = "Baris $row_number: Format tanggal lahir tidak valid: " . $row[6];
                                                    $gagal++;
                                                    continue;
                                                }

                                                // Konversi tahun lahir ayah (kolom 19) - hanya ambil tahunnya
                                                $ayah_tahun_lahir = null;
                                                if (!empty($row[19]))
                                                {
                                                    $converted_date = convertExcelDate($row[19]);
                                                    if ($converted_date !== null)
                                                    {
                                                        $ayah_tahun_lahir = date('Y', strtotime($converted_date));
                                                    }
                                                    else if (is_numeric($row[19]) && strlen($row[19]) == 4)
                                                    {
                                                        $ayah_tahun_lahir = $row[19]; // Sudah format tahun
                                                    }
                                                    else
                                                    {
                                                        $ayah_tahun_lahir = $row[19]; // Biarkan as-is, mungkin sudah tahun
                                                    }
                                                }

                                                // Konversi tahun lahir ibu (kolom 25) - hanya ambil tahunnya
                                                $ibu_tahun_lahir = null;
                                                if (!empty($row[25]))
                                                {
                                                    $converted_date = convertExcelDate($row[25]);
                                                    if ($converted_date !== null)
                                                    {
                                                        $ibu_tahun_lahir = date('Y', strtotime($converted_date));
                                                    }
                                                    else if (is_numeric($row[25]) && strlen($row[25]) == 4)
                                                    {
                                                        $ibu_tahun_lahir = $row[25]; // Sudah format tahun
                                                    }
                                                    else
                                                    {
                                                        $ibu_tahun_lahir = $row[25]; // Biarkan as-is, mungkin sudah tahun
                                                    }
                                                }

                                                // Konversi tahun lahir wali (kolom 31) - hanya ambil tahunnya
                                                $wali_tahun_lahir = null;
                                                if (!empty($row[31]))
                                                {
                                                    $converted_date = convertExcelDate($row[31]);
                                                    if ($converted_date !== null)
                                                    {
                                                        $wali_tahun_lahir = date('Y', strtotime($converted_date));
                                                    }
                                                    else if (is_numeric($row[31]) && strlen($row[31]) == 4)
                                                    {
                                                        $wali_tahun_lahir = $row[31]; // Sudah format tahun
                                                    }
                                                    else
                                                    {
                                                        $wali_tahun_lahir = $row[31]; // Biarkan as-is, mungkin sudah tahun
                                                    }
                                                }

                                                // Bersihkan nilai integer untuk foreign keys
                                                $id_agama = cleanIntegerValue($row[7] ?? '');
                                                $id_kelas = cleanIntegerValue($row[8] ?? '');
                                                $id_semester = cleanIntegerValue($row[10] ?? '');
                                                $id_tahun_pelajaran = cleanIntegerValue($row[11] ?? '');
                                                $ayah_pekerjaan = cleanIntegerValue($row[21] ?? '');
                                                $ibu_pekerjaan = cleanIntegerValue($row[27] ?? '');
                                                $wali_pekerjaan = cleanIntegerValue($row[33] ?? '');

                                                $result = $stmt->execute([
                                                    // Data Siswa (kolom 1-17)
                                                    $row[1] ?? null,  // nama
                                                    $row[2] ?? null,  // nis
                                                    $row[3] ?? null,  // nisn
                                                    $row[4] ?? null,  // nik
                                                    $row[5] ?? null,  // tempat_lahir
                                                    $tgl_lahir,       // tgl_lahir (kolom 6 - sudah dikonversi)
                                                    $id_agama,        // id_agama (sudah dibersihkan)
                                                    $id_kelas,        // id_kelas (sudah dibersihkan)
                                                    $row[9] ?? null,  // ruang
                                                    $id_tahun_pelajaran, // id_tahun_pelajaran (kolom 11 - sudah dibersihkan)
                                                    $id_semester,     // id_semester (kolom 10 - sudah dibersihkan)
                                                    // Alamat (kolom 12-17)
                                                    $row[12] ?? null, // alamat
                                                    $row[13] ?? null, // rt_rw
                                                    $row[14] ?? null, // dusun
                                                    $row[15] ?? null, // kelurahan
                                                    $row[16] ?? null, // kecamatan
                                                    $row[17] ?? null, // kode_pos
                                                    // Ayah (kolom 18-23)
                                                    $row[18] ?? null, // ayah_nama
                                                    $ayah_tahun_lahir, // ayah_tahun_lahir (kolom 19 - sudah dikonversi)
                                                    $row[20] ?? null, // ayah_pendidikan
                                                    $ayah_pekerjaan,  // ayah_pekerjaan (sudah dibersihkan)
                                                    $row[22] ?? null, // ayah_penghasilan
                                                    $row[23] ?? null, // ayah_nik
                                                    // Ibu (kolom 24-29)
                                                    $row[24] ?? null, // ibu_nama
                                                    $ibu_tahun_lahir,  // ibu_tahun_lahir (kolom 25 - sudah dikonversi)
                                                    $row[26] ?? null, // ibu_pendidikan
                                                    $ibu_pekerjaan,   // ibu_pekerjaan (sudah dibersihkan)
                                                    $row[28] ?? null, // ibu_penghasilan
                                                    $row[29] ?? null, // ibu_nik
                                                    // Wali (kolom 30-35)
                                                    $row[30] ?? null, // wali_nama
                                                    $wali_tahun_lahir, // wali_tahun_lahir (kolom 31 - sudah dikonversi)
                                                    $row[32] ?? null, // wali_pendidikan
                                                    $wali_pekerjaan,  // wali_pekerjaan (sudah dibersihkan)
                                                    $row[34] ?? null, // wali_penghasilan
                                                    $row[35] ?? null, // wali_nik
                                                ]);

                                                if ($result)
                                                {
                                                    $sukses++;
                                                }
                                                else
                                                {
                                                    $gagal++;
                                                    $errorInfo = $stmt->errorInfo();
                                                    $error_log[] = "Baris $row_number: Gagal insert - " . $errorInfo[2];
                                                }
                                            }
                                            catch (PDOException $e)
                                            {
                                                $gagal++;
                                                $msg = $e->getMessage();

                                                if (stripos($msg, 'Duplicate') !== false)
                                                {
                                                    if (preg_match_all("/'([^']+)'/", $msg, $m))
                                                    {
                                                        $rawKey = strtolower(end($m[1]));
                                                        $parts = explode('.', $rawKey);
                                                        $last = end($parts);
                                                        $clean = preg_replace("/(_unique|_key|_idx)$/", "", $last);

                                                        if ($clean === 'nisn')
                                                            $error_log[] = "Baris $row_number: <strong>NISN {$row[3]}</strong> sudah digunakan!";
                                                        elseif ($clean === 'nis')
                                                            $error_log[] = "Baris $row_number: <strong>NIS {$row[2]}</strong> sudah digunakan!";
                                                        else
                                                            $error_log[] = "Baris $row_number: Duplicate entry - " . $msg;
                                                    }
                                                    else
                                                    {
                                                        $error_log[] = "Baris $row_number: Data duplikat - " . $msg;
                                                    }
                                                }
                                                else
                                                {
                                                    $error_log[] = "Baris $row_number: Database error - " . $msg;
                                                }
                                            }
                                            catch (Exception $e)
                                            {
                                                $gagal++;
                                                $error_log[] = "Baris $row_number: Exception - " . $e->getMessage();
                                            }
                                        }
                                    }

                                    if ($gagal == 0 && $sukses > 0)
                                    {
                                        $pdo->commit();
                                        $pesan = "<div class='bg-green-100 text-green-700 p-4 rounded mb-4'>Berhasil mengimpor <strong>$sukses</strong> data siswa.</div>";
                                    }
                                    else
                                    {
                                        $pdo->rollBack();
                                        $total_data = $sukses + $gagal;
                                        $pesan = "<div class='bg-yellow-100 text-yellow-800 p-4 rounded mb-4'>
                                                    <strong>Hasil Import:</strong><br>
                                                    Total Data: $total_data<br>
                                                    Berhasil: <strong class='text-green-600'>$sukses</strong><br>
                                                    Gagal: <strong class='text-red-600'>$gagal</strong><br><br>
                                                    <strong>Detail Error:</strong><br>" .
                                            implode('<br>', array_slice($error_log, 0, 10)) .
                                            (count($error_log) > 10 ? "<br>... dan " . (count($error_log) - 10) . " error lainnya" : "") .
                                            "</div>";
                                    }
                                }
                            }
                        }

                        // Cleanup
                        try
                        {
                            $files = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::CHILD_FIRST
                            );
                            foreach ($files as $fileinfo)
                            {
                                $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
                            }
                            rmdir($temp_dir);
                        }
                        catch (Exception $e)
                        {
                            // Ignore cleanup errors
                        }
                    }
                }
            }
        }
    }
}

$intructions = [
    "Jangan mengubah urutan atau nama kolom di template",
    "Kolom Nomor dikosongkan",
    "Tanggal lahir: <strong>DD/MM/YYYY</strong> (contoh: 11/09/2015)",
    "Id Agama, Id Semester, Id Tahun Pelajaran, Id Kelas, dan Id Pekerjaan harus persis sama dengan data master di sistem, bisa dilihat di sheet2 pada template",
    "<strong>Nama dan NIS tidak boleh kosong</strong>",
    "<strong>NIS dan NISN harus unik</strong> - tidak boleh ada yang sama",
]
?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <?php include("layout/sidebar.php") ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class="w-full mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8">

                        <h1 class="text-2xl font-bold text-gray-800 mb-2">
                            Upload Data Siswa <?= strtoupper($jenjang) ?>
                        </h1>
                        <p class="text-gray-600 mb-6">Gunakan template resmi agar proses import berjalan lancar.</p>

                        <?php if ($pesan) echo $pesan; ?>

                        <div class="grid md:grid-cols-2 gap-8 mb-8">
                            <div class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center bg-blue-50">
                                <p class="font-semibold text-lg mb-4">1. Download Template</p>
                                <a href="download-template-siswa.php?jenjang=<?= $jenjang ?>"
                                    class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                                    Download Template Excel
                                </a>
                            </div>

                            <div class="border-2 border-dashed border-green-300 rounded-xl p-8 bg-green-50">
                                <p class="font-semibold text-lg mb-4">2. Upload File yang Sudah Diisi</p>
                                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                                    <input type="file" name="file_excel" accept=".xlsx" required
                                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-600 file:text-white hover:file:bg-green-700">
                                    <button type="submit" name="upload"
                                        class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition font-semibold">
                                        Upload & Simpan Data
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-5 text-sm">
                            <p class="font-bold text-yellow-800 mb-2">Petunjuk Penting:</p>
                            <ul class="list-disc list-inside text-yellow-700 space-y-1">
                                <?php
                                foreach ($intructions as $item) :
                                ?>
                                    <li><?= $item ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
