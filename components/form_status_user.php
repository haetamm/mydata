<?php
// Requires: $post, $canDelete, $formMode, $isAktif
?>
<div class="max-w-7xl mx-auto mt-3">
    <div class="bg-white">
        <div class="p-4">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">

                <!-- Label desktop -->
                <div class="hidden lg:flex items-center gap-3">
                    <i class="fa-solid fa-user-gear text-indigo-600 text-xl"></i>
                    <h1 class="text-lg font-bold text-gray-800">Edit User</h1>
                </div>

                <!-- Toggle aktif/nonaktif -->
                <form method="POST" class="w-full lg:w-auto">
                    <input type="hidden" name="action" value="toggle_aktif">

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                            <i class="fa-solid fa-circle-dot"></i>
                            <span>Status Akun:</span>
                        </div>

                        <?php if ($canDelete): ?>
                            <!-- Toggle Switch -->
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <div class="relative">
                                    <input
                                        type="checkbox"
                                        id="toggleAktif"
                                        name="aktif"
                                        value="1"
                                        <?= $isAktif ? 'checked' : '' ?>
                                        class="sr-only peer"
                                        onchange="this.form.submit()">
                                    <div class="w-11 h-6 bg-gray-300 rounded-full peer
                                                peer-checked:bg-emerald-500
                                                transition-colors duration-200"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow
                                                transition-transform duration-200
                                                peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-medium <?= $isAktif ? 'text-emerald-600' : 'text-gray-400' ?>">
                                    <?= $isAktif ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </label>

                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-lg text-sm border
                                <?= $isAktif
                                    ? 'bg-emerald-50 border-emerald-200 text-emerald-600'
                                    : 'bg-gray-50 border-gray-200 text-gray-500' ?>">
                                <?= $isAktif ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    // Update label teks saat toggle berubah tanpa menunggu submit
    document.getElementById('toggleAktif')?.addEventListener('change', function() {
        const label = this.closest('label').querySelector('span');
        if (this.checked) {
            label.textContent = 'Aktif';
            label.className = 'text-sm font-medium text-emerald-600';
        } else {
            label.textContent = 'Nonaktif';
            label.className = 'text-sm font-medium text-gray-400';
        }
    });
</script>
