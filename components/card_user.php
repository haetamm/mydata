<div class="md:hidden grid grid-cols-1 xs:grid-cols-2 gap-x-2 gap-y-6 mt-6">
    <?php if (empty($dataUser)): ?>
        <div class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400">
            <i class="fa-solid fa-inbox text-6xl mb-4 opacity-50"></i>
            <p class="text-xl font-medium">Data user tidak tersedia</p>
        </div>
    <?php else: ?>
        <?php foreach ($dataUser as $index => $u): ?>
            <?php
            $no      = $offset + $index + 1;
            $isAktif = empty($u['deleted_at']);
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-4">

                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                <?= strtoupper(substr($u['nama_lengkap'], 0, 2)) ?>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">No. <?= $no ?></p>
                                <h3 class="font-semibold text-gray-900 text-sm truncate mt-0.5">
                                    <?= htmlspecialchars($u['nama_lengkap'] ?? '-') ?>
                                </h3>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <span class="px-3 py-1 text-xs font-bold text-indigo-700 bg-indigo-100 rounded-full whitespace-nowrap">
                                <?= htmlspecialchars($u['role_name'] ?? '-') ?>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs mb-4">
                        <div class="flex justify-between py-1.5 px-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500">Username</span>
                            <span class="font-mono font-medium text-gray-800 truncate max-w-[150px] text-right">
                                <?= htmlspecialchars($u['username'] ?? '-') ?>
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 px-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500">Status</span>
                            <?php if ($isAktif): ?>
                                <span class="font-semibold text-green-600">Aktif</span>
                            <?php else: ?>
                                <span class="font-semibold text-red-500">Nonaktif</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <a href="<?= buildUserEditUrl($u['id'], $filter, $page) ?>"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 rounded-lg text-center transition">
                            Edit
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
