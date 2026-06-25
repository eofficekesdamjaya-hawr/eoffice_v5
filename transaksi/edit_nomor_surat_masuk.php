<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once "../config/session.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role_key'], ['admin', 'superadmin', 'setum'])) {
    header("Location: ../index.php");
    exit();
}

$id_surat = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// Proses Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_agenda = mysqli_real_escape_string($conn, $_POST['no_agenda']);
    $no_surat  = mysqli_real_escape_string($conn, $_POST['no_surat']);
    
    $stmt = $conn->prepare("UPDATE surat_masuk SET no_agenda = ?, no_surat = ? WHERE id_surat = ?");
    $stmt->bind_param("ssi", $no_agenda, $no_surat, $id_surat);
    
    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location.href='kelola_surat.php';</script>";
    } else {
        $error = "Gagal memperbarui data.";
    }
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM surat_masuk WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

include __DIR__ . '/../layout/header.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm p-4" style="max-width: 600px; margin: auto;">
            <h4 class="fw-bold text-primary mb-4"><i class="bi bi-pencil-square"></i> Edit Data Surat Masuk</h4>
            
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Agenda</label>
                    <input type="text" name="no_agenda" class="form-control" value="<?= htmlspecialchars($data['no_agenda']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Surat Resmi</label>
                    <input type="text" name="no_surat" class="form-control" value="<?= htmlspecialchars($data['no_surat']) ?>" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    <a href="kelola_surat.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
