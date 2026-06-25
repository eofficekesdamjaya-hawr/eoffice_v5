<?php
require_once __DIR__.'/../config/session.php';

// Jika sudah login sebagai ruangan
if (isset($_SESSION['tipe_akses']) && $_SESSION['tipe_akses'] === 'ruangan') {
    header("Location: ../ruangan/dashboard.php");
    exit;
}

// Flash message error
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Ruangan | E-Office Kesdam Jaya</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --primary:#0f172a;
    --secondary:#1e293b;
    --accent:#0ea5e9;
    --bg:#f1f5f9;
    --card:#ffffff;
    --text:#0f172a;
    --muted:#64748b;
    --border:#e2e8f0;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter',sans-serif;
    background:
        linear-gradient(rgba(15,23,42,.88),rgba(15,23,42,.88)),
        url('../assets/img/bg-login.jpg');
    background-size:cover;
    background-position:center;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
}

.login-wrapper{
    width:100%;
    max-width:1100px;
    display:grid;
    grid-template-columns:1fr 450px;
    overflow:hidden;
    border-radius:30px;
    box-shadow:0 20px 50px rgba(0,0,0,.35);
    background:#fff;
}

.left-side{
    background:linear-gradient(135deg,#0f172a,#1e293b);
    color:white;
    padding:60px;
    position:relative;
    overflow:hidden;
}

.left-side::before{
    content:'';
    position:absolute;
    width:300px;
    height:300px;
    background:rgba(255,255,255,.05);
    border-radius:50%;
    top:-120px;
    right:-100px;
}

.left-side::after{
    content:'';
    position:absolute;
    width:250px;
    height:250px;
    background:rgba(255,255,255,.03);
    border-radius:50%;
    bottom:-100px;
    left:-80px;
}

.logo-img{
    width:90px;
    margin-bottom:20px;
    position:relative;
    z-index:2;
}

.app-title{
    font-size:2rem;
    font-weight:800;
    margin-bottom:10px;
    position:relative;
    z-index:2;
}

.app-subtitle{
    color:#cbd5e1;
    line-height:1.8;
    margin-bottom:35px;
    position:relative;
    z-index:2;
}

.info-box{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.1);
    border-radius:20px;
    padding:20px;
    backdrop-filter:blur(10px);
    position:relative;
    z-index:2;
}

.info-box h6{
    font-weight:700;
    margin-bottom:15px;
}

.info-item{
    display:flex;
    gap:12px;
    margin-bottom:15px;
}

.info-item i{
    color:#38bdf8;
    font-size:1.2rem;
}

.right-side{
    padding:50px 40px;
    background:#fff;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.login-badge{
    background:#e0f2fe;
    color:#0284c7;
    padding:8px 18px;
    border-radius:50px;
    display:inline-block;
    font-size:.8rem;
    font-weight:700;
    margin-bottom:20px;
}

.login-title{
    font-size:2rem;
    font-weight:800;
    color:#0f172a;
    margin-bottom:8px;
}

.login-subtitle{
    color:#64748b;
    margin-bottom:35px;
}

.form-label{
    font-weight:700;
    color:#334155;
    margin-bottom:8px;
}

.input-group{
    border:1px solid #e2e8f0;
    border-radius:14px;
    overflow:hidden;
    transition:.2s;
}

.input-group:focus-within{
    border-color:#0ea5e9;
    box-shadow:0 0 0 4px rgba(14,165,233,.1);
}

.input-group-text{
    background:#fff;
    border:none;
    color:#64748b;
}

.form-control{
    border:none;
    padding:14px;
    font-size:.95rem;
}

.form-control:focus{
    box-shadow:none;
}

.btn-login{
    background:linear-gradient(135deg,#0ea5e9,#0284c7);
    border:none;
    color:white;
    padding:14px;
    border-radius:14px;
    font-weight:700;
    transition:.3s;
}

.btn-login:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 25px rgba(14,165,233,.35);
}

.security-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    padding:15px;
    border-radius:14px;
    margin-top:25px;
}

.footer-login{
    margin-top:30px;
    text-align:center;
    font-size:.8rem;
    color:#94a3b8;
}

.back-home{
    text-decoration:none;
    color:#64748b;
    font-weight:600;
}

.back-home:hover{
    color:#0284c7;
}

@media(max-width:991px){

    .login-wrapper{
        grid-template-columns:1fr;
    }

    .left-side{
        display:none;
    }

    .right-side{
        padding:40px 25px;
    }
}
/* ===== TABLET BESAR ===== */
@media (max-width: 1024px) {
    .login-wrapper{
        grid-template-columns: 1fr 380px;
    }

    .left-side{
        padding: 40px;
    }

    .right-side{
        padding: 40px 30px;
    }
}

/* ===== TABLET ===== */
@media (max-width: 768px) {
    .login-wrapper{
        grid-template-columns: 1fr;
        border-radius: 0;
        height: 100vh;
    }

    .left-side{
        display: none;
    }

    .right-side{
        padding: 30px 20px;
    }

    .login-title{
        font-size: 1.6rem;
    }
}

/* ===== HP ===== */
@media (max-width: 480px) {
    body{
        padding: 10px;
    }

    .right-side{
        padding: 25px 15px;
    }

    .login-title{
        font-size: 1.4rem;
    }

    .btn-login{
        padding: 12px;
        font-size: 0.95rem;
    }

    .input-group-text{
        padding: 10px;
    }

    .form-control{
        padding: 12px;
    }
}
</style>
</head>
<body>

<div class="login-wrapper">

    <!-- LEFT -->
    <div class="left-side">

        <img src="../assets/img/logo1.png" class="logo-img">

        <h1 class="app-title">
            E-OFFICE KESDAM JAYA
        </h1>

        <p class="app-subtitle">
            Sistem Tata Naskah & Persuratan Digital Internal Ruangan 
            Kesdam Jaya/Jayakarta Terintegrasi.
        </p>

        <div class="info-box">

            <h6>
                <i class="bi bi-shield-lock-fill"></i>
                Akses Internal Ruangan
            </h6>

            <div class="info-item">
                <i class="bi bi-envelope-paper-fill"></i>
                <div>
                    Pengiriman surat digital internal ruangan.
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-diagram-3-fill"></i>
                <div>
                    Terintegrasi disposisi Setum dan Pimpinan.
                </div>
            </div>

            <div class="info-item">
                <i class="bi bi-shield-check"></i>
                <div>
                    Sistem keamanan & tracking surat elektronik Kesdam Jaya.
                </div>
            </div>

        </div>

    </div>

    <!-- RIGHT -->
    <div class="right-side">

        <div class="login-badge">
            INTERNAL RUANGAN
        </div>

        <h2 class="login-title">
            Selamat Datang
        </h2>

        <p class="login-subtitle">
            Silakan login menggunakan akun resmi ruangan/unit kerja.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger border-0 rounded-4 py-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST" autocomplete="off">
            <input type="hidden" name="role_target" value="ruangan">

            <div class="mb-4">
                <label class="form-label">
                    Email Ruangan
                </label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope-fill"></i>
                    </span>

                    <input 
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="contoh: ruangan@gmail.com"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Password
                </label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required
                    >

                    <button 
                        type="button"
                        class="btn"
                        onclick="togglePassword()"
                    >
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>

                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                LOGIN RUANGAN
            </button>

        </form>

        <div class="security-box">
            <small class="text-muted">
                <i class="bi bi-shield-lock-fill me-1"></i>
                Sistem hanya dapat diakses oleh personel internal 
                yang memiliki akun resmi E-Office Kesdam Jaya.
            </small>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">

            <a href="../index.php" class="back-home">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>

            <small class="text-muted">
                Build 2.0.0
            </small>

        </div>

        <div class="footer-login">
            © 2026 IT Kesdam Jaya/Jayakarta
        </div>

    </div>

</div>

<script>

function togglePassword(){

    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if(passwordField.type === 'password'){
        passwordField.type = 'text';
        toggleIcon.classList.replace('bi-eye','bi-eye-slash');
    }else{
        passwordField.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash','bi-eye');
    }
}

</script>

</body>
</html>
