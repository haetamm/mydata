<?php
// Ambil data untuk dropdown
$roles = $link->query("SELECT id_role, nama_role FROM role WHERE nama_role != 'Super Admin' ORDER BY nama_role")->fetchAll(PDO::FETCH_KEY_PAIR);
$levels = $link->query("SELECT id_level, CONCAT(jenjang, ' (Kelas ', level_min, '-', level_max, ')') AS label
                        FROM master_level_kelas WHERE deleted_at IS NULL")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="py-6 px-3 md:p-10">
    <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">

        <!-- Nama & Username -->
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama_lengkap"
                    value="<?= htmlspecialchars($post['nama_lengkap'] ?? '') ?>" required
                    class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 <?= isset($errors['nama_lengkap']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (isset($errors['nama_lengkap'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $errors['nama_lengkap'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Username <span class="text-red-500">*</span>
                </label>
                <input type="text" name="username" minlength="4"
                    value="<?= htmlspecialchars($post['username'] ?? '') ?>" required
                    class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 <?= isset($errors['username']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (isset($errors['username'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $errors['username'] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Role & Jenjang -->
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select name="id_role" class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 <?= isset($errors['id_role']) ? 'border-red-500' : 'border-gray-300' ?>" required>
                    <option value="">-- Pilih Role --</option>
                    <?php foreach ($roles as $id => $nama): ?>
                        <option value="<?= $id ?>" <?= ($post['id_role'] ?? '') == $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Jenjang Akses <span class="text-red-500">*</span>
                </label>
                <select name="level_id" class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 <?= isset($errors['level_id']) ? 'border-red-500' : 'border-gray-300' ?>" required>
                    <option value="">Pilih akses</option>
                    <?php foreach ($levels as $id => $label): ?>
                        <option value="<?= $id ?>" <?= ($post['level_id'] ?? '') == $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Password -->
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" minlength="6"
                    class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (isset($errors['password'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Konfirmasi Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password2"
                    class="w-full px-4 py-3 border focus:outline-none rounded-lg focus:ring-2 focus:ring-blue-500 <?= isset($errors['password2']) ? 'border-red-500' : 'border-gray-300' ?>">
                <?php if (isset($errors['password2'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= $errors['password2'] ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-4 pt-6">
            <a href="<?= $back ?>"
                class="px-8 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition">
                Batal
            </a>
            <button type="submit"
                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg transition">
                <?= $buttonLable ?>
            </button>
        </div>
    </form>
</div>
