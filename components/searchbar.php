<?php
$action = $action ?? '';
$placholder = $placholder ?? 'Cari nama';

// (hanya untuk select kelas)
$current_file = basename($_SERVER['PHP_SELF']);
$is_siswa_page = str_starts_with($current_file, 'siswa-');

// Jika halaman siswa → ambil jenjang dan data kelas
$kelas = [];
if ($is_siswa_page)
{
    preg_match('/siswa-(sd|smp|sma)\.php/', $current_file, $matches);
    $jenjang = strtoupper($matches[1] ?? '');

    if ($jenjang)
    {
        $stmt = $link->prepare("
            SELECT mk.id_kelas, mk.nama_kelas
            FROM master_kelas mk
            JOIN master_level_kelas ml ON mk.level_id = ml.id_level
            WHERE ml.jenjang = ? AND mk.deleted_at IS NULL
            ORDER BY mk.nama_kelas
        ");
        $stmt->execute([$jenjang]);
        $kelas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
?>

<form id="filterForm" action="<?= htmlspecialchars($action) ?>" method="get"
    class="w-full flex flex-col xs:flex-row justify-between lg:justify-end gap-3 items-end">

    <!-- select hanya muncul di halaman siswa -->
    <?php if ($is_siswa_page && !empty($kelas)): ?>
        <select name="id_kelas" onchange="this.form.submit()"
            class="order-2 xs:order-1 px-4 py-2 border border-gray-300 w-full xs:w-auto rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
            <option value="">Semua Kelas</option>
            <?php foreach ($kelas as $id => $nama): ?>
                <option value="<?= $id ?>" <?= ($_GET['id_kelas'] ?? '') == $id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($nama) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <div class="<?= $is_siswa_page && !empty($kelas) ? 'order-1 xs:order-2' : 'order-1' ?> flex w-full md:w-auto gap-2">
        <input type="text" name="nama" value="<?= htmlspecialchars($_GET['nama'] ?? '') ?>"
            placeholder="<?= htmlspecialchars($placholder) ?>"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none flex-1">

        <button type="submit"
            class="px-6 py-2 bg-linear-to-r from-[#4d58ef] to-blue-400 text-white rounded-lg hover:opacity-90 transition">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
</form>
