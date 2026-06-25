<?php
// File: ../config/auth_config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Daftar Mapping Jabatan (Master Data)
$jabatanMap = [
    'setum' => 'Setum / Admin',
    'admin' => 'Setum / Admin',
    'superadmin' => 'Super Admin',
    'kasi_tuud' => 'Kasi Tuud',
    'was' => 'Seksi Was',
    'dukkes' => 'Seksi Dukkes',
    'kesprev' => 'Seksi Kesprev',
    'renproggar' => 'Seksi Renproggar',
    'minlogkes' => 'Seksi Minlogkes',
    'matkes' => 'Seksi Matkes',
    'yankes' => 'Seksi Yankes',
    'gudkesrah' => 'Gudang Kesrah',
    'smk' => 'SMK Kesdam Jaya',
    'denkeslap' => 'Dandenkeslap',
    'primkop' => 'Ka Primkop',
    'paku_kesdam' => 'Paku Kesdam',
    'infokes' => 'Kaur Infokes',
    'log' => 'Kaur Log',
    'juyar' => 'Juyar',
    'persit' => 'Persit',
    'pam' => 'Kaurpam',
    'urdal' => 'Kaurdal',
    'korpri' => 'Korpri',
    'pers' => 'Kaur Pers',
    'pers_tuud' => 'Pers Tuud',
    'kakesdam_jaya' => 'Kakesdam Jaya',
    'wakakesdam_jaya' => 'Wakakesdam Jaya',
    'spri_pimpinan' => 'Spri Pimpinan',
    'ruangan' => 'Ruangan'
];

// 2. Logika Penentuan Role
$role = strtolower($_SESSION['role'] ?? $_SESSION['tipe_akses'] ?? 'ruangan');
$email_user = strtolower($_SESSION['email'] ?? '');

// Daftar email akses penuh
$allowed_emails_check = [
    'superadmin@gmail.com', 'setum@gmail.com', 'admin@gmail.com', 
    'kasituud2026@gmail.com', 'kakesdamjaya2026@gmail.com', 
    'wakakesdamjaya2026@gmail.com', 'spripimpinan2026@gmail.com'
];

if ($role === 'ruangan' && in_array($email_user, $allowed_emails_check)) {
    // Sesuaikan mapping email ke role
    $mapEmailToRole = [
        'setum@gmail.com' => 'setum', 'admin@gmail.com' => 'setum',
        'kasituud2026@gmail.com' => 'kasi_tuud',
        'wakakesdamjaya2026@gmail.com' => 'wakakesdam_jaya',
        'kakesdamjaya2026@gmail.com' => 'kakesdam_jaya',
        'spripimpinan2026@gmail.com' => 'spri_pimpinan',
        'superadmin@gmail.com' => 'superadmin'
    ];
    if (isset($mapEmailToRole[$email_user])) $role = $mapEmailToRole[$email_user];
}

// 3. Menentukan variabel $dari dan $is_admin_setum secara global
$raw_role = $_SESSION['nama_role'] ?? $_SESSION['nama_jabatan'] ?? $role;
$dari = $jabatanMap[strtolower($raw_role)] ?? ucwords(str_replace('_', ' ', $raw_role));
$is_admin_setum = in_array(strtolower($role), ['setum', 'admin', 'superadmin']);

/**
 * FUNGSI HELPER UNTUK MENGAMBIL LABEL JABATAN
 * Gunakan ini di disposisi_surat_masuk.php atau surat_keluar.php
 */
function getLabelJabatan($key) {
    global $jabatanMap;
    $key = strtolower($key);
    return $jabatanMap[$key] ?? ucwords(str_replace('_', ' ', $key));
}

// Tambahkan variabel global untuk akses mudah
$id_user_login = $_SESSION['id_user'] ?? null;
$nama_user_login = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'User';
?>
