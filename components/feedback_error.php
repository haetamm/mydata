<?php if ($pesan): ?>
    <div class="mt-4 px-4 py-3 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-700 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>
<?php if ($fetchError): ?>
    <div class="mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($fetchError) ?>
    </div>
<?php endif; ?>
