<?php
$pesan = $pesan ?? ($_GET['pesan'] ?? "");
if (!empty($pesan)): ?>
    <div class="relative bg-green-100 border-l-4  border-green-500 text-green-700 p-4 my-4 rounded">
        <span><?= $pesan ?></span>
        <?php if (isset($_GET['pesan'])): ?>
            <!-- Tombol Close hanya muncul jika pesan dari URL -->
            <a href="<?php
                        $params = $_GET;
                        unset($params['pesan']);
                        echo strtok($_SERVER["REQUEST_URI"], '?') .
                            (!empty($params) ? '?' . http_build_query($params) : '');
                        ?>"
                class="absolute top-2 right-2 text-green-700 hover:text-red-500">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!empty($pesan_cari)): ?>
    <div class=" px-3 py-4 my-6 rounded bg-blue-100 text-blue-800 border border-blue-300">
        <?= $pesan_cari ?>
    </div>
<?php endif; ?>
