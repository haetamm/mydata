<?php
function getGuruData($table, $page, $limit, $search, $pdo)
{
    // === PERSIS SAMA KAYAK SISWA ===
    $offset = ($page - 1) * $limit;

    // --- CARI ---
    $where      = "";
    $params     = [];
    $pesan_cari = "";

    if (!empty($search))
    {
        $cari = "%$search%";
        $where = " WHERE g.nama LIKE ? OR g.nik LIKE ? OR g.nuptk LIKE ? OR g.jenis_kelamin LIKE ?";
        $params = [$cari, $cari, $cari, $cari];
        $pesan_cari = "Hasil pencarian <b>\"" . htmlspecialchars($search) . "\"</b>:";
    }

    // --- TOTAL DATA ---
    $countQuery = "SELECT COUNT(*) FROM `$table` g $where";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalData = $stmt->fetchColumn(); // pake fetchColumn() biar sama kayak siswa (lebih ringkas)

    $totalPages = max(1, ceil($totalData / $limit));

    // --- AMBIL DATA GURU ---
    $query = "
        SELECT
            g.id_guru, g.nik, g.nuptk, g.nama, g.jenis_kelamin,
            g.tempat_lahir, g.tgl_lahir, g.nama_ibu,
            g.status_pegawai, g.jenis_gtk, g.jabatan, g.alamat,
            DATE_FORMAT(g.created_at, '%d-%m-%Y %H:%i') AS tgl_dibuat,
            DATE_FORMAT(g.updated_at, '%d-%m-%Y %H:%i') AS tgl_diupdate
        FROM `$table` g
        $where
        ORDER BY g.id_guru DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $dataGuru = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === RETURN PERSIS SAMA KAYAK SISWA ===
    return [
        "data"        => $dataGuru,
        "totalData"   => (int)$totalData,
        "totalPages"  => $totalPages,
        "currentPage" => $page,
        "pesan_cari"  => $pesan_cari
    ];
}

function deleteGuru($table, $nik, $pdo)
{
    $allowed_tables = ['guru_sd', 'guru_smp', 'guru_sma'];
    if (!in_array($table, $allowed_tables)) return false;

    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE nik = ?");
    return $stmt->execute([$nik]);
}

function createGuru(PDO $link, string $table, array $data)
{
    $sql = "INSERT INTO $table (
                nik, nuptk, nama, jenis_kelamin, tempat_lahir, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan, alamat
            ) VALUES (
                :nik, :nuptk, :nama, :jenis_kelamin, :tempat_lahir, :tgl_lahir, :nama_ibu, :status_pegawai, :jenis_gtk, :jabatan, :alamat
            )";

    $stmt = $link->prepare($sql);
    $stmt->execute($data);
}

function updateGuru(PDO $link, string $tabel, array $data)
{
    $sql = "UPDATE $tabel SET nik=:nik, nuptk=:nuptk, nama=:nama, jenis_kelamin=:jenis_kelamin, tempat_lahir=:tempat_lahir, tgl_lahir=:tgl_lahir,
                nama_ibu=:nama_ibu, status_pegawai=:status_pegawai, jenis_gtk=:jenis_gtk, alamat=:alamat
                WHERE id_guru = :id_guru";
    $stmt = $link->prepare($sql);
    $stmt->execute($data);
}

function getGuruByNik(PDO $link, string $tabel, int $nik)
{
    $stmt = $link->prepare("SELECT id_guru, nama, nik FROM $tabel WHERE nik = ? LIMIT 1");
    $stmt->execute([$nik]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getGuruById(PDO $link, string $tabel, int $id)
{
    $stmt = $link->prepare("SELECT * FROM $tabel WHERE id_guru = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
