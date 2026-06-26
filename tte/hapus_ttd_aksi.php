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

// Ambil data surat saat ini
$query = "SELECT file_surat FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($data) {
    $nama_file_sekarang = trim($data['file_surat']);
    $folder = realpath(__DIR__ . "/../uploads/surat_keluar/") . "/";

    // --------------------------
    // 1. Jika nama file berakhiran _ttd.pdf:
    // - Hapus file tersebut
    // - Ubah nama kembali ke versi asli
    // --------------------------
    if (strpos($nama_file_sekarang, '_ttd.pdf') !== false) {
        // Hapus file hasil tanda tangan
        $file_hapus = $folder . $nama_file_sekarang;
        if (file_exists($file_hapus)) {
            @unlink($file_hapus);
        }

        // Ambil nama asli tanpa akhiran _ttd
        $info = pathinfo($nama_file_sekarang);
        $nama_file_asli = str_replace('_ttd', '', $info['filename']) . '.' . $info['extension'];

        // Pastikan file asli benar-benar ada
        if (!file_exists($folder . $nama_file_asli)) {
            echo "<script>alert('⚠️ File asli surat tidak ditemukan! Tidak bisa mengulang.'); window.history.back();</script>";
            exit;
        }
    } else {
        // Jika sudah versi asli, gunakan apa adanya
        $nama_file_asli = $nama_file_sekarang;
    }

    // --------------------------
    // 2. Update database: kembalikan semua status & nama file
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
    alert('✅ Berhasil dikembalikan! Silakan buka kembali untuk tanda tangan ulang.');
    window.location.href = '../transaksi/kelola_surat_keluar.php';
</script>";
exit;
?>
