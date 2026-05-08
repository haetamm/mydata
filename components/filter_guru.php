<form method="GET" class="">
    <div class="bg-white pt-4 px-1">

        <!-- Search & Filter Utama -->
        <div class="flex flex-col md:flex-row gap-3">

            <!-- Search: nama / NIK / NUPTK -->
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-search text-sm"></i>
                </span>
                <input type="search"
                    name="nama"
                    value="<?= htmlspecialchars($filter['nama']) ?>"
                    placeholder="Cari nama, NIK, atau NUPTK..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Filter dropdown: Status & Status Pegawai -->
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">

                <!-- Status keaktifan -->
                <div class="relative md:w-44">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-user-check text-sm"></i>
                    </span>
                    <select name="status"
                        onchange="this.form.submit()"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua Status</option>
                        <?php foreach (
                            [
                                'aktif'     => 'Aktif',
                                'pensiun'   => 'Pensiun',
                                'pindah'    => 'Pindah',
                                'keluar'    => 'Keluar',
                                'meninggal' => 'Meninggal',
                            ] as $val => $label
                        ): ?>
                            <option value="<?= $val ?>" <?= $filter['status'] === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status pegawai -->
                <div class="relative md:w-44">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-id-badge text-sm"></i>
                    </span>
                    <select name="status_pegawai"
                        onchange="this.form.submit()"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua Kepegawaian</option>
                        <?php foreach (['PNS' => 'PNS', 'Honorer' => 'Honorer', 'GTT' => 'GTT', 'PTT' => 'PTT'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $filter['status_pegawai'] === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>

        <!-- Filter jabatan + Tombol Aksi -->
        <div class="flex flex-col md:flex-row gap-3 mt-3">

            <!-- Filter jabatan (free text, opsional) -->
            <div class="relative md:w-56">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-briefcase text-sm"></i>
                </span>
                <input type="text"
                    name="jabatan"
                    value="<?= htmlspecialchars($filter['jabatan']) ?>"
                    placeholder="Filter jabatan..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Cari & Reset -->
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                <button type="submit" name="search" value="1"
                    class="<?= (!($filter['nama'] || $filter['status'] || $filter['status_pegawai'] || $filter['jabatan'])) ? 'col-span-2 md:col-span-1' : '' ?> px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>Cari</span>
                </button>

                <?php if ($filter['nama'] || $filter['status'] || $filter['status_pegawai'] || $filter['jabatan']): ?>
                    <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
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

                <!-- Download & Upload -->
                <div class="grid grid-cols-2 gap-3">
                    <button type="submit" name="download" value="1"
                        <?= btnDisabled($canExport) ?>
                        class="px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2
                            <?= btnClass($canExport, 'bg-emerald-600 hover:bg-emerald-700') ?>">
                        <i class="fa-solid fa-file-excel text-sm"></i>
                        <span>Download</span>
                    </button>

                    <button type="submit" name="upload" value="1"
                        <?= btnDisabled($canImport) ?>
                        class="px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2
                            <?= btnClass($canImport, 'bg-amber-600 hover:bg-amber-700') ?>">
                        <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
                        <span>Upload</span>
                    </button>
                </div>
            </div>

        </div>

        <input type="hidden" name="page" value="<?= $page ?>">
    </div>
</form>
