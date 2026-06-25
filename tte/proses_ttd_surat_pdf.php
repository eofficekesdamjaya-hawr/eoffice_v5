<?php
// 1. Inisialisasi session dan koneksi database
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../libraries/fpdf/fpdf.php';
require_once __DIR__ . '/../libraries/fpdi/autoload.php';

use setasign\Fpdi\Fpdi;

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
    
    $canvas_width   = isset($_POST['canvas_width']) ? floatval($_POST['canvas_width']) : 1; // hindari pembagian dengan nol
    $canvas_height  = isset($_POST['canvas_height']) ? floatval($_POST['canvas_height']) : 1;

    // Ambil data gambar TTD mentah (Base64) dari signature pad
    $signature_data = isset($_POST['signature_data']) ? $_POST['signature_data'] : '';

    // Ambil identitas penandatangan dari session
    $nama_user      = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'Kakesdam Jaya';
    $role_aktif     = $_SESSION['tipe_akses'] ?? 'Pimpinan';
    $waktu_log      = date('d-m-Y H:i');

    // Tentukan halaman redirect utama
    $halaman_utama  = "../transaksi/kelola_surat_{$jenis_tabel}.php";

    // 3. Validasi minimal parameter data
    if ($id_surat > 0 && !empty($signature_data)) {

        // 4. AMBIL DATA FILE SURAT DARI DATABASE
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

        // ===================================================================
        // PROSES INJEKSI ELEMEN (TTD, STEMPEL, QR) KE PDF MENGGUNAKAN FPDI
        // ===================================================================
        try {
            // Konversi Base64 Signature Pad ke File Gambar Sementara (PNG)
            $filteredData = explode(',', $signature_data);
            $unencodedData = base64_decode($filteredData[1]);
            $temp_ttd_path = "../uploads/surat_keluar/temp_ttd_" . $id_surat . ".png";
            file_put_contents($temp_ttd_path, $unencodedData);

            // Path komponen bawaan statis
            $path_stempel = "../assets/stempel_kesdam1.png";
            $path_qr      = "../assets/qr_dummy.png";

            // Inisialisasi FPDI
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($path_file);

            // Loop seluruh halaman untuk menyalin isinya
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Buat halaman baru dengan orientasi dan ukuran sama seperti aslinya
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // Jika ini adalah halaman terakhir, tempelkan TTD, Stempel, dan QR
                if ($pageNo === $pageCount) {
                    // Ambil dimensi asli halaman PDF (dalam mm)
                    $pdf_w = $size['width'];
                    $pdf_h = $size['height'];

                    // Rasio Konversi dari Koordinat Layar Canvas ke Milimeter PDF
                    $ratio_x = $pdf_w / $canvas_width;
                    $ratio_y = $pdf_h / $canvas_height;

                    // Ukuran komponen di dalam PDF (dalam mm)
                    $w_ttd_mm = 150 * $ratio_x;
                    $h_ttd_mm = 60 * $ratio_y;
                    
                    $w_stempel_mm = 140 * $ratio_x;
                    $h_stempel_mm = 70 * $ratio_y;
                    
                    $w_qr_mm = 75 * $ratio_x;
                    $h_qr_mm = 75 * $ratio_y;

                    // Konversi koordinat X & Y ke Milimeter PDF
                    $mm_x_ttd = $pos_x_ttd * $ratio_x;
                    $mm_y_ttd = $pos_y_ttd * $ratio_y;

                    $mm_x_stempel = $pos_x_stempel * $ratio_x;
                    $mm_y_stempel = $pos_y_stempel * $ratio_y;

                    $mm_x_qr = $pos_x_qr * $ratio_x;
                    $mm_y_qr = $pos_y_qr * $ratio_y;

                    // Tempel Gambar Tanda Tangan Hasil Goresan
                    if (file_exists($temp_ttd_path)) {
                        $pdf->Image($temp_ttd_path, $mm_x_ttd, $mm_y_ttd, $w_ttd_mm, $h_ttd_mm);
                    }
                    
                    // Tempel Gambar Stempel Resmi
                    if (file_exists($path_stempel)) {
                        $pdf->Image($path_stempel, $mm_x_stempel, $mm_y_stempel, $w_stempel_mm, $h_stempel_mm);
                    }

                    // Tempel Gambar QR Code Berkas
                    if (file_exists($path_qr)) {
                        $pdf->Image($path_qr, $mm_x_qr, $mm_y_qr, $w_qr_mm, $h_qr_mm);
                    }
                }
            }

            // Simpan kembali menimpa file draf PDF lama dengan yang sudah di-TTE
            $pdf->Output($path_file, 'F');

            // Hapus file sampah TTD sementara demi kebersihan server
            if (file_exists($temp_ttd_path)) {
                unlink($temp_ttd_path);
            }

        } catch (\Exception $e) {
            echo "<script>alert('Gagal menyisipkan TTE ke dokumen PDF: " . addslashes($e->getMessage()) . "'); window.location.href = '$halaman_utama';</script>";
            exit();
        }

        // 5. UPDATE DATABASE LOG & STATUS PROSES
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