<?php
session_start();
require_once '../config/koneksi.php';

// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

// ============================
// DATA USER LOGIN
// ============================
$id_user = $_SESSION['id_user'];

$error   = "";
$success = "";

// ============================
// PROSES UPDATE PASSWORD
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password_lama = trim($_POST['password_lama']);
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi    = trim($_POST['konfirmasi_password']);

    // ============================
    // AMBIL PASSWORD LAMA
    // ============================
    $stmt = $conn->prepare("
        SELECT password
        FROM users
        WHERE id_user = ?
        LIMIT 1
    ");

    if (!$stmt) {

        $error = "Query gagal : " . $conn->error;

    } else {

        $stmt->bind_param("i", $id_user);
        $stmt->execute();

        $result = $stmt->get_result();

        // ============================
        // VALIDASI USER
        // ============================
        if ($result->num_rows == 0) {

            $error = "User tidak ditemukan!";

        } else {

            $data = $result->fetch_assoc();

            // ============================
            // VALIDASI PASSWORD LAMA
            // ============================
            if ($password_lama !== $data['password']) {

                $error = "Password lama salah!";

            }
            // ============================
            // VALIDASI KONFIRMASI
            // ============================
            elseif ($password_baru !== $konfirmasi) {

                $error = "Konfirmasi password baru tidak cocok!";

            }
            // ============================
            // VALIDASI PANJANG PASSWORD
            // ============================
            elseif (strlen($password_baru) < 5) {

                $error = "Password baru minimal 5 karakter!";

            }
            // ============================
            // UPDATE PASSWORD
            // ============================
            else {

                $update = $conn->prepare("
                    UPDATE users
                    SET password = ?
                    WHERE id_user = ?
                ");

                if (!$update) {

                    $error = "Gagal prepare update : " . $conn->error;

                } else {

                    $update->bind_param(
                        "si",
                        $password_baru,
                        $id_user
                    );

                    if ($update->execute()) {

                        $success = "Password berhasil diperbarui!";

                    } else {

                        $error = "Gagal memperbarui password!";
                    }
                }
            }
        }
    }
}

include '../layout/header.php';
?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow border-0">

                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Ubah Password Jajaran
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($error) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert">
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert">
                            </button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <!-- PASSWORD LAMA -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Password Saat Ini
                            </label>

                            <input type="password"
                                   name="password_lama"
                                   class="form-control"
                                   placeholder="Masukkan password lama"
                                   required>
                        </div>

                        <hr>

                        <!-- PASSWORD BARU -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Password Baru
                            </label>

                            <input type="password"
                                   name="password_baru"
                                   class="form-control"
                                   placeholder="Minimal 5 karakter"
                                   required>
                        </div>

                        <!-- KONFIRMASI -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Konfirmasi Password Baru
                            </label>

                            <input type="password"
                                   name="konfirmasi_password"
                                   class="form-control"
                                   placeholder="Ulangi password baru"
                                   required>
                        </div>

                        <!-- BUTTON -->
                        <div class="d-grid gap-2">

                            <button type="submit"
                                    class="btn btn-primary rounded-pill">

                                <i class="fas fa-save me-2"></i>
                                Simpan Perubahan
                            </button>

                            <a href="dashboard_jajaran.php"
                               class="btn btn-light rounded-pill">

                                <i class="fas fa-arrow-left me-2"></i>
                                Kembali ke Dashboard
                            </a>

                        </div>

                    </form>

                </div>
            </div>

        </div>

    </div>

</div>

<?php include '../layout/footer.php'; ?>