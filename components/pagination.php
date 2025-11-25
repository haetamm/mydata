<!-- PAGINATION + INFO HALAMAN -->
<div class="border-indigo-100 lg:flex border-t justify-center lg:justify-between items-center px-4 py-4 bg-white mt-5 lg:mt-0 rounded-b-xl">

    <div class="text-center text-sm text-gray-600 mb-4 lg:mb-0">
        Halaman
        <span class="font-semibold text-indigo-600"><?= $page ?? 1 ?></span>
        dari
        <span class="font-semibold"><?= $totalPages ?? 1 ?></span>
    </div>

    <!-- Pagination tombol-tombolnya -->
    <div class="flex justify-center gap-2 flex-wrap">
        <?php if ($totalPages > 1): ?>
            <!-- PREV -->
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&nama=<?= urlencode($_GET['nama'] ?? '') ?>&submit=Search"
                    class="w-10 h-10 border border-indigo-300 rounded-md text-indigo-600 bg-white hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center text-sm">
                    &lt;
                </a>
            <?php else: ?>
                <span class="w-10 h-10 border border-indigo-300 rounded-md text-gray-400 bg-gray-50 cursor-not-allowed flex items-center justify-center text-sm">&lt;</span>
            <?php endif; ?>

            <!-- FIRST + ... -->
            <?php if ($page > 3): ?>
                <a href="?page=1&nama=<?= urlencode($_GET['nama'] ?? '') ?>&submit=Search" class="w-10 h-10 border border-indigo-300 rounded-md text-indigo-600 bg-white hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center text-sm">1</a>
                <?php if ($page > 4): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
            <?php endif; ?>

            <!-- HALAMAN SEKITAR -->
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="w-10 h-10 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white rounded-md shadow font-bold flex items-center justify-center text-sm"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&nama=<?= urlencode($_GET['nama'] ?? '') ?>&submit=Search"
                        class="w-10 h-10 border border-indigo-300 rounded-md text-indigo-600 bg-white hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center text-sm">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- LAST + ... -->
            <?php if ($page < $totalPages - 2): ?>
                <?php if ($page < $totalPages - 3): ?><span class="px-2 text-gray-500">...</span><?php endif; ?>
                <a href="?page=<?= $totalPages ?>&nama=<?= urlencode($_GET['nama'] ?? '') ?>&submit=Search"
                    class="w-10 h-10 border border-indigo-300 rounded-md text-indigo-600 bg-white hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center text-sm">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <!-- NEXT -->
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&nama=<?= urlencode($_GET['nama'] ?? '') ?>&submit=Search"
                    class="w-10 h-10 border border-indigo-300 rounded-md text-indigo-600 bg-white hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center text-sm">
                    &gt;
                </a>
            <?php else: ?>
                <span class="w-10 h-10 border border-indigo-300 rounded-md text-gray-400 bg-gray-50 cursor-not-allowed flex items-center justify-center text-sm">&gt;</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
