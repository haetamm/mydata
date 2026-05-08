<?php include("layout/head.php") ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'question',
        title: 'Konfirmasi Logout',
        text: 'Apakah kamu yakin ingin keluar dari sistem?',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#4d58ef',
        cancelButtonColor: '#6b7280',
        timerProgressBar: false,
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('logout_process.php')
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Logout Berhasil',
                        text: 'Anda telah keluar dari sistem.',
                        showConfirmButton: false,
                        timer: 1300,
                        timerProgressBar: true,
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                });
        } else {
            // Batal → kembali ke halaman sebelumnya
            history.back();
        }
    });
</script>
<?php include("layout/footer.php") ?>
