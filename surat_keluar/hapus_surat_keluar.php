<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// ==========================================
// KEAMANAN: Cek Login & Hak Akses
// ==========================================
// Hanya admin/superadmin/setum yang boleh hapus
$user_email = $_SESSION['id_user'] ?? '';
$status_login = $_SESSION['status'] ?? '';

$allowed_roles = [
    'superadmin@gmail.com',
    'admin@gmail.com',
    'setum@gmail.com'
];

if (empty($user_email) || $status_login !== "login" || !in_array($user_email, $allowed_roles)) {
    echo "<script>alert('Akses ditolak! Anda tidak memiliki izin menghapus surat.'); window.location='../auth/login_admin.php';</script>";
    exit;
}

// ==========================================
// Ambil dan validasi ID surat
// ==========================================
$id_surat = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_surat <= 0) {
    echo "<script>alert('ID Surat tidak valid!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
    exit;
}

// ==========================================
// Ambil data surat dari database untuk dapat nama file
// ==========================================
$query = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data surat tidak ditemukan!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
    exit;
}

// ==========================================
// Proses Hapus
// ==========================================
$conn->begin_transaction();

try {
    // 1. Hapus file fisik surat keluar jika ada
    if (!empty($data['file_surat'])) {
        $file_path = "../uploads/surat_keluar/" . $data['file_surat'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // 2. Hapus data dari tabel terkait
    // Hapus riwayat disposisi
    $conn->query("DELETE FROM log_disposisi_keluar WHERE id_surat = $id_surat");
    // Hapus notifikasi
    $conn->query("DELETE FROM notifikasi WHERE id_surat = $id_surat");
    // Hapus data utama surat
    $conn->query("DELETE FROM surat_keluar WHERE id_surat = $id_surat");

    $conn->commit();
    echo "<script>alert('✅ Surat keluar berhasil dihapus beserta berkasnya!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('❌ Gagal menghapus: " . addslashes($e->getMessage()) . "'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
}

$stmt->close();
$conn->close();
?>
