<?php

$inp      = fn(string $field) => htmlspecialchars($post[$field] ?? '');
$err      = fn(string $field) => !empty($errors[$field])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$field]) . '</p>'
    : '';
$hasError = fn(string $field) => !empty($errors[$field])
    ? 'border-red-500 ring-1 ring-red-200'
    : 'border-gray-200';

?>

<form method="POST" class="relative">
    <input type="hidden" name="action" value="save">

    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 -z-10"></div>

    <div class="max-w-7xl mx-auto">
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-xl shadow-indigo-100/50 border border-white/50 overflow-hidden">


            <!-- ── SEKSI 1: IDENTITAS ── -->
            <div class="p-6 sm:p-8 border-b border-slate-100">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-indigo-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i class="fa-solid fa-id-card text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Identitas User</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Nama lengkap dan username untuk login</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Nama Lengkap -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap"
                            value="<?= $inp('nama_lengkap') ?>" <?= $disabled ?>
                            placeholder="Masukkan nama lengkap"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('nama_lengkap') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('nama_lengkap') ?>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username"
                            value="<?= $inp('username') ?>" <?= $disabled ?>
                            placeholder="Contoh: budi.santoso"
                            autocomplete="off"
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('username') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none">
                        <?= $err('username') ?>
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Role</label>
                        <select name="role_id" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border <?= $hasError('role_id') ?> focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none bg-white">
                            <option value="">— Pilih Role —</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role['id']) ?>"
                                    <?= ($post['role_id'] ?? '') === $role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('role_id') ?>
                    </div>

                    <!-- Level -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Level Jenjang
                        </label>
                        <select name="level_id" <?= $disabled ?>
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 outline-none bg-white">
                            <option value="">— Semua Jenjang —</option>
                            <?php foreach ($levels as $level): ?>
                                <option value="<?= htmlspecialchars((string) $level['id_level']) ?>"
                                    <?= ((string)($post['level_id'] ?? '')) === (string)$level['id_level'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(strtoupper($level['jenjang'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </div>


            <!-- ── SEKSI 2: PASSWORD ── -->
            <div class="p-6 sm:p-8 border-b border-slate-100 bg-gradient-to-r from-violet-50/30">
                <div class="flex items-center gap-3 mb-6 pb-2 border-b-2 border-violet-200">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg shadow-violet-200">
                        <i class="fa-solid fa-lock text-white text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Password</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <?= $formMode === 'create'
                                ? 'Buat password untuk akun ini'
                                : 'Kosongkan jika tidak ingin mengubah password' ?>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Password <?= $formMode === 'create' ? '<span class="text-red-500">*</span>' : '' ?>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="inputPassword"
                                value="" <?= $disabled ?>
                                placeholder="<?= $formMode === 'create' ? 'Minimal 6 karakter' : 'Kosongkan jika tidak diubah' ?>"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 pr-11 rounded-xl border <?= $hasError('password') ?> focus:border-violet-400 focus:ring-4 focus:ring-violet-100 transition-all duration-200 outline-none">
                            <button type="button" onclick="togglePw('inputPassword', 'eyePassword')"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eyePassword" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <?= $err('password') ?>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Konfirmasi Password <?= $formMode === 'create' ? '<span class="text-red-500">*</span>' : '' ?>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_konfirmasi" id="inputPasswordKonfirmasi"
                                value="" <?= $disabled ?>
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                                class="w-full px-4 py-3 pr-11 rounded-xl border <?= $hasError('password_konfirmasi') ?> focus:border-violet-400 focus:ring-4 focus:ring-violet-100 transition-all duration-200 outline-none">
                            <button type="button" onclick="togglePw('inputPasswordKonfirmasi', 'eyeKonfirmasi')"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eyeKonfirmasi" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <?= $err('password_konfirmasi') ?>
                    </div>

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
                        <?= $formMode === 'create' ? 'Tambah' : 'Simpan' ?>
                    </button>
                <?php endif; ?>

            </div>

        </div>
    </div>
</form>

<script>
    function togglePw(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
