<?php
$masterChildren = [];
foreach ($menus as $menu)
{
    if ($menu['slug'] === 'master')
    {
        $masterChildren = $menu['children'];
        break;
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside id="sidebar"
    class="hidden lg:block bg-white mt-4 lg:mt-0 p-2 lg:px-1.5 xl:px-4 md:rounded-sm xl:rounded-lg sticky top-0 z-10 col-span-1">
    <nav class="py-4">
        <ul class="space-y-2">
            <?php foreach ($masterChildren as $item):
                $route    = $item['route'] ?? '#';
                $isActive = ($currentPage === $route)
                    ? 'text-blue-500 bg-gray-100'
                    : 'text-gray-600 hover:bg-gray-100';
                $icon     = htmlspecialchars($item['icon'] ?? 'fa-circle');
                $name     = htmlspecialchars($item['name']);
            ?>
                <li>
                    <a href="<?= htmlspecialchars($route) ?>"
                        class="flex items-center justify-start px-4 py-2.5 rounded-lg <?= $isActive ?>">
                        <i class="fa-solid shrink-0 <?= $icon ?> text-xl lg:mr-3"></i>
                        <span class="hidden lg:block ml-2 text-sm"><?= $name ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
