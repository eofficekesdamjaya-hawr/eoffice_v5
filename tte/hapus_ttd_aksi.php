<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Validasi akses
$user_email = $_SESSION['email'] ?? '';
$akses_diizinkan = [
    'kakesdamjaya2026@gmail.com',
    'wakakesdamjaya2026@gmail.com',
    'kasituud2026@gmail.com'
];

if (!in_array($user_email, $akses_diizinkan)) {
    echo "<script>alert('Akses ditolak!'); window.history.back();</script>";
    exit;
}

// Validasi parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jenis = isset($_GET['jenis']) ? trim($_GET['jenis']) : '';

if ($id <= 0 || $jenis !== 'keluar') {
    echo "<script>alert('Data tidak valid!'); window.history.back();</script>";
    exit;
}

// Ambil data surat
$query = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($data) {
    $nama_file = $data['file_surat'];
    $folder = realpath(__DIR__ . "/../uploads/surat_keluar/") . "/";

    // --------------------------
    // 1. Hapus file yang sudah ditandatangani (*_ttd.pdf)
    // --------------------------
    if (strpos($nama_file, '_ttd.pdf') !== false) {
        // Jika saat ini menyimpan file versi ttd, hapus filenya
        $file_hapus = $folder . $nama_file;
        if (file_exists($file_hapus)) {
            @unlink($file_hapus);
        }

        // Ambil nama file asli tanpa akhiran _ttd
        $info = pathinfo($nama_file);
        $nama_file_asli = $info['filename'] . ".pdf";

        // Pastikan file asli masih ada
        if (!file_exists($folder . $nama_file_asli)) {
            echo "<script>alert('File asli surat tidak ditemukan, tidak bisa mengulang!'); window.history.back();</script>";
            exit;
        }
    } else {
        // Jika sudah file asli, tetap gunakan
        $nama_file_asli = $nama_file;
    }

    // --------------------------
    // 2. ✅ Kembalikan SEMUA status ke kondisi awal
    // Sesuai kolom yang ada di tabel surat_keluar
    // --------------------------
    $update = "UPDATE surat_keluar 
               SET file_surat = ?,
                   status_proses = 'Baru',
                   status_tte = 'Menunggu',
                   penandatangan = NULL,
                   tgl_tte = NULL
               WHERE id_surat = ?";

    $stmtUpd = $conn->prepare($update);
    $stmtUpd->bind_param("si", $nama_file_asli, $id);
    $stmtUpd->execute();
    $stmtUpd->close();
}

echo "<script>
    alert('✅ Status dikembalikan! Anda bisa menandatangani ulang sekarang.');
    window.location.href = '../transaksi/kelola_surat_keluar.php';
</script>";
exit;
?>
