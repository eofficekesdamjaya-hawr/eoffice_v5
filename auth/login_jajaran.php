<?php
require_once __DIR__.'/../config/session.php';

// Redirect jika sudah login
if (isset($_SESSION['tipe_akses']) && $_SESSION['tipe_akses'] === 'jajaran') {
    header("Location: ../jajaran/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Jajaran | E-Office Kesdam Jaya</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

:root{
    --primary:#166534;
    --secondary:#14532d;
    --accent:#22c55e;
    --bg:#f0fdf4;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:
        linear-gradient(rgba(20,83,45,.90),rgba(20,83,45,.90)),
        url('../assets/img/bg-login.jpg');
    background-size:cover;
    background-position:center;
    padding:20px;
}

.login-wrapper{
    width:100%;
    max-width:500px;
}

.login-card{
    background:#fff;
    border-radius:28px;
    padding:45px;
    box-shadow:0 25px 50px rgba(0,0,0,.25);
}

.logo-img{
    width:90px;
    margin-bottom:15px;
}

.badge-login{
    background:#dcfce7;
    color:#166534;
    display:inline-block;
    padding:8px 20px;
    border-radius:50px;
    font-size:.8rem;
    font-weight:700;
    margin-bottom:18px;
}

.title{
    font-size:2rem;
    font-weight:800;
    color:#14532d;
}

.subtitle{
    color:#64748b;
    margin-bottom:30px;
}

.form-label{
    font-size:.85rem;
    font-weight:700;
    color:#334155;
}

.input-group{
    border:1px solid #e2e8f0;
    border-radius:14px;
    overflow:hidden;
}

.input-group:focus-within{
    border-color:#22c55e;
    box-shadow:0 0 0 4px rgba(34,197,94,.1);
}

.input-group-text{
    border:none;
    background:#fff;
}

.form-control{
    border:none;
    padding:14px;
}

.form-control:focus{
    box-shadow:none;
}

.btn-login{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    border:none;
    color:white;
    font-weight:700;
    padding:14px;
    border-radius:14px;
    transition:.3s;
}

.btn-login:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 25px rgba(34,197,94,.3);
}

.btn-register{
    border-radius:14px;
    padding:12px;
    font-weight:600;
}

.divider{
    display:flex;
    align-items:center;
    text-align:center;
    margin:25px 0;
    color:#94a3b8;
    font-size:.85rem;
}

.divider::before,
.divider::after{
    content:'';
    flex:1;
    border-bottom:1px solid #e2e8f0;
}

.divider::before{
    margin-right:10px;
}

.divider::after{
    margin-left:10px;
}

.btn-google{
    border:1px solid #e2e8f0;
    border-radius:14px;
    padding:12px;
    font-weight:600;
    transition:.2s;
}

.btn-google:hover{
    background:#f8fafc;
}

.footer-login{
    margin-top:30px;
    text-align:center;
    font-size:.8rem;
    color:#94a3b8;
}

</style>
</head>
<body>

<div class="login-wrapper">

<div class="login-card text-center">

<img src="../assets/img/logo1.png" class="logo-img">

<div class="badge-login">
SATUAN JAJARAN INTERNAL
</div>

<h1 class="title">
Login Jajaran
</h1>

<p class="subtitle">
Silakan login menggunakan akun resmi satuan jajaran Kesdam Jaya/Jayakarta.
</p>

<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger border-0 rounded-4 text-start">

<?php 
$err = $_GET['error'];

if($err == 'wrong_password'){
    echo "Password yang Anda masukkan salah.";
}elseif($err == 'user_not_found'){
    echo "Email jajaran tidak ditemukan.";
}elseif($err == 'not_active'){
    echo "Akun belum aktif.";
}else{
    echo htmlspecialchars($err);
}
?>

</div>
<?php endif; ?>

<form action="proses_login.php" method="POST" class="text-start">
    <input type="hidden" name="role_target" value="jajaran">

<div class="mb-4">

<label class="form-label">
Email Jajaran
</label>

<div class="input-group">

<span class="input-group-text">
<i class="bi bi-envelope-fill"></i>
</span>

<input 
type="email"
name="email"
class="form-control"
placeholder="contoh@jajaran.com"
required
autofocus>

</div>

</div>

<div class="mb-4">

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
required>

<button
type="button"
class="btn"
onclick="togglePassword()">

<i class="bi bi-eye-fill text-muted" id="toggleIcon"></i>

</button>

</div>

</div>

<button type="submit" name="login" class="btn btn-login w-100 mb-3">
<i class="bi bi-box-arrow-in-right me-2"></i>
LOGIN JAJARAN
</button>

<a href="registrasi_jajaran.php" class="btn btn-outline-secondary btn-register w-100">
Registrasi Akun Baru
</a>

</form>

<div class="divider">
Atau
</div>

<a href="google_auth_link.php"
class="btn btn-google w-100 d-flex align-items-center justify-content-center">

<img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
width="18"
class="me-2">

Login dengan Google

</a>

<div class="footer-login">

<div>
© 2026 IT Kesdam Jaya/Jayakarta
</div>

<div class="mt-2">
<a href="../index.php" class="text-decoration-none text-success">
<i class="bi bi-arrow-left"></i>
Kembali ke Beranda
</a>
</div>

</div>

</div>
</div>

<script>

function togglePassword(){

const passwordField = document.getElementById("password");
const icon = document.getElementById("toggleIcon");

if(passwordField.type === "password"){
    passwordField.type = "text";
    icon.classList.replace("bi-eye-fill","bi-eye-slash-fill");
}else{
    passwordField.type = "password";
    icon.classList.replace("bi-eye-slash-fill","bi-eye-fill");
}

}

</script>

</body>
</html>
