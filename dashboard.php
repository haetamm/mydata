<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;

session_start();

// Guard + load menu & permission dari DB
[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'dashboard');

$nama  = $_SESSION['nama'] ?? 'Admin';
$role  = $_SESSION['role']         ?? '';

$hour = (int) date('H');
$greeting = match (true)
{
    $hour >= 5  && $hour < 11 => 'Selamat pagi',
    $hour >= 11 && $hour < 15 => 'Selamat siang',
    $hour >= 15 && $hour < 18 => 'Selamat sore',
    default                   => 'Selamat malam',
};

$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$today = $days[date('w')] . ', ' . date('j') . ' ' . $months[(int)date('n')] . ' ' . date('Y');

$quotes = [
    '✨ Raih prestasi setinggi langit!',
    '📚 Belajar hari ini, sukses esok hari.',
    '🎯 Fokus pada tujuan, gapai cita-cita.',
    '💪 Semangat! Hari penuh berkah.',
    '⭐ Jadilah generasi penerus yang hebat.',
    '🚀 Terus berkarya dan berinovasi!',
];
$randomQuote = $quotes[array_rand($quotes)];
?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-screen overflow-hidden flex flex-col bg-gradient-to-br from-[#f0f4ff] to-white">

            <div class="flex-1 overflow-auto no-scrollbar">
                <div class="min-h-full flex items-center justify-start pt-8 sm:pt-12 md:pt-16 pb-8">

                    <div class="w-full px-4 sm:px-6 md:px-8 lg:px-12 xl:px-16 2xl:px-20">

                        <!-- Tanggal -->
                        <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-white/80 backdrop-blur-sm rounded-full px-3 py-1 sm:px-4 sm:py-1.5 shadow-sm border border-gray-100 mb-5 sm:mb-6 md:mb-8">
                            <i class="fa-regular fa-calendar text-[#4d58ef] text-[10px] sm:text-xs"></i>
                            <span class="text-gray-500 text-[10px] sm:text-sm font-medium whitespace-nowrap"><?= $today ?></span>
                        </div>

                        <!-- Greeting - font size menyesuaikan -->
                        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black text-gray-800 leading-[1.2] tracking-tight">
                            <?= $greeting ?>,<br>
                            <span class="bg-gradient-to-r from-[#4d58ef] via-blue-500 to-cyan-400 bg-clip-text text-transparent break-words">
                                <?= htmlspecialchars($nama) ?>
                            </span>
                            <span class="text-gray-300">!</span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl text-gray-400 font-medium mt-3 sm:mt-4 md:mt-6 max-w-2xl lg:max-w-3xl">
                            Selamat datang di dashboard
                            <span class="hidden xs:inline"><br></span>
                            <span class="text-gray-500">Sistem Informasi Sekolah</span>
                        </p>

                        <!-- Quote -->
                        <div class="mt-6 sm:mt-8 md:mt-10">
                            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                <div class="w-8 sm:w-10 md:w-12 h-px bg-gradient-to-r from-[#4d58ef] to-transparent"></div>
                                <i class="fa-regular fa-message text-[#4d58ef] text-[10px] sm:text-xs md:text-sm"></i>
                                <span class="text-gray-500 text-xs sm:text-sm md:text-base italic text-center max-w-[280px] sm:max-w-md md:max-w-lg lg:max-w-xl">
                                    <?= htmlspecialchars($randomQuote) ?>
                                </span>
                                <div class="w-8 sm:w-10 md:w-12 h-px bg-gradient-to-l from-[#4d58ef] to-transparent"></div>
                            </div>
                        </div>

                        <!-- Jam -->
                        <div class="mt-8 sm:mt-10 md:mt-12 flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 rounded-full bg-gradient-to-r from-[#4d58ef] to-blue-400 flex items-center justify-center shadow-md">
                                <i class="fa-regular fa-clock text-white text-[10px] sm:text-xs md:text-sm"></i>
                            </div>
                            <span id="live-clock" class="text-gray-600 text-sm sm:text-base md:text-lg lg:text-xl font-mono font-semibold tracking-wider"></span>
                            <span class="text-gray-300 text-[10px] sm:text-xs">WIB</span>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>

<script>
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
