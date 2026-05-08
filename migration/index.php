<?php

// ROLES
mysqli_query($link, "CREATE TABLE roles (
    id         CHAR(10)     NOT NULL,
    name       VARCHAR(100) NOT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP    NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error roles: " . mysqli_error($link));
echo "✔ roles<br>";


// PERMISSIONS
mysqli_query($link, "CREATE TABLE permissions (
    id          INT UNSIGNED AUTO_INCREMENT NOT NULL,
    code        VARCHAR(50)  NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_code (code),
    UNIQUE KEY uq_permissions_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error permissions: " . mysqli_error($link));
echo "✔ permissions<br>";


// MENUS
mysqli_query($link, "CREATE TABLE menus (
    id         INT UNSIGNED AUTO_INCREMENT NOT NULL,
    slug       VARCHAR(100) NOT NULL,
    name       VARCHAR(100) NOT NULL,
    route      VARCHAR(255) NULL,
    icon       VARCHAR(100) NULL,
    parent_id  INT UNSIGNED NULL,
    sort_order INT          DEFAULT 0,
    is_visible TINYINT(1)   DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP    NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_menus_slug (slug),
    INDEX idx_menus_parent (parent_id),
    CONSTRAINT fk_menus_parent FOREIGN KEY (parent_id)
        REFERENCES menus(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error menus: " . mysqli_error($link));
echo "✔ menus<br>";


// MASTER LEVEL KELAS
mysqli_query($link, "CREATE TABLE master_level_kelas (
    id_level   INT UNSIGNED AUTO_INCREMENT NOT NULL,
    jenjang    VARCHAR(20) NOT NULL,
    level_min  TINYINT UNSIGNED NOT NULL,
    level_max  TINYINT UNSIGNED NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error master_level_kelas: " . mysqli_error($link));
echo "✔ master_level_kelas<br>";


// USERS
// level_id NULL  = Super Admin (akses semua jenjang)
// level_id !NULL = dibatasi ke jenjang tertentu
// guru_id = link opsional ke guru_sd/smp/sma (jenjang didapat dari level_id, bukan disimpan ulang)
// guru_jenjang DIHAPUS → redundant dengan level_id
mysqli_query($link, "CREATE TABLE users (
    id             CHAR(36)     NOT NULL,
    nama_lengkap   VARCHAR(100) NOT NULL,
    username       VARCHAR(100) NOT NULL,
    password       VARCHAR(255) NOT NULL,
    role_id        CHAR(10)     NULL,
    level_id       INT UNSIGNED NULL DEFAULT NULL,
    guru_id        INT UNSIGNED NULL DEFAULT NULL,
    remember_token VARCHAR(100) NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at     TIMESTAMP    NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    INDEX idx_users_role  (role_id),
    INDEX idx_users_level (level_id),

    CONSTRAINT fk_users_role  FOREIGN KEY (role_id)
        REFERENCES roles(id) ON DELETE SET NULL,
    CONSTRAINT fk_users_level FOREIGN KEY (level_id)
        REFERENCES master_level_kelas(id_level) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error users: " . mysqli_error($link));
echo "✔ users<br>";


// ROLE MENU PERMISSION
mysqli_query($link, "CREATE TABLE role_menu_permission (
    role_id       CHAR(10)     NOT NULL,
    menu_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, menu_id, permission_id),

    CONSTRAINT fk_rmp_role FOREIGN KEY (role_id)
        REFERENCES roles(id)       ON DELETE CASCADE,
    CONSTRAINT fk_rmp_menu FOREIGN KEY (menu_id)
        REFERENCES menus(id)       ON DELETE CASCADE,
    CONSTRAINT fk_rmp_perm FOREIGN KEY (permission_id)
        REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error role_menu_permission: " . mysqli_error($link));
echo "✔ role_menu_permission<br>";


// MASTER AGAMA
mysqli_query($link, "CREATE TABLE master_agama (
    id_agama   INT UNSIGNED AUTO_INCREMENT NOT NULL,
    nama_agama VARCHAR(30)  NOT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL,

    PRIMARY KEY (id_agama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error master_agama: " . mysqli_error($link));
echo "✔ master_agama<br>";


// MASTER PEKERJAAN
mysqli_query($link, "CREATE TABLE master_pekerjaan (
    id_pekerjaan   INT UNSIGNED AUTO_INCREMENT NOT NULL,
    nama_pekerjaan VARCHAR(50)  NOT NULL,
    deleted_at     TIMESTAMP    NULL DEFAULT NULL,

    PRIMARY KEY (id_pekerjaan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error master_pekerjaan: " . mysqli_error($link));
echo "✔ master_pekerjaan<br>";


// MASTER KELAS
mysqli_query($link, "CREATE TABLE master_kelas (
    id_kelas   INT UNSIGNED AUTO_INCREMENT NOT NULL,
    level_id   INT UNSIGNED NOT NULL,
    nama_kelas VARCHAR(10)  NOT NULL,
    deleted_at TIMESTAMP    NULL DEFAULT NULL,

    PRIMARY KEY (id_kelas),
    UNIQUE KEY uq_kelas (level_id, nama_kelas),
    CONSTRAINT fk_kelas_level FOREIGN KEY (level_id)
        REFERENCES master_level_kelas(id_level) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4") or die("Error master_kelas: " . mysqli_error($link));
echo "✔ master_kelas<br>";

// ENTITAS: GURU (terpisah per jenjang)
// status       : kondisi aktif guru — menggantikan fungsi delete
//   aktif       = masih mengajar
//   pensiun     = sudah pensiun
//   pindah      = pindah ke sekolah lain
//   keluar      = mengundurkan diri
//   meninggal   = meninggal dunia
// tahun_masuk  : tahun pertama mengajar di sekolah ini
// tahun_keluar : diisi otomatis saat status berubah dari 'aktif', NULL jika reaktif

$tmpl_guru = "CREATE TABLE guru_%%J%% (
    id_guru            INT UNSIGNED  AUTO_INCREMENT NOT NULL,
    user_id            CHAR(36)      NULL,

    -- Status keaktifan (menggantikan fungsi delete)
    status             ENUM('aktif','pensiun','pindah','keluar','meninggal')
                       NOT NULL DEFAULT 'aktif',
    status_keterangan  TEXT          NULL,
    status_updated_at  TIMESTAMP     NULL,

    -- Tahun masuk & keluar (untuk filter angkatan)
    tahun_masuk        YEAR          NULL,
    tahun_keluar       YEAR          NULL,

    -- Identitas
    nik                VARCHAR(16)   NULL,
    nuptk              VARCHAR(20)   NULL,
    nama               VARCHAR(100)  NOT NULL,
    jenis_kelamin      ENUM('L','P') NOT NULL,
    tempat_lahir       VARCHAR(50)   NULL,
    tgl_lahir          DATE          NULL,
    nama_ibu           VARCHAR(100)  NULL,
    status_pegawai     VARCHAR(50)   NULL,
    jenis_gtk          VARCHAR(50)   NULL,
    jabatan            VARCHAR(50)   NULL,
    alamat             TEXT          NULL,

    created_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         TIMESTAMP     NULL,

    PRIMARY KEY (id_guru),
    UNIQUE KEY uq_guru_%%J%%_nik     (nik),
    UNIQUE KEY uq_guru_%%J%%_user_id (user_id),
    INDEX idx_guru_%%J%%_status      (status),
    INDEX idx_guru_%%J%%_tahun_masuk (tahun_masuk),

    CONSTRAINT fk_guru_%%J%%_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

foreach (['sd', 'smp', 'sma'] as $j)
{
    $sql = str_replace('%%J%%', $j, $tmpl_guru);
    mysqli_query($link, $sql) or die("Error guru_$j: " . mysqli_error($link));
    echo "✔ guru_$j<br>";
}


// ENTITAS: SISWA (terpisah per jenjang)
// status       : kondisi aktif siswa — menggantikan fungsi delete
//   aktif       = masih bersekolah
//   lulus       = lulus / tamat
//   pindah      = pindah ke sekolah lain
//   keluar      = keluar atas permintaan sendiri / orang tua
//   dikeluarkan = dikeluarkan oleh sekolah
// tahun_masuk  : tahun pertama masuk/daftar ke sekolah ini
// tahun_keluar : diisi otomatis saat status berubah dari 'aktif', NULL jika reaktif
// id_semester & id_tahun_pelajaran DIHAPUS (tidak berguna, semua siswa aktif sama)

$tmpl_siswa = "CREATE TABLE siswa_%%J%% (
    id_siswa           INT UNSIGNED  AUTO_INCREMENT NOT NULL,

    -- Status keaktifan (menggantikan fungsi delete)
    status             ENUM('aktif','lulus','pindah','keluar','dikeluarkan')
                       NOT NULL DEFAULT 'aktif',
    status_keterangan  TEXT          NULL,
    status_updated_at  TIMESTAMP     NULL,

    -- Tahun masuk & keluar (untuk filter angkatan)
    tahun_masuk        YEAR          NULL,
    tahun_keluar       YEAR          NULL,

    -- Identitas
    nama               VARCHAR(100)  NOT NULL,
    nis                VARCHAR(20)   NULL,
    nisn               VARCHAR(20)   NULL,
    nik                VARCHAR(16)   NULL,
    tempat_lahir       VARCHAR(50)   NULL,
    tgl_lahir          DATE          NULL,
    id_agama           INT UNSIGNED  NULL,
    id_kelas           INT UNSIGNED  NULL,
    ruang              VARCHAR(20)   NOT NULL,

    -- Data Ayah
    ayah_nama          VARCHAR(100)  NULL,
    ayah_tahun_lahir   YEAR          NULL,
    ayah_pendidikan    VARCHAR(50)   NULL,
    ayah_pekerjaan     INT UNSIGNED  NULL,
    ayah_penghasilan   DECIMAL(15,0) NULL,
    ayah_nik           VARCHAR(16)   NULL,

    -- Data Ibu
    ibu_nama           VARCHAR(100)  NULL,
    ibu_tahun_lahir    YEAR          NULL,
    ibu_pendidikan     VARCHAR(50)   NULL,
    ibu_pekerjaan      INT UNSIGNED  NULL,
    ibu_penghasilan    DECIMAL(15,0) NULL,
    ibu_nik            VARCHAR(16)   NULL,

    -- Data Wali
    wali_nama          VARCHAR(100)  NULL,
    wali_tahun_lahir   YEAR          NULL,
    wali_pendidikan    VARCHAR(50)   NULL,
    wali_pekerjaan     INT UNSIGNED  NULL,
    wali_penghasilan   DECIMAL(15,0) NULL,
    wali_nik           VARCHAR(16)   NULL,

    -- Alamat
    alamat             TEXT          NULL,
    rt_rw              VARCHAR(10)   NULL,
    dusun              VARCHAR(50)   NULL,
    kelurahan          VARCHAR(50)   NULL,
    kecamatan          VARCHAR(50)   NULL,
    kode_pos           VARCHAR(5)    NULL,

    created_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at         TIMESTAMP     NULL,

    PRIMARY KEY (id_siswa),
    UNIQUE KEY uq_siswa_%%J%%_nis    (nis),
    UNIQUE KEY uq_siswa_%%J%%_nisn   (nisn),
    INDEX idx_siswa_%%J%%_status     (status),
    INDEX idx_siswa_%%J%%_tahun_masuk (tahun_masuk),

    CONSTRAINT fk_siswa_%%J%%_agama     FOREIGN KEY (id_agama)       REFERENCES master_agama(id_agama)         ON DELETE SET NULL,
    CONSTRAINT fk_siswa_%%J%%_kelas     FOREIGN KEY (id_kelas)       REFERENCES master_kelas(id_kelas)         ON DELETE SET NULL,
    CONSTRAINT fk_siswa_%%J%%_ayah_pkrj FOREIGN KEY (ayah_pekerjaan) REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL,
    CONSTRAINT fk_siswa_%%J%%_ibu_pkrj  FOREIGN KEY (ibu_pekerjaan)  REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL,
    CONSTRAINT fk_siswa_%%J%%_wali_pkrj FOREIGN KEY (wali_pekerjaan) REFERENCES master_pekerjaan(id_pekerjaan) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

foreach (['sd', 'smp', 'sma'] as $j)
{
    $sql = str_replace('%%J%%', $j, $tmpl_siswa);
    mysqli_query($link, $sql) or die("Error siswa_$j: " . mysqli_error($link));
    echo "✔ siswa_$j<br>";
}
