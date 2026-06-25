<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi
$akses_diizinkan = ['admin', 'setum', 'superadmin'];
$tipe = strtolower($_SESSION['tipe_akses'] ?? '');

if (!isset($_SESSION['id_user']) || !in_array($tipe, $akses_diizinkan)) {
    die("Akses Ditolak!");
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_kirim = $_POST['tanggal_kirim'];
    $stmt = $conn->prepare("UPDATE surat_keluar SET tanggal_kirim = ? WHERE id_surat = ?");
    $stmt->bind_param("si", $tgl_kirim, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Tanggal kirim berhasil diupdate'); window.location='kelola_surat.php';</script>";
    }
}

$stmt = $conn->prepare("SELECT tanggal_kirim FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

include '../layout/header.php';
?>
<div class="container py-4">
    <div class="card shadow-sm border-0" style="max-width: 500px; margin: auto;">
        <div class="card-header bg-warning text-dark">Edit Tanggal Kirim</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Tanggal Kirim</label>
                    <input type="date" name="tanggal_kirim" class="form-control" value="<?= $data['tanggal_kirim'] ?? date('Y-m-d') ?>" required>
                </div>
                <button type="submit" class="btn btn-warning">Simpan Tanggal</button>
                <a href="kelola_surat.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
<?php include '../layout/footer.php'; ?>
