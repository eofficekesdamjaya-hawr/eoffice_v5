<?php
include "../config.php"; // Memanggil koneksi database

// Mengambil data dari form registrasi
$nama_satuan = $_POST['nama_satuan'];
$email       = $_POST['email'];
$password    = $_POST['password']; // Tanpa enkripsi/hash
$no_hp       = $_POST['no_hp'];
$level       = 'jajaran'; // Otomatis diset sebagai jajaran

// Cek apakah email sudah terdaftar
$cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");

if (mysqli_num_rows($cek_email) > 0) {
    echo "<script>alert('Email sudah terdaftar!'); window.location='registrasi_jajaran.php';</script>";
} else {
    // Simpan data ke tabel users
    $query = "INSERT INTO users (nama_satuan, email, password, no_hp, level) 
              VALUES ('$nama_satuan', '$email', '$password', '$no_hp', '$level')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Registrasi Berhasil! Silahkan Login.'); window.location='login_jajaran.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>