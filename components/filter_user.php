<form method="GET" class="">
    <div class="bg-white px-1">
        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-search text-sm"></i>
                </span>
                <input type="search"
                    name="nama"
                    value="<?= htmlspecialchars($filter['nama']) ?>"
                    placeholder="Cari nama atau username..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="relative md:w-52">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </span>
                <select name="role_id"
                    onchange="this.form.submit()"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Semua Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role['id']) ?>"
                            <?= ($filter['role_id'] === $role['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="relative md:w-44">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <i class="fa-solid fa-circle-half-stroke text-sm"></i>
                </span>
                <select name="status"
                    onchange="this.form.submit()"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= ($filter['status'] === 'aktif')    ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= ($filter['status'] === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mt-3">
            <div class="grid grid-cols-2 md:flex md:flex-row gap-3">
                <button type="submit" name="search" value="1"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    <span>Cari</span>
                </button>

                <?php if ($filter['nama'] || $filter['role_id'] || $filter['status']): ?>
                    <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        <span>Reset</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="md:ml-auto">
                <button type="submit" name="add" value="1"
                    <?= btnDisabled($canCreate) ?>
                    class="w-full md:w-auto px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2
                        <?= btnClass($canCreate, 'bg-blue-600 hover:bg-blue-700') ?>">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah</span>
                </button>
            </div>
        </div>

        <input type="hidden" name="page" value="<?= $page ?>">
    </div>
</form>
