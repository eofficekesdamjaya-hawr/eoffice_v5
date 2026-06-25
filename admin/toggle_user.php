<?php
require_once "../config/session.php";
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
// CEK DATA USER
// ===============================
$query = mysqli_query($conn, "SELECT id, status FROM users WHERE id='$id' LIMIT 1");

if (mysqli_num_rows($query) == 0) {
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($query);

// ===============================
// CEGAH NONAKTIFKAN DIRI SENDIRI
// ===============================
if ($id == $_SESSION['id']) {
    die("Anda tidak bisa menonaktifkan akun sendiri!");
}

// ===============================
// TOGGLE STATUS
// ===============================
$status_baru = ($user['status'] == 'aktif') ? 'nonaktif' : 'aktif';

mysqli_query($conn, "UPDATE users SET status='$status_baru' WHERE id='$id'");

// ===============================
header("Location: master_user_jajaran.php");
exit;
