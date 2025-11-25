<div class="bg-white shadow-lg rounded-b-xl p-5 sm:p-8">
    <?php if (isset($_SESSION['msg_success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['msg_success']) ?>
        </div>
        <?php unset($_SESSION['msg_success']); ?>
    <?php endif; ?>

    <form method="POST" class="space-y-8 w-full mx-auto">

        <!-- DATA PRIBADI -->
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
                            value="<?= htmlspecialchars($post['nama_lengkap'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['nama_lengkap']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('nama_lengkap', $errors); ?>
                    </div>
                </div>

                <!-- Username -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <div class="w-full">
                        <input type="text" name="username" required minlength="4"
                            value="<?= htmlspecialchars($post['username'] ?? '') ?>"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['username']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('username', $errors); ?>
                    </div>
                </div>



                <!-- Password Baru -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Password Baru
                    </label>
                    <div class="w-full">
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin ganti"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('password', $errors); ?>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="w-full">
                        <input type="password" name="password2"
                            class="w-full px-4 py-2.5 border <?= !empty($errors['password2']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php field_error('password2', $errors); ?>
                    </div>
                </div>

                <!-- Akses -->
                <div class="xl:flex xl:items-center">
                    <label class="block w-full xl:w-[30%] text-sm font-semibold text-gray-700 mb-2">
                        Akses
                    </label>
                    <div class="w-full">
                        <input type="text"
                            value="<?= $user['jenjang'] ? htmlspecialchars($user['jenjang']) : 'Semua Jenjang (Super Admin)' ?>"
                            class="w-full px-4 py-2.5 border border-gray-300 bg-gray-50 text-gray-600 rounded-lg" disabled>
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
