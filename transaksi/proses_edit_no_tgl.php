<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Kunci Hak Akses Pengeditan kembali secara ketat
    $email_aktif = $_SESSION['email'] ?? '';
    $akses_khusus = ['superadmin@gmail.com', 'setum@gmail.com', 'admin@gmail.com'];

    if (!in_array($email_aktif, $akses_khusus)) {
        echo "<script>alert('Akses Ditolak! Anda tidak memiliki wewenang mengubah Nomor/Tanggal Surat.'); window.location.href='kelola_surat_keluar.php';</script>";
        exit();
    }

    $id_surat  = isset($_POST['id_surat']) ? intval($_POST['id_surat']) : 0;
    $no_surat  = isset($_POST['no_surat']) ? trim($_POST['no_surat']) : '';
    $tgl_surat = isset($_POST['tgl_surat']) ? trim($_POST['tgl_surat']) : '';

    if ($id_surat > 0 && !empty($no_surat) && !empty($tgl_surat)) {
        
        // Ambil Log Lama untuk menambahkan jejak audit perubahan nomor surat
        $queryAmbil = "SELECT keterangan FROM surat_keluar WHERE id_surat = ?";
        $stmtAmbil  = $conn->prepare($queryAmbil);
        $stmtAmbil->bind_param("i", $id_surat);
        $stmtAmbil->execute();
        $resAmbil   = $stmtAmbil->get_result()->fetch_assoc();
        $riwayat_lama = $resAmbil['keterangan'] ?? '';
        $stmtAmbil->close();

        $nama_user  = $_SESSION['nama'] ?? 'Administrator';
        $waktu_log  = date('d-m-Y H:i');
        $riwayat_baru = "[{$waktu_log}] - *{$nama_user}* mengubah nomor surat/tanggal menjadi: No: {$no_surat} | Tgl: {$tgl_surat}\n-------------------\n" . $riwayat_lama;

        // Eksekusi Update data nomor, tanggal, dan keterangan log
        $queryUpdate = "UPDATE surat_keluar SET no_surat = ?, tgl_surat = ?, keterangan = ? WHERE id_surat = ?";
        $stmtUpdate  = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param("sssi", $no_surat, $tgl_surat, $riwayat_baru, $id_surat);

        if ($stmtUpdate->execute()) {
            echo "<script>alert('Nomor dan Tanggal Surat berhasil diperbarui!'); window.location.href='kelola_surat_keluar.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui database: " . addslashes($stmtUpdate->error) . "'); window.location.href='kelola_surat_keluar.php';</script>";
        }
        $stmtUpdate->close();
    } else {
        echo "<script>alert('Parameter formulir tidak lengkap.'); window.location.href='kelola_surat_keluar.php';</script>";
    }
} else {
    header("Location: kelola_surat_keluar.php");
    exit();
}
?>