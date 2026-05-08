<?php

declare(strict_types=1);

function uuidv4(): string
{
    static $lastTimestamp = null;
    static $sequence = 0;

    $now = (int) (microtime(true) * 1000);

    if ($now === $lastTimestamp)
    {
        $sequence++;
        if ($sequence > 9999)
        {
            usleep(1); // tunggu 1 microsecond
            $now = (int) (microtime(true) * 1000);
            $sequence = 0;
        }
    }
    else
    {
        $sequence = 0;
        $lastTimestamp = $now;
    }

    // Format: timestamp (13 digit) + sequence (4 digit) + random (4 digit)
    return sprintf(
        '%d%04d%04d',
        $now,
        $sequence,
        mt_rand(0, 9999)
    );
}


function setAuthSession(array $user): void
{
    $_SESSION['id_user']  = $user['id'];
    $_SESSION['nama']     = $user['nama_lengkap'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id']  = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'];
    $_SESSION['level_id'] = $user['level_id'];
    $_SESSION['jenjang']  = $user['jenjang'] ?? null;
}

function getRedirectAfterLogin(array $user): string
{
    return "dashboard.php";
}

// Helper: warna badge status
function statusBadge(string $status): array
{
    return match ($status)
    {
        'aktif'       => ['bg-emerald-100 text-emerald-700', 'Aktif'],
        'lulus'       => ['bg-blue-100 text-blue-700',       'Lulus'],
        'pindah'      => ['bg-amber-100 text-amber-700',     'Pindah'],
        'keluar'      => ['bg-red-100 text-red-600',         'Keluar'],
        'dikeluarkan' => ['bg-red-200 text-red-800',         'Dikeluarkan'],
        default       => ['bg-gray-100 text-gray-600',       ucfirst($status)],
    };
}

function statusBadgeCard(string $status): array
{
    return match ($status)
    {
        'aktif'       => ['bg-emerald-400/30 text-emerald-100', 'Aktif'],
        'lulus'       => ['bg-blue-400/30 text-blue-100',       'Lulus'],
        'pindah'      => ['bg-amber-400/30 text-amber-100',     'Pindah'],
        'keluar'      => ['bg-red-400/30 text-red-100',         'Keluar'],
        'dikeluarkan' => ['bg-red-600/40 text-red-100',         'Dikeluarkan'],
        default       => ['bg-white/20 text-white',             ucfirst($status)],
    };
}

function btnClass(bool $allowed, string $activeColor = 'bg-blue-600 hover:bg-blue-700'): string
{
    return $allowed
        ? "$activeColor text-white cursor-pointer"
        : 'bg-gray-200 text-gray-400 cursor-not-allowed';
}

function btnDisabled(bool $allowed): string
{
    return $allowed ? '' : 'disabled';
}

function buildUrl($current_params = [])
{

    $params = array_merge($current_params);
    return http_build_query(array_filter($params));
}

function buildSiswaEditUrl(string $id_siswa, $filter = [], $jenjang = '', $page = 1)
{
    $params = array_merge($filter, [
        'id_siswa' => $id_siswa,
        'jenjang'  => $jenjang,
        'page'     => $page
    ]);
    return 'siswa-edit.php?' . http_build_query($params);
}

function buildGuruEditUrl(string $id_guru, $filter = [], $jenjang = '', $page = 1)
{
    $params = array_merge($filter, [
        'id_guru' => $id_guru,
        'jenjang'  => $jenjang,
        'page'     => $page
    ]);
    return 'guru-edit.php?' . http_build_query($params);
}

function buildUserEditUrl(string $id, $filter = [], $page = 1)
{
    $params = array_merge($filter, [
        'id' => $id,
        'page'     => $page
    ]);
    return 'admin-edit.php?' . http_build_query($params);
}

function validateExcelUpload(array $file, int $maxMb = 10): ?string
{
    // Cek error PHP upload
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK)
    {
        $errMap = [
            UPLOAD_ERR_INI_SIZE   => 'File melebihi batas upload_max_filesize di php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas MAX_FILE_SIZE di form.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diunggah.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION  => 'Ekstensi PHP menghentikan upload.',
        ];
        $code = $file['error'] ?? -1;
        return $errMap[$code] ?? 'Error upload tidak dikenali (kode: ' . $code . ').';
    }

    // Cek ekstensi
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if ($ext !== 'xlsx')
    {
        return 'Hanya file <strong>.xlsx</strong> yang diizinkan. File Anda: <strong>.' . htmlspecialchars($ext) . '</strong>';
    }

    // Cek file exists & tidak kosong
    $tmpPath = $file['tmp_name'] ?? '';
    if (!$tmpPath || !file_exists($tmpPath) || filesize($tmpPath) === 0)
    {
        return 'File upload tidak valid atau kosong.';
    }

    // Cek ukuran
    $maxBytes = $maxMb * 1024 * 1024;
    if ($file['size'] > $maxBytes)
    {
        return "File terlalu besar. Maksimal <strong>{$maxMb}MB</strong>.";
    }

    // Cek magic bytes ZIP (xlsx = zip)
    $handle = fopen($tmpPath, 'rb');
    if ($handle === false) return 'Tidak dapat membaca file.';
    $signature = bin2hex(fread($handle, 4));
    fclose($handle);

    $validSigs = ['504b0304', '504b0506', '504b0708'];
    if (!in_array($signature, $validSigs, true))
    {
        return 'File bukan format Excel yang valid. Pastikan file adalah <strong>.xlsx</strong> (Excel 2007+).';
    }

    return null;  // valid
}

function flashMsg(string $type, string $msg): string
{
    $config = match ($type)
    {
        'success' => ['bg-green-50 border-green-200 text-green-800', 'fa-circle-check'],
        'error'   => ['bg-red-50 border-red-200 text-red-700',       'fa-circle-exclamation'],
        'warning' => ['bg-yellow-50 border-yellow-200 text-yellow-800', 'fa-triangle-exclamation'],
        default   => ['bg-indigo-50 border-indigo-200 text-indigo-700', 'fa-circle-info'],
    };

    [$classes, $icon] = $config;

    return '<div class="mt-4 px-4 py-3 rounded-lg border ' . $classes . ' text-sm flex items-start gap-2">'
        . '<i class="fa-solid ' . $icon . ' mt-0.5 shrink-0"></i>'
        . '<div>' . $msg . '</div>'
        . '</div>';
}

function formatRupiah(mixed $val): string
{
    if ($val === null || $val === '') return '-';
    return 'Rp ' . number_format((float) $val, 0, ',', '.');
}

function formatTanggal(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '-';
    $ts = strtotime($date);
    return $ts !== false ? date($format, $ts) : '-';
}
