<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "dataku";
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$link)
{
    die("Koneksi gagal: " . mysqli_connect_errno() . " - " . mysqli_connect_error());
}

// Buat & pilih database
$query = "CREATE DATABASE IF NOT EXISTS `$dbname`";
$result = mysqli_query($link, $query);
mysqli_select_db($link, $dbname);

/* =====================
   HAPUS TABEL
   ===================== */
$drop_order = [
    'siswa_sd',
    'siswa_smp',
    'siswa_sma',
    'master_kelas',
    'guru_sd',
    'guru_smp',
    'guru_sma',
    'users',
    'master_level_kelas',
    'master_agama',
    'master_pekerjaan',
    'master_tahun_pelajaran',
    'master_semester',
    'role'
];
foreach ($drop_order as $t)
{
    mysqli_query($link, "DROP TABLE IF EXISTS `$t`");
}

/* =====================
   TABEL ROLE
   ===================== */
mysqli_query($link, "CREATE TABLE role (
    id_role INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE
)") or die("Error role: " . mysqli_error($link));

mysqli_query($link, "INSERT INTO role (nama_role) VALUES
    ('Super Admin'), ('Kepala Sekolah'), ('Guru')");

/* =====================
   TABEL MASTER LEVEL KELAS
   ===================== */
mysqli_query($link, "CREATE TABLE master_level_kelas (
    id_level INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jenjang ENUM('SD','SMP','SMA') NOT NULL UNIQUE,
    level_min TINYINT NOT NULL,
    level_max TINYINT NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
)");

mysqli_query($link, "INSERT INTO master_level_kelas (jenjang, level_min, level_max) VALUES
    ('SD', 1, 6), ('SMP', 7, 9), ('SMA', 10, 12)");


/* =====================
   TABEL USERS
   ===================== */
mysqli_query($link, "CREATE TABLE users (
    id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_role INT UNSIGNED NOT NULL,
    level_id INT UNSIGNED NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (id_role) REFERENCES role(id_role) ON DELETE RESTRICT,
    FOREIGN KEY (level_id) REFERENCES master_level_kelas(id_level) ON DELETE RESTRICT
)");

$pass = password_hash('123456', PASSWORD_DEFAULT);
mysqli_query($link, "INSERT INTO users (nama_lengkap, username, password, id_role, level_id) VALUES
    ('Super Admin', 'admin',       '$pass', 1, NULL),
    ('Kepsek SD',   'kepsek_sd',   '$pass', 2, (SELECT id_level FROM master_level_kelas WHERE jenjang='SD')),
    ('Kepsek SMP',  'kepsek_smp',  '$pass', 2, (SELECT id_level FROM master_level_kelas WHERE jenjang='SMP')),
    ('Kepsek SMA',  'kepsek_sma',  '$pass', 2, (SELECT id_level FROM master_level_kelas WHERE jenjang='SMA')),
    ('Guru SD',     'guru_sd',     '$pass', 3, (SELECT id_level FROM master_level_kelas WHERE jenjang='SD')),
    ('Guru SMP',     'guru_smp',   '$pass', 3, (SELECT id_level FROM master_level_kelas WHERE jenjang='SMP')),
    ('Guru SMA',     'guru_sma',   '$pass', 3, (SELECT id_level FROM master_level_kelas WHERE jenjang='SMA'))
");

/* =====================
   MASTER DATA
   ===================== */
mysqli_query($link, "CREATE TABLE master_agama (
    id_agama INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_agama VARCHAR(30) NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
)");
mysqli_query($link, "INSERT INTO master_agama (nama_agama) VALUES
    ('Islam'),('Kristen'),('Katolik'),('Hindu'),('Budha'),('Konghucu')");


mysqli_query($link, "CREATE TABLE master_pekerjaan (
    id_pekerjaan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pekerjaan VARCHAR(50) NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
)");
mysqli_query($link, "INSERT INTO master_pekerjaan (nama_pekerjaan) VALUES
    ('PNS'),('Swasta'),('Wirausaha'),('Petani'),('Nelayan'),('Lainnya')");


// MASTER KELAS
mysqli_query($link, "CREATE TABLE master_kelas (
    id_kelas INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level_id INT UNSIGNED NOT NULL,
    nama_kelas VARCHAR(10) NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uniq_kelas (level_id, nama_kelas),
    FOREIGN KEY (level_id) REFERENCES master_level_kelas(id_level)
)");
// Ambil level kelas dari tabel master_level_kelas
$result = mysqli_query($link, "SELECT id_level, level_min, level_max FROM master_level_kelas");
$levels = mysqli_fetch_all($result, MYSQLI_ASSOC);

$values = "";
foreach ($levels as $lv)
{
    // Loop dari min ke max
    for ($tingkat = $lv['level_min']; $tingkat <= $lv['level_max']; $tingkat++)
    {
        $values .= "({$lv['id_level']}, '{$tingkat} A'),";
        $values .= "({$lv['id_level']}, '{$tingkat} B'),";
    }
}

$values = rtrim($values, ',');
mysqli_query($link, "INSERT INTO master_kelas (level_id, nama_kelas) VALUES $values");


mysqli_query($link, "CREATE TABLE master_tahun_pelajaran (
    id_tahun INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tahun VARCHAR(9) NOT NULL,
    is_aktif TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL DEFAULT NULL
)");
mysqli_query($link, "INSERT INTO master_tahun_pelajaran (tahun, is_aktif) VALUES
    ('2024/2025',0),('2025/2026',1)");


mysqli_query($link, "CREATE TABLE master_semester (
    id_semester INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_semester VARCHAR(20) NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
)");
mysqli_query($link, "INSERT INTO master_semester (nama_semester) VALUES ('Ganjil'),('Genap')");


/* =====================
   TABEL SISWA
   ===================== */
$tmpl_siswa_fixed = "CREATE TABLE siswa_%%JENJANG%% (
    id_siswa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Data Siswa
    nama VARCHAR(100) NOT NULL,
    nis VARCHAR(20) UNIQUE,
    nisn VARCHAR(20) UNIQUE,
    tempat_lahir VARCHAR(50),
    tgl_lahir DATE,
    id_agama INT UNSIGNED,
    id_kelas INT UNSIGNED,
    ruang VARCHAR(20) NOT NULL,
    id_tahun_pelajaran INT UNSIGNED,
    id_semester INT UNSIGNED,

    ayah_nama VARCHAR(100),
    ayah_tahun_lahir YEAR(4),
    ayah_pendidikan VARCHAR(50),
    ayah_pekerjaan INT UNSIGNED,
    ayah_penghasilan VARCHAR(20),
    ayah_nik VARCHAR(16),

    -- Data Ibu (lengkap + NIK)
    ibu_nama VARCHAR(100),
    ibu_tahun_lahir YEAR(4),
    ibu_pendidikan VARCHAR(50),
    ibu_pekerjaan INT UNSIGNED,
    ibu_penghasilan VARCHAR(20),
    ibu_nik VARCHAR(16),

    -- Data Wali (lengkap + NIK)
    wali_nama VARCHAR(100),
    wali_tahun_lahir YEAR(4),
    wali_pendidikan VARCHAR(50),
    wali_pekerjaan INT UNSIGNED,
    wali_penghasilan VARCHAR(20),
    wali_nik VARCHAR(16),

    -- Data Lainnya
    nik VARCHAR(16),
    alamat TEXT,
    rt_rw VARCHAR(10),
    dusun VARCHAR(50),
    kelurahan VARCHAR(50),
    kecamatan VARCHAR(50),
    kode_pos VARCHAR(5),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (id_agama) REFERENCES master_agama(id_agama) ON DELETE SET NULL,
    FOREIGN KEY (id_kelas) REFERENCES master_kelas(id_kelas) ON DELETE SET NULL,
    FOREIGN KEY (id_semester) REFERENCES master_semester(id_semester) ON DELETE SET NULL,
    FOREIGN KEY (id_tahun_pelajaran) REFERENCES master_tahun_pelajaran(id_tahun) ON DELETE SET NULL,
    FOREIGN KEY (ayah_pekerjaan) REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL,
    FOREIGN KEY (ibu_pekerjaan) REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL,
    FOREIGN KEY (wali_pekerjaan) REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

foreach (['sd', 'smp', 'sma'] as $j)
{
    $query = str_replace('%%JENJANG%%', $j, $tmpl_siswa_fixed);
    mysqli_query($link, "DROP TABLE IF EXISTS siswa_$j");
    mysqli_query($link, $query) or die("Error membuat siswa_$j: " . mysqli_error($link));
}


/* =====================
   TABEL GURU
   ===================== */
$tmpl_guru = "CREATE TABLE guru_%%JENJANG%% (
    id_guru INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) UNIQUE,
    nuptk VARCHAR(20),
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L','P') NOT NULL,
    tempat_lahir VARCHAR(50),
    tgl_lahir DATE,
    nama_ibu VARCHAR(100),
    status_pegawai VARCHAR(50),
    jenis_gtk VARCHAR(50),
    jabatan VARCHAR(50),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

foreach (['sd', 'smp', 'sma'] as $j)
{
    $q = str_replace('%%JENJANG%%', $j, $tmpl_guru);
    mysqli_query($link, $q) or die("Error guru_$j: " . mysqli_error($link));
}

/* =====================
    DATA DUMMY SISWA SD
   ===================== */
mysqli_query($link, "INSERT INTO siswa_sd
    (nama, nis, nisn, tempat_lahir, tgl_lahir, id_agama, id_kelas, ruang, ayah_nama, id_semester, id_tahun_pelajaran) VALUES
    ('Ahmad Rizky Pratama',    '100001', '0001000010', 'Bandung',    '2015-01-15', 1,  1, 'A', 'Sutrisno', 2, 2),
    ('Siti Nurhaliza',         '100002', '0001000020', 'Jakarta',    '2015-02-20', 1,  2, 'B', 'Hadi Wijaya', 2, 2),
    ('Muhammad Farhan',        '100003', '0001000030', 'Surabaya',   '2015-03-10', 1,  3, 'A', 'Agus Salim', 2, 2),
    ('Aisyah Putri Ramadhani', '100004', '0001000040', 'Yogyakarta', '2015-04-05', 1,  4, 'B', 'Budi Santoso', 2, 2),
    ('Rizki Maulana',          '100005', '0001000050', 'Semarang',   '2015-05-18', 2,  5, 'B', 'Joko Widodo', 2, 2),
    ('Nadia Putri Lestari',    '100006', '0001000060', 'Medan',      '2015-06-22', 1,  6, 'A', 'Rahmat Hidayat', 2, 2),
    ('Fajar Siddiq',           '100007', '0001000070', 'Makassar',   '2014-07-30', 1,  7, 'B', 'Andi Malik', 2, 2),
    ('Putri Ayu Saraswati',    '100008', '0001000080', 'Palembang',  '2014-08-14', 1,  8, 'C', 'Slamet Riyadi', 2, 2),
    ('Dika Pratama',           '100009', '0001000090', 'Bandung',    '2014-09-25', 1,  9, 'B', 'Herman', 2, 2),
    ('Larasati Dewi',          '100010', '0001000100', 'Jakarta',    '2014-10-11', 1, 10, 'A', 'Wahyudi', 2, 2),
    ('Arief Rahman Hakim',     '100011', '0001000110', 'Surabaya',   '2013-11-03', 2, 11, 'B', 'Samsul', 2, 2),
    ('Citra Kirana',           '100012', '0001000120', 'Malang',     '2013-12-19', 1, 12, 'A', 'Edi Susanto', 2, 2),
    ('Bayu Aji Nugroho',       '100013', '0001000130', 'Semarang',   '2016-01-07', 1,  1, 'A', 'Sutopo', 2, 2),
    ('Intan Permata Sari',     '100014', '0001000140', 'Bogor',      '2016-02-28', 1,  2, 'B', 'Yusuf', 2, 2),
    ('Rangga Pratama',         '100015', '0001000150', 'Depok',      '2016-03-12', 1,  3, 'C', 'Darmawan', 2, 2),
    ('Zahra Aulia',            '100016', '0001000160', 'Tangerang',  '2016-04-21', 1,  4, 'D', 'Fajar', 2, 2),
    ('Dimas Senopati',         '100017', '0001000170', 'Bekasi',     '2016-05-09', 1,  5, 'B', 'Gunawan', 2, 2),
    ('Anisa Fitriani',         '100018', '0001000180', 'Bandung',    '2016-06-17', 1,  6, 'B', 'Suparman', 2, 2),
    ('Eko Wijaya',             '100019', '0001000190', 'Jakarta',    '2016-07-25', 2,  7, 'B', 'Sutikno', 2, 2),
    ('Kartika Sari',           '100020', '0001000200', 'Surabaya',   '2016-08-30', 1,  8, 'A', 'Hendra', 2, 2)");

/* =====================
    DATA DUMMY SISWA SMP
   ===================== */
mysqli_query($link, "INSERT INTO siswa_smp
    (nama, nis, nisn, tempat_lahir, tgl_lahir, id_agama, id_kelas, ruang, ayah_nama, id_semester, id_tahun_pelajaran) VALUES
    ('Aditya Pratama',    '200001', '0002000010', 'Jakarta',    '2012-01-10', 1, 13, 'A', 'Slamet', 2, 2),
    ('Bella Safira',      '200002', '0002000020', 'Bandung',    '2012-02-15', 1, 13, 'A', 'Bambang', 2, 2),
    ('Candra Wijaya',     '200003', '0002000030', 'Surabaya',   '2012-03-20', 1, 14, 'B', 'Teguh', 2, 2),
    ('Dina Lestari',      '200004', '0002000040', 'Yogyakarta', '2012-04-25', 1, 14, 'B', 'Agung', 2, 2),
    ('Eka Putra',         '200005', '0002000050', 'Semarang',   '2012-05-30', 2, 15, 'A', 'Rudi', 2, 2),
    ('Fani Andriani',     '200006', '0002000060', 'Medan',      '2012-06-14', 1, 15, 'A', 'Hadi', 2, 2),
    ('Galih Pratama',     '200007', '0002000070', 'Makassar',   '2012-07-19', 1, 16, 'B', 'Junaidi', 2, 2),
    ('Hana Maulida',      '200008', '0002000080', 'Palembang',  '2012-08-23', 1, 16, 'B', 'Surya', 2, 2),
    ('Ilham Ramadhan',    '200009', '0002000090', 'Bandung',    '2011-09-27', 1, 17, 'A', 'Dani', 2, 2),
    ('Jihan Aisyah',      '200010', '0002000100', 'Jakarta',    '2011-10-11', 1, 17, 'A', 'Faisal', 2, 2),
    ('Kemal Pasha',       '200011', '0002000110', 'Surabaya',   '2011-11-05', 1, 18, 'B', 'Yanto', 2, 2),
    ('Laila Nur',         '200012', '0002000120', 'Malang',     '2011-12-20', 1, 18, 'B', 'Imam', 2, 2),
    ('Maulana Yusuf',     '200013', '0002000130', 'Semarang',   '2013-01-08', 2, 13, 'C', 'Samsudin', 2, 2),
    ('Nabila Putri',      '200014', '0002000140', 'Bogor',      '2013-02-14', 1, 14, 'C', 'Haris', 2, 2),
    ('Oka Mahendra',      '200015', '0002000150', 'Depok',      '2013-03-22', 1, 15, 'B', 'Wawan', 2, 2),
    ('Putra Mahardika',   '200016', '0002000160', 'Tangerang',  '2013-04-18', 1, 16, 'C', 'Rudianto', 2, 2),
    ('Qori Aulia',        '200017', '0002000170', 'Bekasi',     '2013-05-25', 1, 17, 'B', 'Suharto', 2, 2),
    ('Raka Wicaksana',    '200018', '0002000180', 'Bandung',    '2013-06-30', 1, 18, 'C', 'Taufik', 2, 2),
    ('Salsa Billa',       '200019', '0002000190', 'Jakarta',    '2013-07-12', 1, 13, 'D', 'Arif', 2, 2),
    ('Taufan Akbar',      '200020', '0002000200', 'Surabaya',   '2013-08-28', 1, 14, 'D', 'Budi', 2, 2)");

/* =====================
    DATA DUMMY SISWA SMA
   ===================== */
mysqli_query($link, "INSERT INTO siswa_sma
    (nama, nis, nisn, tempat_lahir, tgl_lahir, id_agama, id_kelas, ruang, ayah_nama, id_semester, id_tahun_pelajaran) VALUES
    ('Akbar Rizaldi',          '300001', '0003000010', 'Jakarta',    '2009-01-05', 1, 19, 'A', 'Sutopo', 2, 2),
    ('Bunga Citra Lestari',    '300002', '0003000020', 'Bandung',    '2009-02-11', 1, 19, 'A', 'Herman', 2, 2),
    ('Cahya Nugraha',          '300003', '0003000030', 'Surabaya',   '2009-03-17', 2, 20, 'B', 'Yudi', 2, 2),
    ('Dinda Ayu',              '300004', '0003000040', 'Yogyakarta', '2009-04-22', 1, 20, 'B', 'Wahyu', 2, 2),
    ('Edo Pratama',            '300005', '0003000050', 'Semarang',   '2009-05-28', 1, 21, 'A', 'Slamet', 2, 2),
    ('Fira Aulia',             '300006', '0003000060', 'Medan',      '2009-06-15', 1, 21, 'A', 'Joko', 2, 2),
    ('Gavin Alghifari',        '300007', '0003000070', 'Makassar',   '2009-07-20', 1, 22, 'B', 'Andi', 2, 2),
    ('Hana Sabrina',           '300008', '0003000080', 'Palembang',  '2009-08-25', 1, 22, 'B', 'Rudi', 2, 2),
    ('Iqbal Ramadhan',         '300009', '0003000090', 'Bandung',    '2008-09-30', 1, 23, 'A', 'Fajar', 2, 2),
    ('Jasmine Putri',          '300010', '0003000100', 'Jakarta',    '2008-10-14', 1, 23, 'A', 'Teguh', 2, 2),
    ('Kevin Sanjaya',          '300011', '0003000110', 'Surabaya',   '2008-11-19', 1, 24, 'B', 'Bambang', 2, 2),
    ('Laras Ayu Ningrum',      '300012', '0003000120', 'Malang',     '2008-12-24', 1, 24, 'B', 'Surya', 2, 2),
    ('Muhammad Rifky',         '300013', '0003000130', 'Semarang',   '2010-01-09', 1, 19, 'C', 'Dani', 2, 2),
    ('Nadia Salsabila',        '300014', '0003000140', 'Bogor',      '2010-02-13', 2, 20, 'C', 'Hadi', 2, 2),
    ('Oki Setiawan',           '300015', '0003000150', 'Depok',      '2010-03-18', 1, 21, 'B', 'Imam', 2, 2),
    ('Putri Ramadhani',        '300016', '0003000160', 'Tangerang',  '2010-04-23', 1, 22, 'C', 'Yanto', 2, 2),
    ('Rafi Ahmad',             '300017', '0003000170', 'Bekasi',     '2010-05-27', 1, 23, 'C', 'Agus', 2, 2),
    ('Siti Aisyah',            '300018', '0003000180', 'Bandung',    '2010-06-30', 1, 24, 'C', 'Samsul', 2, 2),
    ('Tio Pratama',            '300019', '0003000190', 'Jakarta',    '2010-07-15', 1, 19, 'D', 'Haris', 2, 2),
    ('Vina Melinda',           '300020', '0003000200', 'Surabaya',   '2010-08-20', 1, 20, 'D', 'Junaidi', 2, 2)");


/* =====================
   DATA DUMMY GURU SD
   ===================== */
mysqli_query($link, "INSERT INTO guru_sd
    (nik, nuptk, nama, jenis_kelamin, tempat_lahir, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan) VALUES
    ('3201011508950001', '1234567890123456', 'Dra. Siti Aminah, M.Pd',        'P', 'Bandung',     '1985-08-15', 'Siti Rohayah',      'PNS',      'Guru Kelas',           'Wali Kelas 6A'),
    ('3201012307920002', '2345678901234567', 'Ahmad Sobari, S.Pd',            'L', 'Jakarta',     '1992-07-23', 'Siti Fatimah',      'PNS',      'Guru Kelas',           'Wali Kelas 5A'),
    ('3201015503880003', '3456789012345678', 'Hj. Nurhayati, S.Pd.I',         'P', 'Cirebon',     '1988-03-15', 'Siti Maryam',       'PNS',      'Guru Agama Islam',     'Guru Agama SD'),
    ('3201010811850004', '4567890123456789', 'Budi Santoso, S.Pd',            'L', 'Semarang',    '1985-11-08', 'Siti Aisyah',       'PNS',      'Guru Kelas',           'Wali Kelas 4A'),
    ('3201014406810005', '5678901234567890', 'Rina Wulandari, S.Pd',          'P', 'Yogyakarta',  '1981-06-04', 'Siti Zahro',        'PNS',      'Guru Kelas',           'Wali Kelas 3B'),
    ('3201012005900006', '6789012345678901', 'Drs. Slamet Riyadi',            'L', 'Surabaya',    '1990-05-20', 'Siti Khodijah',     'PNS',      'Pendidikan Jasmani',   'Guru PJOK'),
    ('3201016312930007', '7890123456789012', 'Lestari Dewi, S.Pd',            'P', 'Malang',      '1993-12-16', 'Siti Nurjanah',     'Honorer',  'Guru Kelas',           'Wali Kelas 2A'),
    ('3201011708870008', '8901234567890123', 'Agus Supriyanto, S.Pd',         'L', 'Bandung',     '1987-08-17', 'Siti Aminah',       'PNS',      'Guru Kelas',           'Wali Kelas 1A'),
    ('3201014809850009', '9012345678901234', 'Fitri Handayani, S.Pd',         'P', 'Bogor',       '1985-09-28', 'Siti Halimah',      'PNS',      'Guru Bahasa Inggris',  'Guru Inggris SD'),
    ('3201010511800010', '1122334455667788', 'Joko Widodo, S.Pd',             'L', 'Solo',        '1980-01-05', 'Siti Maimunah',     'PNS',      'Kepala Sekolah',       'Kepala Sekolah SD'),
    ('3201012906940011', '2233445566778899', 'Wulan Sari, S.Pd',              'P', 'Medan',       '1994-06-29', 'Siti Rahayu',       'Honorer',  'Guru Kelas',           'Wali Kelas 4B'),
    ('3201011303890012', '3344556677889900', 'Hadi Susanto, S.Pd',            'L', 'Makassar',    '1989-03-13', 'Siti Jamilah',      'GTT',      'Guru TIK',             'Guru Informatika'),
    ('3201015701920013', '4455667788990011', 'Dewi Kurniawati, S.Pd',         'P', 'Palembang',   '1992-01-27', 'Siti Fatimah',      'Honorer',  'Guru Seni Budaya',     'Guru Seni'),
    ('3201012507850014', '5566778899001122', 'Rudi Hartono, S.Pd',            'L', 'Surabaya',    '1985-07-25', 'Siti Nurhayati',    'PNS',      'Guru Kelas',           'Wali Kelas 5B'),
    ('3201014110870015', '6677889900112233', 'Sari Rahayu, S.Pd',             'P', 'Jakarta',     '1987-10-14', 'Siti Khadijah',     'PNS',      'Guru Kelas',           'Wali Kelas 6B')
") or die("Error insert guru_sd: " . mysqli_error($link));

/* =====================
   DATA DUMMY GURU SMP
   ===================== */
mysqli_query($link, "INSERT INTO guru_smp
    (nik, nuptk, nama, jenis_kelamin, tempat_lahir, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan) VALUES
    ('3201021408820001', '1122334455667781', 'Dr. H. Mulyono, M.Pd',              'L', 'Semarang',  '1982-08-14', 'Siti Aminah',       'PNS', 'Guru Matematika',   'Waka Kurikulum'),
    ('3201025507850002', '2233445566778891', 'Hj. Siti Nurjanah, M.Pd',           'P', 'Bandung',   '1985-07-05', 'Fatimah Zahro',     'PNS', 'Guru B. Indonesia', 'Guru B. Indonesia'),
    ('3201022006800003', '3344556677889901', 'Bambang Suryo, S.Pd',               'L', 'Surabaya',  '1980-06-20', 'Siti Maryam',       'PNS', 'Guru IPA',          'Guru IPA Terpadu'),
    ('3201024311830004', '4455667788990012', 'Rina Puspita, M.Pd',                'P', 'Yogyakarta','1983-11-04', 'Siti Halimah',      'PNS', 'Guru IPS',           'Guru IPS'),
    ('3201020810780005', '5566778899001123', 'Drs. Ahmad Yani',                   'L', 'Jakarta',   '1978-10-08', 'Siti Khodijah',     'PNS', 'Guru PPKn',           'Guru PPKn'),
    ('3201026009870006', '6677889900112234', 'Lina Marlina, S.Pd',                'P', 'Malang',    '1987-09-20', 'Siti Aisyah',       'PNS', 'G Bahasa Inggris',  'Guru B. Inggris'),
    ('3201021505850007', '7788990011223345', 'Hadi Pramono, S.Pd',                'L', 'Medan',     '1985-05-15', 'Siti Rohaya',       'PNS', 'Guru Matematika',   'Guru Matematika'),
    ('3201024709890008', '8899001122334456', 'Dewi Sartika, S.Pd',                'P', 'Bogor',     '1989-09-07', 'Siti Jamilah',      'Honorer', 'Guru Seni Budaya','Guru Seni Musik'),
    ('3201023003820009', '9900112233445567', 'Slamet Haryadi, S.Pd',              'L', 'Solo',      '1982-03-30', 'Siti Maimunah',     'PNS', 'Pendidikan Jasmani', 'Guru PJOK'),
    ('3201026111840010', '1011121314151617', 'Anisa Fitri, M.Pd',                 'P', 'Depok',     '1984-11-21', 'Siti Nurjanah',     'PNS', 'Guru B. Indonesia', 'Wali Kelas 8A'),
    ('3201021907860011', '1213141516171819', 'Fajar Nugroho, S.Pd',               'L', 'Makassar',  '1986-07-19', 'Siti Fatimah',      'GTT', 'Guru TIK',          'Guru Informatika'),
    ('3201025308810012', '1314151617181920', 'Ratih Pratiwi, S.Pd',               'P', 'Palembang', '1981-08-03', 'Siti Zahro',        'PNS', 'Guru Prakarya',     'Guru Prakarya'),
    ('3201020405790013', '1415161718192021', 'Drs. Suparman',                     'L', 'Surabaya',  '1979-05-04', 'Siti Aminah',       'PNS', 'Kepala Sekolah',    'Kepala Sekolah SMP'),
    ('3201026406880014', '1516171819202122', 'Sinta Wulandari, S.Pd',             'P', 'Tangerang', '1988-06-24', 'Siti Khadijah',     'Honorer', 'Guru B. Inggris', 'Guru B. Inggris'),
    ('3201021703830015', '1617181920212223', 'Eko Prasetyo, S.Pd',                'L', 'Bandung',   '1983-03-17', 'Siti Halimah',      'PNS', 'Guru IPA',          'Wali Kelas 9B')
");

/* =====================
   DATA DUMMY GURU SMA
   ===================== */
mysqli_query($link, "INSERT INTO guru_sma
    (nik, nuptk, nama, jenis_kelamin, tempat_lahir, tgl_lahir, nama_ibu, status_pegawai, jenis_gtk, jabatan) VALUES
    ('3201031208760001', '1122334455660011', 'Prof. Dr. H. Abdul Rahman, M.Sc',      'L', 'Jakarta',    '1976-08-12', 'Siti Fatimah',      'PNS', 'Guru Fisika',        'Guru Fisika Kelas XII'),
    ('3201035108790002', '2233445566770022', 'Dr. Hj. Siti Nurhaliza, M.Pd',         'P', 'Bandung',    '1979-07-01', 'Fatimah Zahro',     'PNS', 'Guru Kimia',         'Guru Kimia'),
    ('3201032007740003', '3344556677880033', 'Ir. Budi Santoso, M.T',               'L', 'Surabaya',   '1974-07-20', 'Siti Maryam',       'PNS', 'Guru Matematika',    'Waka Kurikulum'),
    ('3201034510780004', '4455667788990044', 'Rina Susanti, M.Pd',                  'P', 'Yogyakarta', '1978-10-04', 'Siti Halimah',      'PNS', 'Guru Biologi',       'Guru Biologi'),
    ('3201030806730005', '5566778899000055', 'Drs. H. Ahmad Subarjo, M.M',          'L', 'Semarang',   '1973-06-08', 'Siti Khodijah',     'PNS', 'Guru Ekonomi',       'Guru Ekonomi'),
    ('3201035908820006', '6677889900110066', 'Larasati, S.Pd., Gr',                 'P', 'Malang',     '1982-08-29', 'Siti Aisyah',       'PNS', 'Guru B. Inggris',    'Guru B. Inggris'),
    ('3201031405770007', '7788990011220077', 'Hadi Wijaya, M.Pd',                   'L', 'Medan',      '1977-05-14', 'Siti Rohaya',       'PNS', 'Guru Sejarah',       'Guru Sejarah'),
    ('3201034809810008', '8899001122330088', 'Citra Dewi, M.Pd',                    'P', 'Bogor',      '1981-09-08', 'Siti Jamilah',      'PNS', 'Guru Sosiologi',     'Guru Sosiologi'),
    ('3201032506750009', '9900112233440099', 'Agus Salim, S.Pd',                    'L', 'Makassar',   '1975-06-25', 'Siti Maimunah',     'PNS', 'Guru Geografi',      'Guru Geografi'),
    ('3201036210800010', '1011121314150010', 'Nadia Putri, M.Pd',                   'P', 'Palembang',  '1980-10-12', 'Siti Nurjanah',     'PNS', 'Guru B. Indonesia',  'Wali Kelas XI IPA 1'),
    ('3201031807740011', '1112131415160011', 'Fajar Siddiq, M.Kom',                 'L', 'Depok',      '1974-07-18', 'Siti Aminah',       'PNS', 'Guru TIK',           'Guru Informatika'),
    ('3201035508780012', '1213141516170012', 'Dr. Hj. Wulan Permata, M.Pd',         'P', 'Tangerang',  '1978-08-05', 'Siti Khadijah',     'PNS', 'Guru Matematika',    'Guru Matematika'),
    ('3201030306710013', '1314151617180013', 'Drs. Ec. Sukardi, M.Si',              'L', 'Solo',       '1971-01-03', 'Siti Halimah',      'PNS', 'Kepala Sekolah',     'Kepala Sekolah SMA'),
    ('3201036708830014', '1415161718190014', 'Salsa Aulia, S.Pd',                   'P', 'Bekasi',     '1983-08-27', 'Siti Zahro',        'Honorer', 'Guru Seni Budaya', 'Guru Seni Teater'),
    ('3201032106760015', '1516171819200015', 'Rizky Pratama, M.Pd',                 'L', 'Surabaya',   '1976-06-21', 'Siti Nurhayati',    'PNS', 'Pendidikan Jasmani', 'Guru PJOK SMA')
");

mysqli_close($link);

echo "<h3 style='color:green'>SELESAI! SEMUA TABEL & DATA MASUK.</h3>";
echo "<p>Total kelas: <b>36</b> (SD:1A-6B, SMP:7A-9B, SMA:10A-12B)</p>";
