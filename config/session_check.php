<?php
if (session_status() === PHP_SESSION_NONE) {
    // 1. Amankan penyimpanan sesi ke folder khusus proyek agar tidak dihapus otomatis oleh OS Linux
    $custom_session_path = '/var/www/eoffice_kesdamjayav5/sessions';
    if (!file_exists($custom_session_path)) {
        mkdir($custom_session_path, 0755, true);
    }
    session_save_path($custom_session_path);
    
    // 2. Set waktu kedaluwarsa sesi menjadi 30 Hari (2.592.000 detik)
    ini_set('session.cookie_lifetime', 2592000); 
    ini_set('session.gc_maxlifetime', 2592000);  
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    require_once __DIR__.'/../config/session.php';
}

/**
 * Fungsi untuk memproteksi halaman berdasarkan role (Versi Pro - Bebas Kick)
 * @param array $allowed_roles Daftar role yang diizinkan
 */
function check_access($allowed_roles = []) {
    if (!isset($_SESSION['id_user'])) {
        header("Location: ../auth/login_ruangan.php");
        exit;
    }

    if (!empty($allowed_roles) && !in_array($_SESSION['tipe_akses'], $allowed_roles)) {
        // Jika tidak punya hak akses, lempar ke halaman utama mereka
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
}
