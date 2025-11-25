<?php if (isAdmin()): ?>
    <a href="<?= $btnLink ?>"
        class="
        flex items-center justify-center
        text-white font-semibold transition-all duration-300
        hover:from-amber-600 hover:to-orange-600
        focus:ring-2 focus:ring-amber-400 focus:ring-opacity-50
        shadow-md hover:shadow-lg


        fixed bottom-20 xs:bottom-10 md:bottom-4 right-6 z-50
        w-14 h-14 rounded-full text-2xl


        md:static md:mt-0 md:mb-0 md:px-4 md:py-2 md:rounded-lg
         md:h-auto md:text-base md:flex md:gap-2
        bg-linear-to-r from-[#4d58ef] to-blue-400
        md:hover:from-indigo-700 md:hover:to-indigo-800
        md:w-auto
    ">
        <i class="fa-solid fa-plus"></i>
        <span class="hidden md:block"><?= $btnLabel ?></span>
    </a>
<?php endif; ?>
