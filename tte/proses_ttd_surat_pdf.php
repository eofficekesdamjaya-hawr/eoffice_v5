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
// 2. Ambil Posisi
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

if (empty($signatureData) || $canvasW <= 0) {
    echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
    exit;
}

// --------------------------
// 3. File Path
// --------------------------
$folderUpload = realpath(__DIR__ . "/../uploads/surat_keluar/") . "/";
$namaFileAsli = $surat['file_surat'];
$pathAsli     = $folderUpload . $namaFileAsli;

if (!file_exists($pathAsli)) {
    echo "<script>alert('File PDF asli tidak ada!'); window.history.back();</script>";
    exit;
}

$infoFile = pathinfo($namaFileAsli);
$namaFileBaru = $infoFile['filename'] . "_ttd.pdf";
$pathHasil = $folderUpload . $namaFileBaru;

// --------------------------
// 4. Load Library (Urutan Benar)
// --------------------------
require_once "../libraries/fpdf/fpdf.php";
require_once "../libraries/fpdf/autoload.php";

use setasign\Fpdi\Fpdi;

if (!class_exists('FPDF') || !class_exists('setasign\Fpdi\Fpdi')) {
    echo "<script>alert('Gagal memuat library PDF!'); window.history.back();</script>";
    exit;
}

// --------------------------
// 5. Proses PDF
// --------------------------
$pdf = new Fpdi();
$jumlahHalaman = $pdf->setSourceFile($pathAsli);
if ($jumlahHalaman < 1) {
    echo "<script>alert('PDF kosong/rusak!'); window.history.back();</script>";
    exit;
}

$halamanTerakhir = $jumlahHalaman;
$template = $pdf->importPage($halamanTerakhir);
$ukuran = $pdf->getTemplateSize($template);

$pdf->AddPage($ukuran['orientation'], [$ukuran['width'], $ukuran['height']]);
$pdf->useTemplate($template);

// Hitung skala yang akurat
$skala = $ukuran['width'] / $canvasW;


// --------------------------
// 6. Tanda Tangan
// --------------------------
$folderSementara = realpath(__DIR__ . "/../uploads/sementara/") . "/";
if (!file_exists($folderSementara)) mkdir($folderSementara, 0755, true);
$fileTtd = $folderSementara . "ttd_" . time() . ".png";

$ttdData = explode(',', $signatureData)[1];
file_put_contents($fileTtd, base64_decode($ttdData));

if (file_exists($fileTtd)) {
    $lebarTtd  = 150 * $skala;
    $tinggiTtd = 60 * $skala;
    
    // Perbaikan: Konversi koordinat ke skala PDF sebelum divalidasi
    $finalX_ttd = $posXttd * $skala;
    $finalY_ttd = $posYttd * $skala;
    
    if ($finalX_ttd >= 0 && $finalY_ttd >= 0) {
        $pdf->Image($fileTtd, $finalX_ttd, $finalY_ttd, $lebarTtd, $tinggiTtd, 'PNG');
    }
}

// --------------------------
// 7. Stempel
// --------------------------
$fileStempel = realpath(__DIR__ . "/../assets/stempel_kesdam1.png");
if (file_exists($fileStempel)) {
    $lebarStempel  = 140 * $skala;
    $tinggiStempel = 70 * $skala;
    
    $finalX_stempel = $posXstempel * $skala;
    $finalY_stempel = $posYstempel * $skala;
    
    if ($finalX_stempel >= 0 && $finalY_stempel >= 0) {
        $pdf->Image($fileStempel, $finalX_stempel, $finalY_stempel, $lebarStempel, $tinggiStempel, 'PNG');
    }
}

// --------------------------
// ✅ Perbaikan Utama: QR / Barkot
// --------------------------
$fileQr = realpath(__DIR__ . "/../assets/qr_dummy.png");
if (!file_exists($fileQr)) {
    $fileQr = realpath(__DIR__ . "/../assets/qr_dummy.jpg");
}

if (file_exists($fileQr)) {
    $lebarQr = 75 * $skala;
    $tinggiQr = 75 * $skala;

    $finalX_qr = $posXqr * $skala;
    $finalY_qr = $posYqr * $skala;

    if ($finalX_qr >= 0 && $finalY_qr >= 0) {
        $tipeQr = strtolower(pathinfo($fileQr, PATHINFO_EXTENSION));
        $tipeQr = ($tipeQr === 'jpg') ? 'JPG' : strtoupper($tipeQr);

        $pdf->Image($fileQr, $finalX_qr, $finalY_qr, $lebarQr, $tinggiQr, $tipeQr);
    }
} else {
    error_log("File QR tidak ditemukan di: " . $fileQr);
}
// --------------------------
// 8. Simpan & Update DB
// --------------------------
$pdf->Output('F', $pathHasil);
@unlink($fileTtd);

// Tentukan nama penandatangan berdasarkan email resmi
if ($user_email === 'kakesdamjaya2026@gmail.com') {
    $nama_penandatangan = "Komandan Kesdam Jaya";
} elseif ($user_email === 'wakakesdamjaya2026@gmail.com') {
    $nama_penandatangan = "Wakil Komandan Kesdam Jaya";
} elseif ($user_email === 'kasituud2026@gmail.com') {
    $nama_penandatangan = "Kasituud Kesdam Jaya"; // ✅ Diubah dari Kepala Staf Umum
} else {
    $nama_penandatangan = "Pejabat Tidak Dikenal"; // Fallback aman jika ada user lain tembus bypass
}

// Update database dengan kolom ttd_oleh yang bersih
$updateQuery = "UPDATE surat_keluar 
                SET file_surat = ?, 
                    status_tte = 'Selesai', 
                    penandatangan = ?, 
                    ttd_oleh = ?, 
                    tgl_tte = NOW(), 
                    status_proses = 'Selesai' 
                WHERE id_surat = ?";

$stmtUpdate = $conn->prepare($updateQuery);
// PERBAIKAN DI SINI: Mengubah "ssssi" menjadi "sssi" agar cocok dengan 4 variabel
$stmtUpdate->bind_param("sssi", $namaFileBaru, $nama_penandatangan, $user_email, $id_surat);
$stmtUpdate->execute();
$stmtUpdate->close();

// --------------------------
// 9. Selesai
// --------------------------
echo "<script>
    alert('✅ Tanda tangan, stempel & QR berhasil diterapkan!');
    window.location.href = '../transaksi/kelola_surat_keluar.php';
</script>";
exit;
?>
