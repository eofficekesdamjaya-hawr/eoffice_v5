<?php
require_once "../config/session.php";


// Ambil data email dari session
$tipe_akses = $_SESSION['tipe_akses'] ?? '';
$user_email = $_SESSION['id_user'] ?? '';

// Inisialisasi awal semua menu (A sampai O) di-set FALSE (Terkunci default)
$can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = false;
$can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_N = $can_O = false;

$user_role = 'stranger';

// Alokasi Akses Berdasarkan Aturan Baru

// SUPPORT ROLE BARU
if ($tipe_akses === 'ruangan') {
    $user_role = 'ruangan';

    $can_A = $can_B = $can_C = $can_D = true;
    $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = true;
    $can_K = $can_L = $can_M = true;
    $can_N = $can_O = true;

    return;
}

if ($tipe_akses === 'jajaran') {
    $user_role = 'jajaran';

    $can_A = $can_B = $can_C = $can_D = true;
    $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = true;
    $can_K = $can_L = $can_M = true;
    $can_N = $can_O = true;

    return;
}

if ($tipe_akses === 'satuan_lain') {
    $user_role = 'satuan_lain';

    $can_A = $can_B = $can_C = $can_D = true;
    $can_E = $can_F = $can_G = true;
    $can_H = $can_I = $can_J = true;
    $can_K = $can_L = $can_M = true;
    $can_N = $can_O = true;

    return;
}
switch ($user_email) {
    // 1. Superadmin (A sampai G, Manajemen User N)
    case 'superadmin@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_N = true; // Eksklusif Manajemen User
        $user_role = 'superadmin';
        break;

    // 2. Setum, Admin, dan Kasi TUUD (Full Akses A sampai O)
    case 'setum@gmail.com':
    case 'admin@gmail.com':
    case 'kasituud2026@gmail.com':
        $can_A = $can_B = $can_C = $can_D = $can_E = $can_F = $can_G = true;
        $can_H = $can_I = $can_J = $can_K = $can_L = $can_M = $can_O = true;
        // Khusus superadmin saja yang boleh mengelola user (N), maka dikunci untuk grup ini
        $can_N = false; 
        $user_role = 'staff_inti';
        break;

    // 3. Kakesdam, Wakakesdam, dan Spri Pimpinan (Hanya A, C, E, F, G, H, I, J)
    case 'kakesdamjaya2026@gmail.com':
    case 'wakakesdamjaya2026@gmail.com':
    case 'spripimpinan2026@gmail.com':
        $can_A = $can_C = $can_E = $can_F = $can_G = $can_H = $can_I = $can_J = true;
        $user_role = 'pimpinan';
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
