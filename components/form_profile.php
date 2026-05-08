<?php
$inp      = fn(string $f) => htmlspecialchars($post[$f] ?? '');
$err      = fn(string $f) => !empty($errors[$f])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$f]) . '</p>'
    : '';
$hasError = fn(string $f) => !empty($errors[$f])
    ? 'border-red-500 ring-1 ring-red-200'
    : 'border-gray-200';
?>
<div class="bg-white shadow-lg rounded-b-xl p-5 sm:p-8">

    <form method="POST" class="space-y-8 w-full mx-auto">

        <div class="border-b pb-8 border-slate-200">
            <h2 class="text-2xl font-bold text-blue-700 mb-6">Informasi Akun</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Nama Lengkap -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="nama_lengkap" required
                            value="<?= $inp('nama_lengkap') ?>"
                            class="w-full px-4 py-2.5 border <?= $hasError('nama_lengkap') ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?= $err('nama_lengkap') ?>
                    </div>
                </div>

                <!-- Username -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="username" required minlength="4"
                            value="<?= $inp('username') ?>"
                            class="w-full px-4 py-2.5 border <?= $hasError('username') ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?= $err('username') ?>
                    </div>
                </div>

                <!-- Password Baru -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Password Baru
                    </label>
                    <div class="w-full">
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin ganti"
                            class="w-full px-4 py-2.5 border <?= $hasError('password') ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?= $err('password') ?>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="w-full">
                        <input type="password" name="password_konfirmasi"
                            class="w-full px-4 py-2.5 border <?= $hasError('password_konfirmasi') ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?= $err('password_konfirmasi') ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tombol -->
        <div class="flex justify-end gap-4">
            <button onclick="history.back()" type="button"
                class="px-8 py-3 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition">
                Batal
            </button>
            <button type="submit"
                class="px-8 py-3 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow">
                Update
            </button>
        </div>
    </form>
</div>
