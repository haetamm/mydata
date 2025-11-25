<?php
session_start();
include("connection.php"); // $link = PDO

// === CEK LOGIN & VALIDASI ===
$jenjangUp = strtoupper($_GET['jenjang'] ?? '');
authorizeStudentData($jenjangUp);

$jenjang = strtolower($_GET['jenjang'] ?? '');
$nis     = trim($_GET['nis'] ?? '');

if (!in_array($jenjang, ['sd', 'smp', 'sma']) || $nis === '')
{
    echo "<script>alert('Parameter tidak valid!'); window.history.back();</script>";
    exit;
}

$jenjangUpper = strtoupper($jenjang);
$tabel = "siswa_" . $jenjang;

try
{
    $sql = "SELECT s.*, a.nama_agama, CONCAT(k.nama_kelas, ' ') AS kelas_lengkap,
            mp_ayah.nama_pekerjaan AS pekerjaan_ayah, mp_ibu.nama_pekerjaan AS pekerjaan_ibu,
            mp_wali.nama_pekerjaan AS pekerjaan_wali, ms.nama_semester AS semester,
            tp.tahun AS tahun_pelajaran
            FROM $tabel s
            LEFT JOIN master_agama a ON s.id_agama = a.id_agama
            LEFT JOIN master_kelas k ON s.id_kelas = k.id_kelas
            LEFT JOIN master_semester ms ON s.id_semester = ms.id_semester
            LEFT JOIN master_tahun_pelajaran tp ON s.id_tahun_pelajaran = tp.id_tahun
            LEFT JOIN master_pekerjaan mp_ayah ON s.ayah_pekerjaan = mp_ayah.id_pekerjaan
            LEFT JOIN master_pekerjaan mp_ibu ON s.ibu_pekerjaan = mp_ibu.id_pekerjaan
            LEFT JOIN master_pekerjaan mp_wali ON s.wali_pekerjaan = mp_wali.id_pekerjaan
            WHERE s.nis = ? LIMIT 1";

    $stmt = $link->prepare($sql);
    $stmt->execute([$nis]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa)
    {
        echo "<script>alert('Siswa tidak ditemukan!'); window.history.back();</script>";
        exit;
    }
}
catch (Exception $e)
{
    echo "<script>alert('Gagal ambil data!'); window.history.back();</script>";
    exit;
}
?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <?php include("layout/sidebar.php") ?>

        <div class="overflow-auto no-scrollbar">
            <div class="flex flex-col">

                <!-- Tombol Aksi -->
                <div class="mb-20 xs:mb-5 lg:mt-5 order-2 lg:order-1 flex gap-4 w-full px-2 lg:px-10 print:hidden justify-end">
                    <button onclick="history.back()" class="px-6 py-2 bg-gray-400 text-white rounded border border-gray-500 hover:bg-gray-500 transition">
                        Kembali
                    </button>
                    <button id="downloadPdf" class="px-6 py-2 bg-green-600 text-white rounded border border-green-700 hover:bg-green-700 transition">
                        Download PDF
                    </button>
                </div>

                <!-- KONTEN UTAMA (INI YANG JADI PDF) -->
                <div class="lg:pt-5 order-1 lg:order-2 flex-1 px-3 md:px-6 w-full lg:max-w-[800px] mx-auto shadow-lg my-5 lg:px-10 lg:pb-10 bg-white">

                    <!-- Header -->
                    <div class="mb-8 border-b-2 border-gray-800 pb-4">
                        <h1 class="text-2xl font-bold text-gray-900 text-center uppercase">Detail Data Siswa</h1>
                        <div class="text-center text-gray-700 mt-2">
                            <span class="font-semibold"><?= $jenjangUpper ?></span> |
                            NIS: <span class="font-mono"><?= htmlspecialchars($siswa['nis']) ?></span> |
                            Tahun: <?= htmlspecialchars($siswa['tahun_pelajaran'] ?? '-') ?> - Semester <?= htmlspecialchars($siswa['semester'] ?? '-') ?>
                        </div>
                    </div>

                    <!-- INFORMASI PRIBADI -->
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">INFORMASI PRIBADI</h2>
                        <div class="grid ml-2.5 md:ml-4 grid-cols-1 gap-y-3 lg:grid-cols-2 text-sm text-gray-800">
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">Nama Lengkap</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['nama']) ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">NIS / NISN</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['nis']) ?> / <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">Tempat, Tgl Lahir</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= $siswa['tgl_lahir'] ? date('d-m-Y', strtotime($siswa['tgl_lahir'])) : '-' ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">Agama</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['nama_agama'] ?? '-') ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">Kelas</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['kelas_lengkap'] ?? '-') ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">NIK</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['nik'] ?? '-') ?></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="font-semibold">Ruang</div>
                                <div class="col-span-2">: <?= htmlspecialchars($siswa['ruang'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- DATA ORANG TUA -->
                    <div class="my-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">DATA ORANG TUA</h2>

                        <div class="grid lg:grid-cols-2">
                            <!-- Ayah -->
                            <div class="mb-4 ml-2.5 md:ml-4">
                                <h3 class="font-semibold text-gray-800 mb-2">Ayah Kandung</h3>
                                <table class="w-full text-sm text-gray-800">
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Nama Ayah</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ayah_nama'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Tahun Lahir</td>
                                        <td class="py-1">: <?= $siswa['ayah_tahun_lahir'] ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Pendidikan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ayah_pendidikan'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Pekerjaan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Penghasilan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ayah_penghasilan'] ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Ibu -->
                            <div class="mb-4 ml-2.5 md:ml-4">
                                <h3 class="font-semibold text-gray-800 mb-2">Ibu Kandung</h3>
                                <table class="w-full text-sm text-gray-800">
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Nama Ibu</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ibu_nama'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Tahun Lahir</td>
                                        <td class="py-1">: <?= $siswa['ibu_tahun_lahir'] ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Pendidikan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ibu_pendidikan'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Pekerjaan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 w-1/3 lg:w-1/3 font-medium">Penghasilan</td>
                                        <td class="py-1">: <?= htmlspecialchars($siswa['ibu_penghasilan'] ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>

                        </div>

                        <!-- Wali -->
                        <div class="mb-4 ml-2.5 md:ml-4">
                            <h3 class="font-semibold text-gray-800 mb-2">Wali</h3>
                            <table class="w-full text-sm text-gray-800">
                                <tr>
                                    <td class="py-1 w-1/3 lg:w-1/6 font-medium">Nama Wali</td>
                                    <td class="py-1">: <?= htmlspecialchars($siswa['wali_nama'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="py-1 w-1/3 lg:w-1/6 font-medium">Tahun Lahir</td>
                                    <td class="py-1">: <?= $siswa['wali_tahun_lahir'] ?? '-' ?></td>
                                </tr>
                                <tr>
                                    <td class="py-1 w-1/3 lg:w-1/6 font-medium">Pendidikan</td>
                                    <td class="py-1">: <?= htmlspecialchars($siswa['wali_pendidikan'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="py-1 w-1/3 lg:w-1/6 font-medium">Pekerjaan</td>
                                    <td class="py-1">: <?= htmlspecialchars($siswa['pekerjaan_wali'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="py-1 w-1/3 lg:w-1/6 font-medium">Penghasilan</td>
                                    <td class="py-1">: <?= htmlspecialchars($siswa['wali_penghasilan'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- ALAMAT -->
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">ALAMAT TEMPAT TINGGAL</h2>
                        <table class="w-full text-sm text-gray-800 ml-2.5 md:ml-4">
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold align-top">Alamat Lengkap</td>
                                <td class="py-1 align-top">: <?= nl2br(htmlspecialchars($siswa['alamat'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold">RT/RW</td>
                                <td class="py-1">: <?= htmlspecialchars($siswa['rt_rw'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold">Dusun</td>
                                <td class="py-1">: <?= htmlspecialchars($siswa['dusun'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold">Kelurahan</td>
                                <td class="py-1">: <?= htmlspecialchars($siswa['kelurahan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold">Kecamatan</td>
                                <td class="py-1">: <?= htmlspecialchars($siswa['kecamatan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-1 w-1/3 lg:w-1/6  font-semibold">Kode Pos</td>
                                <td class="py-1">: <?= htmlspecialchars($siswa['kode_pos'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- LIBRARY PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- SCRIPT PDF - MARGIN ATAS & BAWAH SAMA (15mm) -->
<script>
    document.getElementById('downloadPdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        const element = document.querySelector('.lg\\:max-w-\\[800px\\]');

        document.body.classList.add('html2canvas-fallback');

        html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = 210;
            const pageHeight = 297;
            const margin = 10;
            const usableHeight = pageHeight - (2 * margin);

            const imgWidth = pageWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = margin;

            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= usableHeight;

            while (heightLeft >= 0) {
                position = heightLeft - imgHeight + margin;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= usableHeight;
            }

            document.body.classList.remove('html2canvas-fallback');
            pdf.save('Detail_Siswa_<?= $jenjangUpper ?>_<?= $siswa['nis'] ?>.pdf');
        }).catch(err => {
            document.body.classList.remove('html2canvas-fallback');
            alert('Gagal generate PDF!');
            console.error(err);
        });
    });
</script>


<!-- FIX OKLCH ERROR + WARNA TAILWIND -->
<style>
    .html2canvas-fallback *,
    .html2canvas-fallback {
        --tw-bg-opacity: 1 !important;
        --tw-text-opacity: 1 !important;
    }

    .html2canvas-fallback .bg-gray-800 {
        background-color: #1f2937 !important;
    }

    .html2canvas-fallback .bg-gray-400 {
        background-color: #9ca3af !important;
    }

    .html2canvas-fallback .bg-green-600 {
        background-color: #16a34a !important;
    }

    .html2canvas-fallback .text-gray-900 {
        color: #111827 !important;
    }

    .html2canvas-fallback .text-gray-800 {
        color: #1f2937 !important;
    }

    .html2canvas-fallback .text-gray-700 {
        color: #374151 !important;
    }

    .html2canvas-fallback .border-gray-800 {
        border-color: #1f2937 !important;
    }

    .html2canvas-fallback .border-gray-300 {
        border-color: #d1d5db !important;
    }

    .html2canvas-fallback .print\:hidden {
        display: none !important;
    }
</style>
