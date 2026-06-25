<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi: Hanya Admin/Setum yang boleh akses
$akses_diizinkan = ['admin', 'setum', 'superadmin'];
$tipe = strtolower($_SESSION['tipe_akses'] ?? '');

if (!isset($_SESSION['id_user']) || !in_array($tipe, $akses_diizinkan)) {
    die("Akses Ditolak! Hubungi Admin.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl = $_POST['tanggal_surat'];
    $stmt = $conn->prepare("UPDATE surat_keluar SET tanggal_surat = ? WHERE id_surat = ?");
    $stmt->bind_param("si", $tgl, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Tanggal surat berhasil diupdate'); window.location='kelola_surat.php';</script>";
    }
}

$stmt = $conn->prepare("SELECT tanggal_surat FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

include '../layout/header.php';
?>
<div class="container py-4">
    <div class="card shadow-sm border-0" style="max-width: 500px; margin: auto;">
        <div class="card-header bg-success text-white">Edit Tanggal Surat</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Tanggal Surat keluar</label>
                    <input type="date" name="tanggal_surat" class="form-control" value="<?= $data['tanggal_surat'] ?? date('Y-m-d') ?>" required>
                </div>
                <button type="submit" class="btn btn-success">Simpan Tanggal</button>
                <a href="kelola_surat.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
<?php include '../layout/footer.php'; ?>
