<div class="hidden md:flex mt-6 <?= $pesan || $pesan_cari ? ' lg:h-[calc(100vh-265px)]' : 'lg:h-[calc(100vh-183px)]' ?> overflow-hidden rounded-t-xl">
    <div class="overflow-y-auto w-full h-full">
        <table class="min-w-full bg-white table-fixed">
            <!-- THEAD -->
            <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                <tr>
                    <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">NAMA</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">USERNAME</th>
                    <th class="py-4 px-3 text-left font-semibold w-[15%]">ROLE</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">AKSES</th>
                    <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[12%]">Aksi</th>
                </tr>
            </thead>

            <!-- TBODY -->
            <tbody class="text-gray-700 text-sm">
                <?php if (empty($dataUser)): ?>
                    <tr>
                        <td colspan="12" class="py-24 px-6 text-center text-gray-500 font-medium text-lg">
                            <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                            Data user tidak tersedia
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataUser as $index => $u): ?>
                        <?php
                        $no = $offset + $index + 1;
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                            <td class="py-4 px-3 font-medium text-center w-[5%]">
                                <div class="truncate" title="<?= $no ?>"><?= $no ?></div>
                            </td>
                            <td class="py-4 px-3 font-semibold text-indigo-800 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['nama_lengkap']) ?>">
                                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['username'] ?? '-') ?>">
                                    <?= htmlspecialchars($u['username'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[15%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['nama_role'] ?? '-') ?>">
                                    <?= htmlspecialchars($u['nama_role'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[8%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['jenjang'] ?? '-') ?>">
                                    <?= htmlspecialchars($u['jenjang'] ?? 'Full Akses') ?>
                                </div>
                            </td>
                            <!-- Aksi -->
                            <td class="py-4 px-3 w-[12%]">
                                <div class="flex justify-center gap-1">
                                    <a href="admin-update.php?username=<?= $u['username'] ?>&page=<?= $page ?>"
                                        class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                        title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="<?= $action ?>?page=<?= $page ?>" method="POST"
                                        onsubmit="return confirm('Yakin hapus user ini?')" class="inline">
                                        <input type="hidden" name="delete_id" value="<?= $u['username'] ?>">
                                        <button type="submit"
                                            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                            title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
