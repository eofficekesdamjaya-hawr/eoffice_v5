<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if (isset($_POST['submit_verif'])) {
    $id_surat      = (int)$_POST['id_surat'];
    $jenis_tabel   = $_POST['jenis_tabel']; // 'masuk' atau 'keluar'
    $status_proses = $_POST['status_proses'];
    $nama_user     = $_SESSION['nama'] ?? 'Verifikator';
    $role_aktif    = $_SESSION['tipe_akses'] ?? 'Staff';

    $waktu_log    = date('d-m-Y H:i');
    $tabel_target = ($jenis_tabel === 'masuk') ? 'surat_masuk' : 'surat_keluar';

    // 1. Ambil Keterangan / Riwayat Lama
    $queryAmbil = "SELECT keterangan FROM {$tabel_target} WHERE id_surat = ?";
    $stmt = $conn->prepare($queryAmbil);
    $stmt->bind_param("i", $id_surat);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $riwayat_lama = $res['keterangan'] ?? '';

    // Append Riwayat Baru (Tanpa isi teks catatan verifikator)
    $riwayat_baru = "[{$waktu_log}] - *{$nama_user} ({$role_aktif})* mengubah status menjadi: **{$status_proses}**\n-------------------\n" . $riwayat_lama;

    // 2. Update Status & Keterangan
    $queryUpdate = "UPDATE {$tabel_target} SET status_proses = ?, keterangan = ? WHERE id_surat = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("ssi", $status_proses, $riwayat_baru, $id_surat);

    if ($stmtUpdate->execute()) {
        // PERBAIKAN DI SINI: Ditambahkan jalur relatif ../transaksi/ agar tidak 404
        echo "<script>alert('Status Verifikasi Berhasil Diperbarui!'); window.location='../transaksi/kelola_surat_{$jenis_tabel}.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui verifikasi!'); window.history.back();</script>";
    }
    $stmt->close();
    $stmtUpdate->close();
}
?>
