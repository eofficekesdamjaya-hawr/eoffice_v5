<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if (isset($_POST['submit_edit_setum'])) {
    $id_surat      = (int)$_POST['id_surat'];
    $no_surat      = trim($_POST['no_surat']);
    $tanggal_surat = $_POST['tanggal_surat'];
    $tanggal_kirim = $_POST['tanggal_kirim'];

    // Ambil info file lama
    $qFile = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
    $stFile = $conn->prepare($qFile);
    $stFile->bind_param("i", $id_surat);
    $stFile->execute();
    $surat_lama = $stFile->get_result()->fetch_assoc();
    $nama_file_final = $surat_lama['file_surat'];

    // Cek jika setum mengunggah draf file PDF baru
    if (!empty($_FILES['file_surat']['name'])) {
        $ext = pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) === 'pdf') {
            $nama_file_final = "REVISI_SETUM_" . time() . "_" . $_FILES['file_surat']['name'];
            move_uploaded_file($_FILES['file_surat']['tmp_path'] ?? $_FILES['file_surat']['tmp_name'], "../uploads/surat_keluar/" . $nama_file_final);
        }
    }

    $qUpdate = "UPDATE surat_keluar SET no_surat = ?, tanggal_surat = ?, tanggal_kirim = ?, file_surat = ? WHERE id_surat = ?";
    $stUp = $conn->prepare($qUpdate);
    $stUp->bind_param("ssssi", $no_surat, $tanggal_surat, $tanggal_kirim, $nama_file_final, $id_surat);

    if ($stUp->execute()) {
        echo "<script>alert('Data Surat Berhasil Diperbarui Setum!'); window.location='kelola_surat_keluar.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data!'); window.history.back();</script>";
    }
}
