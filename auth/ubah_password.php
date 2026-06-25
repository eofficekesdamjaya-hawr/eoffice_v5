<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/*
==========================================================================
VALIDASI SESSION LOGIN (DIPERLONGGAR UNTUK SEMUA ROLE)
==========================================================================
*/
// Kita hanya memastikan user sudah login dengan memeriksa id_user
if (empty($_SESSION['id_user'])) {
    session_destroy();
    header("Location: ../auth/login.php"); // Sesuaikan dengan file login utama Anda
    exit();
}

$id_user = (int) $_SESSION['id_user'];
$message = ""; 

/*
==========================================================================
PROSES PERUBAHAN PASSWORD
==========================================================================
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_lama = trim($_POST['password_lama']);
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi    = trim($_POST['konfirmasi']);

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
        $message = "<div class='alert alert-danger shadow-sm'><i class='fas fa-exclamation-triangle me-2'></i> Semua kolom wajib diisi!</div>";
    } elseif (strlen($password_baru) < 6) {
        $message = "<div class='alert alert-warning shadow-sm'><i class='fas fa-info-circle me-2'></i> Password baru minimal harus 6 karakter!</div>";
    } elseif ($password_baru !== $konfirmasi) {
        $message = "<div class='alert alert-warning shadow-sm'><i class='fas fa-exclamation-circle me-2'></i> Konfirmasi password baru tidak cocok!</div>";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id_user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password_lama, $user['password']) || $password_lama === $user['password']) {
                $password_baru_hashed = password_hash($password_baru, PASSWORD_BCRYPT);
                
                $stmtUpdate = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtUpdate->bind_param("si", $password_baru_hashed, $id_user);

                if ($stmtUpdate->execute()) {
                    $message = "<div class='alert alert-success shadow-sm'><i class='fas fa-check-circle me-2'></i> Password berhasil diubah!</div>";
                } else {
                    $message = "<div class='alert alert-danger shadow-sm'><i class='fas fa-times-circle me-2'></i> Gagal memperbarui database.</div>";
                }
                $stmtUpdate->close();
            } else {
                $message = "<div class='alert alert-danger shadow-sm'><i class='fas fa-times-circle me-2'></i> Password lama salah!</div>";
            }
        }
        $stmt->close();
    }
}

require_once "../layout/header.php";
?>

<div class="d-flex" id="wrapper">
    <?php include_once "../ruangan/sidebar.php"; ?>

    <div id="page-content-wrapper" class="w-100 bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-5">
                    
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-dark text-white p-3 border-0 text-center">
                            <h5 class="fw-bold mb-0"><i class="fas fa-key me-2 text-warning"></i> Ubah Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <?= $message ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">PASSWORD LAMA</label>
                                    <input type="password" name="password_lama" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">PASSWORD BARU</label>
                                    <input type="password" name="password_baru" class="form-control" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">KONFIRMASI PASSWORD</label>
                                    <input type="password" name="konfirmasi" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">SIMPAN PERUBAHAN</button>
                            </form>
                        </div>
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once "../layout/footer.php"; ?>
