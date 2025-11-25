<?php

function getSiswaData($table, $page, $limit, $search, $id_kelas, $pdo)
{
    $offset = ($page - 1) * $limit;

    $where = "";
    $params = [];
    $pesan_cari = "";

    // Filter berdasarkan nama/NIS/NISN
    if (!empty($search))
    {
        $cari = "%$search%";
        $where .= " WHERE (s.nama LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)";
        $params = [$cari, $cari, $cari];
        $pesan_cari = "Hasil pencarian <b>\"" . htmlspecialchars($search) . "\"</b>";
    }

    // Filter berdasarkan kelas
    if (!empty($id_kelas))
    {
        $where .= (!empty($where) ? " AND" : " WHERE") . " s.id_kelas = ?";
        $params[] = $id_kelas;
        // Ambil nama kelas untuk pesan
        $stmt = $pdo->prepare("SELECT nama_kelas FROM master_kelas WHERE id_kelas = ?");
        $stmt->execute([$id_kelas]);
        $nama_kelas = $stmt->fetchColumn() ?: "Kelas";
        $pesan_cari .= ($pesan_cari ? " | " : "") . "Kelas: <b>$nama_kelas</b>";
    }

    // Jika tidak ada filter sama sekali
    if (empty($pesan_cari))
    {
        $pesan_cari = "";
    }
    else
    {
        if (!empty($search)) $pesan_cari .= ":";
    }

    // Hitung total data
    $countQuery = "SELECT COUNT(*) FROM $table s" . $where;
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalData = $stmt->fetchColumn();
    $totalPages = max(1, ceil($totalData / $limit));

    // Query utama
    $sql = "
        SELECT
            s.*,
            k.nama_kelas,
            lv.jenjang,
            a.nama_agama
        FROM $table AS s
        LEFT JOIN master_kelas k ON s.id_kelas = k.id_kelas
        LEFT JOIN master_level_kelas lv ON k.level_id = lv.id_level
        LEFT JOIN master_agama a ON s.id_agama = a.id_agama
    ";

    $sql .= $where;
    $sql .= " ORDER BY s.id_siswa DESC LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dataSiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data"        => $dataSiswa,
        "totalData"   => $totalData,
        "totalPages"  => $totalPages,
        "currentPage" => $page,
        "pesan_cari"  => $pesan_cari,
    ];
}

function deleteSiswa($table, $nis, $pdo)
{
    $stmt = $pdo->prepare("DELETE FROM $table WHERE nis = ?");
    return $stmt->execute([$nis]);
}

function getSiswaByNis(PDO $link, string $tabel, $nis)
{
    $stmt = $link->prepare("SELECT * FROM $tabel WHERE nis = ? LIMIT 1");
    $stmt->execute([$nis]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getSiswaById(PDO $link, string $tabel, int $id)
{
    $stmt = $link->prepare("SELECT * FROM $tabel WHERE id_siswa = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function checkAgama(PDO $link, $id_agama): void
{

    $stmt = $link->prepare("SELECT id_agama FROM master_agama WHERE id_agama = ? AND deleted_at IS NULL");
    $stmt->execute([$id_agama]);
    if (!$stmt->fetch())
    {
        throw new Exception("Agama tidak valid atau sudah dihapus.");
    }
}

function checkKelas(PDO $link, $id_kelas): void
{
    $stmt = $link->prepare("SELECT id_kelas FROM master_kelas WHERE id_kelas = ? AND deleted_at IS NULL");
    $stmt->execute([$id_kelas]);
    if (!$stmt->fetch())
    {
        throw new Exception("Kelas tidak valid atau sudah dihapus.");
    }
}

function checkTahunPelajaran(PDO $link, $id_tahun): void
{
    $stmt = $link->prepare("SELECT id_tahun FROM master_tahun_pelajaran WHERE id_tahun = ? AND deleted_at IS NULL");
    $stmt->execute([$id_tahun]);
    if (!$stmt->fetch())
    {
        throw new Exception("Tahun pelajaran tidak valid.");
    }
}

function checkSemester(PDO $link, $id_semester): void
{
    $stmt = $link->prepare("SELECT id_semester FROM master_semester WHERE id_semester = ? AND deleted_at IS NULL");
    $stmt->execute([$id_semester]);
    if (!$stmt->fetch())
    {
        throw new Exception("Semester tidak valid.");
    }
}

function checkPekerjaanOrtu(PDO $link, $id_pekerjaan): void
{
    if (empty($id_pekerjaan)) return;

    $stmt = $link->prepare("SELECT id_pekerjaan FROM master_pekerjaan WHERE id_pekerjaan = ? AND deleted_at IS NULL");
    $stmt->execute([$id_pekerjaan]);
    if (!$stmt->fetch())
    {
        throw new Exception("Pekerjaan orang tua/wali tidak valid.");
    }
}

function createSiswa(PDO $link, string $tabel, array $data): int
{
    // Validasi wajib
    checkKelas($link, $data['id_kelas'] ?? null);
    checkTahunPelajaran($link, $data['id_tahun_pelajaran'] ?? null);
    checkSemester($link, $data['id_semester'] ?? null);
    checkAgama($link, $data['id_agama'] ?? null);

    // Validasi pekerjaan ortu/wali (boleh null)
    checkPekerjaanOrtu($link, $data['ayah_pekerjaan'] ?? null);
    checkPekerjaanOrtu($link, $data['ibu_pekerjaan'] ?? null);
    checkPekerjaanOrtu($link, $data['wali_pekerjaan'] ?? null);

    // INSERT
    $sql = "INSERT INTO $tabel (
        nama, nis, nisn, nik, tempat_lahir, tgl_lahir, id_agama, id_kelas, ruang,
        id_tahun_pelajaran, id_semester,
        ayah_nama, ayah_tahun_lahir, ayah_pendidikan, ayah_pekerjaan, ayah_penghasilan, ayah_nik,
        ibu_nama, ibu_tahun_lahir, ibu_pendidikan, ibu_pekerjaan, ibu_penghasilan, ibu_nik,
        wali_nama, wali_tahun_lahir, wali_pendidikan, wali_pekerjaan, wali_penghasilan, wali_nik,
        alamat, rt_rw, dusun, kelurahan, kecamatan, kode_pos
    ) VALUES (
        :nama, :nis, :nisn, :nik, :tempat_lahir, :tgl_lahir, :id_agama, :id_kelas, :ruang,
        :id_tahun_pelajaran, :id_semester,
        :ayah_nama, :ayah_tahun_lahir, :ayah_pendidikan, :ayah_pekerjaan, :ayah_penghasilan, :ayah_nik,
        :ibu_nama, :ibu_tahun_lahir, :ibu_pendidikan, :ibu_pekerjaan, :ibu_penghasilan, :ibu_nik,
        :wali_nama, :wali_tahun_lahir, :wali_pendidikan, :wali_pekerjaan, :wali_penghasilan, :wali_nik,
        :alamat, :rt_rw, :dusun, :kelurahan, :kecamatan, :kode_pos
    )";

    $stmt = $link->prepare($sql);
    $stmt->execute($data);

    return (int)$link->lastInsertId();
}

// ====================== UPDATE SISWA + VALIDASI ======================
function updateSiswa(PDO $link, string $tabel, array $data): void
{
    checkKelas($link, $data['id_kelas'] ?? null);
    checkTahunPelajaran($link, $data['id_tahun_pelajaran'] ?? null);
    checkSemester($link, $data['id_semester'] ?? null);
    checkAgama($link, $data['id_agama'] ?? null);

    checkPekerjaanOrtu($link, $data['ayah_pekerjaan'] ?? null);
    checkPekerjaanOrtu($link, $data['ibu_pekerjaan'] ?? null);
    checkPekerjaanOrtu($link, $data['wali_pekerjaan'] ?? null);

    // UPDATE
    $sql = "UPDATE $tabel SET
                nama=:nama, nis=:nis, nisn=:nisn, nik=:nik, tempat_lahir=:tempat_lahir,
                tgl_lahir=:tgl_lahir, id_agama=:id_agama, id_kelas=:id_kelas, ruang=:ruang,
                id_tahun_pelajaran=:id_tahun_pelajaran, id_semester=:id_semester,
                ayah_nama=:ayah_nama, ayah_tahun_lahir=:ayah_tahun_lahir, ayah_pendidikan=:ayah_pendidikan,
                ayah_pekerjaan=:ayah_pekerjaan, ayah_penghasilan=:ayah_penghasilan, ayah_nik=:ayah_nik,
                ibu_nama=:ibu_nama, ibu_tahun_lahir=:ibu_tahun_lahir, ibu_pendidikan=:ibu_pendidikan,
                ibu_pekerjaan=:ibu_pekerjaan, ibu_penghasilan=:ibu_penghasilan, ibu_nik=:ibu_nik,
                wali_nama=:wali_nama, wali_tahun_lahir=:wali_tahun_lahir, wali_pendidikan=:wali_pendidikan,
                wali_pekerjaan=:wali_pekerjaan, wali_penghasilan=:wali_penghasilan, wali_nik=:wali_nik,
                alamat=:alamat, rt_rw=:rt_rw, dusun=:dusun, kelurahan=:kelurahan,
                kecamatan=:kecamatan, kode_pos=:kode_pos
            WHERE id_siswa = :id_siswa";

    $stmt = $link->prepare($sql);
    $stmt->execute($data);
}
