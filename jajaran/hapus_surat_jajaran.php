<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id_jajaran = $_SESSION['id_user'];

// Cek apakah ada ID surat
if (!isset($_GET['id'])) {
    header("Location: surat_terkirim_jajaran.php");
    exit;
}

$id_surat = intval($_GET['id']);

// Ambil data surat untuk memastikan milik user ini
$query = "SELECT file_surat FROM surat_masuk WHERE id_surat = ? AND id_user = ? AND role_pengirim='jajaran'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_surat, $id_jajaran);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Surat tidak ditemukan atau bukan milik user
    header("Location: surat_terkirim_jajaran.php");
    exit;
}

$data = $result->fetch_assoc();

// Hapus file dari folder uploads
$file_path = "../uploads/" . $data['file_surat'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Hapus data dari database
$query_delete = "DELETE FROM surat_masuk WHERE id_surat = ? AND id_user = ?";
$stmt_delete = $conn->prepare($query_delete);
$stmt_delete->bind_param("ii", $id_surat, $id_jajaran);
$stmt_delete->execute();

// Redirect kembali
header("Location: surat_terkirim_jajaran.php?hapus=berhasil");
exit;
?>