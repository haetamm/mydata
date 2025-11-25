<?php
session_start();

if (isset($_SESSION["id_user"]))
{
  header("Location: siswa-" . strtolower($jenjang) . ".php");
  exit;
}

$pesan = $_GET["pesan"] ?? "";

// error per field
$error_username = "";
$error_password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"]))
{
  $username = trim($_POST["username"] ?? "");
  $password = $_POST["password"] ?? "";

  if (empty($username)) $error_username = "Username belum diisi.";
  if (empty($password)) $error_password = "Password belum diisi.";

  if ($error_username === "" && $error_password === "")
  {
    include("connection.php");

    try
    {
      $stmt = $link->prepare("
        SELECT
            u.id_user,
            u.nama_lengkap,
            u.username,
            u.password,
            u.id_role,
            u.level_id,
            m.jenjang
        FROM users u
        LEFT JOIN master_level_kelas m ON u.level_id = m.id_level
        WHERE u.username = ?
          AND u.deleted_at IS NULL
        LIMIT 1
      ");
      $stmt->execute([$username]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($password, $user["password"]))
      {
        $_SESSION["id_user"] = $user["id_user"];
        $_SESSION["nama"] = $user["nama_lengkap"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["id_role"] = $user["id_role"];
        $_SESSION["level_id"] = $user["level_id"];
        $_SESSION["jenjang"] = $user["jenjang"] ?? null;

        if ($_SESSION["id_role"] == 1)
        {
          $redirect = "siswa-sd.php";
        }
        else
        {
          $jenjang = strtolower($_SESSION["jenjang"]);
          $redirect = "siswa-{$jenjang}.php";
        }

        header("Location: $redirect");
        exit;
      }
      else
      {
        $error_password = "Username atau password salah.";
      }
    }
    catch (PDOException $e)
    {
      $error_password = "Sistem sedang bermasalah. Coba lagi nanti.";
      error_log("Login error: " . $e->getMessage());
    }
  }
}
else
{
  $username = "";
}
?>

<?php include("layout/head.php") ?>

<body class=" bg-pattern min-h-screen flex items-center justify-center p-4">

  <div class="login-wrapper overflow-hidden shadow-2xl rounded-2xl">
    <div class="login-box ">
      <h2 class="title">Selamat Datang</h2>

      <form method="POST">

        <!-- USERNAME -->
        <div class="field <?= $error_username ? 'mb-2' : 'mb-4' ?> ">
          <input type="text" required name="username" id="username" value="<?= htmlspecialchars($username) ?>" placeholder=" " />
          <label for="username">Username</label>
          <span class="border-anim"></span>

        </div>
        <?php if (!empty($error_username)) : ?>
          <p class="text-white text-xs mb-2"><?= $error_username ?></p>
        <?php endif; ?>

        <!-- PASSWORD -->
        <div class="field <?= $error_password ? 'mb-2' : 'mb-4' ?> ">
          <input type="password" required name="password" id="password" placeholder=" " />
          <label for="password">Password</label>
          <span class="border-anim"></span>
        </div>

        <?php if (!empty($error_password)) : ?>
          <p class="text-white text-xs mb-2"><?= $error_password ?></p>
        <?php endif; ?>

        <button type="submit" name="submit" class="btn-login mt-4">
          MASUK
        </button>

      </form>
    </div>
  </div>

</body>

</html>
