<?php
// ======================================================
// SAFE SESSION & CONFIG
// ======================================================
require_once "../config/session.php";
require_once "../config/koneksi.php";
require_once "../config/session_check.php"; 

// 1. PROTEKSI OTORITAS AKSES (Hanya Superadmin, Admin, dan Setum)
$allowed_roles = ['superadmin', 'admin', 'setum'];
$user_role = strtolower($_SESSION['tipe_akses'] ?? '');

if (empty($_SESSION['id_user']) || !in_array($user_role, $allowed_roles)) {
    echo "<script>
            alert('Akses Ditolak: Anda tidak memiliki otoritas komando untuk menghapus berkas ini!'); 
            window.location = 'kelola_surat_keluar.php';
          </script>";
    exit();
}

// 2. VALIDASI PARAMETER ID SURAT KELUAR
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<script>
            alert('Gagal: Parameter ID Surat Keluar tidak ditemukan!'); 
            window.location = 'kelola_surat_keluar.php';
          </script>";
    exit();
}

$id_surat_keluar = (int)$_GET['id'];

// 3. AMBIL DATA FILE SEBELUM DIHAPUS (Pembersihan Penyimpanan Server)
$query_file = "SELECT file_surat FROM surat_keluar WHERE id_surat_keluar = ?";
$stmt_file = $conn->prepare($query_file);
$stmt_file->bind_param("i", $id_surat_keluar);
$stmt_file->execute();
$result_file = $stmt_file->get_result();

if ($result_file->num_rows === 0) {
    echo "<script>
            alert('Gagal: Berkas surat keluar tidak ditemukan di database!'); 
            window.location = 'kelola_surat_keluar.php';
          </script>";
    exit();
}

$data_surat = $result_file->fetch_assoc();
$nama_file = $data_surat['file_surat'];
$stmt_file->close();

// 4. EKSEKUSI PENGHAPUSAN SECARA TRANSAKSIONAL
$conn->begin_transaction();

try {
    // Hapus data utama di tabel surat_keluar
    $query_delete = "DELETE FROM surat_keluar WHERE id_surat_keluar = ?";
    $stmt_del = $conn->prepare($query_delete);
    $stmt_del->bind_param("i", $id_surat_keluar);
    $stmt_del->execute();
    $stmt_del->close();

    // Commit perubahan database jika query sukses
    $conn->commit();

    // 5. PEMBERSIHAN FILE FISIK DI SERVER
    if (!empty($nama_file)) {
        $target_file_path = "../uploads/surat_keluar/" . $nama_file;
        if (file_exists($target_file_path)) {
            unlink($target_file_path);
        }
    }

    echo "<script>
            alert('Sukses: Berkas surat keluar dan lampiran PDF berhasil dihapus permanen.'); 
            window.location = 'kelola_surat_keluar.php';
          </script>";

} catch (Exception $e) {
    // Batalkan semua perubahan jika terjadi kegagalan query
    $conn->rollback();
    
    echo "<script>
            alert('Gagal menghapus berkas: " . mysqli_real_escape_string($conn, $e->getMessage()) . "'); 
            window.location = 'kelola_surat_keluar.php';
          </script>";
}

$conn->close();
?>
