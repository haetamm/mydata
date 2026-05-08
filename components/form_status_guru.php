 <!-- form status guru -->
 <?php
    $statusOptions = [
        'aktif'     => 'Aktif',
        'pensiun'   => 'Pensiun',
        'pindah'    => 'Pindah',
        'keluar'    => 'Keluar',
        'meninggal' => 'Meninggal',
    ];

    $statusColor = match ($post['status'] ?? 'aktif')
    {
        'aktif'     => 'text-emerald-600',
        'pensiun'   => 'text-blue-600',
        'pindah'    => 'text-amber-600',
        'keluar'    => 'text-orange-600',
        'meninggal' => 'text-red-700',
        default     => 'text-gray-600',
    };

    $statusBg = match ($post['status'] ?? 'aktif')
    {
        'aktif'     => 'bg-emerald-50 border-emerald-200',
        'pensiun'   => 'bg-blue-50 border-blue-200',
        'pindah'    => 'bg-amber-50 border-amber-200',
        'keluar'    => 'bg-orange-50 border-orange-200',
        'meninggal' => 'bg-red-50 border-red-200',
        default     => 'bg-gray-50 border-gray-200',
    };

    $formTitle = $formMode === 'create' ? 'Tambah Guru' : 'Edit Guru';
    ?>
 <div class="max-w-7xl mx-auto mt-3">
     <div class="bg-white">
         <div class="p-4">
             <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">

                 <!-- Label (desktop) -->
                 <div class="hidden lg:flex items-center gap-3">
                     <i class="fa-solid fa-chalkboard-teacher text-indigo-600 text-xl"></i>
                     <h1 class="text-lg font-bold text-gray-800">
                         <?= $formTitle ?>
                     </h1>
                 </div>

                 <!-- Form toggle status -->
                 <form method="POST" class="w-full lg:w-auto lg:flex-initial">
                     <input type="hidden" name="action" value="toggle_status">

                     <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                         <div class="flex items-center gap-2 text-sm text-gray-600 whitespace-nowrap">
                             <i class="fa-solid fa-circle-dot"></i>
                             <span>Status:</span>
                         </div>

                         <?php if ($canDelete): ?>
                             <select name="status"
                                 class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 <?= $statusColor ?>">
                                 <?php foreach ($statusOptions as $val => $label): ?>
                                     <option value="<?= $val ?>"
                                         <?= ($post['status'] ?? '') === $val ? 'selected' : '' ?>>
                                         <?= $label ?>
                                     </option>
                                 <?php endforeach; ?>
                             </select>

                             <input type="text" name="status_keterangan"
                                 value="<?= htmlspecialchars($post['status_keterangan'] ?? '') ?>"
                                 placeholder="Keterangan (opsional)"
                                 class="flex-1 px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">

                             <button type="submit"
                                 class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium whitespace-nowrap transition">
                                 Simpan
                             </button>
                         <?php else: ?>
                             <span class="px-3 py-1.5 rounded-lg text-sm border <?= $statusBg ?> <?= $statusColor ?>">
                                 <?= $statusOptions[$post['status']] ?? $post['status'] ?>
                             </span>
                             <?php if (!empty($post['status_keterangan'])): ?>
                                 <span class="text-sm text-gray-500">
                                     <?= htmlspecialchars($post['status_keterangan']) ?>
                                 </span>
                             <?php endif; ?>
                         <?php endif; ?>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
