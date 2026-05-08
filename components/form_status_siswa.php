<?php
$statusOptions = [
    'aktif'       => 'Aktif',
    'lulus'       => 'Lulus',
    'pindah'      => 'Pindah',
    'keluar'      => 'Keluar',
    'dikeluarkan' => 'Dikeluarkan',
];

$statusColor = match ($post['status'] ?? 'aktif')
{
    'aktif'       => 'text-green-600',
    'lulus'       => 'text-blue-600',
    'pindah'      => 'text-yellow-600',
    'keluar'      => 'text-orange-600',
    'dikeluarkan' => 'text-red-600',
    default       => 'text-gray-600',
};

$statusBg = match ($post['status'] ?? 'aktif')
{
    'aktif'       => 'bg-green-50 border-green-200',
    'lulus'       => 'bg-blue-50 border-blue-200',
    'pindah'      => 'bg-yellow-50 border-yellow-200',
    'keluar'      => 'bg-orange-50 border-orange-200',
    'dikeluarkan' => 'bg-red-50 border-red-200',
    default       => 'bg-gray-50 border-gray-200',
};

$formTitle = $formMode === 'create' ? 'Tambah Siswa' : 'Edit Siswa';
?>

<div class="max-w-7xl mx-auto mt-3">
    <div class="bg-white">
        <div class="p-4">
            <!-- DI ATAS MD: row, DI MD KE BAWAH: column -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                <!-- Header - hidden di lg -->
                <div class="hidden lg:flex items-center gap-3">
                    <i class="fa-solid fa-user-graduate text-indigo-600 text-xl"></i>
                    <h1 class="text-lg font-bold text-gray-800">
                        <?= $formTitle ?>
                    </h1>
                </div>

                <!-- Form Status - full width pas header hilang -->
                <form method="POST" class="w-full lg:w-auto lg:flex-initial">
                    <input type="hidden" name="action" value="toggle_status">

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Status:</span>
                        </div>

                        <?php if ($canDelete): ?>
                            <select name="status" class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 <?= $statusColor ?>">
                                <?php foreach ($statusOptions as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= ($post['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input type="text" name="status_keterangan" value="<?= htmlspecialchars($post['status_keterangan'] ?? '') ?>"
                                placeholder="Keterangan" class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">

                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm whitespace-nowrap">Simpan</button>
                        <?php else: ?>
                            <span class="px-3 py-1.5 rounded-lg text-sm border <?= $statusBg ?> <?= $statusColor ?>">
                                <?= $statusOptions[$post['status']] ?? $post['status'] ?>
                            </span>
                            <?php if (!empty($post['status_keterangan'])): ?>
                                <span class="text-sm text-gray-500"><?= htmlspecialchars($post['status_keterangan']) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
