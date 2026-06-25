<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];

// =====================
// PROSES HAPUS USER
// =====================
if (isset($_GET['hapus_id'])) {
    $hapus_id = intval($_GET['hapus_id']);
    $del = mysqli_query($conn, "DELETE FROM users_admin WHERE id='$hapus_id'");
    if ($del) {
        $_SESSION['sukses'] = "User berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus user!";
    }
    header("Location: master_user_admin.php");
    exit;
}

// Ambil data semua admin
$q = mysqli_query($conn, "SELECT * FROM users_admin ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master User Admin - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<?php include '../layout/sidebar_admin.php'; ?>

<div class="main-content p-4">
    <h4 class="fw-bold mb-3">Master User Admin</h4>

    <?php if(isset($_SESSION['sukses'])): ?>
        <div class="alert alert-success"><?= $_SESSION['sukses']; unset($_SESSION['sukses']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="tambah_user_admin.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Tambah User</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($q)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['role']); ?></td>
                        <td>
                            <a href="edit_user_admin.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                            <a href="master_user_admin.php?hapus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($q) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data user admin.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
