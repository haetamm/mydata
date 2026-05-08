<?php
$inp      = fn(string $field) => htmlspecialchars($post[$field] ?? '');
$err      = fn(string $field) => !empty($errors[$field])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$field]) . '</p>'
    : '';
$hasError = fn(string $field) => !empty($errors[$field])
    ? 'border-red-500 ring-1 ring-red-200'
    : 'border-gray-200';

// Pilihan jenis kelamin, status pegawai, jenis GTK
$jenisKelaminOptions = ['L' => 'Laki-laki', 'P' => 'Perempuan'];

$jenisGtkOptions = [
    'Guru Kelas'    => 'Guru Kelas',
    'Guru Mapel'    => 'Guru Mapel',
    'Guru Agama'    => 'Guru Agama Islam',
    'Guru BK'       => 'Guru BK',
    'Pend. Jasmani' => 'Pend. Jasmani (PJOK)',
    'Guru B. Inggris' => 'Guru B. Inggris',
    'Kepala Sekolah'  => 'Kepala Sekolah',
    'Wakil KS'        => 'Wakil Kepala Sekolah',
    'Tenaga Admin'    => 'Tenaga Administrasi',
];

$statusPegawaiOptions = [
    'PNS'     => 'PNS',
    'PPPK'    => 'PPPK',
    'Honorer' => 'Honorer',
    'GTT'     => 'GTT (Guru Tidak Tetap)',
    'Kontrak' => 'Kontrak',
];

?>
<form method="POST" class="relative">
    <input type="hidden" name="action" value="save">

    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 -z-10"></div>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl shadow-indigo-100/50 border border-white/50 overflow-hidden">


            <!-- ── SEKSI 1: DATA IDENTITAS ── -->
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-indigo-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i class="fa-solid fa-id-card text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Data Identitas Guru</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Identitas lengkap dan data kepegawaian</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Nama — full width -->
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" value="<?= $inp('nama') ?>" <?= $disabled ?>
                            placeholder="Masukkan nama lengkap guru"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nama') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nama') ?>
                    </div>

                    <!-- NIK -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NIK</label>
                        <input type="text" name="nik" maxlength="16" value="<?= $inp('nik') ?>" <?= $disabled ?>
                            placeholder="16 digit NIK"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nik') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nik') ?>
                    </div>

                    <!-- NUPTK -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">NUPTK</label>
                        <input type="text" name="nuptk" maxlength="20" value="<?= $inp('nuptk') ?>" <?= $disabled ?>
                            placeholder="Nomor Unik PTK"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nuptk') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nuptk') ?>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_kelamin" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('jenis_kelamin') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih</option>
                            <?php foreach ($jenisKelaminOptions as $val => $label): ?>
                                <option value="<?= $val ?>"
                                    <?= ($post['jenis_kelamin'] ?? '') === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('jenis_kelamin') ?>
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?= $inp('tempat_lahir') ?>" <?= $disabled ?>
                            placeholder="Kota/Kabupaten lahir"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('tempat_lahir') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('tempat_lahir') ?>
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" value="<?= $inp('tgl_lahir') ?>" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('tgl_lahir') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('tgl_lahir') ?>
                    </div>

                    <!-- Nama Ibu Kandung -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Ibu Kandung</label>
                        <input type="text" name="nama_ibu" value="<?= $inp('nama_ibu') ?>" <?= $disabled ?>
                            placeholder="Nama ibu kandung"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                    </div>

                </div>
            </div>


            <!-- ── SEKSI 2: DATA KEPEGAWAIAN ── -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-emerald-50/30">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-emerald-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-200">
                        <i class="fa-solid fa-briefcase text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Data Kepegawaian</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Informasi jabatan dan status kepegawaian</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

                    <!-- Status Pegawai -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Status Pegawai</label>
                        <select name="status_pegawai" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Status</option>
                            <?php foreach ($statusPegawaiOptions as $val => $label): ?>
                                <option value="<?= $val ?>"
                                    <?= ($post['status_pegawai'] ?? '') === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jenis GTK -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jenis GTK</label>
                        <select name="jenis_gtk" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none bg-white">
                            <option value="">Pilih Jenis GTK</option>
                            <?php foreach ($jenisGtkOptions as $val => $label): ?>
                                <option value="<?= $val ?>"
                                    <?= ($post['jenis_gtk'] ?? '') === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Jabatan</label>
                        <input type="text" name="jabatan" value="<?= $inp('jabatan') ?>" <?= $disabled ?>
                            placeholder="Contoh: Wali Kelas 6A, Waka Kurikulum"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                    </div>

                    <!-- Tahun Masuk -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Masuk</label>
                        <input type="number" name="tahun_masuk"
                            min="1970" max="<?= date('Y') ?>"
                            value="<?= $inp('tahun_masuk') ?>" <?= $disabled ?>
                            placeholder="Tahun pertama mengajar di sekolah ini"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('tahun_masuk') ?> focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 outline-none">
                        <?= $err('tahun_masuk') ?>
                    </div>

                    <!-- Tahun Keluar (read-only, diisi otomatis saat status berubah) -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Tahun Keluar
                            <span class="text-xs font-normal text-slate-400 ml-1">(otomatis)</span>
                        </label>
                        <input type="text"
                            value="<?= $inp('tahun_keluar') ?: '-' ?>"
                            disabled
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 outline-none cursor-not-allowed">
                    </div>

                </div>
            </div>


            <!-- ── SEKSI 3: ALAMAT ── -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-amber-50/30">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-amber-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-200">
                        <i class="fa-solid fa-location-dot text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Alamat</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Alamat tempat tinggal guru</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Lengkap</label>
                    <textarea name="alamat" rows="4" <?= $disabled ?>
                        placeholder="Jalan, gang, nomor rumah, RT/RW, dusun, kelurahan, kecamatan, kota/kabupaten"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-amber-400 focus:ring-4 focus:ring-amber-100 transition-all duration-200 outline-none resize-none"><?= $inp('alamat') ?></textarea>
                </div>
            </div>


            <!-- ── TOMBOL AKSI ── -->
            <div class="p-6 sm:p-8 <?= ($canEdit || $formMode === 'create') ? 'bg-gradient-to-r from-slate-50 to-indigo-50/30' : 'bg-slate-50' ?> flex justify-end gap-3">

                <a href="<?= $back ?>"
                    class="px-8 py-3 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-sm font-semibold border border-slate-200 transition flex items-center gap-2">
                    <i class="fa-solid <?= ($canEdit || $formMode === 'create') ? 'fa-times' : 'fa-arrow-left' ?>"></i>
                    <?= ($canEdit || $formMode === 'create') ? 'Batal' : 'Kembali' ?>
                </a>

                <?php if ($canEdit || $formMode === 'create'): ?>
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl text-sm font-semibold transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <?= $formMode === 'create' ? 'Tambah Guru' : 'Simpan Perubahan' ?>
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>
</form>
