<?php
require_once __DIR__.'/../config/session.php';
require_once "../config/koneksi.php";

// Proteksi akses: Pastikan hanya pimpinan/admin yang bisa menghapus TTD
$user_role = $_SESSION['tipe_akses'] ?? '';
if (!in_array($user_role, ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya', 'kasi_tuud', 'admin'])) {
    header("Location: ../dashboard/index.php");
    exit();
}

$id_surat = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$jenis    = filter_input(INPUT_GET, 'jenis', FILTER_DEFAULT);

if (!$id_surat) {
    echo "<script>alert('ID Surat tidak valid!'); window.history.back();</script>";
    exit();
}

// Menentukan tabel berdasarkan jenis surat (keluar / masuk)
$table = ($jenis === 'keluar') ? 'surat_keluar' : 'surat_masuk';

// Kembalikan status proses menjadi 'Di terima' (artinya draf disetujui tapi belum di-TTD)
$query = "UPDATE $table SET status_proses = 'Di terima' WHERE id_surat = ?";
$stmt  = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id_surat);
    if (mysqli_stmt_execute($stmt)) {
        // Alihkan kembali ke halaman kelola yang sesuai
        if ($jenis === 'keluar') {
            header("Location: ../transaksi/kelola_surat_keluar.php?status=success_hapus_ttd");
        } else {
            header("Location: ../transaksi/kelola_surat_masuk.php?status=success_hapus_ttd");
        }
        exit();
    } else {
        echo "<script>alert('Gagal memperbarui database!'); window.history.back();</script>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('Gagal menyiapkan instruksi query!'); window.history.back();</script>";
}
?>