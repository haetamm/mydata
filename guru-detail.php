<?php
session_start();
include("connection.php"); // $link = PDO

// === CEK LOGIN & SUPER ADMIN (sama seperti sebelumnya) ===
$jenjangUp = strtoupper($_GET['jenjang'] ?? '');
authorizeTeacherData($jenjangUp);


// === AMBIL & VALIDASI PARAMETER ===
$jenjang = strtolower($_GET['jenjang'] ?? '');
$nik     = trim($_GET['nik'] ?? '');

if (!in_array($jenjang, ['sd', 'smp', 'sma']) || $nik === '')
{
    echo "<script>alert('Parameter jenjang atau NIK tidak valid!'); window.history.back();</script>";
    exit;
}

$jenjangUpper = strtoupper($jenjang);
$tabel = "guru_" . $jenjang;

// === AMBIL DATA guru + JOIN UNTUK NAMA YANG LEBIH BAIK ===
try
{
    $sql = "
        SELECT
            g.*
        FROM $tabel g
        WHERE g.nik = ?
        LIMIT 1
    ";

    $stmt = $link->prepare($sql);
    $stmt->execute([$nik]);
    $guru = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$guru)
    {
        echo "<script>alert('Guru dengan NIK $nik tidak ditemukan di jenjang $jenjangUpper!'); window.history.back();</script>";
        exit;
    }
}
catch (PDOException $e)
{
    echo "<script>alert('Gagal mengambil data guru.'); window.history.back();</script>";
    exit;
}
?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <!-- Sidebar -->
        <?php include("layout/sidebar.php") ?>

        <!-- Content -->
        <div class=" overflow-auto no-scrollbar">
            <div class="flex flex-col">
                <!-- Tombol Aksi -->
                <div class="mb-20 xs:mb-5 lg:mt-5 order-2 lg:order-1 flex gap-4 w-full px-2 lg:px-10 print:hidden justify-between items-center xs:justify-end">
                    <button onclick="history.back()" class="px-6 py-2 bg-gray-400 text-white rounded border border-gray-500 hover:bg-gray-500 transition">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Kembali
                    </button>
                    <button id="downloadPdf" class="px-6 py-2 bg-green-600 text-white rounded border border-green-700 hover:bg-green-700 transition">
                        <i class="fa-solid fa-file-pdf-pdf mr-2"></i>
                        Download PDF
                    </button>
                </div>

                <div class="lg:pt-5 order-1 lg:order-2 flex-1 px-3 md:px-6 w-full lg:max-w-[800px] lg:min-h-[1123px] mx-auto shadow-lg my-5 lg:px-10 lg:pb-5 lg:mb-10">

                    <!-- Header -->
                    <div class="mb-8 border-b-2 border-gray-800 pb-4">
                        <h1 class="text-2xl font-bold text-gray-900 text-center uppercase">Detail Data Guru</h1>
                        <div class="text-center text-gray-700 mt-2">
                            <span class="font-semibold"><?= $jenjangUpper ?></span> |
                            NIK: <span class="font-mono"><?= htmlspecialchars($guru['nik']) ?></span>
                        </div>
                    </div>

                    <!-- Informasi Utama -->
                    <div class="mb-6 space-y-8">

                        <!-- INFORMASI PRIBADI -->
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">
                                INFORMASI PRIBADI
                            </h2>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-y-3 ml-2.5 md:ml-4 text-sm text-gray-800">

                                <!-- Nama -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Nama Lengkap</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['nama']) ?></div>
                                </div>

                                <!-- NIK -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">NIK</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['nik']) ?></div>
                                </div>

                                <!-- Tempat/Tgl Lahir -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Tempat, Tgl Lahir</div>
                                    <div class="col-span-2">
                                        : <?= htmlspecialchars($guru['tempat_lahir'] ?? '-') ?>,
                                        <?= $guru['tgl_lahir'] ? date('d-m-Y', strtotime($guru['tgl_lahir'])) : '-' ?>
                                    </div>
                                </div>

                                <!-- Jenis Kelamin -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Jenis Kelamin</div>
                                    <div class="col-span-2">
                                        : <?= $guru['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                    </div>
                                </div>

                                <!-- Alamat -->
                                <div class="grid grid-cols-3 gap-2 items-start">
                                    <div class="font-semibold">Alamat</div>
                                    <div class="col-span-2 whitespace-pre-line">: <?= nl2br(htmlspecialchars($guru['alamat'] ?? '-')) ?></div>
                                </div>

                            </div>
                        </div>


                        <!-- INFORMASI KEPEGAWAIAN -->
                        <div class="mb-8">
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">
                                INFORMASI KEPEGAWAIAN
                            </h2>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-y-3 ml-2.5 md:ml-4 text-sm text-gray-800">

                                <!-- Status Pegawai -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Status Pegawai</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['status_pegawai'] ?? '-') ?></div>
                                </div>

                                <!-- Jenis GTK -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Jenis GTK</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['jenis_gtk'] ?? '-') ?></div>
                                </div>

                                <!-- Jabatan -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Jabatan</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['jabatan'] ?? '-') ?></div>
                                </div>

                                <!-- NUPTK -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">NUPTK</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['nuptk'] ?? '-') ?></div>
                                </div>

                            </div>
                        </div>


                        <!-- INFORMASI KELUARGA -->
                        <div class="mb-8">
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">
                                INFORMASI KELUARGA
                            </h2>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-y-3 ml-2.5 md:ml-4 text-sm text-gray-800">

                                <!-- Nama Ibu -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Nama Ibu</div>
                                    <div class="col-span-2">: <?= htmlspecialchars($guru['nama_ibu'] ?? '-') ?></div>
                                </div>

                            </div>
                        </div>


                        <!-- METADATA -->
                        <div class="mb-8">
                            <h2 class="text-lg font-bold text-gray-900 border-b border-gray-300 pb-2 mb-4">
                                METADATA
                            </h2>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-y-3 ml-2.5 md:ml-4 text-sm text-gray-800">

                                <!-- Created -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Dibuat Pada</div>
                                    <div class="col-span-2">:
                                        <?= date('d-m-Y H:i', strtotime($guru['created_at'])) ?>
                                    </div>
                                </div>

                                <!-- Updated -->
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div class="font-semibold">Terakhir Diubah</div>
                                    <div class="col-span-2">:
                                        <?= date('d-m-Y H:i', strtotime($guru['updated_at'])) ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    document.getElementById('downloadPdf').addEventListener('click', function() {
        const {
            jsPDF
        } = window.jspdf;
        const element = document.querySelector('.lg\\:max-w-\\[800px\\]');

        // Tambahkan class fallback untuk override colors
        document.body.classList.add('html2canvas-fallback');

        // Sembunyikan tombol
        const buttons = document.querySelectorAll('.print\\:hidden');
        buttons.forEach(b => b.style.display = 'none');

        html2canvas(element, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            // Tambahkan ini untuk ignore errors minor jika perlu
            logging: false
        }).then(canvas => {
            // ... (kode PDF generation sama seperti sebelumnya)

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            const imgWidth = 210;
            const pageHeight = 297;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;
            let heightLeft = imgHeight;
            let position = 10;

            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            while (heightLeft >= 0) {
                position = heightLeft - imgHeight + 10;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            // Hapus fallback dan tampilkan tombol
            document.body.classList.remove('html2canvas-fallback');
            buttons.forEach(b => b.style.display = '');

            pdf.save('Detail_Guru_<?= $jenjangUpper ?>_<?= $guru['nik'] ?>.pdf');
        }).catch(err => {
            console.error('Error generating PDF:', err);
            // Hapus fallback jika error
            document.body.classList.remove('html2canvas-fallback');
            buttons.forEach(b => b.style.display = '');
            alert('Gagal generate PDF. Coba refresh halaman.');
        });
    });
</script>

<style>
    /* Fallback untuk html2canvas: Override Tailwind colors ke HSL/RGB */
    .html2canvas-fallback * {
        --color-gray-50: hsl(210 20% 98%);
        --color-gray-100: hsl(210 16.7% 97%);
        --color-gray-200: hsl(210 14.3% 95%);
        --color-gray-300: hsl(210 11.1% 93%);
        --color-gray-400: hsl(210 9.1% 86%);
        --color-gray-500: hsl(210 8.3% 69%);
        --color-gray-600: hsl(210 7.7% 52%);
        --color-gray-700: hsl(210 7.1% 38%);
        --color-gray-800: hsl(210 6.7% 25%);
        --color-gray-900: hsl(210 6.3% 15%);

        /* Tambahkan fallback untuk warna lain jika perlu, misal green */
        --color-green-500: hsl(142 71% 45%);
        --color-green-600: hsl(142 71% 36%);
        --color-green-700: hsl(142 71% 27%);

        /* Apply ke classes Tailwind */
        .bg-gray-800 {
            background-color: hsl(210 6.7% 25%) !important;
        }

        .text-gray-900 {
            color: hsl(210 6.3% 15%) !important;
        }

        .border-gray-800 {
            border-color: hsl(210 6.7% 25%) !important;
        }

        .text-gray-700 {
            color: hsl(210 7.1% 38%) !important;
        }

        .text-gray-800 {
            color: hsl(210 6.7% 25%) !important;
        }

        /* Tambahkan override untuk classes lain di kode Anda, seperti border-gray-300, bg-green-600, dll. */
        .border-gray-300 {
            border-color: hsl(210 11.1% 93%) !important;
        }

        .bg-green-600 {
            background-color: hsl(142 71% 36%) !important;
        }

        .bg-green-700 {
            background-color: hsl(142 71% 27%) !important;
        }
    }
</style>
