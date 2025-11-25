<?php
session_start();
include("connection.php");
include("services/siswa.php");
include("services/master.php");

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$jenjang = strtolower($_GET['jenjang'] ?? '');
$nis     = trim($_GET['nis'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

if (!in_array($jenjang, ['sd', 'smp', 'sma']) || empty($nis))
{
    echo "<script>alert('Parameter tidak valid!'); window.history.back();</script>";
    exit;
}

$jenjangUpper = strtoupper($jenjang);
$tabel = "siswa_$jenjang";

if (isset($_SESSION['edit_siswa_nis']) && $_SESSION['edit_siswa_nis'] !== $nis)
{
    unset($_SESSION['edit_siswa_id'], $_SESSION['edit_siswa_nama'], $_SESSION['edit_siswa_nis']);
}

if (!isset($_SESSION['edit_siswa_id']))
{
    $result = getSiswaByNis($link, $tabel, $nis);

    if (!$result)
    {
        echo "<script>alert('Siswa tidak ditemukan!'); window.history.back();</script>";
        exit;
    }

    $_SESSION['edit_siswa_id']   = $result['id_siswa'];
    $_SESSION['edit_siswa_nama'] = $result['nama'];
    $_SESSION['edit_siswa_nis']  = $result['nis'];
}

$id_siswa = $_SESSION['edit_siswa_id'];

$siswa = getSiswaById($link, $tabel, $id_siswa);

if (!$siswa)
{
    unset($_SESSION['edit_siswa_id'], $_SESSION['edit_siswa_nama'], $_SESSION['edit_siswa_nis']);
    echo "<script>alert('Data siswa tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$errors = [];

$post = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $siswa;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($post as $key => $value)
    {
        $post[$key] = is_string($value) ? trim($value) : $value;
    }

    $required = ['nama', 'nis', 'nisn', 'tempat_lahir', 'tgl_lahir', 'id_kelas', 'ruang', 'id_semester', 'id_tahun_pelajaran', 'id_agama', 'alamat', 'rt_rw', 'dusun', 'kelurahan', 'kecamatan'];
    foreach ($required as $f)
    {
        if (empty($post[$f])) $errors[$f] = "Wajib diisi.";
    }
    if (!empty($post['nis']) && !ctype_digit($post['nis'])) $errors['nis'] = "NIS harus angka.";
    if (!empty($post['nisn']) && strlen($post['nisn']) !== 10) $errors['nisn'] = "NISN harus 10 digit.";

    if (empty($errors))
    {
        $data = [
            'nama' => $post['nama'],
            'nis' => $post['nis'],
            'nisn' => $post['nisn'] ?: null,
            'nik' => $post['nik'] ?: null,
            'tempat_lahir' => $post['tempat_lahir'],
            'tgl_lahir' => $post['tgl_lahir'],
            'id_semester' => (int)$post['id_semester'],
            'id_tahun_pelajaran' => (int)$post['id_tahun_pelajaran'],
            'id_agama' => (int)$post['id_agama'],
            'id_kelas' => (int)$post['id_kelas'],
            'ruang' => $post['ruang'],
            'ayah_nama' => $post['ayah_nama'] ?: null,
            'ayah_tahun_lahir' => !empty($post['ayah_tahun_lahir']) ? (int)$post['ayah_tahun_lahir'] : null,
            'ayah_pendidikan' => $post['ayah_pendidikan'] ?: null,
            'ayah_pekerjaan' => !empty($post['ayah_pekerjaan']) ? (int)$post['ayah_pekerjaan'] : null,
            'ayah_penghasilan' => $post['ayah_penghasilan'] ?: null,
            'ayah_nik' => $post['ayah_nik'] ?: null,
            'ibu_nama' => $post['ibu_nama'] ?: null,
            'ibu_tahun_lahir' => !empty($post['ibu_tahun_lahir']) ? (int)$post['ibu_tahun_lahir'] : null,
            'ibu_pendidikan' => $post['ibu_pendidikan'] ?: null,
            'ibu_pekerjaan' => !empty($post['ibu_pekerjaan']) ? (int)$post['ibu_pekerjaan'] : null,
            'ibu_penghasilan' => $post['ibu_penghasilan'] ?: null,
            'ibu_nik' => $post['ibu_nik'] ?: null,
            'wali_nama' => $post['wali_nama'] ?: null,
            'wali_tahun_lahir' => !empty($post['wali_tahun_lahir']) ? (int)$post['wali_tahun_lahir'] : null,
            'wali_pendidikan' => $post['wali_pendidikan'] ?: null,
            'wali_pekerjaan' => !empty($post['wali_pekerjaan']) ? (int)$post['wali_pekerjaan'] : null,
            'wali_penghasilan' => $post['wali_penghasilan'] ?: null,
            'wali_nik' => $post['wali_nik'] ?: null,
            'alamat' => $post['alamat'],
            'rt_rw' => $post['rt_rw'],
            'dusun' => $post['dusun'],
            'kelurahan' => $post['kelurahan'],
            'kecamatan' => $post['kecamatan'],
            'kode_pos' => $post['kode_pos'] ?: null,
            'id_siswa' => $id_siswa
        ];

        try
        {
            updateSiswa($link, $tabel, $data);

            unset($_SESSION['edit_siswa_id'], $_SESSION['edit_siswa_nama'], $_SESSION['edit_siswa_nis']);
            header("Location: siswa-$jenjang.php?page=$page&pesan=Siswa berhasil diperbarui");
            exit;
        }
        catch (PDOException $e)
        {
            $msg = $e->getMessage();

            if (stripos($msg, 'Duplicate') !== false)
            {
                if (preg_match_all("/'([^']+)'/", $msg, $m))
                {
                    $rawKey = strtolower(end($m[1]));
                    $parts = explode('.', $rawKey);
                    $last = end($parts);
                    $clean = preg_replace("/(_unique|_key|_idx)$/", "", $last);

                    if ($clean === 'nisn') $errors['nisn'] = "NISN sudah digunakan!";
                    elseif ($clean === 'nis') $errors['nis'] = "NIS sudah digunakan!";
                }
            }
            else
            {
                echo "<script>alert('Gagal simpan!\\nError: " . addslashes($msg) . "');</script>";
            }
        }
        catch (Exception $e)
        {
            $errors['general'] = $e->getMessage();
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
                        <?php $title = 'Edit siswa';
                        $back = "siswa-$jenjang.php?page=$page";
                        $icon = 'fa-user-graduate';
                        $subtitle = 'Hanya Super Admin yang dapat menambah data siswa';
                        include("components/form_header.php") ?>

                        <?php $buttonLable = 'Edit';
                        $back = "siswa-$jenjang.php?page=$page";
                        include("components/form_siswa.php") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
