<?php
session_start();
include("connection.php");
include("services/guru.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$page = max(1, (int)($_GET['page'] ?? 1));

$jenjang = strtolower($_GET['jenjang'] ?? '');
if (!in_array($jenjang, ['sd', 'smp', 'sma']))
{
    die("Parameter jenjang tidak valid!");
}
$jenjangUpper = strtoupper($jenjang);
$tabel = "guru_$jenjang";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $post = $_POST;

    // Trim semua
    foreach ($post as $k => $v)
    {
        $post[$k] = is_string($v) ? trim($v) : $v;
    }

    // Validasi wajib
    $required = ['nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tgl_lahir', 'nama_ibu', 'status_pegawai', 'jenis_gtk', 'alamat'];
    foreach ($required as $f)
    {
        if (empty($post[$f])) $errors[$f] = "Wajib diisi.";
    }

    if (!empty($post['nik']) && strlen($post['nik']) !== 16) $errors['nik'] = "NIK harus 16 digit.";

    if (empty($errors))
    {
        $data = [
            'nik'               => $post['nik'],
            'nuptk'             => $post['nuptk'],
            'nama'              => $post['nama'] ?: null,
            'jenis_kelamin'     => $post['jenis_kelamin'] ?: null,
            'tempat_lahir'      => $post['tempat_lahir'],
            'tgl_lahir'         => $post['tgl_lahir'],
            'nama_ibu'          => $post['nama_ibu'],
            'status_pegawai'    => $post['status_pegawai'],
            'jenis_gtk'         => $post['jenis_gtk'],
            'jabatan'           => $post['jabatan'],
            'alamat'           => $post['alamat']
        ];

        try
        {
            createGuru($link, $tabel, $data);

            $_SESSION['msg_success'] = "Guru berhasil ditambahkan!";
            header("Location: guru-$jenjang.php?pesan=Guru berhasil ditambahkan");
            exit;
        }
        catch (PDOException $e)
        {
            $msg = $e->getMessage();

            // Jika duplicate key
            if (stripos($msg, 'Duplicate') !== false)
            {
                if (stripos($msg, 'nik') !== false)
                {
                    $errors['nik'] = "NIK sudah digunakan!";
                }
            }
            else
            {
                echo "<script>alert('Gagal simpan!\\nError: " . addslashes($msg) . "');</script>";
            }
        }
    }
}
?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <!-- Sidebar -->
        <?php include("layout/sidebar.php") ?>

        <!-- Content -->
        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class=" mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php $title = 'Tambah guru';
                        $back = "guru-$jenjang.php?page=$page";
                        $icon = 'fa-chalkboard-user';
                        $subtitle = 'Hanya Super Admin yang dapat menambah data guru';
                        include("components/form_header.php") ?>

                        <?php $buttonLable = 'Tambah';
                        $back = "guru-$jenjang.php?page=$page";
                        include("components/form_guru.php") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
