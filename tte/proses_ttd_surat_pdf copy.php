<?php
// 1. Inisialisasi session dan koneksi database
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Pastikan hanya diakses melalui pengiriman form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Ambil data input utama dari form TTD
    $id_surat       = isset($_POST['id_surat']) ? intval($_POST['id_surat']) : 0;
    $jenis_tabel    = isset($_POST['jenis_tabel']) ? trim($_POST['jenis_tabel']) : 'keluar';
    $tabel_target   = ($jenis_tabel === 'masuk') ? 'surat_masuk' : 'surat_keluar';
    
    // Ambil data koordinat & ukuran canvas
    $pos_x_ttd      = isset($_POST['pos_x_ttd']) ? floatval($_POST['pos_x_ttd']) : 0;
    $pos_y_ttd      = isset($_POST['pos_y_ttd']) ? floatval($_POST['pos_y_ttd']) : 0;
    $pos_x_stempel  = isset($_POST['pos_x_stempel']) ? floatval($_POST['pos_x_stempel']) : 0;
    $pos_y_stempel  = isset($_POST['pos_y_stempel']) ? floatval($_POST['pos_y_stempel']) : 0;
    $pos_x_qr       = isset($_POST['pos_x_qr']) ? floatval($_POST['pos_x_qr']) : 0;
    $pos_y_qr       = isset($_POST['pos_y_qr']) ? floatval($_POST['pos_y_qr']) : 0;
    
    $canvas_width   = isset($_POST['canvas_width']) ? floatval($_POST['canvas_width']) : 0;
    $canvas_height  = isset($_POST['canvas_height']) ? floatval($_POST['canvas_height']) : 0;

    // Ambil data gambar TTD mentah (Base64) dari signature pad
    $signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';

    // Ambil identitas penandatangan dari session
    $nama_user      = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'Kakesdam Jaya';
    $role_aktif     = $_SESSION['tipe_akses'] ?? 'Pimpinan';
    $waktu_log      = date('d-m-Y H:i');

    // Tentukan halaman redirect utama (Kembali ke kelola surat keluar)
    $halaman_utama  = "../transaksi/kelola_surat_{$jenis_tabel}.php";

    // 3. Validasi minimal parameter data
    if ($id_surat > 0 && !empty($signature_data)) {
        
        /* ===================================================================
          NOTE / TEMPAT PENGEMBANGAN LANJUTAN (FPDF / TCPDF / FPDI):
          Jika Anda ingin menempelkan gambarnya langsung ke dalam file PDF fisik,
          Anda bisa menaruh script library PDF injector Anda di area ini memanfaatkan
          variabel koordinat ($pos_x_ttd, $pos_y_ttd, dll) yang dikirimkan.
          ===================================================================
        */

        // 4. AMBIL DATA RIWAYAT / KETERANGAN LAMA
        $queryAmbil = "SELECT keterangan FROM {$tabel_target} WHERE id_surat = ?";
        $stmtAmbil  = $conn->prepare($queryAmbil);
        $stmtAmbil->bind_param("i", $id_surat);
        $stmtAmbil->execute();
        $resAmbil   = $stmtAmbil->get_result()->fetch_assoc();
        $riwayat_lama = $resAmbil['keterangan'] ?? '';
        $stmtAmbil->close();

        // Susun struktur log riwayat baru (Taruh paling atas)
        $status_baru  = "Selesai (TTE Diterapkan)";
        $riwayat_baru = "[{$waktu_log}] - *{$nama_user} ({$role_aktif})* telah menandatangani dokumen secara digital (TTE).\n-------------------\n" . $riwayat_lama;

        // 5. UPDATE DATABASE (Ubah status_proses menjadi 'selesai' & simpan log)
        $queryUpdate = "UPDATE {$tabel_target} SET status_proses = 'selesai', keterangan = ? WHERE id_surat = ?";
        $stmtUpdate  = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param("si", $riwayat_baru, $id_surat);

        if ($stmtUpdate->execute()) {
            // Berhasil: Berikan feedback pop-up sukses dan redirect ke transaksi surat keluar
            echo "<script>
                    alert('Dokumen Berhasil Ditandatangani Berbasis Digital!');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        } else {
            // Gagal eksekusi query update
            echo "<script>
                    alert('Gagal memperbarui status data di database: " . addslashes($stmtUpdate->error) . "');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        }
        $stmtUpdate->close();

    } else {
        // Parameter data kiriman form tidak lengkap
        echo "<script>
                alert('Gagal memproses TTE, koordinat atau goresan tanda tangan kosong.');
                window.location.href = '$halaman_utama';
              </script>";
        exit();
    }

} else {
    // Proteksi jika file diakses langsung tanpa method POST form
    header("Location: ../transaksi/kelola_surat_keluar.php");
    exit();
}
?>