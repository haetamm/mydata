<?php
session_start();
require_once "connection.php";
require_once "services/user.php";

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$page = max(1, (int)($_GET['page'] ?? 1));

// === Ambil username dari URL ===
if (!isset($_GET['username']) || empty($_GET['username']))
{
    die("<script>alert('Username tidak ditemukan!'); history.back();</script>");
}
$username_url = $_GET['username'];

// === Ambil data user yang akan diedit ===
$user = getUserByUsername($link, $username_url);

if (!$user) die("<script>alert('User tidak ditemukan atau sudah dihapus!'); history.back();</script>");
if ($user['nama_role'] === 'Super Admin') die("<script>alert('Super admin tidak boleh diedit!'); history.back();</script>");

$errors = [];
$post   = [];

// === Proses Update ===
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $post = array_map('trim', $_POST);

    // Validasi wajib
    $required = ['nama_lengkap', 'username', 'id_role', 'level_id'];
    foreach ($required as $field)
    {
        if (empty($post[$field])) $errors[$field] = "Wajib diisi.";
    }

    if (strlen($post['username']) < 4)   $errors['username']     = "Minimal 4 karakter.";

    if (!empty($post['password']))
    {
        if (strlen($post['password']) < 6)
        {
            $errors['password'] = "Minimal 6 karakter.";
        }
        if (!preg_match('/[A-Za-z]/', $post['password']) || !preg_match('/[0-9]/', $post['password']))
        {
            $errors['password'] = "Harus ada huruf dan angka.";
        }
        if ($post['password'] !== $post['password2'])
        {
            $errors['password2'] = "Konfirmasi tidak cocok.";
        }
    }

    if (empty($errors))
    {
        try
        {
            updateUserByUsername($link, $post, $username_url);
            header("Location: admin.php?pesan=User berhasil diperbarui&page=$page");
            exit;
        }
        catch (PDOException $e)
        {
            if (str_contains($e->getMessage(), 'username'))
            {
                $errors['username'] = "Username sudah digunakan!";
            }
            else
            {
                $errors['general'] = "Gagal simpan: " . $e->getMessage();
            }
        }
        catch (Exception $e)
        {
            $errors['general'] = $e->getMessage();
        }
    }
}

// Jika belum POST, isi $post dengan data user saat ini
if (empty($post))
{
    $post = $user;
}
?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <?php include("layout/sidebar.php") ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class=" mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php
                        $title    = 'Edit User';
                        $back = "admin.php?page=$page";
                        $icon = 'fa-user-pen';
                        $subtitle = 'Hanya Super Admin yang dapat mengedit data user';
                        include("components/form_header.php");
                        ?>

                        <?php $buttonLable = "Edit";
                        $back = "admin.php?page=$page";
                        include("components/form_user.php") ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
