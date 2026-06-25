<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

$id     = intval($_POST['id']);
$nama   = trim($_POST['nama']);
$email  = trim($_POST['email']);
$role   = $_POST['role'];
$status = $_POST['status'];

$allowed_roles = ['admin','ruangan','jajaran'];
$allowed_status = ['aktif','nonaktif'];

if (!in_array($role,$allowed_roles) || !in_array($status,$allowed_status)) {
    die("Data tidak valid!");
}

// Cek email duplikat selain dirinya
$cek = mysqli_query($conn, "
    SELECT id FROM users 
    WHERE email='$email' AND id!='$id'
");

if (mysqli_num_rows($cek) > 0) {
    die("Email sudah digunakan!");
}

mysqli_query($conn, "
    UPDATE users 
    SET nama='$nama',
        email='$email',
        role='$role',
        status='$status'
    WHERE id='$id'
");

header("Location: master_user_jajaran.php?success=User berhasil diupdate");
exit;
