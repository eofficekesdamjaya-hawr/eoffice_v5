<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id_user  = $_SESSION['id_user'];
$id_surat = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Ambil data lama & Pastikan surat milik user ini dan masih Pending
$query = "SELECT * FROM surat_masuk WHERE id_surat = ? AND id_user = ? AND status_proses = 'Pending'";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_surat, $id_user);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan atau sudah diproses sehingga tidak bisa diedit.'); window.location='surat_terkirim_jajaran.php';</script>";
    exit;
}

// 2. Proses Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $no_surat    = htmlspecialchars($_POST['no_surat']);
    $perihal     = htmlspecialchars($_POST['perihal']);
    $kepada      = htmlspecialchars($_POST['kepada']);
    $keterangan  = htmlspecialchars($_POST['keterangan']);
    
    // Logika Ganti File (Jika ada file baru diupload)
    $file_sql = "";
    $new_name = $data['file_surat']; // Default pakai file lama

    if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === 0) {
        $allowed = ['pdf','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['file_surat']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Hapus file lama
            if (file_exists("../uploads/" . $data['file_surat'])) {
                unlink("../uploads/" . $data['file_surat']);
            }
            // Upload file baru
            $new_name = "JAJ_EDIT_" . time() . "_" . uniqid() . "." . $ext;
            move_uploaded_file($_FILES['file_surat']['tmp_name'], "../uploads/" . $new_name);
        }
    }

    $update = $conn->prepare("UPDATE surat_masuk SET no_surat=?, perihal=?, kepada=?, keterangan=?, file_surat=? WHERE id_surat=? AND id_user=?");
    $update->bind_param("sssssii", $no_surat, $perihal, $kepada, $keterangan, $new_name, $id_surat, $id_user);

    if ($update->execute()) {
        echo "<script>alert('Surat berhasil diperbarui!'); window.location='surat_terkirim_jajaran.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update: " . $conn->error . "</div>";
    }
}

include '../layout/header.php';
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-warning py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Surat Terkirim</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nomor Surat</label>
                        <input type="text" name="no_surat" class="form-control" value="<?= $data['no_surat'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tujuan / Kepada</label>
                        <select name="kepada" class="form-select" required>
                            <option><?= $data['kepada'] ?></option>
                            <option disabled>──────────</option>
                            <option>Kakesdam Jaya</option>
                            <option>Waka Kesdam Jaya</option>
                            <option>Kasi Tuud</option>
                            <option>Kasi Matkes</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Perihal</label>
                    <textarea name="perihal" class="form-control" rows="3" required><?= $data['perihal'] ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control"><?= $data['keterangan'] ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Ganti Lampiran File (Kosongkan jika tidak ganti)</label>
                    <input type="file" name="file_surat" class="form-control">
                    <small class="text-muted">File saat ini: <a href="../uploads/<?= $data['file_surat'] ?>" target="_blank"><?= $data['file_surat'] ?></a></small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Update Surat</button>
                    <a href="surat_terkirim_jajaran.php" class="btn btn-outline-secondary px-4 rounded-pill">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>