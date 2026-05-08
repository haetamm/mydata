<form method="GET" class="">
    <div class="bg-white pt-4 px-1">

        <!-- Search & Filter Utama -->
        <div class="flex flex-col md:flex-row gap-3">
            <!-- Search input -->
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-search text-sm"></i>
                </span>
                <input type="search"
                    name="nama"
                    value="<?= htmlspecialchars($filter['nama']) ?>"
                    placeholder="Cari nama, NIS, atau NISN..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Filter kelas & status -->
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                <div class="relative md:w-48">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-graduation-cap text-sm"></i>
                    </span>
                    <select name="id_kelas"
                        onchange="this.form.submit()"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white text-black">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($listKelas as $k): ?>
                            <option value="<?= $k['id_kelas'] ?>" <?= $filter['id_kelas'] == $k['id_kelas'] ? 'selected' : '' ?>>
                                Kelas <?= htmlspecialchars($k['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="relative md:w-44">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-user-check text-sm"></i>
                    </span>
                    <select name="status"
                        onchange="this.form.submit()"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua Status</option>
                        <?php foreach (['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar', 'dikeluarkan' => 'Dikeluarkan'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $filter['status'] === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-col md:flex-row gap-3 mt-4 pt-3 border-t border-gray-200">
            <!-- Tombol kiri (Cari & Reset) -->
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                <!-- Cari: di mobile jadi w-full kalo Reset gak ada, setengah kalo ada -->
                <button type="submit" name="search" value="1"
                    class="<?= (!($filter['nama'] || $filter['id_kelas'] || $filter['status'])) ? 'col-span-2 md:col-span-1' : '' ?> px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-white text-xs"></i>
                    <span>Cari</span>
                </button>

                <?php if ($filter['nama'] || $filter['id_kelas'] || $filter['status']): ?>
                    <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left text-white text-xs"></i>
                        <span>Reset</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex flex-col md:flex-row gap-3 md:ml-auto">
                <button type="submit" name="add" value="1"
                    <?= btnDisabled($canCreate) ?>
                    class="w-full md:w-auto px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2
                                        <?= btnClass($canCreate, 'bg-blue-600 hover:bg-blue-700') ?>">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button type="submit" name="download" value="1"
                        <?= btnDisabled($canExport) ?>
                        class="px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 <?= btnClass($canExport, 'bg-emerald-600 hover:bg-emerald-700') ?>">
                        <i class="fa-solid fa-file-excel text-sm"></i>
                        <span>Download</span>
                    </button>

                    <button type="submit" name="upload" value="1"
                        <?= btnDisabled($canImport) ?>
                        class="px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 <?= btnClass($canImport, 'bg-amber-600 hover:bg-amber-700') ?>">
                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                        <span>Upload</span>
                    </button>
                </div>
            </div>
        </div>

        <input type="hidden" name="page" value="<?= $page ?>">
    </div>
</form>
