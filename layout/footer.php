<script src="assets/js/cash.min.js"></script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script src="assets/js/utils.js"></script>

<script>
    window.addEventListener('load', function() {
        document.getElementById('skeleton-body')?.classList.add('hidden');
        document.getElementById('data-body')?.classList.remove('hidden');
        document.getElementById('komposisiBody')?.classList.remove('hidden');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['logout'])):
            $l = $_SESSION['logout'];
            unset($_SESSION['logout']);
        ?>
            Swal.fire({
                icon: '<?= $l['icon'] ?? 'success' ?>',
                title: <?= json_encode($l['title'] ?? 'Success!') ?>,
                text: <?= json_encode($l['text'] ?? '') ?>,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                width: '380px',
                padding: '0.75rem 1rem',
                background: '#ffffff',
                backdrop: false,
                customClass: {
                    container: '!z-[9999]',
                    popup: '!rounded-xl !shadow-lg !border !border-gray-100',
                    title: '!text-sm !font-semibold !text-gray-800 !mt-0 !mb-1',
                    icon: '!w-8 !h-8 !text-base'
                }
            });
        <?php endif; ?>

        <?php if (isset($_SESSION['swal'])):
            $s = $_SESSION['swal'];
            unset($_SESSION['swal']);
        ?>
            Swal.fire({
                icon: '<?= $s['icon'] ?? 'info' ?>',
                title: <?= json_encode($s['title'] ?? '') ?>,
                html: <?= json_encode($s['html'] ?? '') ?>,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                confirmButtonText: <?= json_encode($s['confirmButtonText'] ?? 'OK') ?>,
                allowOutsideClick: <?= isset($s['allowOutsideClick']) && !$s['allowOutsideClick'] ? 'false' : 'true' ?>,
                allowEscapeKey: <?= isset($s['allowEscapeKey']) && !$s['allowEscapeKey'] ? 'false' : 'true' ?>,
                width: '480px',
                padding: '1.25rem',
                buttonsStyling: false,
                customClass: {
                    popup: '!rounded-2xl !shadow-2xl !border !border-gray-100',
                    confirmButton: '!px-5 !py-2.5 !bg-indigo-600 !hover:bg-indigo-700 !text-white !rounded-lg !text-sm !font-semibold !transition !duration-200 !shadow-sm !hover:shadow !focus:outline-none !focus:ring-2 !focus:ring-indigo-500 !focus:ring-offset-2',
                    cancelButton: '!px-5 !py-2.5 !bg-gray-100 !hover:bg-gray-200 !text-gray-700 !rounded-lg !text-sm !font-medium !transition !duration-200 !border !border-gray-300',
                    title: '!text-xl !font-bold !text-gray-800 !mb-2',
                    htmlContainer: '!text-gray-600 !text-sm !my-4 !leading-relaxed',
                    icon: '!w-14 !h-14 !text-3xl'
                }
            }).then(() => {
                <?php if (!empty($s['back'])): ?>
                    if (history.length > 1) {
                        history.back();
                    } else {
                        window.location.href = 'dashboard.php';
                    }
                <?php endif; ?>
            });
        <?php endif; ?>
    });
</script>

<script>
    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const input = document.getElementById('passwordInput');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // INPUT DATE CUSTOM
    document.querySelectorAll('.date-input').forEach(function(input) {
        input.addEventListener('focus', function() {
            this.type = 'date';
            if (this.value) {
                this.showPicker?.();
            }
        });

        input.addEventListener('blur', function() {
            if (!this.value) {
                this.type = 'text';
            }
        });

        if (input.value) {
            input.type = 'date';
        }
    });
</script>
</body>

</html>
