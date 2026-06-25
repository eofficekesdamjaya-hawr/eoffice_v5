<?php
require_once "../config/session.php";

// Jika sudah login
if (isset($_SESSION['id_user']) && $_SESSION['tipe_akses'] === 'satuan_lain') {
    header("Location: ../satuan_lain/dashboard_satuan_lain.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Satuan Luar | E-Office Kesdam Jaya</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Inter',sans-serif;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:
        linear-gradient(rgba(15,23,42,.92),rgba(15,23,42,.92)),
        url('../assets/img/bg-login.jpg');
    background-size:cover;
    background-position:center;
    padding:20px;
}

.login-box{
    width:100%;
    max-width:480px;
    background:#fff;
    border-radius:30px;
    padding:45px;
    box-shadow:0 25px 50px rgba(0,0,0,.35);
}

.logo-login{
    width:90px;
    margin-bottom:18px;
}

.badge-login{
    background:#dbeafe;
    color:#1d4ed8;
    padding:8px 18px;
    border-radius:50px;
    font-size:.8rem;
    font-weight:700;
    display:inline-block;
    margin-bottom:18px;
}

.title-app{
    font-size:2rem;
    font-weight:800;
    color:#0f172a;
}

.subtitle{
    color:#64748b;
    margin-bottom:30px;
}

.form-label{
    font-weight:700;
    font-size:.85rem;
}

.input-group{
    border:1px solid #e2e8f0;
    border-radius:14px;
    overflow:hidden;
}

.input-group:focus-within{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,.1);
}

.input-group-text{
    background:#fff;
    border:none;
}

.form-control{
    border:none;
    padding:14px;
}

.form-control:focus{
    box-shadow:none;
}

.btn-login{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    border:none;
    color:white;
    padding:14px;
    border-radius:14px;
    font-weight:700;
}

.btn-login:hover{
    transform:translateY(-2px);
}

.btn-register{
    border-radius:14px;
    padding:12px;
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

<div class="login-box text-center">

<img src="../assets/img/logo1.png" class="logo-login">

<div class="badge-login">
SATUAN LUAR 
</div>

<h1 class="title-app">
E-Office Kesdam Jaya
</h1>

<p class="subtitle">
Akses pengiriman surat satuan luar dan instansi mitra.
</p>

<?php if(isset($_GET['error'])): ?>

<div class="alert alert-danger border-0 rounded-4 text-start">

<?php

if($_GET['error']=="wrong_password"){
    echo "Password yang dimasukkan salah.";
}elseif($_GET['error']=="user_not_found"){
    echo "Email tidak ditemukan.";
}else{
    echo "Terjadi kesalahan sistem.";
}

?>

</div>

<?php endif; ?>

<form method="POST" action="proses_login.php" class="text-start">
    <input type="hidden" name="role_target" value="satuan_lain">

<div class="mb-4">

<label class="form-label">
Email Instansi
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-envelope"></i>
</span>

<input
type="email"
name="email"
class="form-control"
placeholder="nama@instansi.com"
required>

</div>

</div>

<div class="mb-4">

<label class="form-label">
Password
</label>

<div class="input-group">

<span class="input-group-text">
<i class="fas fa-lock"></i>
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

<i class="fas fa-eye text-muted" id="icon-eye"></i>

</button>

</div>

</div>

<button type="submit" class="btn btn-login w-100 mb-3">

<i class="fas fa-sign-in-alt me-2"></i>
LOGIN SATUAN LUAR

</button>

<a href="registrasi_satuan_lain.php"
class="btn btn-outline-secondary btn-register w-100">

Registrasi Satuan Luar

</a>

<div class="footer-login">

<div>
© 2026 IT Kesdam Jaya/Jayakarta
</div>

<div class="mt-2">
<a href="../index.php" class="text-decoration-none">
<i class="fas fa-arrow-left"></i>
Kembali ke Beranda
</a>
</div>

</div>

</form>

</div>

<script>

function togglePassword(){

const password = document.getElementById("password");
const icon = document.getElementById("icon-eye");

if(password.type==="password"){
password.type="text";
icon.classList.replace("fa-eye","fa-eye-slash");
}else{
password.type="password";
icon.classList.replace("fa-eye-slash","fa-eye");
}

}

</script>

</body>
</html>
