<?php
$current_page = basename($_SERVER['PHP_SELF']);

function getActiveParentSlug(string $currentPage, array $menus): ?string
{
    $page = strtolower($currentPage);

    foreach ($menus as $menu)
    {
        // ✅ Root menu (tanpa children) — cocokkan langsung ke route-nya
        if (empty($menu['children']))
        {
            $route = strtolower($menu['route'] ?? '');
            if ($route && $page === $route) return $menu['slug'];
            continue;
        }

        foreach ($menu['children'] as $child)
        {
            $route = strtolower($child['route'] ?? '');
            if (!$route) continue;

            if ($page === $route) return $menu['slug'];

            $prefix = strtolower(basename($route, '.php'));
            if ($prefix && str_starts_with($page, $prefix)) return $menu['slug'];
        }

        $groupPrefix = strtolower($menu['slug']);
        if ($groupPrefix && str_starts_with($page, $groupPrefix . '-'))
        {
            return $menu['slug'];
        }
    }

    return null;
}

$activeParentSlug = getActiveParentSlug($current_page, $menus);

$masterMenu = null;
foreach ($menus as $menu)
{
    if ($menu['slug'] === 'master')
    {
        $masterMenu = $menu;
        break;
    }
}
?>

<!-- SIDEBAR DESKTOP -->
<div class="hidden xs:flex h-screen bg-linear-to-r from-[#4d58ef] to-blue-400 text-white flex-col top-0 w-[70px] xl:w-[180px] z-40">

    <!-- Logo -->
    <div class="h-[70px] flex items-center justify-center border-b border-white/30">
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-2 py-4 space-y-3 overflow-y-auto">

        <?php foreach ($menus as $menu):
            $isActiveGroup = ($activeParentSlug === $menu['slug']);
            $dropdownOpen  = $isActiveGroup ? '' : 'hidden';
            $chevronRotate = $isActiveGroup ? 'rotate-90' : '';
            $headerClass   = $isActiveGroup ? 'bg-white/30 font-semibold' : '';
            $isMasterGroup = ($menu['slug'] === 'master');
            $hasChildren   = !empty($menu['children']);
        ?>

            <div class="menu-group">

                <?php if (!$hasChildren): ?>
                    <!-- ✅ Root menu: langsung link -->
                    <a href="<?= htmlspecialchars($menu['route'] ?? '#') ?>"
                        class="menu-header flex items-center gap-3 px-2 xl:px-3 py-2 rounded-lg hover:bg-white/20 transition <?= $headerClass ?>">
                        <i class="fa-solid text-lg <?= htmlspecialchars($menu['icon'] ?? 'fa-circle') ?>"></i>
                        <span class="hidden xl:inline"><?= htmlspecialchars($menu['name']) ?></span>
                    </a>

                <?php else: ?>
                    <!-- Menu dengan dropdown -->
                    <button
                        class="menu-header flex items-center justify-between w-full px-2 xl:px-3 py-2 rounded-lg hover:bg-white/20 transition <?= $headerClass ?>"
                        data-group="<?= htmlspecialchars($menu['slug']) ?>">
                        <div class="flex cursor-pointer items-center gap-3">
                            <i class="fa-solid text-lg <?= htmlspecialchars($menu['icon'] ?? 'fa-circle') ?>"></i>
                            <span class="hidden xl:inline"><?= htmlspecialchars($menu['name']) ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs hidden xl:inline transition-transform duration-300 <?= $chevronRotate ?>"></i>
                    </button>

                    <div class="dropdown-content ml-3 <?= $dropdownOpen ?> space-y-2 mt-2">
                        <?php foreach ($menu['children'] as $index => $child):
                            $route     = $child['route'] ?? '#';
                            $isActive  = ($current_page === $route) ? 'bg-white/30 font-semibold' : '';
                            $childIcon = htmlspecialchars($child['icon'] ?? 'fa-circle');
                            $childName = htmlspecialchars($child['name']);

                            $hiddenOnLg     = ($isMasterGroup && $index > 0) ? 'lg:hidden' : '';
                            $iconOverride   = ($isMasterGroup && $index === 0) ? 'fa-bars-staggered' : $childIcon;
                            $isNameOverride = ($isMasterGroup && $index === 0) ? 'Data' : $childName;
                        ?>
                            <a href="<?= htmlspecialchars($route) ?>"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/20 text-sm <?= $isActive ?> <?= $hiddenOnLg ?>">
                                <i class="fa-solid <?= $iconOverride ?> text-md opacity-80"></i>
                                <span class="hidden xl:inline text-md"><?= $isNameOverride ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </nav>

    <!-- User info -->
    <div class="px-2 py-6 border-t border-white/30 flex justify-center">
        <p class="font-semibold hidden text-md lg:inline">
            <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Guest') ?>
        </p>
    </div>
</div>


<!-- BOTTOM NAV MOBILE -->
<div class="xs:hidden fixed w-full bottom-0 left-0 right-0 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white flex justify-around py-3 z-50 shadow-2xl">
    <?php foreach ($menus as $menu):
        $isActive    = ($activeParentSlug === $menu['slug']);
        $hasChildren = !empty($menu['children']);
    ?>
        <button
            class="bottom-nav-btn flex flex-col items-center px-2 py-2 rounded-lg transition-all <?= $isActive ? 'active' : '' ?>"
            data-group="<?= htmlspecialchars($menu['slug']) ?>"
            onclick="<?= $hasChildren
                            ? "toggleMobileMenu('" . htmlspecialchars($menu['slug']) . "')"
                            : "location.href='" . htmlspecialchars($menu['route'] ?? '#') . "'"
                        ?>">
            <i class="fa-solid <?= htmlspecialchars($menu['icon'] ?? 'fa-circle') ?> text-xl"></i>
            <span class="text-xs mt-1 hidden sm:inline"><?= htmlspecialchars($menu['name']) ?></span>
        </button>
    <?php endforeach; ?>
</div>

<!-- Overlay Mobile Menu -->
<div id="mobileMenu"
    class="fixed inset-0 bg-transparent bg-opacity-50 z-50 hidden"
    onclick="closeMobileMenu()">
    <div class="absolute bottom-0 left-0 right-0 bg-linear-to-r from-[#4d58ef] to-blue-400 rounded-t-2xl p-4 max-h-[70vh] overflow-y-auto"
        onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-white" id="mobileMenuTitle">Menu</h2>
            <button onclick="closeMobileMenu()" class="text-white">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        <div id="mobileMenuContent" class="space-y-2"></div>
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
    const menuData = <?= json_encode(
                            array_map(function ($menu)
                            {
                                return [
                                    'slug'     => $menu['slug'],
                                    'name'     => $menu['name'],
                                    'icon'     => $menu['icon'],
                                    'route'    => $menu['route'] ?? null,
                                    'children' => array_values(array_map(fn($c) => [
                                        'name'  => $c['name'],
                                        'route' => $c['route'],
                                        'icon'  => $c['icon'],
                                    ], $menu['children'])),
                                ];
                            }, $menus),
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        ) ?>;

    const currentPage = '<?= $current_page ?>';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.menu-header').forEach(header => {
            header.addEventListener('click', function() {
                const group = this.closest('.menu-group');
                const dropdown = group.querySelector('.dropdown-content');
                const chevron = this.querySelector('.fa-chevron-down');
                dropdown.classList.toggle('hidden');
                chevron?.classList.toggle('rotate-90');
            });
        });

        const activeGroup = detectActiveGroup();
        if (activeGroup) {
            document.querySelector(`.bottom-nav-btn[data-group="${activeGroup}"]`)
                ?.classList.add('active');
        }
    });

    function detectActiveGroup() {
        for (const menu of menuData) {
            // ✅ Root menu (tanpa children)
            if (menu.children.length === 0) {
                if (menu.route && currentPage === menu.route) return menu.slug;
                continue;
            }

            for (const child of menu.children) {
                if (!child.route) continue;
                const prefix = child.route.replace(/\.php$/, '');
                if (currentPage === child.route || currentPage.startsWith(prefix)) {
                    return menu.slug;
                }
            }

            if (currentPage.startsWith(menu.slug + '-')) {
                return menu.slug;
            }
        }
        return null;
    }

    function toggleMobileMenu(slug) {
        const menu = menuData.find(m => m.slug === slug);
        if (!menu) return;

        document.getElementById('mobileMenuTitle').textContent = menu.name;

        const html = menu.children.map(child => {
            const active = child.route === currentPage;
            const icon = child.icon || 'fa-circle';
            return `
            <a href="${child.route}"
               class="flex items-center py-3 px-4 rounded-lg
                      ${active ? 'bg-white/30 text-yellow-200' : 'text-white hover:bg-white/20'}">
                <i class="fa-solid ${icon} mr-3"></i>
                <span>${child.name}</span>
            </a>`;
        }).join('');

        document.getElementById('mobileMenuContent').innerHTML = html;
        document.getElementById('mobileMenu').classList.remove('hidden');
    }

    function closeMobileMenu() {
        document.getElementById('mobileMenu').classList.add('hidden');
    }
</script>
