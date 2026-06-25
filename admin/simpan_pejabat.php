<?php
session_start();
include "../config/koneksi.php";

$nama = $_POST['nama_pejabat'];
$jabatan = $_POST['jabatan'];
$email = $_POST['email'];
$hp = $_POST['nomor_hp'];

mysqli_query($conn, "INSERT INTO pejabat 
(nama_pejabat,jabatan,email,nomor_hp) 
VALUES 
('$nama','$jabatan','$email','$hp')");

header("Location: master_pejabat.php");
exit;