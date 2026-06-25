<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * KONTROLER VERIFIKASI & PERSETUJUAN STATUS SURAT (PRODUCTION)
 * UTK SURAT MASUK DAN SURAT KELUAR
 * ==========================================================
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// 1. Proteksi keamanan hak akses
if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak. Silakan login kembali.");
}

if (!isset($conn) && isset($koneksi)) { $conn = $koneksi; }

// 2. Ambil parameter data dari tombol kelola_surat.php
$id_surat = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status   = isset($_GET['status']) ? mysqli_real_escape_string($conn, trim($_GET['status'])) : '';
$type     = isset($_GET['type']) ? mysqli_real_escape_string($conn, trim($_GET['type'])) : 'masuk';

$role_key  = $_SESSION['role_key'] ?? $_SESSION['tipe_akses'] ?? 'setum';
$role_key  = strtolower(trim($role_key));
$nama_user = $_SESSION['nama'] ?? $_SESSION['nama_user'] ?? 'Operator';

// 3. Validasi parameter wajib
if ($id_surat <= 0 || empty($status)) {
    echo "<script>
            alert('Parameter verifikasi data tidak valid atau kurang lengkap!');
            window.location.href = '../transaksi/kelola_surat.php';
          </script>";
    exit();
}

// 4. Eksekusi pembaruan status berdasarkan jenis berkas
if ($type === 'keluar') {
    // Jalur Surat Keluar
    $query_update = "UPDATE surat_keluar SET status_proses = '$status' WHERE id_surat = $id_surat";
    $nama_tabel   = "Surat Keluar";
} else {
    // Jalur Surat Masuk
    $query_update = "UPDATE surat_masuk SET status_proses = '$status' WHERE id_surat = $id_surat";
    $nama_tabel   = "Surat Masuk";
}

$eksekusi = mysqli_query($conn, $query_update);

// 5. Pencatatan otomatis ke Log Riwayat (Audit Trail) jika database Anda mendukung tabel riwayat
if ($eksekusi) {
    $keterangan_log = "Status $nama_tabel berhasil diubah menjadi: '$status' oleh $nama_user ($role_key)";
    
    // Mencoba mencatat ke tabel riwayat jika tersedia di sistem database Anda
    mysqli_query($conn, "
        INSERT INTO riwayat_surat (id_surat, kategori_surat, nama_user, aksi, keterangan, tanggal_aksi) 
        VALUES ($id_surat, '$type', '$nama_user', 'Verifikasi Status', '$keterangan_log', NOW())
    ");

    // Kembalikan ke halaman pengendali utama dengan pesan sukses bahasa indonesia
    echo "<script>
            alert('Berhasil! Status berkas $nama_tabel telah diperbarui menjadi [$status].');
            window.location.href = '../transaksi/kelola_surat.php';
          </script>";
} else {
    // Penanganan jika terjadi error pada database
    $pesan_error = mysqli_error($conn);
    echo "<script>
            alert('Gagal memperbarui status berkas! Masalah Sistem: $pesan_error');
            window.location.href = '../transaksi/kelola_surat.php';
          </script>";
}
exit();
