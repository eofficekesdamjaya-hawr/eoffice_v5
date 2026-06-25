<?php
// 1. Inisialisasi session dan proteksi halaman
require_once __DIR__.'/../config/session.php';
require_once "../config/koneksi.php";
require_once "../config/auth_config.php";
require_once "../config/hak_akses.php";

// Pastikan hanya diakses melalui pengiriman form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Ambil data input dan bersihkan untuk keamanan query
    $id_surat = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status_baru = isset($_POST['status']) ? mysqli_real_escape_string($conn, trim($_POST['status'])) : '';

    // Validasi data minimal harus terpenuhi
    if ($id_surat > 0 && !empty($status_baru)) {
        
        // 3. Jalankan Query Update status_proses berdasarkan id_surat
        $queryUpdate = "UPDATE surat_keluar SET status_proses = '$status_baru' WHERE id_surat = $id_surat";
        $eksekusi = mysqli_query($conn, $queryUpdate);

        if ($eksekusi) {
            // Berhasil: Alihkan kembali ke halaman utama pengendali surat
            echo "<script>
                    alert('Status berkas berhasil diperbarui menjadi: $status_baru');
                    window.location.href = '../transifikasi/nama_file_utama_anda.php';
                  </script>";
            exit();
        } else {
            // Gagal query database
            echo "<script>
                    alert('Gagal memperbarui database: " . mysqli_error($conn) . "');
                    window.location.href = '../transifikasi/nama_file_utama_anda.php';
                  </script>";
            exit();
        }
    } else {
        // Data input tidak valid atau kosong
        echo "<script>
                alert('Parameter verifikasi data tidak lengkap.');
                window.location.href = '../transifikasi/nama_file_utama_anda.php';
              </script>";
        exit();
    }
} else {
    // Jika diakses langsung via URL tanpa form, paksa tendang kembali ke halaman utama
    header("Location: ../transifikasi/nama_file_utama_anda.php");
    exit();
}
?>