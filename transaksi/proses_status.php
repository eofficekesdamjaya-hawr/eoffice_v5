<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * WORKFLOW ENGINE & VERIFICATION PROCESSOR (VPS PRODUCTION READY)
 * INTEGRASI KEAMANAN PREPARED STATEMENTS & VALIDASI STATUS
 * ==========================================================
 */

ini_set('display_errors', 0); // Proteksi VPS: Sembunyikan error mentah dari publik
error_reporting(E_ALL);

require_once "../config/session.php";

require_once __DIR__ . '/../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// 1. Proteksi Akses & Validasi Sesi Operator
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit();
}

$role_key = strtolower(trim($_SESSION['role_key'] ?? $_SESSION['tipe_akses'] ?? ''));
$id_operator = $_SESSION['id_user'];

// Batasi hak akses hanya untuk role yang berwenang melakukan verifikasi
$authorized_roles = ['admin', 'setum', 'superadmin', 'kasi_tuud', 'kakesdam_jaya', 'wakakesdam_jaya'];
if (!in_array($role_key, $authorized_roles)) {
    die("Akses Ditolak: Anda tidak memiliki otoritas untuk memverifikasi berkas.");
}

if (!isset($conn) && isset($koneksi)) { $conn = $koneksi; }

// 2. Tangkap dan Amankan Parameter Input
$id_surat = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tab      = isset($_GET['tab']) ? trim($_GET['tab']) : ''; // 'masuk' atau 'keluar'
$status   = isset($_GET['status']) ? trim($_GET['status']) : '';

// Normalisasi penamaan tabel tujuan
$table_target = ($tab === 'keluar') ? 'surat_keluar' : 'surat_masuk';

// 3. Validasi Parameter Wajib
if ($id_surat <= 0 || !in_array($tab, ['masuk', 'keluar']) || empty($status)) {
    echo "<script>alert('Parameter tidak valid atau tidak lengkap!'); window.history.back();</script>";
    exit();
}

// 4. Mapping Status Valid Sesuai Sistem Pengendali
$valid_statuses = ['Di pending', 'Di terima', 'Di tolak', 'Proses Disposisi', 'Selesai'];
if (!in_array($status, $valid_statuses)) {
    echo "<script>alert('Status verifikasi tidak dikenali oleh sistem!'); window.history.back();</script>";
    exit();
}

/* ======================================================
    LOGIKA PERADILAN ALUR BERKAS (DATABASE ENGINE)
====================================================== */

if ($status === 'Selesai') {
    // Jalur Khusus: Status 'Selesai' membutuhkan input nomor surat resmi dan tanggal dari modal penomoran
    $no_surat  = isset($_GET['no_surat']) ? trim($_GET['no_surat']) : '';
    $tgl_surat = isset($_GET['tgl_surat']) ? trim($_GET['tgl_surat']) : '';

    if (empty($no_surat) || empty($tgl_surat)) {
        echo "<script>alert('Untuk menyatakan Selesai, Nomor dan Tanggal Surat wajib diisi!'); window.history.back();</script>";
        exit();
    }

    // Update status, nomor resmi, dan tanggal administratif surat (Prepared Statement)
    $stmt = mysqli_prepare($conn, "UPDATE $table_target SET status_proses = ?, no_surat = ?, tanggal_surat = ? WHERE id_surat = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $status, $no_surat, $tgl_surat, $id_surat);
    $execution = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

} else {
    // Jalur Standar: Update status proses berkas langsung (Di pending, Di terima, Di tolak, Proses Disposisi)
    $stmt = mysqli_prepare($conn, "UPDATE $table_target SET status_proses = ? WHERE id_surat = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id_surat);
    $execution = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/* ======================================================
    PENCATATAN LOG HISTORY SISTEM (AUDIT TRAIL)
====================================================== */
if ($execution) {
    $keterangan_log = "Berkas dilembagakan menjadi status [" . $status . "] oleh Operator ID: " . $id_operator . " (" . strtoupper($role_key) . ")";
    
    $stmt_log = mysqli_prepare($conn, "INSERT INTO riwayat_surat (id_surat, jenis_surat, status_perubahan, keterangan, id_user, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt_log, "isssi", $id_surat, $tab, $status, $keterangan_log, $id_operator);
    mysqli_stmt_execute($stmt_log);
    mysqli_stmt_close($stmt_log);

    // Redirect kembali ke halaman utama dengan notifikasi sukses
    echo "<script>
            alert('Sukses! Berkas berhasil diverifikasi menjadi: " . $status . "');
            window.location.href = 'index.php?module=" . urlencode($tab) . "';
          </script>";
} else {
    // Jika eksekusi query gagal pada VPS
    echo "<script>
            alert('Gagal memproses verifikasi. Silakan hubungi Administrator IT Kesdam.');
            window.history.back();
          </script>";
}

mysqli_close($conn);
?>
