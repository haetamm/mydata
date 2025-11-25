<div class="lg:hidden mt-6 md:mt-0">
    <!-- Card View untuk Mobile & Tablet (hidden di desktop) -->
    <div class="space-y-6">
        <?php if (empty($dataSiswa)): ?>
            <div class="bg-linear-to-r from-gray-50 to-gray-100 rounded-2xl shadow-lg p-8 text-center">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user-slash text-gray-500 text-xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">Data siswa tidak tersedia</p>
                </div>
            </div>
        <?php else: ?>
            <!-- INI YANG BENAR: Grid di LUAR loop -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($dataSiswa as $index => $s): ?>
                    <?php
                    $no = $offset + $index + 1;
                    $tgl_lahir = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
                    $kelas = $s['nama_kelas'] ? "{$s['nama_kelas']}" : '-';
                    ?>

                    <!-- Card per siswa -->
                    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <!-- Header Card -->
                        <div class="bg-linear-to-r from-blue-500 to-indigo-600 p-4 text-white">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                        <span class="font-bold text-sm"><?= $no ?></span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg"><?= htmlspecialchars($s['nama']) ?></h3>
                                        <p class="text-blue-100 text-sm"><?= htmlspecialchars($s['nis'] ?? '-') ?> / R - <?= htmlspecialchars($s['ruang']) ?></p>
                                    </div>
                                </div>
                                <span class="bg-white/20 backdrop-blur-sm text-white text-xs px-3 py-1.5 rounded-full font-semibold">
                                    <?= htmlspecialchars($kelas) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Body Card -->
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
                                    <p class="font-semibold text-gray-800"><?= $tgl_lahir ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-medium">Agama</p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($s['nama_agama'] ?? '-') ?></p>
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex gap-2 pt-4 border-t border-gray-100">
                                <a href="siswa-detail.php?nis=<?= $s['nis'] ?>&jenjang=<?= $jenjang ?>"
                                    class="flex-1 bg-linear-to-r from-blue-500 to-blue-600 text-white text-center py-2.5 rounded-xl hover:from-blue-600 hover:to-blue-700 text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg">
                                    <i class="fa-solid fa-eye text-xs"></i> Lihat
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="siswa-update.php?nis=<?= $s['nis'] ?>&jenjang=<?= $jenjang ?>"
                                        class="flex-1 bg-linear-to-r from-amber-400 to-amber-500 text-gray-900 text-center py-2.5 rounded-xl hover:from-amber-500 hover:to-amber-600 text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg">
                                        <i class="fa-solid fa-edit text-xs"></i> Edit
                                    </a>
                                    <form action="<?= $action ?>?page=<?= $page ?>" method="POST" class="flex-1"
                                        onsubmit="return confirm('Yakin hapus siswa ini?')">
                                        <input type="hidden" name="delete_id" value="<?= $s['nis'] ?>">
                                        <button type="submit"
                                            class="w-full bg-linear-to-r from-red-500 to-red-600 text-white py-2.5 rounded-xl hover:from-red-600 hover:to-red-700 text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg">
                                            <i class="fa-solid fa-trash text-xs"></i> Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- End Card -->
                <?php endforeach; ?>
            </div>
            <!-- End Grid -->
        <?php endif; ?>
    </div>
</div>
