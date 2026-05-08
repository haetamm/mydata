 <?php
    $hasMsg   = $pesan || $fetchError;
    $tableH   = $hasMsg ? 'lg:h-[calc(100vh-310px)]' : 'lg:h-[calc(100vh-260px)]';
    ?>
 <div class="hidden lg:block mt-6 h-screen <?= $tableH ?> overflow-hidden rounded-t-xl">
     <div class="overflow-y-auto h-full noscrollbar">
         <table class="min-w-full bg-white table-fixed">
             <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                 <tr>
                     <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                     <th class="py-4 px-3 text-left font-semibold w-[18%]">Nama</th>
                     <th class="py-4 px-3 text-left font-semibold w-[10%]">NIS</th>
                     <th class="py-4 px-3 text-left font-semibold w-[11%]">NISN</th>
                     <th class="py-4 px-3 text-left font-semibold w-[11%]">Tempat Lahir</th>
                     <th class="py-4 px-3 text-left font-semibold w-[9%]">Tgl Lahir</th>
                     <th class="py-4 px-3 text-left font-semibold w-[7%]">Agama</th>
                     <th class="py-4 px-3 text-center font-semibold w-[7%]">Kelas</th>
                     <th class="py-4 px-3 text-center font-semibold w-[5%]">Ruang</th>
                     <th class="py-4 px-3 text-center font-semibold w-[8%]">Status</th>
                     <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[9%]">Aksi</th>
                 </tr>
             </thead>
             <tbody class="text-gray-700 text-sm">
                 <?php if (empty($dataSiswa)): ?>
                     <tr>
                         <td colspan="11" class="py-24 text-center text-gray-500 font-medium text-lg">
                             <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                             Data siswa tidak tersedia
                         </td>
                     </tr>
                 <?php else: ?>
                     <?php foreach ($dataSiswa as $idx => $s):
                            $no        = $offset + $idx + 1;
                            $tglLahir  = $s['tgl_lahir'] ? date('d/m/Y', strtotime($s['tgl_lahir'])) : '-';
                            [$badgeClass, $badgeLabel] = statusBadge($s['status']);
                        ?>
                         <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                             <td class="py-4 px-3 text-center font-medium"><?= $no ?></td>
                             <td class="py-4 px-3 font-semibold text-indigo-800">
                                 <div class="truncate" title="<?= htmlspecialchars($s['nama']) ?>"><?= htmlspecialchars($s['nama']) ?></div>
                             </td>
                             <td class="py-4 px-3">
                                 <div class="truncate"><?= htmlspecialchars($s['nis'] ?? '-') ?></div>
                             </td>
                             <td class="py-4 px-3">
                                 <div class="truncate"><?= htmlspecialchars($s['nisn'] ?? '-') ?></div>
                             </td>
                             <td class="py-4 px-3 text-gray-600">
                                 <div class="truncate"><?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?></div>
                             </td>
                             <td class="py-4 px-3">
                                 <div class="truncate"><?= $tglLahir ?></div>
                             </td>
                             <td class="py-4 px-3 text-gray-600">
                                 <div class="truncate"><?= htmlspecialchars($s['nama_agama'] ?? '-') ?></div>
                             </td>
                             <td class="py-4 px-3 text-center"><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></td>
                             <td class="py-4 px-3 text-center"><?= htmlspecialchars($s['ruang'] ?? '-') ?></td>
                             <td class="py-4 px-3 text-center">
                                 <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                             </td>
                             <td class="py-4 px-3">
                                 <div class="flex justify-center gap-1">
                                     <a href="<?= buildSiswaEditUrl($s['id_siswa'], $filter, $jenjang, $page) ?>"
                                         class="px-3 py-2 <?= $canEdit ? 'bg-amber-500 hover:bg-amber-600' : 'bg-indigo-600 hover:bg-indigo-700' ?> text-white rounded-lg hover:shadow-lg transform hover:scale-105 transition-all text-xs"
                                         title="<?= $canEdit ? 'Edit' : 'Lihat' ?>">
                                         <i class="fa-solid <?= $canEdit ? 'fa-edit' : 'fa-eye' ?>"></i>
                                     </a>
                                 </div>
                             </td>
                         </tr>
                     <?php endforeach; ?>
                 <?php endif; ?>
             </tbody>
         </table>
     </div>
 </div>
