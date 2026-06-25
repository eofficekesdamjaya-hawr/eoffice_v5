<?php
// 1. Inisialisasi session dan koneksi database
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// 2. Pemanggilan Vendor Autoloader Resmi (Otomatis memuat FPDF & FPDI tanpa error path)
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

// Kelas eksekusi gabungan FPDF + FPDI
class FpdiBridge extends Fpdi {
    // Kosongkan karena semua fungsionalitas otomatis diwarisi dari library vendor terbaru
}
// Pastikan hanya diakses melalui pengiriman form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil data input utama dari form TTD
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
    
    $canvas_width   = isset($_POST['canvas_width']) ? floatval($_POST['canvas_width']) : 1;
    $canvas_height  = isset($_POST['canvas_height']) ? floatval($_POST['canvas_height']) : 1;

    $signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';

    $nama_user      = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'Kakesdam Jaya';
    $role_aktif     = $_SESSION['tipe_akses'] ?? 'Pimpinan';
    $waktu_log      = date('d-m-Y H:i');

    $halaman_utama  = "../transaksi/kelola_surat_{$jenis_tabel}.php";

    if ($id_surat > 0 && !empty($signature_data)) {

        $queryFile = "SELECT file_surat, keterangan FROM {$tabel_target} WHERE id_surat = ?";
        $stmtFile  = $conn->prepare($queryFile);
        $stmtFile->bind_param("i", $id_surat);
        $stmtFile->execute();
        $resFile   = $stmtFile->get_result()->fetch_assoc();
        $nama_file = $resFile['file_surat'] ?? '';
        $riwayat_lama = $resFile['keterangan'] ?? '';
        $stmtFile->close();

        $path_file = "../uploads/surat_keluar/" . $nama_file;

        if (empty($nama_file) || !file_exists($path_file)) {
            echo "<script>alert('Berkas PDF fisik tidak ditemukan di server!'); window.location.href = '$halaman_utama';</script>";
            exit();
        }

        try {
            $filteredData = explode(',', $signature_data);
            $unencodedData = base64_decode($filteredData[1]);
            $temp_ttd_path = "../uploads/surat_keluar/temp_ttd_" . $id_surat . ".png";
            file_put_contents($temp_ttd_path, $unencodedData);

            $path_stempel = "../assets/stempel_kesdam1.png";
            $path_qr      = "../assets/qr_dummy.png";

            // Inisialisasi jembatan objek FPDI baru hasil deklarasi autoloader manual
            $pdf = new FpdiBridge();
            $pageCount = $pdf->setSourceFile($path_file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                if ($pageNo === $pageCount) {
                    $pdf_w = $size['width'];
                    $pdf_h = $size['height'];

                    $ratio_x = $pdf_w / $canvas_width;
                    $ratio_y = $pdf_h / $canvas_height;

                    $w_ttd_mm = 150 * $ratio_x;
                    $h_ttd_mm = 60 * $ratio_y;
                    
                    $w_stempel_mm = 140 * $ratio_x;
                    $h_stempel_mm = 70 * $ratio_y;
                    
                    $w_qr_mm = 75 * $ratio_x;
                    $h_qr_mm = 75 * $ratio_y;

                    $mm_x_ttd = $pos_x_ttd * $ratio_x;
                    $mm_y_ttd = $pos_y_ttd * $ratio_y;

                    $mm_x_stempel = $pos_x_stempel * $ratio_x;
                    $mm_y_stempel = $pos_y_stempel * $ratio_y;

                    $mm_x_qr = $pos_x_qr * $ratio_x;
                    $mm_y_qr = $pos_y_qr * $ratio_y;

                    if (file_exists($temp_ttd_path)) {
                        $pdf->Image($temp_ttd_path, $mm_x_ttd, $mm_y_ttd, $w_ttd_mm, $h_ttd_mm);
                    }
                    
                    if (file_exists($path_stempel)) {
                        $pdf->Image($path_stempel, $mm_x_stempel, $mm_y_stempel, $w_stempel_mm, $h_stempel_mm);
                    }

                    if (file_exists($path_qr)) {
                        $pdf->Image($path_qr, $mm_x_qr, $mm_y_qr, $w_qr_mm, $h_qr_mm);
                    }
                }
            }

            $pdf->Output($path_file, 'F');

            if (file_exists($temp_ttd_path)) {
                unlink($temp_ttd_path);
            }

        } catch (\Exception $e) {
            echo "<script>alert('Gagal menyisipkan TTE ke dokumen PDF: " . addslashes($e->getMessage()) . "'); window.location.href = '$halaman_utama';</script>";
            exit();
        }

        $status_baru  = "Selesai (TTE Diterapkan)";
        $riwayat_baru = "[{$waktu_log}] - *{$nama_user} ({$role_aktif})* telah menandatangani dokumen secara digital (TTE).\n-------------------\n" . $riwayat_lama;

        $queryUpdate = "UPDATE {$tabel_target} SET status_proses = 'selesai', keterangan = ? WHERE id_surat = ?";
        $stmtUpdate  = $conn->prepare($queryUpdate);
        $stmtUpdate->bind_param("si", $riwayat_baru, $id_surat);

        if ($stmtUpdate->execute()) {
            echo "<script>
                    alert('Dokumen Berhasil Ditandatangani Berbasis Digital!');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('Gagal memperbarui status data di database: " . addslashes($stmtUpdate->error) . "');
                    window.location.href = '$halaman_utama';
                  </script>";
            exit();
        }
        $stmtUpdate->close();

    } else {
        echo "<script>
                alert('Gagal memproses TTE, koordinat atau goresan tanda tangan kosong.');
                window.location.href = '$halaman_utama';
              </script>";
        exit();
    }

} else {
    header("Location: ../transaksi/kelola_surat_keluar.php");
    exit();
}
?>