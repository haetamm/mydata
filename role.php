<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\RoleService;

session_start();

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-role');

$service   = new RoleService($pdo);
$canCreate = PermissionService::can($permMap, 'master-role', 'create');
$canEdit   = PermissionService::can($permMap, 'master-role', 'edit');

$limit = 10;
$page  = max(1, (int) ($_GET['page'] ?? 1));

$filter = [
    'nama'   => trim($_GET['nama']   ?? ''),
    'status' => trim($_GET['status'] ?? ''),
];

if (isset($_GET['add']) && $canCreate)
{
    header('Location: role-edit.php?' . http_build_query(array_merge($filter, ['id' => 'new', 'page' => $page])));
    exit;
}

$result     = null;
$fetchError = null;

try
{
    $result = $service->getRoles($filter, $page, $limit);
}
catch (\Throwable $e)
{
    $fetchError = 'Terjadi kesalahan dalam mengambil data.';
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
}

$dataRole   = $result['data']        ?? [];
$totalPages = $result['total_pages'] ?? 1;
$totalData  = $result['total']       ?? 0;
$page       = $result['page']        ?? $page;
$offset     = $result['offset']      ?? 0;

$baseQuery = http_build_query(array_filter([
    'nama'   => $filter['nama'],
    'status' => $filter['status'],
]));

$title = 'Master Role';
$icon  = 'fa-shield-halved';
?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-full lg:h-screen overflow-auto no-scrollbar">
            <div class="lg:min-h-screen lg:bg-slate-100 md:p-2 lg:py-4">
                <div class="grid grid-cols-6 gap-2 lg:gap-3 h-full lg:h-[calc(100vh-32px)]">

                    <?php include 'layout/nav-master.php' ?>

                    <main class="col-span-6 p-3 xs:pb-10 lg:pb-4 lg:col-span-5 bg-white md:rounded-sm xl:rounded-lg overflow-hidden flex flex-col h-full">

                        <?php $title = 'Daftar Role';
                        include 'components/header_page.php' ?>

                        <!-- Filter -->
                        <form method="GET" class="mt-4">
                            <div class="bg-white px-1">
                                <div class="flex flex-col md:flex-row gap-3">
                                    <div class="flex-1 relative">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                            <i class="fa-solid fa-search text-sm"></i>
                                        </span>
                                        <input type="search" name="nama"
                                            value="<?= htmlspecialchars($filter['nama']) ?>"
                                            placeholder="Cari nama role..."
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>

                                    <div class="relative md:w-44">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                            <i class="fa-solid fa-circle-half-stroke text-sm"></i>
                                        </span>
                                        <select name="status" onchange="this.form.submit()"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="">Semua Status</option>
                                            <option value="aktif" <?= $filter['status'] === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                                            <option value="nonaktif" <?= $filter['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex flex-col md:flex-row gap-3 mt-3">
                                    <div class="grid grid-cols-2 md:flex gap-3">
                                        <button type="submit" name="search" value="1"
                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                            <span>Cari</span>
                                        </button>

                                        <?php if ($filter['nama'] || $filter['status']): ?>
                                            <a href="role.php"
                                                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                                <span>Reset</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="md:ml-auto">
                                        <button type="submit" name="add" value="1"
                                            <?= btnDisabled($canCreate) ?>
                                            class="w-full md:w-auto px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2
                                                <?= btnClass($canCreate, 'bg-blue-600 hover:bg-blue-700') ?>">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                            <span>Tambah Role</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="<?= $page ?>">
                        </form>

                        <?php if ($fetchError): ?>
                            <?= flashMsg('error', $fetchError) ?>
                        <?php endif; ?>

                        <!-- Table -->
                        <div class="flex-1 overflow-hidden flex flex-col min-h-0 mt-4">
                            <div class="overflow-auto flex-1">
                                <table class="min-w-full bg-white table-fixed">
                                    <thead class="bg-linear-to-r from-[#4d58ef] to-blue-400 text-white uppercase text-xs tracking-wider sticky top-0 z-10 shadow-md">
                                        <tr>
                                            <th class="py-4 px-3 text-left font-semibold rounded-tl-xl w-[5%]">No.</th>
                                            <th class="py-4 px-3 text-left font-semibold w-[15%]">ID Role</th>
                                            <th class="py-4 px-3 text-left font-semibold w-[30%]">Nama Role</th>
                                            <th class="py-4 px-3 text-center font-semibold w-[10%]">Status</th>
                                            <th class="py-4 px-3 text-center font-semibold rounded-tr-xl w-[10%]">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-700 text-sm">
                                        <?php if (empty($dataRole)): ?>
                                            <tr>
                                                <td colspan="5" class="py-24 text-center text-gray-500">
                                                    <i class="fa-solid fa-inbox text-5xl text-gray-300 mb-4 block"></i>
                                                    Data role tidak tersedia
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($dataRole as $index => $r): ?>
                                                <?php $isAktif = empty($r['deleted_at']); ?>
                                                <tr class="border-b border-gray-100 hover:bg-indigo-50/70 transition-all duration-200">
                                                    <td class="py-4 px-3 text-center"><?= $offset + $index + 1 ?></td>
                                                    <td class="py-4 px-3">
                                                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                                            <?= htmlspecialchars($r['id']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-4 px-3 font-semibold text-indigo-800">
                                                        <?= htmlspecialchars($r['name']) ?>
                                                    </td>
                                                    <td class="py-4 px-3 text-center">
                                                        <?php if ($isAktif): ?>
                                                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-600 rounded-full">Nonaktif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="py-4 px-3">
                                                        <div class="flex justify-center">
                                                            <a href="role-edit.php?<?= http_build_query(array_merge($filter, ['id' => $r['id'], 'page' => $page])) ?>"
                                                                class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-all duration-200 text-xs">
                                                                <i class="fa-solid fa-<?= $canEdit ? 'edit' : 'eye' ?>"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 bg-white sticky bottom-0">
                                <?php include 'components/pagination.php' ?>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
