<?php
$disabled = (!$canCreate) ? 'disabled' : '';
$err = fn(string $field) => !empty($errors[$field])
    ? '<p class="text-red-500 text-xs mt-1.5">' . htmlspecialchars($errors[$field]) . '</p>'
    : '';
?>
<div class="bg-white px-4 py-3 rounded-lg border border-gray-200 mb-3">
    <h2 class="text-lg font-semibold mb-3">Tambah <?= $title_form ?> Baru</h2>
    <form method="POST" action="<?= $action_form ?>" class="flex gap-3">
        <input <?= $disabled ?> type="text" name="<?= $input_value ?>" placeholder="<?= $placeholder ?>"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        <button type="submit" <?= btnDisabled($canCreate) ?>
            class="px-4 py-2 text-white rounded-md text-sm
                <?= btnClass($canCreate, 'bg-blue-600 hover:bg-blue-700') ?>">
            Tambah
        </button>
    </form>
    <?= $err('nama') ?>
</div>
