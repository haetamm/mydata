<?php
session_start();
include("connection.php");
include("services/guru.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$jenjang = strtolower($_GET['jenjang'] ?? '');
$nik     = trim($_GET['nik'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

if (!in_array($jenjang, ['sd', 'smp', 'sma']) || empty($nik))
{
    echo "<script>alert('Parameter tidak valid!'); window.history.back();</script>";
    exit;
}

$jenjangUpper = strtoupper($jenjang);
$tabel = "guru_$jenjang";

if (isset($_SESSION['edit_guru_nik']) && $_SESSION['edit_guru_nik'] !== $nik)
{
    unset($_SESSION['edit_guru_id'], $_SESSION['edit_guru_nama'], $_SESSION['edit_guru_nik']);
}

if (!isset($_SESSION['edit_guru_id']))
{

    $result = getGuruByNik($link, $tabel, $nik);
    if (!$result)
    {
        echo "<script>alert('Guru tidak ditemukan!'); window.history.back();</script>";
        exit;
    }

    $_SESSION['edit_guru_id']   = $result['id_guru'];
    $_SESSION['edit_guru_nama'] = $result['nama'];
    $_SESSION['edit_guru_nik']  = $result['nik'];
}

$id_guru = $_SESSION['edit_guru_id'];

$guru = getGuruById($link, $tabel, $id_guru);
if (!$guru)
{
    unset($_SESSION['edit_guru_id'], $_SESSION['edit_guru_nama'], $_SESSION['edit_guru_nik']);
    echo "<script>alert('Data guru tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$errors = [];

$post = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $guru;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($post as $key => $value)
    {
        $post[$key] = is_string($value) ? trim($value) : $value;
    }

    $required = ['nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tgl_lahir', 'nama_ibu', 'status_pegawai', 'jenis_gtk', 'alamat'];
    foreach ($required as $f)
    {
        if (empty($post[$f])) $errors[$f] = "Wajib diisi.";
    }

    if (empty($errors))
    {
        $data = [
            'nik' => $post['nik'],
            'nuptk' => $post['nuptk'],
            'nama' => $post['nama'],
            'jenis_kelamin' => $post['jenis_kelamin'],
            'tempat_lahir' => $post['tempat_lahir'],
            'tgl_lahir' => $post['tgl_lahir'],
            'nama_ibu' => $post['nama_ibu'],
            'status_pegawai' => $post['status_pegawai'],
            'jenis_gtk' => $post['jenis_gtk'],
            'alamat' => $post['alamat'],
            'id_guru' => $id_guru
        ];

        try
        {
            updateGuru($link, $tabel, $data);

            unset($_SESSION['edit_guru_id'], $_SESSION['edit_guru_nama'], $_SESSION['edit_guru_nis']);
            header("Location: guru-$jenjang.php?page=$page&pesan=Guru berhasil diperbarui");
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
                        <?php $title = 'Edit guru';
                        $back = "guru-$jenjang.php?page=$page";
                        $icon = 'fa-chalkboard-user';
                        $subtitle = 'Hanya Super Admin yang dapat menambah data guru';
                        include("components/form_header.php") ?>

                        <?php $buttonLable = 'Edit';
                        $back = "guru-$jenjang.php?page=$page";
                        include("components/form_guru.php") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
