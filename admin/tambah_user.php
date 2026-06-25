<?php
require_once "../config/session.php";
require_once '../auth/auth_middleware.php';
requireLogin();
require_once '../config/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak!");
}

include '../layout/header.php';
?>

<div class="main-content p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Tambah User Jajaran</h4>
        <a href="master_user_jajaran.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST" action="simpan_user.php">

                <input type="hidden" name="role" value="jajaran">

                <div class="mb-3">
                    <label class="form-label">Nama Jajaran</label>
                    <input type="text" name="nama" 
                           class="form-control" 
                           placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" 
                           class="form-control" 
                           placeholder="Masukkan email aktif" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" 
                           class="form-control" 
                           placeholder="Masukkan password" required>
                    <small class="text-muted">
                        Gunakan minimal 6 karakter
                    </small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-light">
                        Reset
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Simpan User
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<?php include '../layout/footer.php'; ?>