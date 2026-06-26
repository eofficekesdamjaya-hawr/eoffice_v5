<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Validasi akses
$user_email = $_SESSION['email'] ?? '';
$akses_diizinkan = [
    'kakesdamjaya2026@gmail.com',
    'wakakesdamjaya2026@gmail.com',
    'kasituud2026@gmail.com'
];

if (!in_array($user_email, $akses_diizinkan)) {
    echo "<script>alert('Akses ditolak!'); window.history.back();</script>";
    exit;
}

// Validasi parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jenis = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';

if ($id <= 0 || $jenis !== 'keluar') {
    echo "<script>alert('Data tidak valid!'); window.history.back();</script>";
    exit;
}

// Ambil data file saat ini
$query = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($data) {
    $fileLama = "../uploads/surat_keluar/" . $data['file_surat'];
    // Hapus file hasil tanda tangan jika ada
    if (file_exists($fileLama) && strpos($data['file_surat'], '_ttd.pdf') !== false) {
        @unlink($fileLama);
    }

    // ✅ Kembalikan status menggunakan kolom yang BENAR-BENAR ADA di tabel
    $update = "UPDATE surat_keluar 
               SET status_proses = 'Baru',
                   status_tte = 'Menunggu',
                   penandatangan = NULL,
                   tgl_tte = NULL
               WHERE id_surat = ?";
    $stmtUpd = $conn->prepare($update);
    $stmtUpd->bind_param("i", $id);
    $stmtUpd->execute();
    $stmtUpd->close();
}

echo "<script>
    alert('✅ Status dikembalikan, Anda bisa menandatangani ulang!');
    window.location.href = '../transaksi/kelola_surat_keluar.php';
</script>";
exit;
?>
