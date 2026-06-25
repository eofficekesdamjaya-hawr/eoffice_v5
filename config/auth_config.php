<?php
// File: ../config/auth_config.php

require_once "../config/session.php";

// 1. Daftar Mapping Jabatan (Master Data)
$jabatanMap = [
    'setum'           => 'Setum / Admin',
    'admin'           => 'Setum / Admin',
    'superadmin'      => 'Super Admin',
    'kasi_tuud'       => 'Kasi Tuud',
    'kakesdam_jaya'   => 'Kakesdam Jaya',
    'wakakesdam_jaya' => 'Wakakesdam Jaya',
    'spri_pimpinan'   => 'Spri Pimpinan',
    'was'             => 'Seksi Was',
    'dukkes'          => 'Seksi Dukkes',
    'kesprev'         => 'Seksi Kesprev',
    'renproggar'      => 'Seksi Renproggar',
    'minlogkes'       => 'Seksi Minlogkes',
    'matkes'          => 'Seksi Matkes',
    'yankes'          => 'Seksi Yankes',
    'gudkesrah'       => 'Gudang Kesrah',
    'smk'             => 'SMK Kesdam Jaya',
    'denkeslap'       => 'Dandenkeslap',
    'primkop'         => 'Ka Primkop',
    'paku_kesdam'     => 'Paku Kesdam',
    'infokes'         => 'Kaur Infokes',
    'log'             => 'Kaur Log',
    'juyar'           => 'Juyar',
    'persit'          => 'Persit',
    'pam'             => 'Kaurpam',
    'urdal'           => 'Kaurdal',
    'korpri'          => 'Korpri',
    'pers'            => 'Kaur Pers',
    'pers_tuud'       => 'Pers Tuud',
    'ruangan'         => 'Ruangan',
    'jajaran'         => 'Jajaran',
    'satuan_lain'     => 'Satuan Lain'
];

// 2. Mengambil Email & Tipe Akses dari Session (Gunakan fallback yang aman)
$email_user = trim(strtolower($_SESSION['email'] ?? $_SESSION['id_user'] ?? ''));
$tipe_akses = trim(strtolower($_SESSION['tipe_akses'] ?? 'ruangan'));

// Set role default berdasarkan tipe_akses session terlebih dahulu
$role = $tipe_akses;

// 3. Sinkronisasi Logika Role Berdasarkan Email (Sama persis dengan hak_akses.php)
$mapEmailToRole = [
    'superadmin@gmail.com'         => 'superadmin',
    'setum@gmail.com'              => 'setum',
    'admin@gmail.com'              => 'admin',
    'kasituud2026@gmail.com'       => 'kasi_tuud',
    'kakesdamjaya2026@gmail.com'   => 'kakesdam_jaya',
    'wakakesdamjaya2026@gmail.com' => 'wakakesdam_jaya',
    'spripimpinan2026@gmail.com'   => 'spri_pimpinan'
];

// Jika email terdaftar di dalam list, timpa variabel $role dengan role spesifiknya
if (array_key_exists($email_user, $mapEmailToRole)) {
    $role = $mapEmailToRole[$email_user];
}

// 4. Menentukan variabel $dari dan $is_admin_setum secara global
$raw_role = $_SESSION['nama_role'] ?? $_SESSION['nama_jabatan'] ?? $role;
$dari = $jabatanMap[strtolower($raw_role)] ?? ucwords(str_replace('_', ' ', $raw_role));

// Flag Admin/Setum/Superadmin untuk kebutuhan bypass filter/fitur sekretariat
$is_admin_setum = in_array($role, ['setum', 'admin', 'superadmin']);

/**
 * FUNGSI HELPER UNTUK MENGAMBIL LABEL JABATAN
 */
function getLabelJabatan($key) {
    global $jabatanMap;
    $key = strtolower(trim($key));
    return $jabatanMap[$key] ?? ucwords(str_replace('_', ' ', $key));
}

// Tambahkan variabel global untuk akses mudah di file lain
$id_user_login = $_SESSION['id_user'] ?? null;
$nama_user_login = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'User';
?>