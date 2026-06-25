<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ============================
// CEK ID SURAT
// ============================
if (!isset($_GET['id'])) {
    header("Location: riwayat_terkirim_satuan_lain.php");
    exit;
}

$id_surat = intval($_GET['id']);

// ============================
// AMBIL DATA SURAT
// ============================
$stmt = $conn->prepare("
    SELECT * FROM surat_masuk
    WHERE id_surat = ?
    AND id_user = ?
    AND role_pengirim = 'satuan_lain'
");

$stmt->bind_param("ii", $id_surat, $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Surat tidak ditemukan');window.location='riwayat_terkirim_satuan_lain.php';</script>";
    exit;
}

$data = $result->fetch_assoc();

// hanya boleh edit jika status pending
if ($data['status_proses'] !== 'Pending') {
    echo "<script>alert('Surat tidak bisa diedit karena sudah diproses');window.location='riwayat_terkirim_satuan_lain.php';</script>";
    exit;
}

// ============================
// PROSES UPDATE
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $no_surat  = $_POST['no_surat'];
    $perihal   = $_POST['perihal'];
    $kepada    = $_POST['kepada'];
    $tembusan  = $_POST['tembusan'];

    $file_lama = $data['file_surat'];
    $file_baru = $file_lama;

    // cek upload file baru
    if (!empty($_FILES['file_surat']['name'])) {

        $nama_file = time() . "_" . basename($_FILES['file_surat']['name']);
        $target    = "../uploads/" . $nama_file;

        if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $target)) {
            $file_baru = $nama_file;
        }
    }

    // update database
    $update = $conn->prepare("
        UPDATE surat_masuk SET
        no_surat=?,
        perihal=?,
        kepada=?,
        tembusan=?,
        file_surat=?
        WHERE id_surat=?
    ");

    $update->bind_param(
        "sssssi",
        $no_surat,
        $perihal,
        $kepada,
        $tembusan,
        $file_baru,
        $id_surat
    );

    if ($update->execute()) {

        echo "<script>
        alert('Surat berhasil diperbarui');
        window.location='riwayat_terkirim_satuan_lain.php';
        </script>";

        exit;
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui surat</div>";
    }
}

include '../layout/header.php';
?>

<div class="container py-4">

<h4 class="fw-bold mb-3">
<i class="fas fa-edit me-2 text-warning"></i>
Edit Surat
</h4>

<a href="riwayat_terkirim_satuan_lain.php" class="btn btn-secondary mb-3">
← Kembali
</a>

<div class="card shadow-sm border-0 rounded-4">
<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label class="form-label fw-bold">Tujuan Surat</label>
<input type="text" name="kepada" class="form-control"
value="<?= htmlspecialchars($data['kepada']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label fw-bold">Nomor Surat</label>
<input type="text" name="no_surat" class="form-control"
value="<?= htmlspecialchars($data['no_surat']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label fw-bold">Perihal</label>
<input type="text" name="perihal" class="form-control"
value="<?= htmlspecialchars($data['perihal']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label fw-bold">Tembusan</label>

<textarea name="tembusan" class="form-control" rows="6"><?php
if(!empty($data['tembusan'])){
    echo htmlspecialchars($data['tembusan']);
}else{
    for($i=1;$i<=5;$i++){
        echo $i.". \n";
    }
}
?></textarea>

</div>

<div class="mb-3">

<label class="form-label fw-bold">File Surat</label>

<?php if(!empty($data['file_surat'])): ?>

<div class="mb-2">
<a href="../uploads/<?= $data['file_surat'] ?>" target="_blank"
class="btn btn-sm btn-outline-primary">
<i class="fas fa-eye"></i> Lihat File
</a>
</div>

<?php endif; ?>

<input type="file" name="file_surat" class="form-control">

<small class="text-muted">
Upload file baru jika ingin mengganti file surat
</small>

</div>

<button type="submit" class="btn btn-warning">
<i class="fas fa-save me-1"></i> Simpan Perubahan
</button>

</form>

</div>
</div>

</div>

<?php include '../layout/footer.php'; ?>