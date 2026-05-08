<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/utils.php';
require_once 'lib/connection.php';

use App\Services\AuthService;
use App\Validation\AuthValidation;

session_start();
ob_start();
date_default_timezone_set('Asia/Jakarta');

$auth = new AuthService($pdo);

// Sudah login → redirect
if (isset($_SESSION['id_user']))
{
    header('Location: ' . getRedirectAfterLogin($_SESSION));
    exit;
}

$errors  = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']))
{

    $errors = AuthValidation::login($_POST);

    if (!$errors)
    {
        try
        {
            $user = $auth->login(trim($_POST['username']));

            if ($user && password_verify($_POST['password'], $user['password']))
            {
                setAuthSession($user);
                header('Location: ' . getRedirectAfterLogin($user));
                exit;
            }
            else
            {
                $errors['password'] = 'Username atau password salah.';
            }
        }
        catch (\Throwable $e)
        {
            $message = 'Sistem sedang bermasalah. Coba lagi nanti.';
            error_log(
                date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL,
                3,
                __DIR__ . '/logs/error.log'
            );
        }
    }
}

$username = htmlspecialchars($_POST['username'] ?? '');

?>
<?php include('layout/head.php') ?>

<body class="bg-pattern min-h-screen flex items-center justify-center p-4">
    <div class="login-wrapper overflow-hidden shadow-2xl rounded-2xl">
        <div class="login-box">

            <h2 class="title">Selamat Datang</h2>

            <?php if ($message): ?>
                <p class="text-white text-xs mb-3"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">

                <!-- USERNAME -->
                <div class="field <?= isset($errors['username']) ? 'mb-2' : 'mb-4' ?>">
                    <input type="text" name="username" id="username"
                        value="<?= $username ?>" placeholder=" " />
                    <label for="username">Username</label>
                    <span class="border-anim"></span>
                </div>
                <?php if (isset($errors['username'])): ?>
                    <p class="text-white text-xs mb-2"><?= $errors['username'] ?></p>
                <?php endif; ?>

                <!-- PASSWORD -->
                <div class="field <?= isset($errors['password']) ? 'mb-2' : 'mb-4' ?>">
                    <input type="password" name="password" id="password"
                        placeholder=" " />
                    <label for="password">Password</label>
                    <span class="border-anim"></span>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <p class="text-white text-xs mb-2"><?= $errors['password'] ?></p>
                <?php endif; ?>

                <button type="submit" name="submit" class="btn-login mt-4">
                    MASUK
                </button>

            </form>
        </div>
    </div>
</body>

</html>
