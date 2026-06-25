<?php
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'verifikasi') {
        $sql = "UPDATE surat_masuk SET status_verifikasi = 'Terverifikasi' WHERE id = ?";
    } elseif ($aksi === 'selesai') {
        $sql = "UPDATE surat_masuk SET status_verifikasi = 'Selesai' WHERE id = ?";
    }

    if (isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: ../verifikasi/verifikasi_surat.php");
    exit;
}
