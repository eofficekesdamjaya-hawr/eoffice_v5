<?php
// 1. Inisialisasi session dan proteksi halaman
require_once __DIR__.'/../config/session.php';
require_once "../config/koneksi.php";
require_once "../config/auth_config.php";
require_once "../config/hak_akses.php";

// Pastikan hanya diakses melalui pengiriman form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Ambil data input form utama
    $id_surat      = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status_baru   = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // Penentu jenis tabel (default ke 'keluar')
    $jenis_tabel   = isset($_POST['jenis_tabel']) ? $_POST['jenis_tabel'] : 'keluar'; 
    $tabel_target  = ($jenis_tabel === 'masuk') ? 'surat_masuk' : 'surat_keluar';

    // Ambil identitas verifikator dari session sistem Anda
    $nama_user     = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'Verifikator';
    $role_aktif    = $_SESSION['tipe_akses'] ?? 'Staff';
    
    // Set format waktu log aktivitas Indonesia
    date_default_timezone_set('Asia/Jakarta');
    $waktu_log     = date('d-m-Y H:i');

    // PERBAIKAN UTAMA: Mengunci jalur kembali tepat ke halaman transaksi keluar/masuk Anda
    $halaman_utama = "../transaksi/kelola_surat_{$jenis_tabel}.php"; 

    // Validasi data minimal harus terpenuhi
    if ($id_surat > 0 && !empty($status_baru)) {
        
        // 3. AMBIL DATA RIWAYAT / KETERANGAN LAMA (Menggunakan Prepared Statement)
        $queryAmbil = "SELECT keterangan FROM {$tabel_target} WHERE id_surat = ?";
        $stmtAmbil  = $conn->prepare($queryAmbil);
        $stmtAmbil->bind_param("i", $id_surat);
        $stmtAmbil->execute();
        $resAmbil   = $stmtAmbil->get_result()->fetch_assoc();
        $riwayat_lama = $resAmbil['keterangan'] ?? '';
        $stmtAmbil->close();

        // Gabungkan Riwayat Baru ke Baris Paling Atas (Diikuti riwayat lama di bawahnya)
        $riwayat_baru = "[{$waktu_log}] - *{$nama_user} ({$role_aktif})* mengubah status menjadi: **{$status_baru}**\n-------------------\n" . $riwayat_lama;

        // 4. UPDATE STATUS PROSES & KETERANGAN LOG BARU (Menggunakan Prepared Statement)
        $queryUpdate = "UPDATE {$tabel_target} SET status_proses = ?, keterangan = ? WHERE id_surat = ?";
        $stmtUpdate  = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param("ssi", $status_baru, $riwayat_baru, $id_surat);

        if ($stmtUpdate->execute()) {
            // Berhasil: Beri alert dan alihkan kembali ke halaman utama kelola surat
            echo "<script>
                    alert('Status berkas berhasil diperbarui menjadi: $status_baru');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        } else {
            // Gagal query update database
            echo "<script>
                    alert('Gagal memperbarui database: " . addslashes($stmtUpdate->error) . "');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        }
        $stmtUpdate->close();

    } else {
        // Data input tidak valid atau kosong
        echo "<script>
                alert('Parameter verifikasi data tidak lengkap.');
                window.location.href = '$halaman_utama';
              </script>";
        exit();
    }
} else {
    // Jika diakses langsung via URL browser tanpa form, paksa tendang kembali ke halaman utama surat keluar
    header("Location: ../transaksi/kelola_surat_keluar.php");
    exit();
}
?>