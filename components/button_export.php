<a href="<?= $linkExport ?>"
    class="
        flex items-center justify-center
        text-white font-semibold transition-all duration-300
        bg-linear-to-r from-[#4d58ef] to-blue-400
        hover:from-green-600 hover:to-green-700
        focus:ring-2 focus:ring-green-300 focus:ring-opacity-50
        shadow-md hover:shadow-lg py-2 xs:py-0

        px-4 rounded-lg text-base flex gap-2 h-10 py-2
        w-full justify-center md:w-auto
    ">
    <i class="fa-solid <?= $icon ?>"></i>
    <span><?= $label ?></span>
</a>
