<div class="bg-white px-4 py-3 rounded-lg border border-gray-200 mb-3">
    <h2 class="text-lg font-semibold mb-3">Tambah <?= $title_form ?> Baru</h2>
    <form method="POST" action="<?= $action_form ?>" class="flex gap-3">
        <input type="text" name="<?= $input_value ?>" placeholder="<?= $placeholder ?>" required
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit"
            class="px-4 py-2 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Tambah
        </button>
    </form>
</div>
