<?php
$resultAgama = getAgama($link);
$agama = $resultAgama['data'];

$resultPekerjaan = getPekerjaan($link);
$pekerjaan = $resultPekerjaan['data'];

$resultKelas = getKelasByJenjang($link, $jenjangUpper);
$kelas = $resultKelas['data'];

$resultSemester = getSemester($link);
$semester = $resultSemester['data'];

$resultTahunPelajaran = getTahunPelajaran($link);
$tahunPelajaran = $resultTahunPelajaran['data'];

?>

<div class="py-6 px-1 md:p-6">
    <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-10">

        <!-- DATA PRIBADI -->
        <div class="px-2 xl:px-4">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Pribadi Siswa</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="text" required name="nama" value="<?= htmlspecialchars($post['nama'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['nama']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nama', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NIS <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="text" required name="nis" value="<?= htmlspecialchars($post['nis'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['nis']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nis', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NISN <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="text" required name="nisn" maxlength="10" value="<?= htmlspecialchars($post['nisn'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['nisn']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nisn', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NIK</label>
                    <div class="w-full">
                        <input type="text" name="nik" maxlength="16" value="<?= htmlspecialchars($post['nik'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['nik']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nik', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="text" required name="tempat_lahir" value="<?= htmlspecialchars($post['tempat_lahir'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['tempat_lahir']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('tempat_lahir', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="date" required name="tgl_lahir" value="<?= $post['tgl_lahir'] ?? '' ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['tgl_lahir']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('tgl_lahir', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <select name="id_kelas" required class="w-full px-4 py-3 border <?= !empty($errors['id_kelas']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Kelas</option>
                            <?php foreach ($kelas as $row): ?>
                                <option value="<?= $row['id_kelas'] ?>" <?= ($post['id_kelas'] ?? '') == $row['id_kelas'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php field_error('id_kelas', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Ruang <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <input type="text" required name="ruang" value="<?= htmlspecialchars($post['ruang'] ?? '') ?>"
                            class="w-full px-4 py-3 border <?= !empty($errors['ruang']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('ruang', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <select name="id_semester" required class="w-full px-4 py-3 border <?= !empty($errors['id_semester']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Semester</option>
                            <?php foreach ($semester as $row): ?>
                                <option value="<?= $row['id_semester'] ?>" <?= ($post['id_semester'] ?? '') == $row['id_semester'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php field_error('id_semester', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tahun Pelajaran <span class="text-red-500">*</span></label>
                    <div class="w-full">
                        <select name="id_tahun_pelajaran" required class="w-full px-4 py-3 border <?= !empty($errors['id_tahun_pelajaran']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Tahun</option>
                            <?php foreach ($tahunPelajaran as $row): ?>
                                <option value="<?= $row['id_tahun'] ?>" <?= ($post['id_tahun_pelajaran'] ?? '') == $row['id_tahun'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['tahun']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php field_error('id_tahun_pelajaran', $errors); ?>
                    </div>
                </div>

                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">
                        Agama <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <select name="id_agama" required class="w-full px-4 py-3 border <?= !empty($errors['id_agama']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Agama</option>
                            <?php foreach ($agama as $row): ?>
                                <option value="<?= $row['id_agama'] ?>" <?= ($post['id_agama'] ?? '') == $row['id_agama'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_agama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php field_error('id_agama', $errors); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- AYAH -->
        <div class="pt-8 border-t border-gray-300 px-2 xl:px-4">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Ayah Kandung</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NIK Ayah</label>
                    <input type="text" name="ayah_nik" maxlength="16" value="<?= htmlspecialchars($post['ayah_nik'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Nama Ayah</label>
                    <input type="text" name="ayah_nama" value="<?= htmlspecialchars($post['ayah_nama'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tahun Lahir</label>
                    <input type="number" name="ayah_tahun_lahir" min="1940" max="2000" value="<?= $post['ayah_tahun_lahir'] ?? '' ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <input type="text" name="ayah_pendidikan" value="<?= htmlspecialchars($post['ayah_pendidikan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <select name="ayah_pekerjaan" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pekerjaan</option>
                        <?php foreach ($pekerjaan as $row): ?>
                            <option value="<?= $row['id_pekerjaan'] ?>" <?= ($post['ayah_pekerjaan'] ?? '') == $row['id_pekerjaan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nama_pekerjaan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Penghasilan / Bulan</label>
                    <input type="text" name="ayah_penghasilan" value="<?= htmlspecialchars($post['ayah_penghasilan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- IBU -->
        <div class="pt-8 border-t border-gray-300 px-2 xl:px-4">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Ibu Kandung</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NIK Ibu</label>
                    <input type="text" name="ibu_nik" maxlength="16" value="<?= htmlspecialchars($post['ibu_nik'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Nama Ibu</label>
                    <input type="text" name="ibu_nama" value="<?= htmlspecialchars($post['ibu_nama'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tahun Lahir</label>
                    <input type="number" name="ibu_tahun_lahir" min="1940" max="2005" value="<?= $post['ibu_tahun_lahir'] ?? '' ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <input type="text" name="ibu_pendidikan" value="<?= htmlspecialchars($post['ibu_pendidikan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <select name="ibu_pekerjaan" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pekerjaan</option>
                        <?php foreach ($pekerjaan as $row): ?>
                            <option value="<?= $row['id_pekerjaan'] ?>" <?= ($post['ibu_pekerjaan'] ?? '') == $row['id_pekerjaan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nama_pekerjaan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Penghasilan / Bulan</label>
                    <input type="text" name="ibu_penghasilan" value="<?= htmlspecialchars($post['ibu_penghasilan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- WALI -->
        <div class="pt-8 border-t border-gray-300 px-2 xl:px-4">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Data Wali <span class="text-sm font-normal text-gray-500">(Opsional)</span></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">NIK Wali</label>
                    <input type="text" name="wali_nik" maxlength="16" value="<?= htmlspecialchars($post['wali_nik'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Nama Wali</label>
                    <input type="text" name="wali_nama" value="<?= htmlspecialchars($post['wali_nama'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Tahun Lahir</label>
                    <input type="number" name="wali_tahun_lahir" min="1940" max="2000" value="<?= $post['wali_tahun_lahir'] ?? '' ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pendidikan</label>
                    <input type="text" name="wali_pendidikan" value="<?= htmlspecialchars($post['wali_pendidikan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                    <select name="wali_pekerjaan" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Pekerjaan</option>
                        <?php foreach ($pekerjaan as $row): ?>
                            <option value="<?= $row['id_pekerjaan'] ?>" <?= ($post['wali_pekerjaan'] ?? '') == $row['id_pekerjaan'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nama_pekerjaan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Penghasilan / Bulan</label>
                    <input type="text" name="wali_penghasilan" value="<?= htmlspecialchars($post['wali_penghasilan'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- ALAMAT -->
        <div class="pt-8 border-t border-gray-300 px-2 xl:px-4">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Alamat Lengkap</h2>
            <div class="space-y-6">
                <div>
                    <label class="block w-full xl:w-[30%] text-sm font-medium text-gray-700 mb-1">Jalan<span class="text-red-500">*</span></label>
                    <textarea name="alamat" required rows="2" class="w-full px-4 py-3 border <?= !empty($errors['alamat']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($post['alamat'] ?? '') ?></textarea>
                    <?php field_error('alamat', $errors); ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RT/RW <span class="text-red-500">*</span></label>
                        <input type="text" required name="rt_rw" value="<?= htmlspecialchars($post['rt_rw'] ?? '') ?>" class="w-full px-4 py-3 border <?= !empty($errors['rt_rw']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('rt_rw', $errors); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dusun <span class="text-red-500">*</span></label>
                        <input type="text" required name="dusun" value="<?= htmlspecialchars($post['dusun'] ?? '') ?>" class="w-full px-4 py-3 border <?= !empty($errors['dusun']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('dusun', $errors); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelurahan/Desa <span class="text-red-500">*</span></label>
                        <input type="text" required name="kelurahan" value="<?= htmlspecialchars($post['kelurahan'] ?? '') ?>" class="w-full px-4 py-3 border <?= !empty($errors['kelurahan']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('kelurahan', $errors); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan <span class="text-red-500">*</span></label>
                        <input type="text" required name="kecamatan" value="<?= htmlspecialchars($post['kecamatan'] ?? '') ?>" class="w-full px-4 py-3 border <?= !empty($errors['kecamatan']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('kecamatan', $errors); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos</label>
                        <input type="text" name="kode_pos" maxlength="5" value="<?= htmlspecialchars($post['kode_pos'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- TOMBOL -->
        <div class="flex justify-end gap-4">
            <a href="<?= $back ?>" class="px-8 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 transition">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                <?= $buttonLable ?>
            </button>
        </div>
    </form>
</div>
