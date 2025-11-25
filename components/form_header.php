<div class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white p-4 sm:p-6 text-center relative min-h-[120px] flex items-center justify-center">

    <?php if ($back): ?>
        <a href="<?= $back ?>"
            class="absolute left-2 z-10 sm:left-4 md:left-6 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 rounded-lg px-3 py-1.5 sm:px-4 sm:py-2 transition-all flex items-center gap-1 sm:gap-2 backdrop-blur-sm border border-white/10 text-white text-xs sm:text-sm">
            <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>

            <span class="hidden lg:inline">
                Kembali
            </span>
        </a>
    <?php endif; ?>

    <div class="-pb-8 sm:pt-0">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold flex items-center justify-center gap-2 sm:gap-3">
            <i class="fa-solid <?= $icon ?> text-lg sm:text-xl md:text-2xl"></i>
            <?= $title ?> <?= $jenjangUpper ?? '' || '' ?>
        </h1>
        <p class="mt-1 sm:mt-2 opacity-90 text-sm sm:text-base">
            <?= $subtitle ?>
        </p>
    </div>
</div>
