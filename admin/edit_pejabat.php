<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: master_pejabat.php");
    exit;
}

$id = intval($_GET['id']);
$q = mysqli_query($conn, "SELECT * FROM pejabat WHERE id='$id'");
if(mysqli_num_rows($q) == 0){
    $_SESSION['error'] = "Data pejabat tidak ditemukan!";
    header("Location: master_pejabat.php");
    exit;
}
$pejabat = mysqli_fetch_assoc($q);

if(isset($_POST['submit'])){
    $nama    = trim($_POST['nama']);
    $jabatan = trim($_POST['jabatan']);
    $unit    = trim($_POST['unit']);
    $email   = trim($_POST['email']);
    $no_hp   = trim($_POST['no_hp']);

    // cek email unik selain sendiri
    $cek = mysqli_query($conn, "SELECT * FROM pejabat WHERE email='$email' AND id != '$id'");
    if(mysqli_num_rows($cek) > 0){
        $_SESSION['error'] = "Email sudah digunakan!";
    } else {
        $update = mysqli_query($conn, "UPDATE pejabat SET nama_lengkap='$nama', jabatan='$jabatan', unit_kerja='$unit', email='$email', no_hp='$no_hp' WHERE id='$id'");
        if($update){
            $_SESSION['sukses'] = "Data pejabat berhasil diupdate!";
            header("Location: master_pejabat.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal mengupdate data!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pejabat - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../layout/sidebar_admin.php'; ?>

<div class="main-content p-4">
    <h4 class="fw-bold mb-3">Edit Pejabat</h4>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($pejabat['nama_lengkap']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($pejabat['jabatan']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Unit Kerja</label>
                <input type="text" name="unit" class="form-control" value="<?= htmlspecialchars($pejabat['unit_kerja']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($pejabat['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($pejabat['no_hp']); ?>" required>
            </div>
            <button type="submit" name="submit" class="btn btn-success">Update</button>
            <a href="master_pejabat.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
</body>
</html>
