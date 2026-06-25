<?php
session_start();
include "../config/koneksi.php";

$nama = $_POST['nama'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$nama_ruangan = $_POST['nama_ruangan'];
$role = $_POST['role'];

$query = "INSERT INTO user_internal 
(nama,email,username,password,nama_ruangan,role) 
VALUES 
('$nama','$email','$username','$password','$nama_ruangan','$role')";

mysqli_query($conn, $query);

header("Location: master_user_internal.php");
exit;