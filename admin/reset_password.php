<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

if (!isset($_GET['id'])) {
    header("Location: master_user_jajaran.php");
    exit;
}

$id = intval($_GET['id']);

// Password default baru
$password_baru = "123456"; // bisa Anda ganti
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

mysqli_query($conn, "
    UPDATE users 
    SET password='$password_hash'
    WHERE id='$id'
");

header("Location: master_user_jajaran.php?success=Password berhasil direset ke 123456");
exit;
