<?php
session_start();
include("connection.php");
include("services/user.php");

// === CEK LOGIN ===
if (!isset($_SESSION["id_user"]))
{
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION["id_user"];
$errors  = [];
$post    = [];

// === AMBIL DATA USER ===
try
{
    $user = getUserCurrent($id_user, $link);
    if (!$user)
    {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
catch (Exception $e)
{
    die("Error: " . $e->getMessage());
}

// === PROSES UPDATE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $post = $_POST;

    foreach ($post as $k => $v)
    {
        $post[$k] = is_string($v) ? trim($v) : $v;
    }

    // VALIDASI
    if (empty($post['nama_lengkap'])) $errors['nama_lengkap'] = "Wajib diisi.";
    if (empty($post['username'])) $errors['username'] = "Wajib diisi.";

    if (strlen($post['username']) < 4)
        $errors['username'] = "Username minimal 4 karakter.";

    if (!empty($post['password']))
    {
        if (strlen($post['password']) < 6)
        {
            $errors['password'] = "Password minimal 6 karakter.";
        }
        if (
            !preg_match("/[A-Za-z]/", $post['password']) ||
            !preg_match("/[0-9]/", $post['password'])
        )
        {
            $errors['password'] = "Password harus mengandung huruf & angka.";
        }
        if ($post['password'] !== $post['password2'])
        {
            $errors['password2'] = "Konfirmasi tidak cocok.";
        }
    }

    // === EKSEKUSI UPDATE ===
    if (empty($errors))
    {
        try
        {
            // cek username
            if (isUsernameTaken($post['username'], $id_user, $link))
            {
                $errors['username'] = "Username sudah digunakan!";
            }
            else
            {
                // update
                updateUserProfile($id_user, $post, $link);

                // update session
                $_SESSION['nama'] = $post['nama_lengkap'];
                $_SESSION['username'] = $post['username'];

                $_SESSION['msg_success'] = "Profil berhasil diperbarui!";
                header("Location: profile.php");
                exit;
            }
        }
        catch (PDOException $e)
        {
            $errors['global'] = $e->getMessage();
        }
    }
}

// default value
$post = $post ?: [
    'nama_lengkap' => $user['nama_lengkap'],
    'username'     => $user['username']
];
?>


<?php include("layout/head.php") ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include("layout/sidebar.php") ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="pt-5 pb-[120px] px-3 sm:px-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class=" mx-auto">
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                        <?php $title = 'Profil Pengguna';
                        $back = '';
                        $icon = "fa-user-pen";
                        $subtitle = 'Perbarui data Anda';
                        include("components/form_header.php") ?>

                        <?php include("components/form_profile.php") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
