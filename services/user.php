<?php
function getUsersData($page, $limit, $search, $pdo)
{
    $offset = ($page - 1) * $limit;

    // Kondisi dasar: user tidak terhapus
    $where  = " WHERE u.deleted_at IS NULL ";
    $params = [];
    $pesan_cari = "";

    // Jika ada pencarian
    if (!empty($search))
    {
        $cari = "%$search%";

        // CONCATKAN di dalam AND (...)
        $where .= " AND (
                        u.nama_lengkap LIKE ?
                        OR u.username LIKE ?
                        OR r.nama_role LIKE ?
                    )";

        $params = [$cari, $cari, $cari];

        $pesan_cari = "Hasil pencarian <b>\"" . htmlspecialchars($search) . "\"</b>:";
    }

    // --- TOTAL DATA ---
    $countQuery = "
        SELECT COUNT(*) AS total
        FROM users u
        LEFT JOIN role r ON u.id_role = r.id_role
        LEFT JOIN master_level_kelas lv ON u.level_id = lv.id_level
        $where
    ";

    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $totalData = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $totalPages = max(1, ceil($totalData / $limit));


    // --- AMBIL DATA USERS ---
    $sql = "
        SELECT
            u.*,
            r.nama_role,
            lv.jenjang
        FROM users u
        LEFT JOIN role r ON u.id_role = r.id_role
        LEFT JOIN master_level_kelas lv ON u.level_id = lv.id_level
        $where
        ORDER BY u.id_user DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);

    // Bind params SEARCH
    $paramIndex = 1;
    foreach ($params as $val)
    {
        $stmt->bindValue($paramIndex++, $val);
    }

    // Bind LIMIT & OFFSET
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $dataUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        "data"        => $dataUsers,
        "totalData"   => (int)$totalData,
        "totalPages"  => $totalPages,
        "currentPage" => (int)$page,
        "pesan_cari"  => $pesan_cari,
    ];
}

function deleteUser($username, $pdo)
{
    $sql = "UPDATE users SET deleted_at = NOW() WHERE username = ? AND deleted_at IS NULL";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$username]);
    return $result;
}

function getUserCurrent($id, $pdo)
{
    $stmt = $pdo->prepare("
        SELECT u.*, m.jenjang
        FROM users u
        LEFT JOIN master_level_kelas m ON u.level_id = m.id_level
        WHERE u.id_user = ? AND u.deleted_at IS NULL
    ");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}

function isUsernameTaken($username, $currentId, $pdo)
{
    $stmt = $pdo->prepare("
        SELECT id_user
        FROM users
        WHERE username = ? AND id_user != ? AND deleted_at IS NULL
    ");
    $stmt->execute([$username, $currentId]);
    return $stmt->rowCount() > 0;
}

function updateUserProfile($id_user, $data, $pdo)
{
    if (!empty($data['password']))
    {
        $sql = "UPDATE users SET nama_lengkap = ?, username = ?, password = ? WHERE id_user = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['nama_lengkap'],
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $id_user
        ]);
    }

    // tanpa password
    $sql = "UPDATE users SET nama_lengkap = ?, username = ? WHERE id_user = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['nama_lengkap'],
        $data['username'],
        $id_user
    ]);
}

function checkRole(PDO $link, $id_role)
{
    $stmt = $link->prepare("SELECT id_role, nama_role FROM role WHERE id_role = ?");
    $stmt->execute([$id_role]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$role)
    {
        throw new Exception("Role tidak valid.");
    }

    // tolak jika role = super admin
    if (strtolower($role['nama_role']) === 'super admin')
    {
        throw new Exception("User dengan role 'Super Admin' tidak boleh dibuat.");
    }
}

function checkLevelKelas(PDO $link, $level_id)
{
    // cek level_id alias jenjang

    $stmt = $link->prepare("
            SELECT id_level
            FROM master_level_kelas
            WHERE id_level = ? AND deleted_at IS NULL
        ");
    $stmt->execute([$level_id]);
    if (!$stmt->fetch())
    {
        throw new Exception("Jenjang akses tidak valid.");
    }
}

function createUser(PDO $link, array $data)
{
    // cek role
    checkRole($link, $data['id_role']);

    // cek level_id alias jenjang (akses)
    checkLevelKelas($link, $data['level_id']);

    // --- INSERT USER ---
    $sql = "INSERT INTO users (nama_lengkap, username, password, id_role, level_id)
            VALUES (:nama, :username, :pass, :role, :level)";

    $stmt = $link->prepare($sql);
    $stmt->execute([
        ':nama'     => $data['nama_lengkap'],
        ':username' => $data['username'],
        ':pass'     => password_hash($data['password'], PASSWORD_DEFAULT),
        ':role'     => $data['id_role'],
        ':level'    => $data['level_id']
    ]);
}

function getUserByUsername(PDO $link, string $username)
{
    $stmt = $link->prepare("
        SELECT u.*, r.nama_role, ml.jenjang, ml.level_min, ml.level_max
        FROM users u
        LEFT JOIN role r ON u.id_role = r.id_role
        LEFT JOIN master_level_kelas ml ON u.level_id = ml.id_level
        WHERE u.username = ?
    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}

function updateUserByUsername(PDO $link, array $data, string $username_url)
{
    // cek role
    checkRole($link, $data['id_role']);

    // cek level_id alias jenjang (akses)
    checkLevelKelas($link, $data['level_id']);

    $sql = "UPDATE users SET
            nama_lengkap = :nama,
            username     = :username,
            id_role      = :id_role,
            level_id     = :level_id";

    $params = [
        ':nama'     => $data['nama_lengkap'],
        ':username' => $data['username'],
        ':id_role'  => $data['id_role'],
        ':level_id' => $data['level_id'],
        ':old_user' => $username_url
    ];

    // Jika ada password baru
    if (!empty($data['password']))
    {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql .= " WHERE username = :old_user";

    $stmt = $link->prepare($sql);
    $stmt->execute($params);
}
