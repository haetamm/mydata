<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'lib/connection.php';
require_once 'lib/utils.php';

use App\Middleware\PermissionMiddleware;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Validation\RoleValidation;

session_start();

[$menus, $permMap] = PermissionMiddleware::handle($pdo, 'master-role');

$canEdit   = PermissionService::can($permMap, 'master-role', 'edit');
$canCreate = PermissionService::can($permMap, 'master-role', 'create');
$canDelete = PermissionService::can($permMap, 'master-role', 'delete');

$service  = new RoleService($pdo);
$page     = max(1, (int) ($_GET['page'] ?? 1));
$param    = $_GET['id'] ?? null;
$formMode = ($param === 'new') ? 'create' : 'edit';
$back     = 'role.php?' . buildUrl($_GET);

$errors      = [];
$allMenus    = [];
$allPerms    = [];
$rolePerms   = [];

$defaultForm = [
    'id'         => '',
    'name'       => '',
    'deleted_at' => null,
];

if ($formMode === 'create' && !$canCreate)
{
    header("Location: {$back}");
    exit;
}

try
{
    $allMenus = $service->getAllMenus();
    $allPerms = $service->getAllPermissions();

    if ($formMode === 'edit')
    {
        $result = $service->getById($param);
        if (!$result)
        {
            header("Location: {$back}");
            exit;
        }
        $defaultForm = array_merge($defaultForm, $result);
        $rolePerms   = $service->getPermissionsByRoleId($param);
    }
}
catch (\Throwable $e)
{
    error_log(date('[Y-m-d H:i:s] ') . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/logs/error.log');
    $_SESSION['swal'] = [
        'icon'  => 'error',
        'title' => 'Gagal',
        'html'  => 'Terjadi kesalahan saat memuat data.',
    ];
    header("Location: {$back}");
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'toggle_aktif' &&
    $canDelete
)
{
    $aktif = ($_POST['aktif'] ?? '0') === '1';
    $res   = $service->toggleAktif($param, $aktif);

    $_SESSION['swal'] = [
        'icon'  => $res['success'] ? 'success' : 'error',
        'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
        'html'  => $res['message'],
    ];
    header('Location: role-edit.php?' . buildUrl($_GET));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save')
{
    if ($formMode === 'create' && !$canCreate)
    {
        header("Location: {$back}");
        exit;
    }
    if ($formMode === 'edit' && !$canEdit)
    {
        header("Location: {$back}");
        exit;
    }

    $post = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);

    $errors = RoleValidation::save($post, $formMode);

    $permissions = [];
    if (!empty($post['permissions']) && is_array($post['permissions']))
    {
        foreach ($post['permissions'] as $menuId => $permIds)
        {
            if (is_array($permIds))
            {
                $permissions[(int)$menuId] = array_map('intval', $permIds);
            }
        }
    }

    if (empty($errors))
    {
        $data = [
            'id'          => $post['id']   ?? $param,
            'name'        => $post['name'],
            'permissions' => $permissions,
        ];

        $res = $formMode === 'create'
            ? $service->create($data)
            : $service->update($param, $data);

        $_SESSION['swal'] = [
            'icon'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'Berhasil!' : 'Gagal',
            'html'  => $res['message'],
        ];

        if ($res['success'])
        {
            header("Location: {$back}");
            exit;
        }
    }

    $defaultForm  = array_merge($defaultForm, $post);
    $rolePerms    = $permissions;
}

$post      = $defaultForm;
$disabled  = ($formMode === 'edit' && !$canEdit) ? 'disabled' : '';
$isAktif   = empty($post['deleted_at']);
$pageTitle = $formMode === 'create' ? 'Tambah Role' : 'Edit Role';
$title     = $pageTitle;
$icon      = 'fa-shield-halved';
$subtitle  = $canEdit || $formMode === 'create'
    ? 'Atur nama dan hak akses role'
    : 'Anda hanya memiliki akses lihat data';

$allMenus = array_filter($allMenus, fn($m) => $m['slug'] !== 'dashboard');

$menuTree  = [];
$menuIndex = [];
foreach ($allMenus as $m)
{
    $m['children']    = [];
    $menuIndex[(int)$m['id']] = $m;
}
foreach ($menuIndex as $id => $m)
{
    $pid = $m['parent_id'] ? (int)$m['parent_id'] : null;
    if ($pid && isset($menuIndex[$pid]))
    {
        $menuIndex[$pid]['children'][] = &$menuIndex[$id];
    }
    else
    {
        $menuTree[] = &$menuIndex[$id];
    }
}

?>
<?php include 'layout/head.php' ?>

<div class="kontener mx-auto text-sm md:text-md">
    <div class="h-screen grid grid-cols-1 xs:grid-cols-[70px_1fr] xl:grid-cols-[180px_1fr]">

        <?php include 'layout/sidebar.php' ?>

        <div class="h-screen overflow-auto no-scrollbar">
            <div class="lg:pt-5 pb-[120px] p-3 sm:p-4 lg:px-3 xs:pb-20 md:pb-5">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                    <?php include 'components/form_header.php' ?>

                    <!-- Status toggle (edit only) -->
                    <?php if ($formMode === 'edit'): ?>
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                            <div>
                                <p class=" font-medium text-gray-700">Status Role</p>
                                <p class=text-gray-500 mt-0.5">
                                    <?= $isAktif ? 'Role ini aktif dan dapat digunakan.' : 'Role ini nonaktif.' ?>
                                </p>
                            </div>
                            <?php if ($canDelete): ?>
                                <form method="POST" action="role-edit.php?<?= buildUrl($_GET) ?>">
                                    <input type="hidden" name="action" value="toggle_aktif">
                                    <input type="hidden" name="aktif" value="<?= $isAktif ? '0' : '1' ?>">
                                    <button type="submit"
                                        class="px-4 py-2 rounded-lg  font-medium transition-all
                                            <?= $isAktif
                                                ? 'bg-red-100 text-red-600 hover:bg-red-200'
                                                : 'bg-green-100 text-green-700 hover:bg-green-200' ?>">
                                        <i class="fa-solid <?= $isAktif ? 'fa-ban' : 'fa-circle-check' ?> mr-1"></i>
                                        <?= $isAktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" action="role-edit.php?<?= buildUrl($_GET) ?>" class="p-6 space-y-6">
                        <input type="hidden" name="action" value="save">

                        <!-- ID Role (create only) -->
                        <?php if ($formMode === 'create'): ?>
                            <div>
                                <label class="block lg: font-medium text-gray-700 mb-1">
                                    ID Role <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="id"
                                    value="<?= htmlspecialchars($post['id']) ?>"
                                    placeholder="Contoh: guru_sd"
                                    class="w-full px-4 py-2 border rounded-lg  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                        <?= isset($errors['id']) ? 'border-red-400' : 'border-gray-300' ?>">
                                <?php if (isset($errors['id'])): ?>
                                    <p class="mt-1 text-red-500"><?= $errors['id'] ?></p>
                                <?php endif; ?>
                                <p class="mt-1 text-gray-400">Gunakan huruf kecil dan underscore. Tidak bisa diubah setelah disimpan.</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <label class="block md: font-medium text-gray-700 mb-1">ID Role</label>
                                <div class="px-4 py-2 bg-gray-100 rounded-lg  font-mono text-gray-600">
                                    <?= htmlspecialchars($post['id']) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Nama Role -->
                        <div>
                            <label class="block md: font-medium text-gray-700 mb-1">
                                Nama Role <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name"
                                value="<?= htmlspecialchars($post['name']) ?>"
                                placeholder="Contoh: Guru SD"
                                <?= $disabled ?>
                                class="w-full px-4 py-2 border rounded-lg  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                    <?= isset($errors['name']) ? 'border-red-400' : 'border-gray-300' ?>
                                    <?= $disabled ? 'bg-gray-100 cursor-not-allowed' : '' ?>">
                            <?php if (isset($errors['name'])): ?>
                                <p class="mt-1 text-red-500"><?= $errors['name'] ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Permissions -->
                        <div>
                            <label class="block md: font-medium text-gray-700 mb-3">Hak Akses Menu</label>

                            <?php if (empty($menuTree)): ?>
                                <p class=" text-gray-400">Tidak ada menu tersedia.</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($menuTree as $parent): ?>
                                        <?php
                                        $children = $parent['children'];
                                        if (empty($children)) $children = [$parent];
                                        ?>
                                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-sm">

                                            <!-- Parent header -->
                                            <div class="bg-indigo-50 px-4 py-2.5 flex items-center gap-2">
                                                <i class="fa-solid fa-folder text-indigo-400"></i>
                                                <span class="font-semibold text-indigo-800">
                                                    <?= htmlspecialchars($parent['name']) ?>
                                                </span>
                                            </div>

                                            <div class="w-full overflow-x-auto">
                                                <table class="w-full border-collapse">
                                                    <thead>
                                                        <tr class="bg-gray-50 border-b border-gray-200">
                                                            <td class="sticky left-0 w-[160px] z-10 py-2 px-3 font-medium text-gray-800 border-r border-gray-200 <?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>" style="width:100px; min-width:100px; max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                                <?= htmlspecialchars($menu['name']) ?>
                                                            </td>
                                                            <?php foreach ($allPerms as $perm): ?>
                                                                <th class="py-2 px-3 text-center font-semibold text-gray-600 whitespace-nowrap">
                                                                    <?= htmlspecialchars($perm['name']) ?>
                                                                </th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($children as $i => $menu): ?>
                                                            <?php $menuId = (int)$menu['id']; ?>
                                                            <tr class="border-b border-gray-100 <?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' ?> hover:bg-indigo-50/30 transition-colors">
                                                                <td class="sticky left-0 z-10 py-2 px-3 font-medium text-gray-800 border-r border-gray-200 whitespace-nowrap <?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                                                                    <?= htmlspecialchars($menu['name']) ?>
                                                                </td>
                                                                <?php foreach ($allPerms as $perm): ?>
                                                                    <?php
                                                                    $permId  = (int)$perm['id'];
                                                                    $checked = in_array($permId, $rolePerms[$menuId] ?? []);
                                                                    ?>
                                                                    <td class="py-2 px-3 text-center">
                                                                        <label class="inline-flex items-center justify-center <?= $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' ?>">
                                                                            <input type="checkbox"
                                                                                name="permissions[<?= $menuId ?>][]"
                                                                                value="<?= $permId ?>"
                                                                                <?= $checked ? 'checked' : '' ?>
                                                                                <?= $disabled ? 'disabled' : '' ?>
                                                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 <?= $disabled ? 'cursor-not-allowed' : 'cursor-pointer' ?>">
                                                                        </label>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex gap-3 pt-2 items-center justify-end">
                            <a href="<?= $back ?>"
                                class="px-6 py-2 border border-gray-300 text-gray-600 rounded-lg  hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <?php if ($canEdit || $formMode === 'create'): ?>
                                <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg  font-medium transition">
                                    <i class="fa-solid fa-floppy-disk mr-1"></i>
                                    Simpan
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'layout/footer.php' ?>
