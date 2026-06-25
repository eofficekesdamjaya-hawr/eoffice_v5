<?php
require_once "../config/session.php";

if (isset($_SESSION['id_user'])) {
    header("Location: ../dashboard/dashboard_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin & Pimpinan - E-Office Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .logo-img {
            max-height: 100px;
            display: block;
            margin: 0 auto 20px auto;
        }
        .footer-login {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="login-card">
    <img src="../assets/img/logo1.png" alt="Logo Kesdam Jaya" class="logo-img">

    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark m-0">Halaman Login Admin</h4>
        <small class="text-muted">Superadmin • Setum • Kasi tuud • kakesdam_jaya • wakakesdam_jaya • spri_pimpinan</small>
    </div>

    <form action="proses_login.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Alamat Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="contoh: superadmin@gmail.com" required>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan kata sandi" required>
                <button class="btn btn-outline-secondary" type="button" id="btn-toggle-password">
                    <i class="bi bi-eye" id="icon-mata"></i>
                </button>
            </div>
            <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">* Sistem menggunakan verifikasi kata sandi langsung (tanpa enkripsi/hash).</small>
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" name="login_admin" class="btn btn-primary fw-bold">Masuk Sistem</button>
        </div>

        <div class="text-center">
            <a href="../dashboard_utama.php" class="text-decoration-none text-secondary small">
                <i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama
            </a>
        </div>
    </form>

    <div class="footer-login">
        © 2026 IT Kesdam Jaya/Jayakarta
    </div>
</div>

<script>
    const btnToggle = document.getElementById('btn-toggle-password');
    const inputPassword = document.getElementById('password');
    const iconMata = document.getElementById('icon-mata');

    btnToggle.addEventListener('click', function () {
        // Tukar tipe input antara password dan text
        if (inputPassword.type === 'password') {
            inputPassword.type = 'text';
            iconMata.classList.remove('bi-eye');
            iconMata.classList.add('bi-eye-slash');
        } else {
            inputPassword.type = 'password';
            iconMata.classList.remove('bi-eye-slash');
            iconMata.classList.add('bi-eye');
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
