<?php
// FILE: proses/simpan_instansi.php
include '../config/koneksi.php';
$nama     = $_POST['nama'];
$alamat   = $_POST['alamat'];
$telepon  = $_POST['telepon'];
$pimpinan = $_POST['pimpinan'];
$website  = $_POST['website'];
$email    = $_POST['email'];

$sql = "INSERT INTO instansi (nama, alamat, telepon, pimpinan, website, email)
        VALUES ('$nama', '$alamat', '$telepon', '$pimpinan', '$website', '$email')";
$conn->query($sql);
header("Location: ../master/instansi.php");
