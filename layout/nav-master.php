<?php
$menuItems = [
    ['name' => 'Agama',           'url' => 'agama.php',           'icon' => 'fa-pray'],
    ['name' => 'Pekerjaan',       'url' => 'pekerjaan.php',       'icon' => 'fa-briefcase'],
    ['name' => 'Level Kelas',     'url' => 'level-kelas.php',     'icon' => 'fa-layer-group'],
    ['name' => 'Kelas',           'url' => 'kelas.php',           'icon' => 'fa-chalkboard'],
    ['name' => 'Tahun Pelajaran', 'url' => 'tahun-pelajaran.php', 'icon' => 'fa-calendar-check'],
    ['name' => 'Semester',        'url' => 'semester.php',        'icon' => 'fa-clock'],
];
?>
<aside id="sidebar"
    class="hidden lg:block bg-white mt-4 lg:mt-0 p-2 lg:px-1.5 xl:px-4 md:rounded-sm xl:rounded-lg sticky top-0 z-10 col-span-1">
    <nav class="py-4">
        <ul class="space-y-2">
            <?php
            // Tentukan halaman aktif (sesuaikan dengan kebutuhan)
            $currentPage = basename($_SERVER['PHP_SELF']);

            foreach ($menuItems as $item):
                $isActive = ($currentPage === $item['url']) ? 'text-blue-500 bg-gray-100' : 'text-gray-600 hover:bg-gray-100';
            ?>
                <li>
                    <a href="<?= $item['url'] ?>"
                        class="flex items-center justify-start px-4 py-2.5 rounded-lg <?= $isActive ?>">
                        <i class="fa-solid shrink-0 <?= $item['icon'] ?> text-xl lg:mr-3"></i>
                        <span class="hidden lg:block ml-2 text-sm"><?= $item['name'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
