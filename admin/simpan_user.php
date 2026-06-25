<?php
session_start();
require_once '../config/koneksi.php';

// ===============================
// PROTEKSI LOGIN & ROLE
// ===============================
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

// ===============================
// CEK METHOD POST
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: master_user_jajaran.php");
    exit;
}

// ===============================
// AMBIL DATA
// ===============================
$nama     = trim($_POST['nama']);
$email    = trim($_POST['email']);
$password = $_POST['password'];
$role     = $_POST['role']; // admin / ruangan / jajaran

// ===============================
// VALIDASI DASAR
// ===============================
if (empty($nama) || empty($email) || empty($password) || empty($role)) {
    die("Semua field wajib diisi!");
}

// Validasi role agar tidak bisa disusupi
$allowed_roles = ['admin', 'ruangan', 'jajaran'];
if (!in_array($role, $allowed_roles)) {
    die("Role tidak valid!");
}

// ===============================
// CEK EMAIL DUPLIKAT
// ===============================
$cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");

if (mysqli_num_rows($cek) > 0) {
    die("Email sudah digunakan!");
}

// ===============================
// HASH PASSWORD
// ===============================
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// ===============================
// SIMPAN KE DATABASE
// ===============================
$query = mysqli_query($conn, "
    INSERT INTO users (nama, email, password, role, status) 
    VALUES ('$nama', '$email', '$password_hash', '$role', 'aktif')
");

if ($query) {
    header("Location: master_user_jajaran.php?success=User berhasil ditambahkan");
    exit;
} else {
    die("Gagal menyimpan user.");
}
