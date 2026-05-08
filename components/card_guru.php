<div class="block lg:hidden mt-6">
    <?php if (empty($dataGuru)): ?>
        <div class="bg-gray-50 rounded-2xl shadow p-8 text-center">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-user-slash text-gray-500 text-xl"></i>
                </div>
                <p class="text-gray-600 font-medium">Data guru tidak tersedia</p>
            </div>
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
                    'PNS'            => 'bg-emerald-100 text-emerald-800',
                    'Honorer', 'GTT' => 'bg-amber-100 text-amber-800',
                    default          => 'bg-gray-100 text-gray-800'
                };
                ?>

                <!-- CARD GURU -->
                <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-4">
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

                            <!-- Status & Jabatan -->
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1.5 <?= $status_color ?> rounded-full text-xs font-bold">
                                    <?= htmlspecialchars($g['status_pegawai']) ?>
                                </span>
                                <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                    <?= htmlspecialchars($g['jenis_gtk'] ?? 'Guru') ?>
                                </span>
                            </div>
                        </div>


                        <?php if ($g['jabatan']): ?>
                            <div class="border-t pt-3 border-slate-200">
                                <p class="text-xs text-gray-500">Jabatan</p>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($g['jabatan']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Aksi Buttons -->
                    <div class="bg-gray-50 px-5 py-4 border-t border-slate-200 flex gap-3 ">

                        <a href="<?= buildGuruEditUrl($g['id_guru'], $filter, $jenjang, $page) ?>"
                            class="px-3 py-2 <?= $canEdit ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700' ?> flex-1 bg-linear-to-r from-amber-400 to-amber-500 text-gray-900 text-center py-2.5 rounded-xl hover:from-amber-500 hover:to-amber-600 text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg"
                            title="<?= $canEdit ? 'Edit' : 'Lihat' ?>">
                            <i class="fa-solid <?= $canEdit ? 'fa-edit' : 'fa-eye' ?>"></i>
                            <?= $canEdit ? 'Edit' : 'Lihat' ?>
                        </a>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
