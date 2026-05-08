<?php
require_once 'lib/utils.php';

/**
 * seeder/index.php
 * Semua INSERT data, urutan mengikuti dependency FK.
 *
 */


function ins(mysqli $link, string $sql): void
{
    mysqli_query($link, $sql) or die("Seeder error: " . mysqli_error($link) . "<br>");
}

function e(mysqli $link, ?string $v): string
{
    return $v === null ? 'NULL' : "'" . mysqli_real_escape_string($link, $v) . "'";
}


// 1. ROLES
ins($link, "INSERT INTO roles (id, name, is_active) VALUES
    ('superadmin', 'Super Admin', 1),
    ('kepsek_sd',  'Kepala Sekolah SD',  1),
    ('kepsek_smp', 'Kepala Sekolah SMP', 1),
    ('kepsek_sma', 'Kepala Sekolah SMA', 1),
    ('guru_sd',    'Guru SD', 1),
    ('guru_smp',   'Guru SMP', 1),
    ('guru_sma',   'Guru SMA', 1)");
echo "✔ roles<br>";


// 2. PERMISSIONS
ins($link, "INSERT INTO permissions (code, name, description) VALUES
    ('view',   'View',   'Lihat / baca data'),
    ('create', 'Create', 'Tambah data baru'),
    ('edit',   'Edit',   'Ubah / update data'),
    ('delete', 'Delete', 'Non-aktifkan data (soft delete / ubah status)'),
    ('export', 'Export', 'Download data ke Excel atau CSV'),
    ('import', 'Import', 'Upload data bulk dari Excel atau CSV')");
echo "✔ permissions<br>";


// 3. MENUS
ins($link, "INSERT INTO menus (slug, name, route, icon, sort_order) VALUES
    ('dashboard',         'Dashboard', 'dashboard.php',         'fa-house',             0),
    -- Parent
    ('siswa',             'Siswa',       NULL,                  'fa-graduation-cap',     1),
    ('guru',              'Guru',        NULL,                  'fa-chalkboard-teacher', 2),
    ('master',            'Master Data', NULL,                  'fa-layer-group',        3),
    ('setting',           'Settings',    NULL,                  'fa-cog',                4),

    -- Sub: Siswa
    ('siswa-sd',          'SD',          'siswa-sd.php',        'fa-school',             1),
    ('siswa-smp',         'SMP',         'siswa-smp.php',       'fa-building-columns',   2),
    ('siswa-sma',         'SMA',         'siswa-sma.php',       'fa-user-graduate',      3),

    -- Sub: Guru
    ('guru-sd',           'SD',          'guru-sd.php',         'fa-school',             1),
    ('guru-smp',          'SMP',         'guru-smp.php',        'fa-building-columns',   2),
    ('guru-sma',          'SMA',         'guru-sma.php',        'fa-user-graduate',      3),

    -- Sub: Master Data (hanya yang masih relevan)
    ('master-agama',      'Agama',       'agama.php',           'fa-pray',               1),
    ('master-pkrj',       'Pekerjaan',   'pekerjaan.php',       'fa-briefcase',          2),
    ('master-level',      'Level Kelas', 'level-kelas.php',     'fa-layer-group',        3),
    ('master-kelas',      'Kelas',       'kelas.php',           'fa-chalkboard',         4),
    ('master-admin',     'Admin',       'admin.php',           'fa-user-plus',          5),
    ('master-role',      'Role',        'role.php',            'fa-shield-alt',         6),

    -- Sub: Settings
    ('setting-profil',    'Profil',      'profile.php',         'fa-user-pen',           1),
    ('setting-logout',    'Logout',      'logout.php',          'fa-sign-out-alt',       2)");

// Set parent_id
$getMenuId = fn(string $slug): int => (int)(mysqli_fetch_assoc(
    mysqli_query($link, "SELECT id FROM menus WHERE slug='" . mysqli_real_escape_string($link, $slug) . "' LIMIT 1")
)['id'] ?? 0);

foreach (
    [
        'siswa'   => ['siswa-sd', 'siswa-smp', 'siswa-sma'],
        'guru'    => ['guru-sd', 'guru-smp', 'guru-sma'],
        'master'  => ['master-agama', 'master-pkrj', 'master-level', 'master-kelas', 'master-admin', 'master-role'],
        'setting' => ['setting-profil', 'setting-logout'],
    ] as $parent => $children
)
{
    $pid = $getMenuId($parent);
    foreach ($children as $child)
    {
        ins($link, "UPDATE menus SET parent_id=$pid WHERE slug=" . e($link, $child));
    }
}
echo "✔ menus<br>";


// 4. ROLE MENU PERMISSION
$menuIds = [];
$r = mysqli_query($link, "SELECT id, slug FROM menus");
while ($row = mysqli_fetch_assoc($r)) $menuIds[$row['slug']] = (int)$row['id'];

$permIds = [];
$r = mysqli_query($link, "SELECT id, code FROM permissions");
while ($row = mysqli_fetch_assoc($r)) $permIds[$row['code']] = (int)$row['id'];

$FULL = ['view', 'create', 'edit', 'delete', 'export', 'import'];
$V    = ['view'];
$VE   = ['view', 'export'];
$VC   = ['view', 'create', 'edit', 'delete'];
$VCEI = ['view', 'create', 'edit', 'export', 'import'];

$matrix = [

    // SUPER ADMIN — akses penuh semua menu & semua permission
    'superadmin' => [
        'dashboard'      => $V,
        'siswa'          => $FULL,
        'siswa-sd'       => $FULL,
        'siswa-smp'      => $FULL,
        'siswa-sma'      => $FULL,
        'guru'           => $FULL,
        'guru-sd'        => $FULL,
        'guru-smp'       => $FULL,
        'guru-sma'       => $FULL,
        'master'         => $FULL,
        'master-agama'   => $FULL,
        'master-pkrj'    => $FULL,
        'master-level'   => $FULL,
        'master-kelas'   => $FULL,
        'master-admin'   => $FULL,
        'master-role'    => $FULL,
        'setting'        => $FULL,
        'setting-profil' => $FULL,
        'setting-logout' => $V,
    ],

    // KEPALA SEKOLAH — lihat & download semua data jenjangnya
    'kepsek_sd' => [
        'dashboard'      => $V,
        'siswa'          => $VE,
        'siswa-sd'       => $VE,
        'guru'           => $VE,
        'guru-sd'        => $VE,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],

    'kepsek_smp' => [
        'dashboard'      => $V,
        'siswa'          => $VE,
        'siswa-smp'       => $VE,
        'guru'           => $VE,
        'guru-smp'        => $VE,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],

    'kepsek_sma' => [
        'dashboard'      => $V,
        'siswa'          => $VE,
        'siswa-sma'       => $VE,
        'guru'           => $VE,
        'guru-sma'        => $VE,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],

    // GURU — CRUD + import/export siswa jenjangnya, tanpa delete
    'guru_sd' => [
        'dashboard'      => $V,
        'siswa'          => $V,
        'siswa-sd'       => $VCEI,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],

    'guru_smp' => [
        'dashboard'      => $V,
        'siswa'          => $V,
        'siswa-smp'       => $VCEI,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],

    'guru_sma' => [
        'dashboard'      => $V,
        'siswa'          => $V,
        'siswa-sma'       => $VCEI,
        'setting'        => $V,
        'setting-profil' => $VC,
        'setting-logout' => $V,
    ],
];

$rmpCount = 0;
foreach ($matrix as $roleId => $menuPerms)
{
    foreach ($menuPerms as $slug => $perms)
    {
        $mid = $menuIds[$slug] ?? null;
        if (!$mid) continue;
        foreach ($perms as $pcode)
        {
            $pid = $permIds[$pcode] ?? null;
            if (!$pid) continue;
            ins($link, "INSERT IGNORE INTO role_menu_permission (role_id, menu_id, permission_id)
                VALUES (" . e($link, $roleId) . ", $mid, $pid)");
            $rmpCount++;
        }
    }
}
echo "✔ role_menu_permission ($rmpCount baris)<br>";


// 5. MASTER LEVEL KELAS
ins($link, "INSERT INTO master_level_kelas (jenjang, level_min, level_max) VALUES
    ('SD',  1,  6),
    ('SMP', 7,  9),
    ('SMA', 10, 12)");
echo "✔ master_level_kelas<br>";


// 6. MASTER AGAMA
ins($link, "INSERT INTO master_agama (nama_agama) VALUES
    ('Islam'),('Kristen'),('Katolik'),('Hindu'),('Budha'),('Konghucu')");
echo "✔ master_agama<br>";


// 7. MASTER PEKERJAAN
ins($link, "INSERT INTO master_pekerjaan (nama_pekerjaan) VALUES
    ('PNS'),('Swasta'),('Wirausaha'),('Petani'),('Nelayan'),('Lainnya')");
echo "✔ master_pekerjaan<br>";


// 8. MASTER KELAS (generate otomatis dari level_kelas)
$res    = mysqli_query($link, "SELECT id_level, level_min, level_max FROM master_level_kelas");
$levels = mysqli_fetch_all($res, MYSQLI_ASSOC);
$kvals  = [];
foreach ($levels as $lv)
{
    for ($t = (int)$lv['level_min']; $t <= (int)$lv['level_max']; $t++)
    {
        $kvals[] = "({$lv['id_level']}, '$t A')";
        $kvals[] = "({$lv['id_level']}, '$t B')";
    }
}
ins($link, "INSERT INTO master_kelas (level_id, nama_kelas) VALUES " . implode(',', $kvals));
echo "✔ master_kelas (" . count($kvals) . " kelas)<br>";


// 9. USERS
$pass = password_hash('123456', PASSWORD_DEFAULT);

$getLevelId = function (string $jenjang) use ($link): string
{
    $j   = mysqli_real_escape_string($link, strtoupper($jenjang));
    $res = mysqli_query($link, "SELECT id_level FROM master_level_kelas WHERE jenjang='$j' LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    return $row ? (string)(int)$row['id_level'] : 'NULL';
};

// [nama_lengkap, username, role_id, jenjang|null]
$users = [
    ['Super Admin', 'admin',      'superadmin',     null],
    ['Kepsek SD',   'kepsek_sd',  'kepsek_sd',      'SD'],
    ['Kepsek SMP',  'kepsek_smp', 'kepsek_smp',     'SMP'],
    ['Kepsek SMA',  'kepsek_sma', 'kepsek_sma',     'SMA'],
    ['Guru SD',     'guru_sd',    'guru_sd',        'SD'],
    ['Guru SMP',    'guru_smp',   'guru_smp',       'SMP'],
    ['Guru SMA',    'guru_sma',   'guru_sma',       'SMA'],
];

foreach ($users as [$nama, $username, $roleId, $jenjang])
{
    $id      = uuidv4();
    $levelId = $jenjang ? $getLevelId($jenjang) : 'NULL';
    ins($link, "INSERT INTO users (id, nama_lengkap, username, password, role_id, level_id) VALUES ("
        . e($link, $id)       . ","
        . e($link, $nama)     . ","
        . e($link, $username) . ","
        . e($link, $pass)     . ","
        . e($link, $roleId)   . ","
        . "$levelId)");
}
echo "✔ users (" . count($users) . " baris, password default: 123456)<br>";


// 10. GURU DUMMY (user_id NULL — belum terhubung ke akun)
// Kolom baru: tahun_masuk (estimasi dari tgl_lahir + ~25 tahun)
// tahun_keluar NULL karena semua berstatus aktif
$guruData = [
    'sd' => [
        // [nik, nuptk, nama, jk, ttl, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan, tahun_masuk]
        ['3201011508950001', '1234567890123456', 'Dra. Siti Aminah, M.Pd',          'P', 'Bandung',    '1985-08-15', 'Siti Rohayah',   'PNS',     'Guru Kelas',        'Wali Kelas 6A',       2010],
        ['3201012307920002', '2345678901234567', 'Ahmad Sobari, S.Pd',              'L', 'Jakarta',    '1992-07-23', 'Siti Fatimah',   'PNS',     'Guru Kelas',        'Wali Kelas 5A',       2017],
        ['3201015503880003', '3456789012345678', 'Hj. Nurhayati, S.Pd.I',           'P', 'Cirebon',    '1988-03-15', 'Siti Maryam',    'PNS',     'Guru Agama Islam',  'Guru Agama SD',       2013],
        ['3201010811850004', '4567890123456789', 'Budi Santoso, S.Pd',              'L', 'Semarang',   '1985-11-08', 'Siti Aisyah',    'PNS',     'Guru Kelas',        'Wali Kelas 4A',       2010],
        ['3201014406810005', '5678901234567890', 'Rina Wulandari, S.Pd',            'P', 'Yogyakarta', '1981-06-04', 'Siti Zahro',     'PNS',     'Guru Kelas',        'Wali Kelas 3B',       2006],
        ['3201012005900006', '6789012345678901', 'Drs. Slamet Riyadi',              'L', 'Surabaya',   '1990-05-20', 'Siti Khodijah',  'PNS',     'Pend. Jasmani',     'Guru PJOK',           2015],
        ['3201016312930007', '7890123456789012', 'Lestari Dewi, S.Pd',              'P', 'Malang',     '1993-12-16', 'Siti Nurjanah',  'Honorer', 'Guru Kelas',        'Wali Kelas 2A',       2018],
        ['3201011708870008', '8901234567890123', 'Agus Supriyanto, S.Pd',           'L', 'Bandung',    '1987-08-17', 'Siti Aminah',    'PNS',     'Guru Kelas',        'Wali Kelas 1A',       2012],
        ['3201014809850009', '9012345678901234', 'Fitri Handayani, S.Pd',           'P', 'Bogor',      '1985-09-28', 'Siti Halimah',   'PNS',     'Guru B. Inggris',   'Guru Inggris SD',     2010],
        ['3201010511800010', '1122334455667788', 'Joko Widodo, S.Pd',               'L', 'Solo',       '1980-01-05', 'Siti Maimunah',  'PNS',     'Kepala Sekolah',    'Kepala Sekolah SD',   2005],
        ['3201011511900011', '9988776655443322', 'Eka Prasetya, S.Pd',              'L', 'Bandung',    '1990-11-15', 'Siti Lestari',   'PNS',     'Guru Kelas',        'Wali Kelas 5B',       2015],
        ['3201016205940012', '8877665544332211', 'Mega Sari, S.Pd',                 'P', 'Jakarta',    '1994-05-22', 'Siti Patimah',   'Honorer', 'Guru Kelas',        'Wali Kelas 3A',       2019],
        ['3201010808880013', '7766554433221100', 'Rizky Fadillah, S.Pd',            'L', 'Cirebon',    '1988-08-08', 'Siti Kalsum',    'PNS',     'Pend. Jasmani',     'Guru PJOK',           2014],
        ['3201015101010014', '6655443322110099', 'Nadia Ramadhani, S.Pd',           'P', 'Surabaya',   '2001-01-21', 'Siti Nurdiana',  'Honorer', 'Guru B. Sunda',     'Guru Bahasa Sunda',   2020],
        ['3201011907970015', '5544332211009988', 'Dedi Mulyadi, S.Pd.I',            'L', 'Bandung',    '1997-07-19', 'Siti Romlah',    'PNS',     'Guru Agama Islam',  'Guru Agama SD',       2019],
    ],
    'smp' => [
        ['3201021408820001', '1122334455667781', 'Dr. H. Mulyono, M.Pd',            'L', 'Semarang',   '1982-08-14', 'Siti Aminah',    'PNS',     'Guru Matematika',   'Waka Kurikulum',      2007],
        ['3201025507850002', '2233445566778891', 'Hj. Siti Nurjanah, M.Pd',         'P', 'Bandung',    '1985-07-05', 'Fatimah Zahro',  'PNS',     'Guru B. Indonesia', 'Guru B. Indonesia',   2010],
        ['3201022006800003', '3344556677889901', 'Bambang Suryo, S.Pd',             'L', 'Surabaya',   '1980-06-20', 'Siti Maryam',    'PNS',     'Guru IPA',          'Guru IPA Terpadu',    2005],
        ['3201024311830004', '4455667788990012', 'Rina Puspita, M.Pd',              'P', 'Yogyakarta', '1983-11-04', 'Siti Halimah',   'PNS',     'Guru IPS',          'Guru IPS',            2008],
        ['3201020810780005', '5566778899001123', 'Drs. Ahmad Yani',                 'L', 'Jakarta',    '1978-10-08', 'Siti Khodijah',  'PNS',     'Guru PPKn',         'Guru PPKn',           2003],
        ['3201026009870006', '6677889900112234', 'Lina Marlina, S.Pd',              'P', 'Malang',     '1987-09-20', 'Siti Aisyah',    'PNS',     'Guru B. Inggris',   'Guru B. Inggris',     2012],
        ['3201021505850007', '7788990011223345', 'Hadi Pramono, S.Pd',              'L', 'Medan',      '1985-05-15', 'Siti Rohaya',    'PNS',     'Guru Matematika',   'Guru Matematika',     2010],
        ['3201024709890008', '8899001122334456', 'Dewi Sartika, S.Pd',              'P', 'Bogor',      '1989-09-07', 'Siti Jamilah',   'Honorer', 'Guru Seni Budaya',  'Guru Seni Musik',     2014],
        ['3201023003820009', '9900112233445567', 'Slamet Haryadi, S.Pd',            'L', 'Solo',       '1982-03-30', 'Siti Maimunah',  'PNS',     'Pend. Jasmani',     'Guru PJOK',           2007],
        ['3201026111840010', '1011121314151617', 'Anisa Fitri, M.Pd',               'P', 'Depok',      '1984-11-21', 'Siti Nurjanah',  'PNS',     'Guru B. Indonesia', 'Wali Kelas 8A',       2009],
        ['3201022405890011', '9988776655443301', 'Bayu Prakoso, S.Pd',              'L', 'Bandung',    '1989-05-24', 'Siti Komariah',  'PNS',     'Guru Matematika',   'Guru Matematika',     2013],
        ['3201026105960012', '8877665544334402', 'Citra Lestari, S.Pd',             'P', 'Jakarta',    '1996-05-21', 'Siti Hasanah',   'PNS',     'Guru IPA',          'Guru IPA',            2018],
        ['3201021503930013', '7766554433225503', 'Dwi Prasetyo, M.Pd',              'L', 'Surabaya',   '1993-03-15', 'Siti Umi',       'PNS',     'Guru IPS',          'Guru IPS',            2017],
        ['3201024904880014', '6655443322116604', 'Eka Rahmawati, S.Pd',             'P', 'Yogyakarta', '1988-04-09', 'Siti Zaenab',    'PNS',     'Guru B. Inggris',   'Guru B. Inggris',     2011],
        ['3201023009840015', '5544332211007705', 'Fajar Nugroho, S.Pd',             'L', 'Semarang',   '1984-09-30', 'Siti Patonah',   'PNS',     'Guru PPKn',         'Wali Kelas 9C',       2008],
        ['3201026712970016', '4433221100889906', 'Gina Febrianti, S.Pd',            'P', 'Malang',     '1997-12-27', 'Siti Murtini',   'Honorer', 'Guru Seni Budaya',  'Guru Seni Tari',      2020],
        ['3201020801860017', '3322110099771107', 'Hendra Kusuma, S.Pd',             'L', 'Bandung',    '1986-01-08', 'Siti Munawaroh', 'PNS',     'Pend. Jasmani',     'Guru PJOK',           2010],
        ['3201025203890018', '2211009988662208', 'Indah Permatasari, S.Pd',         'P', 'Bogor',      '1989-03-22', 'Siti Nurkholisah', 'PNS', 'Guru Prakarya',     'Guru Prakarya',       2012],
        ['3201021707940019', '1100998877553309', 'Joko Susilo, S.Pd',               'L', 'Medan',      '1994-07-17', 'Siti Rodiah',    'PNS',     'Guru B. Indonesia', 'Guru B. Indonesia',   2016],
        ['3201026311900020', '0099887766444410', 'Kartika Sari, M.Pd',              'P', 'Palembang',  '1990-11-23', 'Siti Ngaisah',   'PNS',     'Guru Matematika',   'Waka Sapras',         2014],
    ],
    'sma' => [
        ['3201031208760001', '1122334455660011', 'Prof. Dr. H. Abdul Rahman, M.Sc', 'L', 'Jakarta',    '1976-08-12', 'Siti Fatimah',   'PNS',     'Guru Fisika',       'Guru Fisika XII',     2001],
        ['3201035108790002', '2233445566770022', 'Dr. Hj. Siti Nurhaliza, M.Pd',    'P', 'Bandung',    '1979-07-01', 'Fatimah Zahro',  'PNS',     'Guru Kimia',        'Guru Kimia',          2004],
        ['3201032007740003', '3344556677880033', 'Ir. Budi Santoso, M.T',           'L', 'Surabaya',   '1974-07-20', 'Siti Maryam',    'PNS',     'Guru Matematika',   'Waka Kurikulum',      1999],
        ['3201034510780004', '4455667788990044', 'Rina Susanti, M.Pd',              'P', 'Yogyakarta', '1978-10-04', 'Siti Halimah',   'PNS',     'Guru Biologi',      'Guru Biologi',        2003],
        ['3201030806730005', '5566778899000055', 'Drs. H. Ahmad Subarjo, M.M',      'L', 'Semarang',   '1973-06-08', 'Siti Khodijah',  'PNS',     'Guru Ekonomi',      'Guru Ekonomi',        1998],
        ['3201035908820006', '6677889900110066', 'Larasati, S.Pd., Gr',             'P', 'Malang',     '1982-08-29', 'Siti Aisyah',    'PNS',     'Guru B. Inggris',   'Guru B. Inggris',     2007],
        ['3201031405770007', '7788990011220077', 'Hadi Wijaya, M.Pd',               'L', 'Medan',      '1977-05-14', 'Siti Rohaya',    'PNS',     'Guru Sejarah',      'Guru Sejarah',        2002],
        ['3201034809810008', '8899001122330088', 'Citra Dewi, M.Pd',                'P', 'Bogor',      '1981-09-08', 'Siti Jamilah',   'PNS',     'Guru Sosiologi',    'Guru Sosiologi',      2006],
        ['3201032506750009', '9900112233440099', 'Agus Salim, S.Pd',                'L', 'Makassar',   '1975-06-25', 'Siti Maimunah',  'PNS',     'Guru Geografi',     'Guru Geografi',       2000],
        ['3201036210800010', '1011121314150010', 'Nadia Putri, M.Pd',               'P', 'Palembang',  '1980-10-12', 'Siti Nurjanah',  'PNS',     'Guru B. Indonesia', 'Wali Kelas XI IPA 1', 2005],
        ['3201031105830011', '9988776655440101', 'Aditya Maulana, S.Pd',            'L', 'Bandung',    '1983-05-11', 'Siti Nuraeni',   'PNS',     'Guru Fisika',       'Guru Fisika XI',      2008],
        ['3201036507880012', '8877665544330202', 'Bella Safitri, M.Sc',             'P', 'Jakarta',    '1988-07-25', 'Siti Rukoyah',   'PNS',     'Guru Kimia',        'Guru Kimia XII',      2012],
        ['3201032408980013', '7766554433220303', 'Candra Wijaya, S.Pd',             'L', 'Surabaya',   '1998-08-24', 'Siti Khoiriyah', 'Honorer', 'Guru Matematika',   'Guru Matematika X',   2021],
        ['3201035105000014', '6655443322110404', 'Dinda Aprilia, S.Pd',             'P', 'Yogyakarta', '2000-04-11', 'Siti Muawanah',  'Honorer', 'Guru Biologi',      'Guru Biologi X',      2022],
        ['3201031508870015', '5544332211000505', 'Eko Prasetyo, M.Pd',              'L', 'Semarang',   '1987-08-15', 'Siti Azizah',    'PNS',     'Guru Ekonomi',      'Guru Ekonomi XI',     2011],
        ['3201036905940016', '4433221100880606', 'Fanny Rosalina, S.Pd',            'P', 'Malang',     '1994-05-29', 'Siti Mufidah',   'PNS',     'Guru B. Inggris',   'Guru B. Inggris XII', 2016],
        ['3201032007900017', '3322110099770707', 'Gilang Ramadhan, S.Pd',           'L', 'Bandung',    '1990-07-20', 'Siti Nailah',    'PNS',     'Guru Sejarah',      'Guru Sejarah XI',     2015],
        ['3201036311830018', '2211009988660808', 'Hilda Nurhidayah, M.Pd',          'P', 'Bogor',      '1983-11-23', 'Siti Masyitoh',  'PNS',     'Guru Sosiologi',    'Guru Sosiologi XII',  2009],
        ['3201030805950019', '1100998877550909', 'Irfan Hakim, S.Pd',               'L', 'Medan',      '1995-05-08', 'Siti Nurkholis', 'PNS',     'Guru Geografi',     'Guru Geografi XI',    2018],
        ['3201035012960020', '0099887766441010', 'Jihan Fadhila, S.Pd',             'P', 'Palembang',  '1996-12-10', 'Siti Salamah',   'Honorer', 'Guru B. Indonesia', 'Guru B. Indonesia X', 2019],
        ['3201031407910021', '1283746512098345', 'Kevin Sanjaya, S.Pd',             'L', 'Makassar',   '1991-07-14', 'Siti Rahmah',    'PNS',     'Guru Fisika',       'Guru Fisika X',       2015],
        ['3201035904970022', '2394857612309456', 'Laila Maharani, M.Pd',            'P', 'Manado',     '1997-04-29', 'Siti Sholeha',   'PNS',     'Guru Kimia',        'Guru Kimia XI',       2019],
        ['3201032102930023', '3405968721410567', 'Moch. Rizky, S.Pd',               'L', 'Bali',       '1993-02-21', 'Siti Maesaroh',  'PNS',     'Guru Matematika',   'Wali Kelas XII IPA 2', 2016],
        ['3201036504880024', '4516079832521678', 'Nabila Zalsa, S.Pd',              'P', 'Lampung',    '1988-04-25', 'Siti Umaiyah',   'PNS',     'Guru Biologi',      'Guru Biologi XI',     2011],
        ['3201031309990025', '5627180943632789', 'Oscar Pratama, S.Pd',             'L', 'Padang',     '1999-09-13', 'Siti Kamilah',   'Honorer', 'Guru B. Inggris',   'Guru B. Inggris X',   2021],
    ],
];

foreach ($guruData as $j => $rows)
{
    foreach ($rows as [$nik, $nuptk, $nama, $jk, $ttl, $tgl, $ibu, $status, $gtk, $jabatan, $tahunMasuk])
    {
        ins($link, "INSERT INTO guru_$j
            (nik, nuptk, nama, jenis_kelamin, tempat_lahir, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan, tahun_masuk)
            VALUES ("
            . e($link, $nik)     . "," . e($link, $nuptk)   . ","
            . e($link, $nama)    . ",'$jk',"
            . e($link, $ttl)     . ",'" . $tgl . "',"
            . e($link, $ibu)     . "," . e($link, $status)  . ","
            . e($link, $gtk)     . "," . e($link, $jabatan) . ","
            . "$tahunMasuk)");
    }
    echo "✔ guru_$j (" . count($rows) . " baris)<br>";
}


// 11. SISWA DUMMY
// tahun_masuk = tahun pertama siswa mendaftar ke sekolah ini
// tahun_keluar = NULL (semua masih aktif)
$siswaData = [
    'sd' => [
        // [nama, nis, nisn, ttl, tgl_lahir, id_agama, id_kelas, ruang, ayah_nama, tahun_masuk]
        ['Ahmad Rizky Pratama',    '100001', '0001000010', 'Bandung',    '2015-01-15', 1, 1,  'A', 'Sutrisno',       2021],
        ['Siti Nurhaliza',         '100002', '0001000020', 'Jakarta',    '2015-02-20', 1, 2,  'B', 'Hadi Wijaya',    2021],
        ['Muhammad Farhan',        '100003', '0001000030', 'Surabaya',   '2015-03-10', 1, 3,  'A', 'Agus Salim',     2021],
        ['Aisyah Putri Ramadhani', '100004', '0001000040', 'Yogyakarta', '2015-04-05', 1, 4,  'B', 'Budi Santoso',   2021],
        ['Rizki Maulana',          '100005', '0001000050', 'Semarang',   '2015-05-18', 2, 5,  'B', 'Joko Widodo',    2021],
        ['Nadia Putri Lestari',    '100006', '0001000060', 'Medan',      '2015-06-22', 1, 6,  'A', 'Rahmat Hidayat', 2021],
        ['Fajar Siddiq',           '100007', '0001000070', 'Makassar',   '2014-07-30', 1, 7,  'B', 'Andi Malik',     2020],
        ['Putri Ayu Saraswati',    '100008', '0001000080', 'Palembang',  '2014-08-14', 1, 8,  'C', 'Slamet Riyadi',  2020],
        ['Dika Pratama',           '100009', '0001000090', 'Bandung',    '2014-09-25', 1, 9,  'B', 'Herman',         2020],
        ['Larasati Dewi',          '100010', '0001000100', 'Jakarta',    '2014-10-11', 1, 10, 'A', 'Wahyudi',        2020],
        ['Arief Rahman Hakim',     '100011', '0001000110', 'Surabaya',   '2013-11-03', 2, 11, 'B', 'Samsul',         2019],
        ['Citra Kirana',           '100012', '0001000120', 'Malang',     '2013-12-19', 1, 12, 'A', 'Edi Susanto',    2019],
        ['Bayu Aji Nugroho',       '100013', '0001000130', 'Semarang',   '2016-01-07', 1, 1,  'A', 'Sutopo',         2022],
        ['Intan Permata Sari',     '100014', '0001000140', 'Bogor',      '2016-02-28', 1, 2,  'B', 'Yusuf',          2022],
        ['Rangga Pratama',         '100015', '0001000150', 'Depok',      '2016-03-12', 1, 3,  'C', 'Darmawan',       2022],
        ['Zahra Aulia',            '100016', '0001000160', 'Tangerang',  '2016-04-21', 1, 4,  'D', 'Fajar',          2022],
        ['Dimas Senopati',         '100017', '0001000170', 'Bekasi',     '2016-05-09', 1, 5,  'B', 'Gunawan',        2022],
        ['Anisa Fitriani',         '100018', '0001000180', 'Bandung',    '2016-06-17', 1, 6,  'B', 'Suparman',       2022],
        ['Eko Wijaya',             '100019', '0001000190', 'Jakarta',    '2016-07-25', 2, 7,  'B', 'Sutikno',        2022],
        ['Kartika Sari',           '100020', '0001000200', 'Surabaya',   '2016-08-30', 1, 8,  'A', 'Hendra',         2022],
    ],
    'smp' => [
        ['Aditya Pratama',   '200001', '0002000010', 'Jakarta',    '2012-01-10', 1, 13, 'A', 'Slamet',    2024],
        ['Bella Safira',     '200002', '0002000020', 'Bandung',    '2012-02-15', 1, 13, 'A', 'Bambang',   2024],
        ['Candra Wijaya',    '200003', '0002000030', 'Surabaya',   '2012-03-20', 1, 14, 'B', 'Teguh',     2024],
        ['Dina Lestari',     '200004', '0002000040', 'Yogyakarta', '2012-04-25', 1, 14, 'B', 'Agung',     2024],
        ['Eka Putra',        '200005', '0002000050', 'Semarang',   '2012-05-30', 2, 15, 'A', 'Rudi',      2024],
        ['Fani Andriani',    '200006', '0002000060', 'Medan',      '2012-06-14', 1, 15, 'A', 'Hadi',      2024],
        ['Galih Pratama',    '200007', '0002000070', 'Makassar',   '2012-07-19', 1, 16, 'B', 'Junaidi',   2024],
        ['Hana Maulida',     '200008', '0002000080', 'Palembang',  '2012-08-23', 1, 16, 'B', 'Surya',     2024],
        ['Ilham Ramadhan',   '200009', '0002000090', 'Bandung',    '2011-09-27', 1, 17, 'A', 'Dani',      2023],
        ['Jihan Aisyah',     '200010', '0002000100', 'Jakarta',    '2011-10-11', 1, 17, 'A', 'Faisal',    2023],
        ['Kemal Pasha',      '200011', '0002000110', 'Surabaya',   '2011-11-05', 1, 18, 'B', 'Yanto',     2023],
        ['Laila Nur',        '200012', '0002000120', 'Malang',     '2011-12-20', 1, 18, 'B', 'Imam',      2023],
        ['Maulana Yusuf',    '200013', '0002000130', 'Semarang',   '2013-01-08', 2, 13, 'C', 'Samsudin',  2025],
        ['Nabila Putri',     '200014', '0002000140', 'Bogor',      '2013-02-14', 1, 14, 'C', 'Haris',     2025],
        ['Oka Mahendra',     '200015', '0002000150', 'Depok',      '2013-03-22', 1, 15, 'B', 'Wawan',     2025],
        ['Putra Mahardika',  '200016', '0002000160', 'Tangerang',  '2013-04-18', 1, 16, 'C', 'Rudianto',  2025],
        ['Qori Aulia',       '200017', '0002000170', 'Bekasi',     '2013-05-25', 1, 17, 'B', 'Suharto',   2025],
        ['Raka Wicaksana',   '200018', '0002000180', 'Bandung',    '2013-06-30', 1, 18, 'C', 'Taufik',    2025],
        ['Salsa Billa',      '200019', '0002000190', 'Jakarta',    '2013-07-12', 1, 13, 'D', 'Arif',      2025],
        ['Taufan Akbar',     '200020', '0002000200', 'Surabaya',   '2013-08-28', 1, 14, 'D', 'Budi',      2025],
    ],
    'sma' => [
        ['Akbar Rizaldi',       '300001', '0003000010', 'Jakarta',    '2009-01-05', 1, 19, 'A', 'Sutopo',   2024],
        ['Bunga Citra Lestari', '300002', '0003000020', 'Bandung',    '2009-02-11', 1, 19, 'A', 'Herman',   2024],
        ['Cahya Nugraha',       '300003', '0003000030', 'Surabaya',   '2009-03-17', 2, 20, 'B', 'Yudi',     2024],
        ['Dinda Ayu',           '300004', '0003000040', 'Yogyakarta', '2009-04-22', 1, 20, 'B', 'Wahyu',    2024],
        ['Edo Pratama',         '300005', '0003000050', 'Semarang',   '2009-05-28', 1, 21, 'A', 'Slamet',   2024],
        ['Fira Aulia',          '300006', '0003000060', 'Medan',      '2009-06-15', 1, 21, 'A', 'Joko',     2024],
        ['Gavin Alghifari',     '300007', '0003000070', 'Makassar',   '2009-07-20', 1, 22, 'B', 'Andi',     2024],
        ['Hana Sabrina',        '300008', '0003000080', 'Palembang',  '2009-08-25', 1, 22, 'B', 'Rudi',     2024],
        ['Iqbal Ramadhan',      '300009', '0003000090', 'Bandung',    '2008-09-30', 1, 23, 'A', 'Fajar',    2023],
        ['Jasmine Putri',       '300010', '0003000100', 'Jakarta',    '2008-10-14', 1, 23, 'A', 'Teguh',    2023],
        ['Kevin Sanjaya',       '300011', '0003000110', 'Surabaya',   '2008-11-19', 1, 24, 'B', 'Bambang',  2023],
        ['Laras Ayu Ningrum',   '300012', '0003000120', 'Malang',     '2008-12-24', 1, 24, 'B', 'Surya',    2023],
        ['Muhammad Rifky',      '300013', '0003000130', 'Semarang',   '2010-01-09', 1, 19, 'C', 'Dani',     2025],
        ['Nadia Salsabila',     '300014', '0003000140', 'Bogor',      '2010-02-13', 2, 20, 'C', 'Hadi',     2025],
        ['Oki Setiawan',        '300015', '0003000150', 'Depok',      '2010-03-18', 1, 21, 'B', 'Imam',     2025],
        ['Putri Ramadhani',     '300016', '0003000160', 'Tangerang',  '2010-04-23', 1, 22, 'C', 'Yanto',    2025],
        ['Rafi Ahmad',          '300017', '0003000170', 'Bekasi',     '2010-05-27', 1, 23, 'C', 'Agus',     2025],
        ['Siti Aisyah',         '300018', '0003000180', 'Bandung',    '2010-06-30', 1, 24, 'C', 'Samsul',   2025],
        ['Tio Pratama',         '300019', '0003000190', 'Jakarta',    '2010-07-15', 1, 19, 'D', 'Haris',    2025],
        ['Vina Melinda',        '300020', '0003000200', 'Surabaya',   '2010-08-20', 1, 20, 'D', 'Junaidi',  2025],
    ],
];

foreach ($siswaData as $j => $rows)
{
    foreach ($rows as [$nama, $nis, $nisn, $ttl, $tgl, $agama, $kelas, $ruang, $ayah, $tahunMasuk])
    {
        ins($link, "INSERT INTO siswa_$j
            (nama, nis, nisn, tempat_lahir, tgl_lahir, id_agama, id_kelas, ruang, ayah_nama, tahun_masuk)
            VALUES ("
            . e($link, $nama)  . "," . e($link, $nis)   . "," . e($link, $nisn) . ","
            . e($link, $ttl)   . ",'" . $tgl . "',"
            . "$agama, $kelas," . e($link, $ruang) . ","
            . e($link, $ayah)  . ", $tahunMasuk)");
    }
    echo "✔ siswa_$j (" . count($rows) . " baris)<br>";
}
