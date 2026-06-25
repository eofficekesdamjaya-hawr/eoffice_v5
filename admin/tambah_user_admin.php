<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama      = trim($_POST['nama']);
    $email     = trim($_POST['email']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];
    $role      = trim($_POST['role']);

    // Cek email atau username sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM users_admin WHERE email='$email' OR username='$username'");
    if(mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Email atau username sudah digunakan!";
    } else {
        $hash_pass = password_hash($password, PASSWORD_DEFAULT);
        $q = mysqli_query($conn, "INSERT INTO users_admin (nama_lengkap,email,username,password,role) VALUES ('$nama','$email','$username','$hash_pass','$role')");
        if($q) {
            $_SESSION['sukses'] = "User admin berhasil ditambahkan!";
            header("Location: master_user_admin.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan user!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User Admin - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../layout/sidebar_admin.php'; ?>

<div class="main-content p-4">
    <h4 class="fw-bold mb-3">Tambah User Admin</h4>

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
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="master_user_admin.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
