<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: master_user_admin.php");
    exit;
}

$id = intval($_GET['id']);
$q = mysqli_query($conn, "SELECT * FROM users_admin WHERE id='$id'");
if(mysqli_num_rows($q) == 0){
    $_SESSION['error'] = "User tidak ditemukan!";
    header("Location: master_user_admin.php");
    exit;
}
$user = mysqli_fetch_assoc($q);

if(isset($_POST['submit'])){
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role     = trim($_POST['role']);
    $password = $_POST['password'];

    // Cek email/username unik
    $cek = mysqli_query($conn, "SELECT * FROM users_admin WHERE (email='$email' OR username='$username') AND id != '$id'");
    if(mysqli_num_rows($cek) > 0){
        $_SESSION['error'] = "Email atau username sudah digunakan!";
    } else {
        if(!empty($password)){
            $hash_pass = password_hash($password, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE users_admin SET nama_lengkap='$nama', email='$email', username='$username', password='$hash_pass', role='$role' WHERE id='$id'");
        } else {
            $update = mysqli_query($conn, "UPDATE users_admin SET nama_lengkap='$nama', email='$email', username='$username', role='$role' WHERE id='$id'");
        }
        if($update){
            $_SESSION['sukses'] = "User admin berhasil diupdate!";
            header("Location: master_user_admin.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal mengupdate user!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User Admin - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../layout/sidebar_admin.php'; ?>

<div class="main-content p-4">
    <h4 class="fw-bold mb-3">Edit User Admin</h4>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    <option value="superadmin" <?= $user['role']=='superadmin'?'selected':'' ?>>Superadmin</option>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Update</button>
            <a href="master_user_admin.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
