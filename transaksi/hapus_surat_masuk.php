<?php
// ======================================================
// SAFE SESSION & CONFIG
// ======================================================
require_once "../config/session.php";
require_once "../config/koneksi.php";
require_once "../config/session_check.php"; // Proteksi berkas terpusat

// 1. PROTEKSI OTORITAS AKSES (Hanya Superadmin, Admin, dan Setum yang diizinkan menghapus)
$allowed_roles = ['superadmin', 'admin', 'setum'];
$user_role = strtolower($_SESSION['tipe_akses'] ?? '');

if (empty($_SESSION['id_user']) || !in_array($user_role, $allowed_roles)) {
    echo "<script>
            alert('Akses Ditolak: Anda tidak memiliki otoritas komando untuk menghapus berkas ini!'); 
            window.location = 'kelola_surat_masuk.php';
          </script>";
    exit();
}

// 2. VALIDASI PARAMETER ID SURAT
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<script>
            alert('Gagal: Parameter ID Surat tidak ditemukan!'); 
            window.location = 'kelola_surat_masuk.php';
          </script>";
    exit();
}

$id_surat = (int)$_GET['id'];

// 3. AMBIL DATA FILE SEBELUM DIHAPUS (Untuk Pembersihan Storage Server)
$query_file = "SELECT file_surat FROM surat_masuk WHERE id_surat = ?";
$stmt_file = $conn->prepare($query_file);
$stmt_file->bind_param("i", $id_surat);
$stmt_file->execute();
$result_file = $stmt_file->get_result();

if ($result_file->num_rows === 0) {
    echo "<script>
            alert('Gagal: Berkas surat memang tidak ditemukan di database!'); 
            window.location = 'kelola_surat_masuk.php';
          </script>";
    exit();
}

$data_surat = $result_file->fetch_assoc();
$nama_file = $data_surat['file_surat'];
$stmt_file->close();

// 4. EKSEKUSI PENGHAPUSAN SECARA TRANSAKSIONAL
// Mulai database transaction untuk memastikan konsistensi jika ada kegagalan
$conn->begin_transaction();

try {
    // Hapus data utama di tabel surat_masuk
    $query_delete = "DELETE FROM surat_masuk WHERE id_surat = ?";
    $stmt_del = $conn->prepare($query_delete);
    $stmt_del->bind_param("i", $id_surat);
    $stmt_del->execute();
    $stmt_del->close();

    // Jika berhasil sampai sini, commit transaksi database
    $conn->commit();

    // 5. PEMBERSIHAN FILE FISIK DI SERVER (Unlink)
    if (!empty($nama_file)) {
        $target_file_path = "../uploads/surat_masuk/" . $nama_file;
        if (file_exists($target_file_path)) {
            unlink($target_file_path); // Menghapus file PDF dari folder storage
        }
    }

    echo "<script>
            alert('Sukses: Berkas surat masuk dan lampiran PDF berhasil dihapus permanen dari sistem.'); 
            window.location = 'kelola_surat_masuk.php';
          </script>";

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan di database
    $conn->rollback();
    
    echo "<script>
            alert('Gagal menghapus berkas: " . mysqli_real_escape_string($conn, $e->getMessage()) . "'); 
            window.location = 'kelola_surat_masuk.php';
          </script>";
}

$conn->close();
?>
