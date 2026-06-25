<?php
require_once "../config/session.php";
require_once '../auth/auth_middleware.php';
requireLogin();
require_once '../config/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak! Halaman ini hanya untuk Admin.");
}

$data = mysqli_query($conn, "
    SELECT id, nama, email, role, status 
    FROM users 
    WHERE role = 'jajaran'
    ORDER BY id DESC
");

include '../layout/header.php';
?>

<div class="main-content p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Master User Jajaran</h4>
            <small class="text-muted">Kelola akun jajaran pengirim surat</small>
        </div>

        <a href="tambah_user.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Tambah User
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($data)): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="badge bg-info text-dark">
                            <?= strtoupper($row['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['status'] == 'aktif'): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id'] ?>" 
                           class="btn btn-primary btn-sm">Edit</a>

                        <a href="reset_password.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('Reset password ke default?')" 
                           class="btn btn-info btn-sm">Reset</a>

                        <a href="toggle_user.php?id=<?= $row['id'] ?>" 
                           class="btn btn-warning btn-sm">Toggle</a>

                        <a href="hapus_user.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('Hapus user ini?')" 
                           class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>

                </tbody>
            </table>

        </div>
    </div>

</div>

<?php include '../layout/footer.php'; ?>