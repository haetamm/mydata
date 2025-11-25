<div class="hidden lg:block mt-6 <?= $pesan || $pesan_cari ? 'lg:h-[calc(100vh-265px)]' : 'lg:h-[calc(100vh-183px)]' ?> overflow-hidden rounded-t-xl">
    <div class="overflow-y-auto h-full">
        <table class="min-w-full bg-white table-fixed">
            <!-- THEAD -->
            <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                <tr>
                    <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">NIK</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">NUPTK</th>
                    <th class="py-4 px-3 text-left font-semibold w-[15%]">NAMA</th>
                    <th class="py-4 px-3 text-left font-semibold w-[8%]">Jenis Kelamin</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">Tempat Lahir</th>
                    <th class="py-4 px-3 text-left font-semibold w-[8%]">Tgl Lahir</th>
                    <th class="py-4 px-3 text-left font-semibold w-[12%]">NAMA IBU</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">STATUS PEGAWAI</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">JENIS GTK</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">JABATAN</th>
                    <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[12%]">Aksi</th>
                </tr>
            </thead>

            <!-- TBODY -->
            <tbody class="text-gray-700 text-sm">
                <?php if (empty($dataGuru)): ?>
                    <tr>
                        <td colspan="12" class="py-24 px-6 text-center text-gray-500 font-medium text-lg">
                            <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                            Data guru tidak tersedia
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataGuru as $index => $g): ?>
                        <?php
                        $no = $offset + $index + 1;
                        $tgl_lahir = $g['tgl_lahir'] ? date('d/m/Y', strtotime($g['tgl_lahir'])) : '-';
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                            <td class="py-4 px-3 font-medium text-center w-[5%]">
                                <div class="truncate" title="<?= $no ?>"><?= $no ?></div>
                            </td>
                            <td class="py-4 px-3 font-semibold text-indigo-800 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['nik']) ?>">
                                    <?= htmlspecialchars($g['nik']) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['nuptk'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['nuptk'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[15%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['nama'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['nama'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[8%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['jenis_kelamin'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['jenis_kelamin'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['tempat_lahir'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['tempat_lahir'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[8%]">
                                <div class="truncate" title="<?= $tgl_lahir ?>"><?= $tgl_lahir ?></div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[12%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['nama_ibu'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['nama_ibu'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[10%]">
                                <div class="flex justify-center">
                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium truncate max-w-full" title="<?= htmlspecialchars($g['status_pegawai']) ?>">
                                        <?= htmlspecialchars($g['status_pegawai']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['jenis_gtk'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['jenis_gtk'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($g['jabatan'] ?? '-') ?>">
                                    <?= htmlspecialchars($g['jabatan'] ?? '-') ?>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-3 w-[12%]">
                                <div class="flex justify-center gap-1">
                                    <a href="guru-detail.php?nik=<?= $g['nik'] ?>&jenjang=<?= $jenjang ?>&page=<?= $page ?>"
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                        title="Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="guru-update.php?nik=<?= $g['nik'] ?>&jenjang=<?= $jenjang ?>&page=<?= $page ?>"
                                            class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                            title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="<?= $action ?>?page=<?= $page ?>" method="POST"
                                            onsubmit="return confirm('Yakin hapus guru ini?')" class="inline">
                                            <input type="hidden" name="delete_id" value="<?= $g['nik'] ?>">
                                            <button type="submit"
                                                class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
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
