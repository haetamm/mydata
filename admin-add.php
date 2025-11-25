<?php
session_start();
require_once "connection.php";
require_once "services/user.php";

// === 1. CEK LOGIN & HAK AKSES ===
allowSuperAdminOnly();

$page = max(1, (int)($_GET['page'] ?? 1));

$errors = [];
$post = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $post = array_map('trim', $_POST);

    // Validasi wajib
    $required = ['nama_lengkap', 'username', 'id_role', 'password', 'level_id'];
    foreach ($required as $field)
    {
        if (empty($post[$field])) $errors[$field] = "Wajib diisi.";
    }

    if (strlen($post['username']) < 4)
    {
        $errors['username'] = "Minimal 4 karakter.";
    }

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
        $errors['password2'] = "Konfirmasi password tidak cocok.";
    }

    if (empty($errors))
    {
        try
        {
            createUser($link, [
                'nama_lengkap' => $post['nama_lengkap'],
                'username'     => $post['username'],
                'id_role'      => $post['id_role'],
                'level_id'     => $post['level_id'] ?: null,
                'password'     => $post['password']
            ]);
            header("Location: admin.php?pesan=User berhasil ditambahkan");
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

?>

<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">
        <?php include("layout/sidebar.php") ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class=" mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php $title = 'Tambah User';
                        $back = "admin.php?page=$page";
                        $icon = 'fa-user-plus';
                        $subtitle = 'Hanya Super Admin yang dapat menambahkan data user';
                        include("components/form_header.php") ?>

                        <?php $buttonLable = "Tambah";
                        $back = "admin.php?page=$page";
                        include("components/form_user.php") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
