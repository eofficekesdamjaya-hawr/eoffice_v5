<?php
require_once "../config/session.php";

// Ambil data dari session
$tipe_akses = $_SESSION['tipe_akses'] ?? '';
$user_email = $_SESSION['email'] ?? $_SESSION['id_user'] ?? ''; // Menghindari ketukar antara id_user / email

// Inisialisasi awal semua menu (A sampai O) di-set FALSE (Terkunci default)
$can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = false;
$can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_N = $can_O = false;

$user_role = 'stranger';

// --- BYPASS UNTUK AKSES LEVEL RUANGAN / JAJARAN / SATUAN LAIN ---
if ($tipe_akses === 'ruangan') {
    $user_role = 'ruangan';
    $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_N = $can_O = true;
    return;
}
if ($tipe_akses === 'jajaran') {
    $user_role = 'jajaran';
    $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_N = $can_O = true;
    return;
}
if ($tipe_akses === 'satuan_lain') {
    $user_role = 'satuan_lain';
    $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_N = $can_O = true;
    return;
}

// --- ALOKASI HAK AKSES BERDASARKAN EMAIL KESDAM JAYA ---
switch (trim($user_email)) {

    // 1. Superadmin (Menu A sampai G, Manajemen User N)
    case 'superadmin@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_N = true; 
        $user_role = 'superadmin';
        break;

    // 2. Setum (Full Akses A sampai O, Kecuali Manajemen User N)
    case 'setum@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_O = true;
        $can_N = false; 
        $user_role = 'setum';
        break;

    // 3. Admin (Full Akses A sampai O, Kecuali Manajemen User N)
    case 'admin@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_O = true;
        $can_N = false; 
        $user_role = 'admin';
        break;

    // 4. Kasi TUUD (Full Akses A sampai O termasuk TTD Surat, Kecuali Manajemen User N)
    case 'kasituud2026@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_O = true;
        $can_N = false; 
        $user_role = 'kasi_tuud';
        break;

    // 5. Kakesdam Jaya (Hanya A, C, E, F, G, H, I, J termasuk TTD Surat)
    case 'kakesdamjaya2026@gmail.com':
        $can_A = $can_C = $can_E = $can_F = $can_G = $can_H = $can_I = $can_J = true;
        $user_role = 'kakesdam_jaya';
        break;

    // 6. Wakakesdam Jaya (Hanya A, C, E, F, G, H, I, J termasuk TTD Surat)
    case 'wakakesdamjaya2026@gmail.com':
        $can_A = $can_C = $can_E = $can_F = $can_G = $can_H = $can_I = $can_J = true;
        $user_role = 'wakakesdam_jaya';
        break;

    // 7. Spri Pimpinan (Hanya A, C, E, F, G, H, I, J - Tidak Memiliki Akses TTD)
    case 'spripimpinan2026@gmail.com':
        $can_A = $can_C = $can_E = $can_F = $can_G = $can_H = $can_I = $can_J = true;
        $user_role = 'spri_pimpinan';
        break;

    default:
        $user_role = 'stranger';
        break;
}

// Proteksi Keamanan: Jika user ilegal, tendang keluar kembali ke halaman login
if ($user_role === 'stranger' || empty($_SESSION['id_user']) || $_SESSION['status'] !== "login") {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}
?>