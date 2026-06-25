<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

$id_user      = $_SESSION['id_user'];
$email_user   = $_SESSION['email'];
$message      = "";

// ============================
// PROSES UBAH PASSWORD
// ============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $password_lama = trim($_POST['password_lama']);
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi    = trim($_POST['konfirmasi']);

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Validasi Password Lama (Plain Text sesuai permintaan)
        if ($password_lama !== $user['password']) {
            $message = "<div class='alert alert-danger alert-dismissible fade show small'>
                            <i class='fas fa-times-circle me-2'></i>Password lama salah!
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } elseif ($password_baru !== $konfirmasi) {
            $message = "<div class='alert alert-warning alert-dismissible fade show small'>
                            <i class='fas fa-exclamation-triangle me-2'></i>Konfirmasi password tidak cocok!
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } elseif (strlen($password_baru) < 6) {
            $message = "<div class='alert alert-warning alert-dismissible fade show small'>
                            <i class='fas fa-info-circle me-2'></i>Password minimal 6 karakter!
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } else {
            // Update password baru
            $stmtUpdate = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmtUpdate->bind_param("si", $password_baru, $id_user);

            if ($stmtUpdate->execute()) {
                $message = "<div class='alert alert-success alert-dismissible fade show small'>
                                <i class='fas fa-check-circle me-2'></i>Password berhasil diubah!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            } else {
                $message = "<div class='alert alert-danger alert-dismissible fade show small'>
                                Gagal memperbarui password!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
            }
        }
    }
}

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .input-group-text {
        cursor: pointer;
        background-color: #f8f9fa;
    }
    .card-password {
        border-radius: 15px;
        border: none;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            
            <div class="card shadow-lg card-password">
                <div class="card-body p-4">

                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-key me-2 text-primary"></i>
                        Ubah Password
                    </h4>

                    <p class="text-muted small">
                        Akun: <strong><?= htmlspecialchars($email_user) ?></strong>
                    </p>

                    <hr>

                    <?= $message ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password Lama</label>
                            <div class="input-group">
                                <input type="password" name="password_lama" id="pass1" class="form-control" required>
                                <span class="input-group-text" onclick="togglePass('pass1', 'icon1')">
                                    <i class="fas fa-eye text-muted" id="icon1"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="password_baru" id="pass2" class="form-control" placeholder="Min. 6 Karakter" required>
                                <span class="input-group-text" onclick="togglePass('pass2', 'icon2')">
                                    <i class="fas fa-eye text-muted" id="icon2"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" name="konfirmasi" id="pass3" class="form-control" required>
                                <span class="input-group-text" onclick="togglePass('pass3', 'icon3')">
                                    <i class="fas fa-eye text-muted" id="icon3"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="dashboard_satuan_lain.php" class="btn btn-outline-secondary px-4 rounded-pill">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>

                            <button type="submit" class="btn btn-primary px-4 rounded-pill">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>

<?php include '../layout/footer.php'; ?>