<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Pastikan hanya bisa diakses melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registrasi_satuan_lain.php");
    exit;
}

// Ambil dan bersihkan data dari form
$satuan   = mysqli_real_escape_string($conn, trim($_POST['satuan']));
$nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
$email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$password = trim($_POST['password']);

// 1. Cek apakah email sudah terdaftar sebelumnya
$checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$resEmail = $checkEmail->get_result();

if ($resEmail->num_rows > 0) {
    // Jika email sudah ada, kembalikan ke halaman registrasi dengan error
    header("Location: registrasi_satuan_lain.php?error=email_exists");
    exit;
}

// 2. Persiapkan data tambahan
$role   = 'satuan_lain';
$status = 'aktif'; // Langsung aktif agar bisa langsung login (atau ubah ke 'nonaktif' jika butuh verifikasi admin)

// 3. Simpan data ke database
// Catatan: Password disimpan tanpa hash sesuai permintaan Anda untuk proses belajar
$query = "INSERT INTO users (nama, email, password, satuan, role, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $nama, $email, $password, $satuan, $role, $status);

if ($stmt->execute()) {
    // Jika berhasil, arahkan ke login dengan pesan sukses (opsional)
    echo "<script>
            alert('Registrasi Berhasil! Silakan Login.');
            window.location.href = 'login_satuan_lain.php';
          </script>";
} else {
    // Jika gagal insert ke database
    header("Location: registrasi_satuan_lain.php?error=failed");
}

$stmt->close();
$conn->close();