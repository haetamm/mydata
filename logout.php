<?php
    session_start();

    // Hapus semua data sesi
    session_unset();
    session_destroy();

    // Tampilkan alert logout sukses (opsional)
    echo "<script>alert('Anda telah logout.'); window.location.href='login.php';</script>";
    exit;
?>