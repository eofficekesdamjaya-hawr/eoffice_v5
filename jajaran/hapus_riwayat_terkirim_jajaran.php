<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id_user  = $_SESSION['id_user'];
$id_surat = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Cek apakah surat milik user ini dan statusnya masih Pending
$check = $conn->prepare("SELECT file_surat FROM surat_masuk WHERE id_surat = ? AND id_user = ? AND status_proses = 'Pending'");
$check->bind_param("ii", $id_surat, $id_user);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    $data = $res->fetch_assoc();
    $file_path = "../uploads/" . $data['file_surat'];

    // 2. Hapus file fisik di folder uploads
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // 3. Hapus data di database
    $delete = $conn->prepare("DELETE FROM surat_masuk WHERE id_surat = ?");
    $delete->bind_param("i", $id_surat);
    
    if ($delete->execute()) {
        echo "<script>alert('Surat berhasil dihapus.'); window.location='surat_terkirim_jajaran.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data di database.'); window.location='surat_terkirim_jajaran.php';</script>";
    }
} else {
    echo "<script>alert('Surat tidak ditemukan atau tidak diizinkan untuk dihapus.'); window.location='surat_terkirim_jajaran.php';</script>";
}
exit;