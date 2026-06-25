<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ===============================
// PROTEKSI LOGIN & ROLE
// ===============================
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak! Halaman ini hanya untuk Admin.");
}

// ===============================
// AMBIL DATA USER RUANGAN
// ===============================
$data = mysqli_query($conn, "
    SELECT id, nama, email, role, status 
    FROM users 
    WHERE role = 'ruangan'
    ORDER BY id DESC
");
include '../layout/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master User Internal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h4 class="fw-bold">Master User Internal (Ruangan)</h4>
    <p class="text-muted small">Kelola akun ruangan/internal</p>
    <hr>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
        + Tambah User Ruangan
    </button>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Ruangan</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="250">Aksi</th>
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
                            <span class="badge bg-primary"><?= strtoupper($row['role']) ?></span>
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

<!-- MODAL TAMBAH USER RUANGAN -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <form method="POST" action="simpan_user.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah User Ruangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <input type="text" name="nama" 
               class="form-control mb-2" 
               placeholder="Nama Ruangan" required>

        <input type="email" name="email" 
               class="form-control mb-2" 
               placeholder="Email" required>

        <input type="password" name="password" 
               class="form-control mb-2" 
               placeholder="Password" required>

        <input type="hidden" name="role" value="ruangan">

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php include '../layout/footer.php'; ?>