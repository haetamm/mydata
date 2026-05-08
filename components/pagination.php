<div class="bg-white border-t border-gray-200 px-4 py-4 rounded-b-lg">
    <div class="flex flex-col lg:flex-row justify-between items-center gap-4">

        <!-- Info data (kiri) -->
        <div class="text-sm text-gray-600 order-2 lg:order-1">
            <?php if ($totalData > 0): ?>
                <i class="fa-solid fa-database text-gray-400 mr-1 text-xs"></i>
                Menampilkan
                <span class="font-semibold text-indigo-600"><?= $offset + 1 ?>–<?= min($offset + $limit, $totalData) ?></span>
                dari <span class="font-semibold text-gray-800"><?= $totalData ?></span> data
                <span class="hidden sm:inline">·</span>
                <span class="block sm:inline mt-1 sm:mt-0">
                    Halaman <span class="font-semibold text-indigo-600"><?= $page ?></span>
                    dari <span class="font-semibold text-gray-800"><?= $totalPages ?></span>
                </span>
            <?php else: ?>
                <i class="fa-solid fa-circle-info text-gray-400 mr-1"></i>
                Tidak ada data yang ditemukan
            <?php endif; ?>
        </div>

        <!-- Tombol pagination (kanan) -->
        <?php if ($totalPages > 1): ?>
            <div class="flex gap-1.5 flex-wrap justify-center order-1 lg:order-2">

                <!-- Tombol Previous -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&<?= $baseQuery ?>"
                        class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-indigo-300 transition">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </a>
                <?php else: ?>
                    <span class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </span>
                <?php endif; ?>

                <!-- Halaman pertama + ... -->
                <?php if ($page > 3): ?>
                    <a href="?page=1&<?= $baseQuery ?>"
                        class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-indigo-300 transition">
                        1
                    </a>
                    <?php if ($page > 4): ?>
                        <span class="pag-dots px-2 h-9 flex items-center justify-center text-gray-400 text-sm">•••</span>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Halaman sekitar -->
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="pag-active min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-sm">
                            <?= $i ?>
                        </span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&<?= $baseQuery ?>"
                            class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-indigo-300 transition">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Halaman terakhir + ... -->
                <?php if ($page < $totalPages - 2): ?>
                    <?php if ($page < $totalPages - 3): ?>
                        <span class="pag-dots px-2 h-9 flex items-center justify-center text-gray-400 text-sm">•••</span>
                    <?php endif; ?>
                    <a href="?page=<?= $totalPages ?>&<?= $baseQuery ?>"
                        class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-indigo-300 transition">
                        <?= $totalPages ?>
                    </a>
                <?php endif; ?>

                <!-- Tombol Next -->
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&<?= $baseQuery ?>"
                        class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-indigo-300 transition">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                <?php else: ?>
                    <span class="pag-btn min-w-[36px] h-9 px-3 flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </span>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>
</div>
