<?php
session_start();
include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body {
        background-color: #f8f9fa;
    }
    .card-regis {
        border-radius: 20px;
        border: none;
    }
    .btn-regis {
        border-radius: 50px;
        padding: 10px;
        font-weight: bold;
    }
    .input-group-text {
        cursor: pointer;
        background-color: white;
    }
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-regis shadow-lg p-4">
                <div class="text-center mb-3">
                    <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                    <h4 class="fw-bold">Registrasi Satuan Luar</h4>
                    <p class="text-muted small">Buat akun untuk akses pengiriman surat ke Kesdam Jaya</p>
                </div>
                
                <hr>

                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php 
                            if($_GET['error'] == 'email_exists') echo "Email tersebut sudah terdaftar!";
                            else echo "Terjadi kesalahan saat pendaftaran.";
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="proses_registrasi_satuan_lain.php">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Satuan / Instansi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                            <input type="text" name="satuan" class="form-control bg-light" placeholder="Contoh: RSPAD Gatot Soebroto" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap PIC</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="nama" class="form-control bg-light" placeholder="Nama penanggung jawab" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control bg-light" placeholder="email@instansi.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control bg-light border-end-0" placeholder="Minimal 5 karakter" required>
                            <span class="input-group-text bg-light" onclick="togglePassword()">
                                <i class="fas fa-eye text-muted" id="icon-eye"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-regis w-100 shadow-sm mb-3">
                        <i class="fas fa-check-circle me-2"></i> Daftar Sekarang
                    </button>
                    
                    <div class="text-center">
                        <small class="text-muted">Sudah punya akun?</small><br>
                        <a href="login_satuan_lain.php" class="text-decoration-none fw-bold small text-primary">Kembali ke Login</a>
                    </div>
                </form>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted x-small">Pastikan data yang Anda masukkan valid untuk keperluan korespondensi resmi.</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const iconEye = document.getElementById("icon-eye");
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        iconEye.classList.remove("fa-eye");
        iconEye.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        iconEye.classList.remove("fa-eye-slash");
        iconEye.classList.add("fa-eye");
    }
}
</script>

<?php include '../layout/footer.php'; ?>