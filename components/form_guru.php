<div class="py-6 px-2 md:p-6">

    <form method="POST" class="space-y-10">
        <!-- DATA PRIBADI -->
        <div class="border-b pb-8 border-slate-200">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Pribadi</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="nama" required value="<?= htmlspecialchars($post['nama'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['nama']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php field_error('nama', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        NIK <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="nik" required maxlength="16" inputmode="numeric"
                            value="<?= htmlspecialchars($post['nik'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['nik']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nik', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">NUPTK</label>
                    <input type="text" name="nuptk" maxlength="16" value="<?= htmlspecialchars($post['nuptk'] ?? '') ?>"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <select name="jenis_kelamin" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="L" <?= ($post['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= ($post['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <?php field_error('jenis_kelamin', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Tempat Lahir <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="tempat_lahir" required value="<?= htmlspecialchars($post['tempat_lahir'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('tempat_lahir', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Tanggal Lahir <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="date" name="tgl_lahir" required value="<?= $post['tgl_lahir'] ?? '' ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('tgl_lahir', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Nama Ibu <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="nama_ibu" required value="<?= htmlspecialchars($post['nama_ibu'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nama_ibu', $errors); ?>
                    </div>
                </div>
            </div>
            <div class="xl:flex xl:items-center mt-6">
                <label class=" block w-full xl:w-[13%] text-sm font-medium text-gray-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                <div class="w-full">
                    <textarea name="alamat" required rows="3" class="w-full px-4 py-2.5 border <?= !empty($errors['alamat']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($post['alamat'] ?? '') ?></textarea>
                    <?php field_error('alamat', $errors); ?>
                </div>
            </div>
        </div>

        <!-- DATA KEPEGAWAIAN -->
        <div>
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Kepegawaian</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Status Pegawai <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="status_pegawai" required value="<?= htmlspecialchars($post['status_pegawai'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['status_pegawai']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php field_error('status_pegawai', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Jenis GTK <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="jenis_gtk" required value="<?= htmlspecialchars($post['jenis_gtk'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['jenis_gtk']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <?php field_error('jenis_gtk', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                    <input type="text" name="jabatan" value="<?= htmlspecialchars($post['jabatan'] ?? '') ?>"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ex: Wali Kelas 5A">
                </div>

            </div>
        </div>

        <!-- TOMBOL -->
        <div class="flex justify-end gap-4 pt-8 border-t border-slate-200">
            <a href="<?= $back ?>"
                class="px-8 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition">
                Batal
            </a>
            <button type="submit"
                class="px-8 py-3 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                <?= $buttonLable ?>
            </button>
        </div>
    </form>
</div>
