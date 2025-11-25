<?php
function getAgama($pdo)
{
    $query = "
        SELECT
            *
        FROM master_agama
        WHERE deleted_at IS NULL
        ORDER BY nama_agama
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $dataAgama = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $dataAgama,
    ];
}

function addAgama($nama_agama, $pdo)
{
    try
    {
        // Cek duplikat
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM master_agama WHERE nama_agama = ? AND deleted_at IS NULL");
        $check_stmt->execute([$nama_agama]);
        $exists = $check_stmt->fetchColumn();

        if ($exists > 0)
        {
            return ['success' => false, 'message' => 'Agama sudah ada'];
        }

        // Insert baru
        $stmt = $pdo->prepare("INSERT INTO master_agama (nama_agama) VALUES (?)");
        $result = $stmt->execute([$nama_agama]);

        return ['success' => $result, 'message' => $result ? 'Agama berhasil ditambahkan' : 'Gagal menambahkan agama'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkAgamaExists($id, $pdo)
{
    // Cek apakah agama ada dan belum di-delete
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_agama WHERE id_agama = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function deleteAgama($id, $pdo)
{
    try
    {
        // Cek dulu apakah agama exists
        if (!checkAgamaExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Agama tidak ditemukan'];
        }

        // Lakukan soft delete
        $stmt = $pdo->prepare("UPDATE master_agama SET deleted_at = NOW() WHERE id_agama = ?");
        $result = $stmt->execute([$id]);

        return ['success' => $result, 'message' => $result ? 'Agama berhasil dihapus' : 'Gagal menghapus agama'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function getSemester($pdo)
{
    $query = "
        SELECT
            *
        FROM master_semester
        WHERE deleted_at IS NULL
        ORDER BY nama_semester
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $dataPekerjaan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $dataPekerjaan,
    ];
}

function addSemester($nama_semester, $pdo)
{
    try
    {
        // Cek duplikat
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM master_semester WHERE nama_semester = ? AND deleted_at IS NULL");
        $check_stmt->execute([$nama_semester]);
        $exists = $check_stmt->fetchColumn();

        if ($exists > 0)
        {
            return ['success' => false, 'message' => 'Nama semester sudah ada'];
        }

        // Insert baru
        $stmt = $pdo->prepare("INSERT INTO master_semester (nama_semester) VALUES (?)");
        $result = $stmt->execute([$nama_semester]);

        return ['success' => $result, 'message' => $result ? 'Nama Semester berhasil ditambahkan' : 'Gagal menambahkan Nama Semester'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkSemesterExists($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_semester WHERE id_semester = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function deleteSemester($id, $pdo)
{
    try
    {
        if (!checkSemesterExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Nama semester tidak ditemukan'];
        }

        $stmt = $pdo->prepare("UPDATE master_semester SET deleted_at = NOW() WHERE id_semester = ?");
        $result = $stmt->execute([$id]);

        return ['success' => $result, 'message' => $result ? 'nama semester berhasil dihapus' : 'Gagal menghapus nama semester'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function getPekerjaan($pdo)
{
    $query = "
        SELECT
            *
        FROM master_pekerjaan
        WHERE deleted_at IS NULL
        ORDER BY nama_pekerjaan
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $dataPekerjaan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $dataPekerjaan,
    ];
}

function addPekerjaan($nama_pekerjaan, $pdo)
{
    try
    {
        // Cek duplikat
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM master_pekerjaan WHERE nama_pekerjaan = ? AND deleted_at IS NULL");
        $check_stmt->execute([$nama_pekerjaan]);
        $exists = $check_stmt->fetchColumn();

        if ($exists > 0)
        {
            return ['success' => false, 'message' => 'Pekerjaan sudah ada'];
        }

        // Insert baru
        $stmt = $pdo->prepare("INSERT INTO master_pekerjaan (nama_pekerjaan) VALUES (?)");
        $result = $stmt->execute([$nama_pekerjaan]);

        return ['success' => $result, 'message' => $result ? 'Pekerjaan berhasil ditambahkan' : 'Gagal menambahkan Pekerjaan'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkPekerjaanExists($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_pekerjaan WHERE id_pekerjaan = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function deletePekerjaan($id, $pdo)
{
    try
    {
        if (!checkPekerjaanExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Pekerjaan tidak ditemukan'];
        }

        $stmt = $pdo->prepare("UPDATE master_pekerjaan SET deleted_at = NOW() WHERE id_pekerjaan = ?");
        $result = $stmt->execute([$id]);

        return ['success' => $result, 'message' => $result ? 'pekerjaan berhasil dihapus' : 'Gagal menghapus pekerjaan'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function getKelas($pdo)
{
    $query = "
        SELECT
            k.id_kelas,
            k.nama_kelas,
            l.jenjang,
            l.level_min,
            l.level_max
        FROM master_kelas k
        JOIN master_level_kelas l ON k.level_id = l.id_level
        WHERE k.deleted_at IS NULL
        ORDER BY l.level_min, k.nama_kelas
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $result,
    ];
}

function getKelasByJenjang(PDO $link, $jenjang)
{
    $stmt = $link->prepare("
        SELECT
            mk.id_kelas,
            mk.nama_kelas
        FROM master_kelas mk
        JOIN master_level_kelas ml ON mk.level_id = ml.id_level
        WHERE UPPER(TRIM(ml.jenjang)) = ?
          AND mk.deleted_at IS NULL
        ORDER BY mk.nama_kelas
    ");

    $stmt->execute([$jenjang]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ["data" => $result];
}

function addKelas($nama_kelas, $level_id, $pdo)
{
    try
    {
        // pastikan level ada
        $check_level = $pdo->prepare("SELECT COUNT(*) FROM master_level_kelas WHERE id_level = ?");
        $check_level->execute([$level_id]);
        if ($check_level->fetchColumn() == 0)
        {
            return ['success' => false, 'message' => 'Level kelas tidak valid'];
        }

        // cek duplikat kelas di level tersebut
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM master_kelas
            WHERE nama_kelas = ? AND level_id = ? AND deleted_at IS NULL
        ");
        $check_stmt->execute([$nama_kelas, $level_id]);
        $exists = $check_stmt->fetchColumn();

        if ($exists > 0)
        {
            return ['success' => false, 'message' => 'Nama kelas sudah ada di level ini'];
        }

        // insert
        $stmt = $pdo->prepare("INSERT INTO master_kelas (nama_kelas, level_id) VALUES (?, ?)");
        $result = $stmt->execute([$nama_kelas, $level_id]);

        return [
            'success' => $result,
            'message' => $result ? 'Kelas berhasil ditambahkan' : 'Gagal menambahkan kelas'
        ];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkKelasExists($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_kelas WHERE id_kelas = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function deleteKelas($id, $pdo)
{
    try
    {
        if (!checkKelasExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Kelas tidak ditemukan'];
        }

        $stmt = $pdo->prepare("UPDATE master_kelas SET deleted_at = NOW() WHERE id_kelas = ?");
        $result = $stmt->execute([$id]);

        return ['success' => $result, 'message' => $result ? 'kelas berhasil dihapus' : 'Gagal menghapus kelas'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}


function getLevelKelas($pdo)
{
    $query = "
        SELECT
            *
        FROM master_level_kelas
        WHERE deleted_at IS NULL
        ORDER BY jenjang
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $result,
    ];
}

function addLevelKelas($jenjang, $level_min, $level_max, $pdo)
{
    try
    {
        // Cek duplikat jenjang
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM master_level_kelas
            WHERE jenjang = ? AND deleted_at IS NULL
        ");
        $check_stmt->execute([$jenjang]);

        if ($check_stmt->fetchColumn() > 0)
        {
            return ['success' => false, 'message' => 'Level Kelas sudah ada'];
        }

        // Insert baru
        $stmt = $pdo->prepare("
            INSERT INTO master_level_kelas (jenjang, level_min, level_max)
            VALUES (?, ?, ?)
        ");

        $result = $stmt->execute([$jenjang, $level_min, $level_max]);

        return [
            'success' => $result,
            'message' => $result ? 'Level Kelas berhasil ditambahkan' : 'Gagal menambahkan Level Kelas'
        ];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkLevelKelasExists($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_level_kelas WHERE id_level = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function deleteLevelKelas($id, $pdo)
{
    try
    {
        if (!checkLevelKelasExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Level kelas tidak ditemukan'];
        }

        $stmt = $pdo->prepare("UPDATE master_level_kelas SET deleted_at = NOW() WHERE id_level = ?");
        $result = $stmt->execute([$id]);

        return ['success' => $result, 'message' => $result ? 'Level kelas berhasil dihapus' : 'Gagal menghapus Level kelas'];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function getTahunPelajaran($pdo)
{
    $query = "
        SELECT
            *
        FROM master_tahun_pelajaran
        WHERE deleted_at IS NULL
        ORDER BY id_tahun
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data" => $result,
    ];
}

function addTahunPelajaran($tahun, $pdo)
{
    try
    {
        // Cek duplikat tahun
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM master_tahun_pelajaran
            WHERE tahun = ? AND deleted_at IS NULL
        ");
        $check_stmt->execute([$tahun]);

        if ($check_stmt->fetchColumn() > 0)
        {
            return ['success' => false, 'message' => 'Tahun pelajaran sudah ada'];
        }

        // Insert baru
        $stmt = $pdo->prepare("
            INSERT INTO master_tahun_pelajaran (tahun)
            VALUES (?)
        ");

        $result = $stmt->execute([$tahun]);

        return [
            'success' => $result,
            'message' => $result ? 'Tahun pelajaran berhasil ditambahkan' : 'Gagal menambahkan Tahun pelajaran'
        ];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}

function checkTahunPelajaranExists($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_tahun_pelajaran WHERE id_tahun = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

function isTahunPelajaranAktif($id, $pdo)
{
    $stmt = $pdo->prepare("SELECT is_aktif FROM master_tahun_pelajaran WHERE id_tahun = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() == 1;
}


function deleteTahunPelajaran($id, $pdo)
{
    try
    {
        if (!checkTahunPelajaranExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Tahun pelajaran tidak ditemukan'];
        }

        // CEK: Tidak boleh hapus data yang aktif
        if (isTahunPelajaranAktif($id, $pdo))
        {
            return ['success' => false, 'message' => 'Tahun pelajaran yang sedang aktif tidak bisa dihapus'];
        }

        $stmt = $pdo->prepare("UPDATE master_tahun_pelajaran SET deleted_at = NOW() WHERE id_tahun = ?");
        $result = $stmt->execute([$id]);

        return [
            'success' => $result,
            'message' => $result ? 'Tahun pelajaran berhasil dihapus' : 'Gagal menghapus tahun pelajaran'
        ];
    }
    catch (Exception $e)
    {
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}


function setActiveTahunPelajaran($id, $pdo)
{
    try
    {
        if (!checkTahunPelajaranExists($id, $pdo))
        {
            return ['success' => false, 'message' => 'Tahun pelajaran tidak ditemukan'];
        }

        $pdo->beginTransaction();

        // Matikan semua
        $pdo->exec("UPDATE master_tahun_pelajaran SET is_aktif = 0 WHERE deleted_at IS NULL");

        // Aktifkan yang dipilih
        $stmt = $pdo->prepare("UPDATE master_tahun_pelajaran SET is_aktif = 1 WHERE id_tahun = ?");
        $result = $stmt->execute([$id]);

        $pdo->commit();

        return ['success' => $result, 'message' => 'Tahun pelajaran diaktifkan'];
    }
    catch (Exception $e)
    {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Error sistem: ' . $e->getMessage()];
    }
}
