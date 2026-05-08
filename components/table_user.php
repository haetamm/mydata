<?php
$hasMsg   = $pesan || $fetchError;
$tableH   = $hasMsg ? 'lg:h-[calc(100vh-310px)]' : 'lg:h-[calc(100vh-265px)]';
?>
<div class="hidden md:flex mt-6 <?= $tableH ?> overflow-hidden rounded-t-xl">
    <div class="overflow-y-auto w-full h-full">
        <table class="min-w-full bg-white table-fixed">
            <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                <tr>
                    <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                    <th class="py-4 px-3 text-left font-semibold w-[20%]">NAMA</th>
                    <th class="py-4 px-3 text-left font-semibold w-[15%]">USERNAME</th>
                    <th class="py-4 px-3 text-left font-semibold w-[15%]">ROLE</th>
                    <th class="py-4 px-3 text-center font-semibold w-[10%]">STATUS</th>
                    <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[12%]">AKSI</th>
                </tr>
            </thead>

            <tbody class="text-gray-700 text-sm">
                <?php if (empty($dataUser)): ?>
                    <tr>
                        <td colspan="6" class="py-24 px-6 text-center text-gray-500 font-medium text-lg">
                            <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                            Data user tidak tersedia
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataUser as $index => $u): ?>
                        <?php
                        $no     = $offset + $index + 1;
                        $isAktif = empty($u['deleted_at']);
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                            <td class="py-4 px-3 font-medium text-center w-[5%]">
                                <div class="truncate"><?= $no ?></div>
                            </td>
                            <td class="py-4 px-3 font-semibold text-indigo-800 w-[20%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['nama_lengkap']) ?>">
                                    <?= htmlspecialchars($u['nama_lengkap']) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[15%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['username'] ?? '-') ?>">
                                    <?= htmlspecialchars($u['username'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[15%]">
                                <div class="truncate" title="<?= htmlspecialchars($u['role_name'] ?? '-') ?>">
                                    <?= htmlspecialchars($u['role_name'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-center w-[10%]">
                                <?php if ($isAktif): ?>
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-600 rounded-full">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-3 w-[12%]">
                                <div class="flex justify-center gap-1">
                                    <a href="<?= buildUserEditUrl($u['id'], $filter, $page) ?>"
                                        class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium"
                                        title="Edit">
                                        <i class="fa-solid fa-<?= $canEdit ? 'edit' : 'eye' ?>"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
