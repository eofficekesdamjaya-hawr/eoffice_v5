<?php
session_start();
include '../config/koneksi.php';

// WAJIB LOGIN
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$id_user = $_SESSION['id'];
$pesan = "";

if (isset($_POST['submit'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi    = $_POST['konfirmasi'];

    // Ambil password lama dari tabel users
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();

    if (!$data) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show'><i class='bi bi-exclamation-octagon me-2'></i>User tidak ditemukan!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } 
    elseif ($password_lama !== $data['password']) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show'><i class='bi bi-x-circle me-2'></i>Password lama salah!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } 
    elseif ($password_baru !== $konfirmasi) {
        $pesan = "<div class='alert alert-warning alert-dismissible fade show'><i class='bi bi-info-circle me-2'></i>Konfirmasi password tidak cocok!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } 
    else {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password_baru, $id_user);
        $stmt->execute();
        $pesan = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle me-2'></i>Password berhasil diubah!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// LOAD HEADER
include '../layout/header.php';
?>

<div class="main-content p-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center mb-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-key-fill me-2 text-success"></i>Pengaturan Akun</h4>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-dark">Ubah Password</h6>
                </div>
                <div class="card-body p-4">

                    <?= $pesan ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password Lama</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password saat ini" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password baru" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="dashboard_admin.php" class="btn btn-light px-4">Batal</a>
                            <button type="submit" name="submit" class="btn btn-success px-4">
                                <i class="bi bi-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded border">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> <strong>Tips Keamanan:</strong> Gunakan kombinasi huruf dan angka agar password Anda sulit ditebak oleh orang lain.
                </small>
            </div>
        </div>
    </div>
</div>

<?php 
// LOAD FOOTER
include '../layout/footer.php'; 
?>