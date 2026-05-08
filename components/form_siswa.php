<?php
$inp = fn(string $field) => htmlspecialchars($post[$field] ?? '');
$err = fn(string $field) => !empty($errors[$field])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$field]) . '</p>'
    : '';
$hasError = fn(string $field) => !empty($errors[$field]) ? 'border-red-500 ring-red-100' : 'border-gray-200';
?>

<form method="POST" class="relative">
    <input type="hidden" name="action" value="save">

    <!-- HIDDEN BACKGROUND & DECORATION -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 -z-10"></div>

    <!-- MAIN CONTAINER -->
    <div class="max-w-7xl mx-auto">

        <!-- FORM CARD -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl shadow-indigo-100/50 border border-white/50 overflow-hidden">

            <!-- DATA PRIBADI -->
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-indigo-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i class="fa-solid fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Data Pribadi Siswa</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Identitas lengkap siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Nama - full width on mobile -->
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" value="<?= $inp('nama') ?>" <?= $disabled ?>
                            placeholder="Masukkan nama lengkap siswa"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nama') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nama') ?>
                    </div>

                    <!-- NIS -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            NIS <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nis" value="<?= $inp('nis') ?>" <?= $disabled ?>
                            placeholder="Nomor Induk Siswa"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nis') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nis') ?>
                    </div>

                    <!-- NISN -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            NISN <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nisn" maxlength="10" value="<?= $inp('nisn') ?>" <?= $disabled ?>
                            placeholder="10 digit NISN"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nisn') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nisn') ?>
                    </div>

                    <!-- NIK -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            NIK
                        </label>
                        <input type="text" name="nik" maxlength="16" value="<?= $inp('nik') ?>" <?= $disabled ?>
                            placeholder="16 digit NIK"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nik') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nik') ?>
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="tempat_lahir" value="<?= $inp('tempat_lahir') ?>" <?= $disabled ?>
                            placeholder="Kota/Kabupaten lahir"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('tempat_lahir') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('tempat_lahir') ?>
                    </div>

                    <!-- Tgl Lahir -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tgl_lahir" value="<?= $inp('tgl_lahir') ?>" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('tgl_lahir') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('tgl_lahir') ?>
                    </div>

                    <!-- Agama -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Agama <span class="text-red-500">*</span>
                        </label>
                        <select name="id_agama" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('id_agama') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Agama</option>
                            <?php foreach ($listAgama as $a): ?>
                                <option value="<?= $a['id_agama'] ?>" <?= $post['id_agama'] == $a['id_agama'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['nama_agama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('id_agama') ?>
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select name="id_kelas" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('id_kelas') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($listKelas as $k): ?>
                                <option value="<?= $k['id_kelas'] ?>" <?= $post['id_kelas'] == $k['id_kelas'] ? 'selected' : '' ?>>
                                    Kelas <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('id_kelas') ?>
                    </div>

                    <!-- Ruang -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ruang <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="ruang" value="<?= $inp('ruang') ?>" <?= $disabled ?>
                            placeholder="Ruang kelas"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('ruang') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('ruang') ?>
                    </div>

                    <!-- Tahun Masuk -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tahun Masuk
                        </label>
                        <input type="number" name="tahun_masuk" min="2000" max="<?= date('Y') ?>"
                            value="<?= $inp('tahun_masuk') ?>" <?= $disabled ?>
                            placeholder="Tahun ajaran masuk"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                    </div>
                </div>
            </div>

            <!-- DATA AYAH -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-slate-50/50">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-emerald-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                        <i class="fa-solid fa-person text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Data Ayah Kandung</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Informasi orang tua siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIK Ayah</label>
                        <input type="text" name="ayah_nik" maxlength="16" value="<?= $inp('ayah_nik') ?>" <?= $disabled ?>
                            placeholder="16 digit NIK Ayah"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Ayah</label>
                        <input type="text" name="ayah_nama" value="<?= $inp('ayah_nama') ?>" <?= $disabled ?>
                            placeholder="Nama lengkap ayah"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Lahir</label>
                        <input type="number" name="ayah_tahun_lahir" min="1940" max="2000" value="<?= $inp('ayah_tahun_lahir') ?>" <?= $disabled ?>
                            placeholder="Tahun lahir ayah"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pendidikan</label>
                        <input type="text" name="ayah_pendidikan" value="<?= $inp('ayah_pendidikan') ?>" <?= $disabled ?>
                            placeholder="Pendidikan terakhir"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pekerjaan</label>
                        <select name="ayah_pekerjaan" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Pekerjaan</option>
                            <?php foreach ($listPekerjaan as $p): ?>
                                <option value="<?= $p['id_pekerjaan'] ?>" <?= $post['ayah_pekerjaan'] == $p['id_pekerjaan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_pekerjaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penghasilan / Bulan</label>
                        <input type="text" name="ayah_penghasilan" value="<?= $inp('ayah_penghasilan') ?>" <?= $disabled ?>
                            placeholder="Rp. 5.000.000"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>
                </div>
            </div>

            <!-- DATA IBU -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-rose-50/30">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-rose-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-200">
                        <i class="fa-solid fa-person-dress text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Data Ibu Kandung</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Informasi orang tua siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIK Ibu</label>
                        <input type="text" name="ibu_nik" maxlength="16" value="<?= $inp('ibu_nik') ?>" <?= $disabled ?>
                            placeholder="16 digit NIK Ibu"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Ibu</label>
                        <input type="text" name="ibu_nama" value="<?= $inp('ibu_nama') ?>" <?= $disabled ?>
                            placeholder="Nama lengkap ibu"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Lahir</label>
                        <input type="number" name="ibu_tahun_lahir" min="1940" max="2005" value="<?= $inp('ibu_tahun_lahir') ?>" <?= $disabled ?>
                            placeholder="Tahun lahir ibu"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pendidikan</label>
                        <input type="text" name="ibu_pendidikan" value="<?= $inp('ibu_pendidikan') ?>" <?= $disabled ?>
                            placeholder="Pendidikan terakhir"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pekerjaan</label>
                        <select name="ibu_pekerjaan" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Pekerjaan</option>
                            <?php foreach ($listPekerjaan as $p): ?>
                                <option value="<?= $p['id_pekerjaan'] ?>" <?= $post['ibu_pekerjaan'] == $p['id_pekerjaan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_pekerjaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penghasilan / Bulan</label>
                        <input type="text" name="ibu_penghasilan" value="<?= $inp('ibu_penghasilan') ?>" <?= $disabled ?>
                            placeholder="Rp. 5.000.000"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-400 focus:ring-4 focus:ring-rose-100 transition-all duration-200 outline-none">
                    </div>
                </div>
            </div>

            <!-- DATA WALI -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-purple-50/30">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-purple-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-lg shadow-purple-200">
                        <i class="fa-solid fa-people-roof text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Data Wali
                            <span class="text-xs font-normal text-slate-400 ml-2">(Opsional)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Jika ada wali yang merawat siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIK Wali</label>
                        <input type="text" name="wali_nik" maxlength="16" value="<?= $inp('wali_nik') ?>" <?= $disabled ?>
                            placeholder="16 digit NIK Wali"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Wali</label>
                        <input type="text" name="wali_nama" value="<?= $inp('wali_nama') ?>" <?= $disabled ?>
                            placeholder="Nama lengkap wali"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Lahir</label>
                        <input type="number" name="wali_tahun_lahir" min="1940" max="2000" value="<?= $inp('wali_tahun_lahir') ?>" <?= $disabled ?>
                            placeholder="Tahun lahir wali"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pendidikan</label>
                        <input type="text" name="wali_pendidikan" value="<?= $inp('wali_pendidikan') ?>" <?= $disabled ?>
                            placeholder="Pendidikan terakhir"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Pekerjaan</label>
                        <select name="wali_pekerjaan" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Pekerjaan</option>
                            <?php foreach ($listPekerjaan as $p): ?>
                                <option value="<?= $p['id_pekerjaan'] ?>" <?= $post['wali_pekerjaan'] == $p['id_pekerjaan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama_pekerjaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penghasilan / Bulan</label>
                        <input type="text" name="wali_penghasilan" value="<?= $inp('wali_penghasilan') ?>" <?= $disabled ?>
                            placeholder="Rp. 5.000.000"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-400 focus:ring-4 focus:ring-purple-100 transition-all duration-200 outline-none">
                    </div>
                </div>
            </div>

            <!-- ALAMAT -->
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-amber-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-200">
                        <i class="fa-solid fa-location-dot text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Alamat Lengkap</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Alamat tempat tinggal siswa</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Jalan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alamat" rows="3" <?= $disabled ?>
                            placeholder="Nama jalan, gang, nomor rumah"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('alamat') ?> focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none resize-none"><?= $inp('alamat') ?></textarea>
                        <?= $err('alamat') ?>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                RT/RW <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="rt_rw" value="<?= $inp('rt_rw') ?>" <?= $disabled ?>
                                placeholder="RT 01 / RW 02"
                                class="w-full px-4 py-3 rounded-xl border <?= $hasError('rt_rw') ?> focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none">
                            <?= $err('rt_rw') ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Dusun <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="dusun" value="<?= $inp('dusun') ?>" <?= $disabled ?>
                                placeholder="Dusun/lingkungan"
                                class="w-full px-4 py-3 rounded-xl border <?= $hasError('dusun') ?> focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none">
                            <?= $err('dusun') ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Kelurahan/Desa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kelurahan" value="<?= $inp('kelurahan') ?>" <?= $disabled ?>
                                placeholder="Kelurahan/desa"
                                class="w-full px-4 py-3 rounded-xl border <?= $hasError('kelurahan') ?> focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none">
                            <?= $err('kelurahan') ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Kecamatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kecamatan" value="<?= $inp('kecamatan') ?>" <?= $disabled ?>
                                placeholder="Kecamatan"
                                class="w-full px-4 py-3 rounded-xl border <?= $hasError('kecamatan') ?> focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none">
                            <?= $err('kecamatan') ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kode Pos</label>
                            <input type="text" name="kode_pos" maxlength="5" value="<?= $inp('kode_pos') ?>" <?= $disabled ?>
                                placeholder="Kode pos"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BUTTON ACTIONS -->
            <div class="p-6 sm:p-8 <?= ($canEdit || $formMode === 'create') ? 'bg-gradient-to-r from-slate-50 to-indigo-50/30' : 'bg-gradient-to-r from-slate-50 to-gray-50' ?> flex justify-end gap-3">

                <a href="<?= $back ?>" class="px-8 py-3 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-semibold border border-slate-200">
                    <i class="fa-solid <?= ($canEdit || $formMode === 'create') ? 'fa-times' : 'fa-arrow-left' ?> mr-2"></i>
                    <?= ($canEdit || $formMode === 'create') ? 'Batal' : 'Kembali' ?>
                </a>

                <?php if ($canEdit || $formMode === 'create'): ?>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-save mr-2"></i>
                        <?= $formMode === 'create' ? 'Tambah Siswa' : 'Simpan Perubahan' ?>
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>
</form>
