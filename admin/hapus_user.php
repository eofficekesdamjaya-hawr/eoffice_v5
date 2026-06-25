<?php
session_start();
require_once '../config/koneksi.php';

// ===============================
// PROTEKSI LOGIN & ROLE
// ===============================
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

if (!isset($_GET['id'])) {
    header("Location: master_user_jajaran.php");
    exit;
}

$id = intval($_GET['id']);

// ===============================
// CEGAH HAPUS DIRI SENDIRI
// ===============================
if ($id == $_SESSION['id']) {
    die("Anda tidak bisa menghapus akun sendiri!");
}

// ===============================
// CEK USER ADA ATAU TIDAK
// ===============================
$cek = mysqli_query($conn, "SELECT id FROM users WHERE id='$id' LIMIT 1");

if (mysqli_num_rows($cek) == 0) {
    die("User tidak ditemukan.");
}

// ===============================
// HAPUS USER
// ===============================
mysqli_query($conn, "DELETE FROM users WHERE id='$id'");

// ===============================
header("Location: master_user_jajaran.php");
exit;
