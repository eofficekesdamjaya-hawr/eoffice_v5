<?php
session_start();
require_once '../config/koneksi.php';

// Validasi akses ketat
$akses_diizinkan = ['admin', 'setum', 'superadmin'];
$tipe_akses = strtolower($_SESSION['tipe_akses'] ?? '');

if (!isset($_SESSION['id_user']) || !in_array($tipe_akses, $akses_diizinkan)) {
    echo "<script>alert('Akses Ditolak! Hanya Admin dan Setum yang berwenang.'); window.location.href='../transaksi/kelola_surat.php';</script>";
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Proses Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_surat = htmlspecialchars(trim($_POST['no_surat']));
    
    $stmt = $conn->prepare("UPDATE surat_keluar SET no_surat = ? WHERE id_surat = ?");
    $stmt->bind_param("si", $no_surat, $id);
    
    if ($stmt->execute()) {
        // Log ke riwayat
        $log = $conn->prepare("INSERT INTO riwayat_surat (id_user, id_surat, jenis, status, created_at) VALUES (?, ?, 'edit_surat', 'Ubah Nomor Surat', NOW())");
        $log->bind_param("ii", $_SESSION['id_user'], $id);
        $log->execute();
        
        echo "<script>alert('Berhasil diperbarui'); window.location='../transaksi/kelola_surat.php';</script>";
    }
}

// Ambil data lama
$stmt = $conn->prepare("SELECT no_surat FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();


include '../layout/header.php';
?>

<div class="container py-4">
    <div class="card shadow-sm border-0" style="max-width: 500px; margin: auto;">
        <div class="card-header bg-primary text-white">Edit Nomor Surat</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Nomor Surat Resmi</label>
                    <input type="text" name="no_surat" class="form-control" value="<?= htmlspecialchars($data['no_surat'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="../transaksi/kelola_surat.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include '../layout/footer.php'; ?>
