<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi: Hanya Admin yang bisa akses
if ($_SESSION['role_key'] !== 'admin') {
    die("Akses ditolak!");
}

$users = $conn->query("SELECT * FROM users");
include '../layout/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-4">
        <h4 class="fw-bold"><i class="bi bi-people-fill text-dark me-2"></i>User Management</h4>
        <a href="tambah_user.php" class="btn btn-sm btn-dark">Tambah Staf</a>
    </div>

    <div class="card shadow-sm border-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['nama_lengkap'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><span class="badge bg-info"><?= $row['role_key'] ?></span></td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id_user'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="hapus_user.php?id=<?= $row['id_user'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../layout/footer.php'; ?>