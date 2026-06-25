<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

// Cek parameter ID
if (!isset($_GET['id'])) {
    header("Location: master_user_jajaran.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' LIMIT 1");

if (mysqli_num_rows($query) == 0) {
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($query);

// LOAD HEADER
include '../layout/header.php';
?>

<div class="main-content p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Edit User</h4>
        <a href="master_user_jajaran.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST" action="update_user.php">

                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control"
                           value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                        <option value="ruangan" <?= $user['role']=='ruangan'?'selected':'' ?>>Ruangan</option>
                        <option value="jajaran" <?= $user['role']=='jajaran'?'selected':'' ?>>Jajaran</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="aktif" <?= $user['status']=='aktif'?'selected':'' ?>>Aktif</option>
                        <option value="nonaktif" <?= $user['status']=='nonaktif'?'selected':'' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="master_user_jajaran.php" class="btn btn-light">Batal</a>
                </div>

            </form>

        </div>
    </div>

</div>

<?php 
// LOAD FOOTER
include '../layout/footer.php'; 
?>