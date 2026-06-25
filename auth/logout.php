<?php
// File: /var/www/eoffice_kesdamjayav5/auth/logout.php
require_once "../config/session.php";

// Ambil parameter asal user untuk menentukan halaman redirect tujuan akhir
$dari = $_GET['dari'] ?? '';

// 1. Menghapus semua variabel session di server dan memori browser
$_SESSION = array();

// 2. Jika ingin menghapus session cookie di browser secara total (Sangat Direkomendasikan untuk Keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session resmi di server
session_destroy();

// 4. ROUTING SENTRAL LOGOUT: Mengarahkan user ke pintu login yang tepat sesuai asal mereka
switch ($dari) {
    case 'admin':
    case 'setum':
         // header("Location: ");
        break;
        
    case 'kasituud':
        // Menjaga historis jika kasituud memiliki halaman login_admin tersendiri atau digabung
         // header("Location: ?pesan=logout_berhasil");
        break;

    case 'pimpinan':
    case 'spri':
    case 'wakakesdam':
        header("Location: login_pimpinan.php");
        break;

    case 'ruangan':
        header("Location: login_ruangan.php");
        break;

    case 'jajaran':
        header("Location: login_jajaran.php");
        break;

    case 'satuan_lain':
        header("Location: login_satuan_lain.php");
        break;

    case 'force':
    case 'total':
    default:
        // Pintu darurat/default jika session expired atau dipaksa keluar oleh sistem
        header("Location: ../dashboard_utama.php?pesan=logout_total");
        break;
}
exit();
?>
