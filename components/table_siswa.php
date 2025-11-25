<div class="hidden lg:block mt-6 h-screen <?= $pesan || $pesan_cari ? 'lg:h-[calc(100vh-265px)]' : 'lg:h-[calc(100vh-183px)]' ?> overflow-hidden rounded-t-xl">
    <div class="overflow-y-auto h-full noscrollbar">
        <table class="min-w-full bg-white table-fixed">
            <!-- THEAD -->
            <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                <tr>
                    <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                    <th class="py-4 px-3 text-left font-semibold w-[18%]">Nama</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">NIS</th>
                    <th class="py-4 px-3 text-left font-semibold w-[12%]">NISN</th>
                    <th class="py-4 px-3 text-left font-semibold w-[12%]">Tempat Lahir</th>
                    <th class="py-4 px-3 text-left font-semibold w-[10%]">Tgl Lahir</th>
                    <th class="py-4 px-3 text-left font-semibold w-[7%]">Agama</th>
                    <th class="py-4 px-3 text-left font-semibold w-[8%]">Kelas</th>
                    <th class="py-4 px-3 text-left font-semibold w-[5%]">Ruang</th>
                    <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[13%]">Aksi</th>
                </tr>
            </thead>

            <!-- TBODY -->
            <tbody class="text-gray-700 text-sm">
                <?php if (empty($dataSiswa)): ?>
                    <tr>
                        <td colspan="10" class="py-24 px-6 text-center items-center text-gray-500 font-medium text-lg">
                            <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4"></i>
                            <p>
                                Data siswa tidak tersedia
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dataSiswa as $index => $s): ?>
                        <?php
                        $no = $offset + $index + 1;
                        $tgl_lahir = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
                        $kelas = $s['nama_kelas'] ?? '-';
                        $ruang = $s['ruang'] ?? '-';
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                            <td class="py-4 px-3 font-medium text-center w-[5%]">
                                <div class="truncate" title="<?= $no ?>"><?= $no ?></div>
                            </td>
                            <td class="py-4 px-3 font-semibold text-indigo-800 w-[18%]">
                                <div class="truncate" title="<?= htmlspecialchars($s['nama']) ?>">
                                    <?= htmlspecialchars($s['nama']) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[10%]">
                                <div class="truncate" title="<?= htmlspecialchars($s['nis'] ?? '-') ?>">
                                    <?= htmlspecialchars($s['nis'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[12%]">
                                <div class="truncate" title="<?= htmlspecialchars($s['nisn'] ?? '-') ?>">
                                    <?= htmlspecialchars($s['nisn'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[12%]">
                                <div class="truncate" title="<?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?>">
                                    <?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[10%]">
                                <div class="truncate" title="<?= $tgl_lahir ?>"><?= $tgl_lahir ?></div>
                            </td>
                            <td class="py-4 px-3 text-gray-600 w-[7%]">
                                <div class="truncate" title="<?= htmlspecialchars($s['nama_agama'] ?? '-') ?>">
                                    <?= htmlspecialchars($s['nama_agama'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[8%]">
                                <div class="truncate text-center" title="<?= htmlspecialchars($kelas) ?>">
                                    <?= htmlspecialchars($kelas) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[5%]">
                                <div class="truncate text-center" title="<?= htmlspecialchars($ruang) ?>">
                                    <?= htmlspecialchars($ruang) ?>
                                </div>
                            </td>
                            <td class="py-4 px-3 w-[13%]">
                                <div class="flex justify-center gap-1">
                                    <a href="siswa-detail.php?nis=<?= $s['nis'] ?>&jenjang=<?= $jenjang ?>"
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                        title="Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="siswa-update.php?nis=<?= $s['nis'] ?>&jenjang=<?= $jenjang ?>&page=<?= $page ?>"
                                            class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-xs font-medium flex items-center gap-1"
                                            title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="<?= $action ?>?page=<?= $page ?>" method="POST"
                                            onsubmit="return confirm('Yakin hapus siswa ini?')" class="inline">
                                            <input type="hidden" name="delete_id" value="<?= $s['nis'] ?>">
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
