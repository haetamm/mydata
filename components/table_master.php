<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden <?= $pesan ? 'lg:max-h-[calc(100vh-280px)] ' : 'lg:max-h-[calc(100vh-183px)]' ?> flex flex-col">
    <div class="overflow-y-auto flex-1">
        <table class="min-w-full divide-y table-fixed">
            <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>

                    <?php if ($currentPage === 'kelas.php'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Jenjang</th>
                    <?php endif; ?>

                    <?php if ($currentPage === 'level-kelas.php'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Level Min</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Level Max</th>
                    <?php endif; ?>

                    <?php if ($currentPage === 'tahun-pelajaran.php'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    <?php endif; ?>

                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($dataMaster)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataMaster as $index => $master): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $index + 1 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($master['nama_master']) ?>
                            </td>

                            <?php if ($currentPage === 'kelas.php'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($master['jenjang']) ?>
                                </td>
                            <?php endif; ?>

                            <?php if ($currentPage === 'level-kelas.php'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($master['level_min']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($master['level_max']) ?>
                                </td>
                            <?php endif; ?>

                            <?php if ($currentPage === 'tahun-pelajaran.php'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="text-xs px-2 py-1 rounded-full <?= $master['is_aktif'] ? 'bg-green-300' : 'bg-red-300' ?>"><?= $master['is_aktif'] ? 'Aktif' : 'Inaktif' ?></span>
                                </td>
                            <?php endif; ?>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center justify-center gap-x-2">
                                    <form method="POST" action="<?= $action ?>"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus master <?= htmlspecialchars($master['nama_master']) ?>?')">
                                        <input type="hidden" name="delete_id" value="<?= $master['id_master'] ?>">
                                        <button type="submit"
                                            class="flex items-center cursor-pointer gap-1 px-2 py-1 text-red-600 border border-red-600 rounded
                                                hover:bg-red-600 hover:text-white transition">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>

                                    <!-- Toggle Aktif -->
                                    <?php if ($currentPage === 'tahun-pelajaran.php'): ?>
                                        <form method="POST" action="<?= $action ?>">

                                            <input type="hidden" name="toggle_id" value="<?= $master['id_master'] ?>">

                                            <button
                                                type="submit"
                                                <?= $master['is_aktif'] ? 'disabled' : '' ?>
                                                class="relative inline-flex h-5 w-10 items-center rounded-full transition-colors duration-300
                                                    <?= $master['is_aktif'] ? 'bg-green-500 cursor-not-allowed opacity-70' : 'cursor-pointer bg-gray-300 hover:bg-gray-400' ?>">

                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-300
                                                            <?= $master['is_aktif'] ? 'translate-x-5' : 'translate-x-1' ?>">
                                                </span>
                                            </button>

                                        </form>
                                    <?php endif; ?>


                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
