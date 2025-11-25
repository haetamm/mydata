<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role      = $_SESSION['id_role'] ?? null;
$levelName = ($role == 1) ? '' : strtolower($_SESSION['jenjang'] ?? '');

function filterMenuByLevel($menus, $levelName)
{
   // Jika levelName kosong → artinya Super Admin → jangan filter apa-apa
   if ($levelName === '')
   {
      return $menus;
   }

   foreach ($menus as &$menu)
   {
      if (in_array($menu['id'], ['siswa', 'guru']))
      {
         $menu['items'] = array_filter($menu['items'], function ($i) use ($levelName)
         {
            return strtolower($i['name']) === $levelName;
         });
      }
   }
   unset($menu);
   return $menus;
}

function filterMenusByRole($menus, $role)
{
   $allowed = [];

   foreach ($menus as $menu)
   {
      // Super Admin → semua menu
      if ($role == 1)
      {
         $allowed[] = $menu;
         continue;
      }

      // Kepala Sekolah → siswa + guru + setting
      if ($role == 2)
      {
         if (in_array($menu['id'], ['siswa', 'guru', 'setting']))
            $allowed[] = $menu;
         continue;
      }

      // Guru → siswa + setting
      if ($role == 3)
      {
         if (in_array($menu['id'], ['siswa', 'setting']))
            $allowed[] = $menu;
         continue;
      }
   }

   return $allowed;
}

function filterSettingMenu($menus, $role)
{
   foreach ($menus as &$menu)
   {
      if ($menu['id'] === 'setting')
      {
         if ($role == 1)
         {
            // Biarkan semua item (Profil, Admin, Logout)
            // Tidak perlu filter apa-apa
            continue;
         }

         // Hanya tampilkan Profil dan Logout
         $menu['items'] = array_filter($menu['items'], function ($i)
         {
            return in_array(strtolower($i['name']), ['profil', 'logout']);
         });
      }
   }
   return $menus;
}



$menus = [
   [
      'id'      => 'siswa',
      'icon'    => 'fa-graduation-cap',
      'title'   => 'Siswa',
      'items'   => [
         ['name' => 'SD',   'url' => 'siswa-sd.php',   'icon' => 'fa-school'],
         ['name' => 'SMP',  'url' => 'siswa-smp.php',  'icon' => 'fa-building-columns'],
         ['name' => 'SMA',  'url' => 'siswa-sma.php',  'icon' => 'fa-user-graduate'],
      ]
   ],
   [
      'id'      => 'guru',
      'icon'    => 'fa-chalkboard-teacher',
      'title'   => 'Guru',
      'items'   => [
         ['name' => 'SD',   'url' => 'guru-sd.php',   'icon' => 'fa-school'],
         ['name' => 'SMP',  'url' => 'guru-smp.php',  'icon' => 'fa-building-columns'],
         ['name' => 'SMA',  'url' => 'guru-sma.php',  'icon' => 'fa-user-graduate'],
      ]
   ],
   [
      'id'      => 'master',
      'icon'    => 'fa-layer-group',
      'title'   => 'Master Data',
      'items'   => [
         ['name' => 'Master',           'url' => 'agama.php',          'icon' => 'fa-database'],
         ['name' => 'Agama',           'url' => 'agama.php',           'icon' => 'fa-pray'],
         ['name' => 'Pekerjaan',       'url' => 'pekerjaan.php',       'icon' => 'fa-briefcase'],
         ['name' => 'Level Kelas',     'url' => 'level-kelas.php',     'icon' => 'fa-layer-group'],
         ['name' => 'Kelas',           'url' => 'kelas.php',           'icon' => 'fa-chalkboard'],
         ['name' => 'Tahun Pelajaran', 'url' => 'tahun-pelajaran.php', 'icon' => 'fa-calendar-check'],
         ['name' => 'Semester',        'url' => 'semester.php',        'icon' => 'fa-clock'],
      ]
   ],
   [
      'id'      => 'setting',
      'icon'    => 'fa-cog',
      'title'   => 'Settings',
      'items'   => [
         ['name' => 'Profil',          'url' => 'profile.php',         'icon' => 'fa-user-pen'],
         ['name' => 'Admin',           'url' => 'admin.php',           'icon' => 'fa-user-plus'],
         ['name' => 'Logout',          'url' => 'logout.php',          'icon' => 'fa-sign-out-alt'],
      ]
   ],
];

$menus = filterMenusByRole($menus, $role);
$menus = filterMenuByLevel($menus, $levelName);
$menus = filterSettingMenu($menus, $role);

// === FUNGSI DETEKSI PARENT AKTIF BERDASARKAN PREFIX ===
function getActiveParent($current_page, $menus)
{
   $page = strtolower($current_page);

   foreach ($menus as $menu)
   {
      $prefixes = [];
      if ($menu['id'] === 'siswa')
      {
         $prefixes = ['siswa-sd.php', 'siswa-smp.php', 'siswa-sma.php', 'siswa-add.php', 'siswa-upload', 'siswa-edit.php', 'siswa-update.php', 'siswa-detail.php', 'tambah-siswa.php', 'siswa-delete.php'];
      }
      elseif ($menu['id'] === 'guru')
      {
         $prefixes = ['guru-sd.php', 'guru-smp.php', 'guru-sma.php', 'guru-add.php', 'guru-upload.php', 'guru-edit.php', 'guru-update.php', 'guru-detail.php', 'tambah-guru.php', 'guru-delete.php'];
      }
      elseif ($menu['id'] === 'master')
      {
         $prefixes = ['agama.php', 'pekerjaan.php', 'level-kelas.php', 'kelas.php', 'tahun-pelajaran.php', 'semester.php'];
      }
      elseif ($menu['id'] === 'setting')
      {
         $prefixes = ['profile.php', 'admin.php'];
      }

      foreach ($prefixes as $url)
      {
         if (
            strpos($page, strtolower(basename($url, '.php'))) !== false ||
            $page === strtolower($url)
         )
         {
            return $menu['id'];
         }
      }

      // Fallback: cek exact match di items
      foreach ($menu['items'] as $item)
      {
         if ($page === strtolower($item['url']))
         {
            return $menu['id'];
         }
      }
   }
   return null;
}

$active_parent_id = getActiveParent($current_page, $menus);
?>

<div class="hidden xs:flex h-screen bg-linear-to-r from-[#4d58ef] to-blue-400 text-white flex-col  top-0 w-[70px] xl:w-[180px] z-40">
   <!-- Logo -->
   <div class="h-[70px] flex items-center justify-center border-b border-white/30">
   </div>

   <!-- Menu -->
   <nav class="flex-1 px-2 py-4 space-y-3 overflow-y-auto">
      <?php foreach ($menus as $menu):
         $groupUrls   = array_column($menu['items'], 'url');
         $isActiveGroup = ($active_parent_id === $menu['id']);
         $dropdownOpen  = $isActiveGroup ? '' : 'hidden';
         $chevronRotate = $isActiveGroup ? 'rotate-90' : '';
         $headerClass   = $isActiveGroup ? 'bg-white/30 font-semibold' : '';
      ?>
         <div class="menu-group">
            <button class="menu-header flex items-center justify-between w-full px-2 xl:px-3 py-2 rounded-lg hover:bg-white/20 transition <?= $headerClass ?>">
               <div class="flex cursor-pointer items-center justify-between gap-3">
                  <i class="fa-solid text-lg  <?= $menu['icon']; ?>"></i>
                  <span class="hidden xl:inline"><?= $menu['title']; ?></span>
               </div>
               <i class="fa-solid fa-chevron-down text-xs hidden xl:inline transition-transform duration-300 <?= $chevronRotate; ?>"></i>
            </button>

            <div class="dropdown-content ml-3 <?= $dropdownOpen ?> space-y-2 mt-2">
               <?php foreach ($menu['items'] as $item):
                  $active = ($current_page === $item['url']) ? 'bg-white/30 font-semibold' : '';
                  $childIcon = $item['icon'] ?? 'fa-circle';

                  if ($menu['id'] === 'master'): ?>
                     <!-- Untuk menu master -->
                     <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/20 text-sm <?= $active ?>
                        <?= ($item['name'] === 'Master') ? 'hidden lg:flex justify-first' : 'lg:hidden' ?>">
                        <i class="fa-solid <?= $childIcon ?> text-md opacity-80"></i>
                        <span class="hidden xl:inline text-md"><?= $item['name'] ?></span>
                     </a>
                  <?php else: ?>
                     <!-- Menu lainnya tetap normal -->
                     <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/20 text-sm <?= $active ?>">
                        <i class="fa-solid <?= $childIcon ?> text-md opacity-80"></i>
                        <span class="hidden xl:inline text-md"><?= $item['name'] ?></span>
                     </a>
                  <?php endif; ?>
               <?php endforeach; ?>
            </div>
         </div>
      <?php endforeach; ?>

   </nav>

   <!-- User Info -->
   <div class="px-2 py-6 border-t border-white/30 flex justify-center">
      <p class="font-semibold hidden text-md lg:inline"><?= htmlspecialchars($_SESSION["nama"] ?? "Guest") ?></p>
   </div>
</div>

<div class="xs:hidden fixed w-full bottom-0 left-0 right-0 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white flex justify-around py-3 z-50 shadow-2xl">
   <?php foreach ($menus as $menu):
      $icon = $menu['icon'] ?? 'fa-circle';
      $isActive = ($active_parent_id === $menu['id']);
   ?>
      <button
         class="bottom-nav-btn flex flex-col items-center px-2 py-2 rounded-lg transition-all <?= $isActive ? 'active' : '' ?>"
         data-group="<?= $menu['id'] ?>"
         onclick="toggleMobileMenu('<?= $menu['id'] ?>')">
         <i class="fa-solid <?= $icon ?> text-xl"></i>
         <span class="text-xs mt-1 hidden sm:inline"><?= $menu['title'] ?></span>
      </button>
   <?php endforeach; ?>
</div>

<div id="mobileMenu" class="fixed inset-0 bg-transparent bg-opacity-50 z-50 hidden" onclick="closeMobileMenu()">
   <div class="absolute bottom-0 left-0 right-0 bg-linear-to-r from-[#4d58ef] to-blue-400 rounded-t-2xl p-4 max-h-[70vh] overflow-y-auto" onclick="event.stopPropagation()">
      <div class="flex justify-between items-center mb-4">
         <h2 class="text-lg font-bold text-white" id="mobileMenuTitle">Menu</h2>
         <button onclick="closeMobileMenu()" class="text-white"><i class="fa-solid fa-times text-xl"></i></button>
      </div>
      <div id="mobileMenuContent" class="space-y-2 z-99"></div>
   </div>
</div>

<style>
   .bottom-nav-btn.active {
      background: rgba(255, 255, 255, 0.3);
      color: #C7D2FE;
      transform: scale(1.1);
   }
</style>

<script>
   const menuData = <?php echo json_encode(array_map(function ($m)
                     {
                        return [
                           'id'    => $m['id'],
                           'title' => $m['title'],
                           'icon'  => $m['icon'],
                           'items' => array_values($m['items']) // PAKSA JADI ARRAY NUMERIK!
                        ];
                     }, $menus), JSON_UNESCAPED_SLASHES); ?>;

   const currentPage = '<?php echo $current_page; ?>';

   // ---------- DROPDOWN DESKTOP ----------
   document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.menu-header').forEach(header => {
         header.addEventListener('click', function() {
            const group = this.parentElement;
            const dropdown = group.querySelector('.dropdown-content');
            const chevron = group.querySelector('.fa-chevron-down');

            // Toggle hanya untuk group yang diklik
            dropdown.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-90');
         });
      });

      // === CLASS ACTIVE GROUP YANG SEDANG DIPAKAI
      let activeGroup = null;
      menuData.forEach(menu => {
         const prefixes = {
            siswa: ['siswa-', 'tambah-siswa.php'],
            guru: ['guru-', 'tambah-guru.php'],
            master: ['agama.php', 'pekerjaan.php', 'level-kelas.php', 'kelas.php', 'tahun-pelajaran.php', 'semester.php'],
            setting: ['profile.php', 'admin.php', 'logout.php']
         };

         const check = prefixes[menu.id];
         if (check) {
            const isActive = check.some(p => currentPage.includes(p)) ||
               menu.items.some(item => item.url === currentPage);
            if (isActive) {
               activeGroup = menu.id;

               const activeDropdown = document.querySelector(`.menu-group:has(button[data-group="${menu.id}"])`);
               if (activeDropdown) {
                  const dd = activeDropdown.querySelector('.dropdown-content');
                  const ch = activeDropdown.querySelector('.fa-chevron-down');
                  if (dd) dd.classList.remove('hidden');
                  if (ch) ch.classList.add('rotate-90');
               }
            }
         }
      });

      // Highlight bottom nav (mobile)
      if (activeGroup) {
         const btn = document.querySelector(`.bottom-nav-btn[data-group="${activeGroup}"]`);
         if (btn) btn.classList.add('active');
      }


   });

   function toggleMobileMenu(menuId) {
      const menu = menuData.find(m => m.id === menuId);
      if (!menu) return;
      document.getElementById('mobileMenuTitle').textContent = menu.title;
      let html = '';

      // Filter items - hilangkan item dengan name 'Master' untuk menu mobile
      const filteredItems = menu.items.filter(item => item.name !== 'Master');

      filteredItems.forEach(item => {
         const active = item.url === currentPage;
         const icon = item.icon || 'fa-circle';
         html += `
              <a href="${item.url}" class="flex items-center py-3 px-4 rounded-lg ${active?'bg-white/30 text-yellow-200':'text-white hover:bg-white/20'}">
                 <i class="fa-solid ${icon} mr-3"></i><span>${item.name}</span>
              </a>
           `;
      });
      document.getElementById('mobileMenuContent').innerHTML = html;
      document.getElementById('mobileMenu').classList.remove('hidden');
   }

   function closeMobileMenu() {
      document.getElementById('mobileMenu').classList.add('hidden');
   }
</script>
