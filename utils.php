<?php
function field_error($field, $errors)
{
    if (!empty($errors[$field]))
    {
        echo '<p class="mt-1 ml-2 text-sm text-red-600">' . htmlspecialchars($errors[$field]) . '</p>';
    }
}

function isAdmin()
{
    return $_SESSION['id_role'] === 1;
}

function redirectToLogin(): never
{
    header('Location: login.php');
    exit;
}

function denyAccess(string $reason = 'Akses ditolak!'): never
{
    echo "<script>
        alert('" . htmlspecialchars($reason, ENT_QUOTES) . "');
        window.history.back();
    </script>";
    exit;
}

// 1. Halaman siswa → Super Admin + Kepsek + Guru (jenjang cocok)
function authorizeStudentData(string $requiredLevel): void
{
    if (!isset($_SESSION['id_user'])) redirectToLogin();

    $role = $_SESSION['id_role'];
    $level = $_SESSION['jenjang'] ?? null;

    if ($role === 1) return;                                      // Super Admin lolos
    if (in_array($role, [2, 3]) && $level === $requiredLevel) return; // Kepsek/Guru cocok
    denyAccess("Anda hanya dapat mengakses jenjang {$level}.");
}

// 2. Halaman guru / kepala sekolah / admin → Hanya Super Admin + Kepsek (jenjang cocok)
function authorizeTeacherData(string $requiredLevel): void
{
    if (!isset($_SESSION['id_user'])) redirectToLogin();

    $role = $_SESSION['id_role'];
    $level = $_SESSION['jenjang'] ?? null;

    if ($role === 1) return;                          // Super Admin lolos
    if ($role === 2 && $level === $requiredLevel) return; // Hanya Kepsek yang cocok
    denyAccess($role === 3
        ? "Guru tidak diizinkan mengakses halaman ini."
        : "Anda hanya dapat mengelola jenjang {$level}.");
}

// 3. Hanya Super Admin
function allowSuperAdminOnly(): void
{
    if (!isset($_SESSION['id_user'])) redirectToLogin();
    if ($_SESSION['id_role'] !== 1)
    {
        denyAccess('Hanya Super Admin yang diizinkan.');
    }
}
