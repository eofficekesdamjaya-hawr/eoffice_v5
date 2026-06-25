<?php
require_once __DIR__.'/../config/session.php';

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
