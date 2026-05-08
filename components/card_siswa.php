    <div class="lg:hidden mt-6">
        <?php if (empty($dataSiswa)): ?>
            <div class="bg-gray-50 rounded-2xl shadow p-8 text-center">
                <div class="flex flex-col items-center space-y-3">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user-slash text-gray-500 text-xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">Data siswa tidak tersedia</p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($dataSiswa as $idx => $s):
                    $no       = $offset + $idx + 1;
                    $tglLahir = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
                    $kelas    = $s['nama_kelas'] ?? '-';
                    [$badgeClass, $badgeLabel] = statusBadgeCard($s['status']);
                ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">

                        <!-- Header -->
                        <div class="bg-linear-to-r from-blue-500 to-indigo-600 p-4 text-white">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                                        <span class="font-bold text-sm"><?= $no ?></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-lg truncate"><?= htmlspecialchars($s['nama']) ?></h3>
                                        <p class="text-blue-100 text-sm"><?= htmlspecialchars($s['nis'] ?? '-') ?> / R - <?= htmlspecialchars($s['ruang'] ?? '-') ?></p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="bg-white/20 text-white text-xs px-3 py-1 rounded-full font-semibold"><?= htmlspecialchars($kelas) ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                <div>
                                    <p class="text-gray-500 font-medium">NISN</p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['nisn'] ?? '-') ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Tempat Lahir</p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Tanggal Lahir</p>
                                    <p class="font-semibold text-gray-800"><?= $tglLahir ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Agama</p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['nama_agama'] ?? '-') ?></p>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-4 border-t border-gray-100">
                                <a href="<?= buildSiswaEditUrl($s['id_siswa'], $filter, $jenjang, $page) ?>"
                                    class="px-3 py-2 <?= $canEdit ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700' ?> flex-1 bg-linear-to-r from-amber-400 to-amber-500 text-gray-900 text-center py-2.5 rounded-xl hover:from-amber-500 hover:to-amber-600 text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg"
                                    title="<?= $canEdit ? 'Edit' : 'Lihat' ?>">
                                    <i class="fa-solid <?= $canEdit ? 'fa-edit' : 'fa-eye' ?>"></i>
                                    <?= $canEdit ? 'Edit' : 'Lihat' ?>
                                </a>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
