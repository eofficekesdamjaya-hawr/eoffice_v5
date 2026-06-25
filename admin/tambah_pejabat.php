<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama      = trim($_POST['nama']);
    $jabatan   = trim($_POST['jabatan']);
    $unit      = trim($_POST['unit']);
    $email     = trim($_POST['email']);
    $no_hp     = trim($_POST['no_hp']);

    // Cek email unik
    $cek = mysqli_query($conn, "SELECT * FROM pejabat WHERE email='$email'");
    if(mysqli_num_rows($cek) > 0){
        $_SESSION['error'] = "Email sudah digunakan!";
    } else {
        $q = mysqli_query($conn, "INSERT INTO pejabat (nama_lengkap,jabatan,unit_kerja,email,no_hp) VALUES ('$nama','$jabatan','$unit','$email','$no_hp')");
        if($q){
            $_SESSION['sukses'] = "Data pejabat berhasil ditambahkan!";
            header("Location: master_pejabat.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan data!";
        }
    }
}
include '../layout/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pejabat - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>


<div class="main-content p-4">
    <h4 class="fw-bold mb-3">Tambah Pejabat</h4>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jabatan</label>
                <input type="text" name="jabatan" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Unit Kerja</label>
                <input type="text" name="unit" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-success">Simpan</button>
            <a href="master_pejabat.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
</body>
</html>
<?php include '../layout/footer.php'; ?>