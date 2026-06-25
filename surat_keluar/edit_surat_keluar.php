<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_surat = $_GET['id'] ?? 0;

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();

if (!$surat || ($surat['status_proses'] !== 'Baru' && $surat['status_proses'] !== 'Ditolak')) {
    echo "<script>alert('Surat sudah diproses Setum, tidak boleh diedit!'); window.location='kelola_surat_keluar.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $file_name = $surat['file_surat']; // Default pakai file lama

    // Jika user upload file baru
    if (!empty($_FILES['file_surat']['name'])) {
        $target_dir = "../uploads/surat_keluar/";
        $file = $_FILES['file_surat'];
        $file_name = "SRT_OUT_" . date('Ymd_His') . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file['name']);
        move_uploaded_file($file['tmp_name'], $target_dir . $file_name);
    }

    // Update data dengan mengubah kembali status_proses ke 'Baru' agar dicek ulang oleh Setum
    $stmtUpdate = $conn->prepare("
        UPDATE surat_keluar SET 
            kode_arsip = ?, bentuk_surat = ?, jenis_surat = ?, klasifikasi_surat = ?, 
            derajat_surat = ?, tujuan_disposisi = ?, tujuan_utama = ?, perihal = ?, 
            tembusan = ?, keterangan = ?, file_surat = ?, status_proses = 'Baru' 
        WHERE id_surat = ?
    ");
    $stmtUpdate->bind_param("sssssssssssi", 
        $_POST['kode'], $_POST['shapes_surat'], $_POST['jenis_surat'], $_POST['klasifikasi_surat'],
        $_POST['derajat_surat'], $_POST['tujuan_disposisi'], $_POST['tujuan_utama'], $_POST['perihal'],
        $_POST['tembusan'], $_POST['keterangan'], $file_name, $id_surat
    );

    if ($stmtUpdate->execute()) {
        // Log Perubahan
        $id_user = $_SESSION['id_user'];
        $conn->query("INSERT INTO riwayat_surat (id_user, id_surat, jenis, status, created_at) VALUES ($id_user, $id_surat, 'surat_keluar', 'diedit', NOW())");
        
        echo "<script>alert('Surat berhasil diperbarui dan diajukan ulang!'); window.location='kelola_surat_keluar.php';</script>";
        exit;
    }
}

require_once '../layout/header.php';
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Pengajuan Surat Keluar</h5>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Kode Arsip</label>
                    <input type="text" name="kode" class="form-value form-control" value="<?= htmlspecialchars($surat['kode_arsip']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Bentuk Surat</label>
                    <input type="text" name="shapes_surat" class="form-control" value="<?= htmlspecialchars($surat['bentuk_surat']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Jenis Surat</label>
                    <input type="text" name="jenis_surat" class="form-control" value="<?= htmlspecialchars($surat['jenis_surat']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Klasifikasi Surat</label>
                    <input type="text" name="klasifikasi_surat" class="form-control" value="<?= htmlspecialchars($surat['klasifikasi_surat']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Derajat Surat</label>
                    <input type="text" name="derajat_surat" class="form-control" value="<?= htmlspecialchars($surat['derajat_surat']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tujuan Disposisi</label>
                    <input type="text" name="tujuan_disposisi" class="form-control" value="<?= htmlspecialchars($surat['tujuan_disposisi']) ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Tujuan Utama</label>
                    <input type="text" name="tujuan_utama" class="form-control" value="<?= htmlspecialchars($surat['tujuan_utama']) ?>" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Perihal</label>
                    <textarea name="perihal" class="form-control" rows="3" required><?= htmlspecialchars($surat['perihal']) ?></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Tembusan</label>
                    <textarea name="tembusan" class="form-control" rows="2"><?= htmlspecialchars($surat['tembusan'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($surat['keterangan'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-bold">File Surat PDF <small class="text-danger">(Biarkan kosong jika tidak diganti)</small></label>
                    <input type="file" name="file_surat" class="form-control" accept="application/pdf">
                </div>
            </div>
            <div class="text-end mt-4">
                <a href="kelola_surat_keluar.php" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-success px-4">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
