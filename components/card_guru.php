<div class="block lg:hidden mt-6 ">
    <?php if (empty($dataGuru)): ?>
        <div class="text-center py-20">
            <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4 blocks"></i>
            <p class="text-gray-500 text-lg font-medium">Data guru tidak tersedia</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($dataGuru as $index => $g): ?>
                <?php
                $no = $offset + $index + 1;
                $tgl_lahir = $g['tgl_lahir'] ? date('d M Y', strtotime($g['tgl_lahir'])) : '-';
                $jk = $g['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan';
                $status_color = match ($g['status_pegawai'])
                {
                    'PNS' => 'bg-emerald-100 text-emerald-800',
                    'Honorer', 'GTT' => 'bg-amber-100 text-amber-800',
                    default => 'bg-gray-100 text-gray-800'
                };
                ?>

                <!-- CARD GURU -->
                <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                    <div class="bg-linear-to-r from-blue-500 to-indigo-600 text-white p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs opacity-90">Guru #<?= $no ?></p>
                                <h3 class="text-lg font-bold"><?= htmlspecialchars($g['nama']) ?></h3>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur">
                                    <?= $jk ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Info Utama -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 text-xs">NIK</p>
                                <p class="font-semibold text-indigo-700"><?= htmlspecialchars($g['nik']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">NUPTK</p>
                                <p class="font-mono text-xs"><?= htmlspecialchars($g['nuptk'] ?? '-') ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">Tempat, Tgl Lahir</p>
                                <p class="font-medium"><?= htmlspecialchars($g['tempat_lahir']) ?>, <?= $tgl_lahir ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-xs">Ibu Kandung</p>
                                <p class="font-medium"><?= htmlspecialchars($g['nama_ibu']) ?></p>
                            </div>
                        </div>

                        <!-- Status & Jabatan -->
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 <?= $status_color ?> rounded-full text-xs font-bold">
                                <?= htmlspecialchars($g['status_pegawai']) ?>
                            </span>
                            <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                <?= htmlspecialchars($g['jenis_gtk'] ?? 'Guru') ?>
                            </span>
                        </div>

                        <?php if ($g['jabatan']): ?>
                            <div class="border-t pt-3 border-slate-200">
                                <p class="text-xs text-gray-500">Jabatan</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($g['jabatan']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Aksi Buttons -->
                    <div class="bg-gray-50 px-5 py-4 border-t border-slate-200 flex <?= isAdmin() ? 'justify-between' : 'justify-end' ?> gap-3">
                        <a href="guru-detail.php?nik=<?= $g['nik'] ?>&jenjang=<?= $jenjang ?>&page=<?= $page ?> "
                            class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 hover:shadow-md transform hover:scale-105 transition-all duration-200 text-sm font-medium">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="guru-update.php?nik=<?= $g['nik'] ?>&jenjang=<?= $jenjang ?>&page=<?= $page ?> "
                                class="flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 hover:shadow-md transform hover:scale-105 transition-all duration-200 text-sm font-medium">
                                <i class="fa-solid fa-edit"></i>
                            </a>
                            <form action="<?= $action ?>?page=<?= $page ?>" method="POST" class="inline"
                                onsubmit="return confirm('Yakin ingin menghapus guru <?= addslashes(htmlspecialchars($g['nama'])) ?>?')">
                                <input type="hidden" name="delete_id" value="<?= $g['nik'] ?>">
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 hover:shadow-md transform hover:scale-105 transition-all duration-200 text-sm font-medium">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
