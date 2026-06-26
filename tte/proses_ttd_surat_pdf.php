<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// --------------------------
// 1. Validasi Akses & Data
// --------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../transaksi/kelola_surat_keluar.php");
    exit;
}

$id_surat    = isset($_POST['id_surat']) ? intval($_POST['id_surat']) : 0;
$jenis_tabel = isset($_POST['jenis_tabel']) ? trim($_POST['jenis_tabel']) : '';

if ($id_surat <= 0 || $jenis_tabel !== 'keluar') {
    echo "<script>alert('Data tidak valid!'); window.history.back();</script>";
    exit;
}

// ✅ Akses untuk 3 pengguna yang berwenang
$user_email = $_SESSION['email'] ?? '';
$akses_diizinkan = [
    'kakesdamjaya2026@gmail.com',
    'wakakesdamjaya2026@gmail.com',
    'kasituud2026@gmail.com'
];

if (!in_array($user_email, $akses_diizinkan)) {
    echo "<script>alert('Anda tidak memiliki otoritas untuk menandatangani surat ini!'); window.history.back();</script>";
    exit;
}

// Ambil data surat dari database
$query = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$surat || empty($surat['file_surat'])) {
    echo "<script>alert('Data surat tidak ditemukan!'); window.history.back();</script>";
    exit;
}

// --------------------------
// 2. Ambil Data Posisi & Gambar
// --------------------------
$signatureData = $_POST['signature_data'] ?? '';
$posXttd       = isset($_POST['pos_x_ttd']) ? floatval($_POST['pos_x_ttd']) : 0;
$posYttd       = isset($_POST['pos_y_ttd']) ? floatval($_POST['pos_y_ttd']) : 0;
$posXstempel   = isset($_POST['pos_x_stempel']) ? floatval($_POST['pos_x_stempel']) : 0;
$posYstempel   = isset($_POST['pos_y_stempel']) ? floatval($_POST['pos_y_stempel']) : 0;
$posXqr        = isset($_POST['pos_x_qr']) ? floatval($_POST['pos_x_qr']) : 0;
$posYqr        = isset($_POST['pos_y_qr']) ? floatval($_POST['pos_y_qr']) : 0;
$canvasW       = isset($_POST['canvas_width']) ? floatval($_POST['canvas_width']) : 0;
$canvasH       = isset($_POST['canvas_height']) ? floatval($_POST['canvas_height']) : 0;

if (empty($signatureData) || $canvasW <= 0 || $canvasH <= 0) {
    echo "<script>alert('Data posisi atau tanda tangan tidak lengkap!'); window.history.back();</script>";
    exit;
}

// --------------------------
// 3. Konfigurasi File & Folder
// --------------------------
$folderUpload = "../uploads/surat_keluar/";
$namaFileAsli = $surat['file_surat'];
$pathAsli     = $folderUpload . $namaFileAsli;

if (!file_exists($pathAsli)) {
    echo "<script>alert('File PDF asli tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$infoFile = pathinfo($namaFileAsli);
$namaFileBaru = $infoFile['filename'] . "_ttd.pdf";
$pathHasil = $folderUpload . $namaFileBaru;

// --------------------------
// 4. ✅ MUAT LIBRARY DENGAN URUTAN BENAR
// --------------------------
require_once "../libraries/fpdf/fpdf.php";
require_once "../libraries/fpdf/autoload.php";

use setasign\Fpdi\Fpdi;

if (!class_exists('FPDF') || !class_exists('setasign\Fpdi\Fpdi')) {
    echo "<script>alert('Gagal memuat library PDF!'); window.history.back();</script>";
    exit;
}

$pdf = new Fpdi();

$jumlahHalaman = $pdf->setSourceFile($pathAsli);
if ($jumlahHalaman < 1) {
    echo "<script>alert('File PDF kosong atau rusak!'); window.history.back();</script>";
    exit;
}

$nomorHalaman = $jumlahHalaman;
$templateId = $pdf->importPage($nomorHalaman);
$ukuranHalaman = $pdf->getTemplateSize($templateId);

$pdf->AddPage($ukuranHalaman['orientation'], [$ukuranHalaman['width'], $ukuranHalaman['height']]);
$pdf->useTemplate($templateId);

$skala = $ukuranHalaman['width'] / $canvasW;

// --------------------------
// 5. Proses & Sisipkan Gambar
// --------------------------
$folderSementara = "../uploads/sementara/";
if (!file_exists($folderSementara)) mkdir($folderSementara, 0755, true);
$fileTtd = $folderSementara . "ttd_" . time() . ".png";

$ttdData = explode(',', $signatureData)[1];
file_put_contents($fileTtd, base64_decode($ttdData));

// Sisipkan Tanda Tangan
$lebarTtd = 150 * $skala;
$tinggiTtd = 60 * $skala;
$pdf->Image($fileTtd, $posXttd * $skala, $posYttd * $skala, $lebarTtd, $tinggiTtd);

// Sisipkan Stempel
$fileStempel = "../assets/stempel_kesdam1.png";
if (file_exists($fileStempel)) {
    $lebarStempel = 140 * $skala;
    $tinggiStempel = 70 * $skala;
    $pdf->Image($fileStempel, $posXstempel * $skala, $posYstempel * $skala, $lebarStempel, $tinggiStempel);
}

// Sisipkan QR Code
$fileQr = "../assets/qr_dummy.png";
if (file_exists($fileQr)) {
    $ukuranQr = 75 * $skala;
    $pdf->Image($fileQr, $posXqr * $skala, $posYqr * $skala, $ukuranQr, $ukuranQr);
}

// --------------------------
// 6. Simpan File & UPDATE DATABASE SESUAI KOLOM YANG ADA
// --------------------------
$pdf->Output('F', $pathHasil);
@unlink($fileTtd);

// Tentukan nama penandatangan sesuai email login
if ($user_email === 'kakesdamjaya2026@gmail.com') {
    $nama_penandatangan = "Komandan Kesdam Jaya";
} elseif ($user_email === 'wakakesdamjaya2026@gmail.com') {
    $nama_penandatangan = "Wakil Komandan Kesdam Jaya";
} else {
    $nama_penandatangan = "Kepala Staf Umum";
}

// Update ke kolom yang benar-benar ada di tabel
$updateQuery = "UPDATE surat_keluar 
                SET file_surat = ?, 
                    status_tte = 'Selesai', 
                    penandatangan = ?, 
                    tgl_tte = NOW(), 
                    status_proses = 'Selesai' 
                WHERE id_surat = ?";

$stmtUpdate = $conn->prepare($updateQuery);
$stmtUpdate->bind_param("ssi", $namaFileBaru, $nama_penandatangan, $id_surat);
$stmtUpdate->execute();
$stmtUpdate->close();

// --------------------------
// 7. Selesai
// --------------------------
echo "<script>
    alert('✅ Tanda tangan berhasil diterapkan!');
    window.location.href = '../transaksi/kelola_surat_keluar.php';
</script>";
exit;
?>
