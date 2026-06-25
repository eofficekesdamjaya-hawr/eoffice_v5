<?php
include '../config/koneksi.php';

$id       = $_POST['id'];
$nama     = $_POST['nama'];
$alamat   = $_POST['alamat'];
$telepon  = $_POST['telepon'];
$pimpinan = $_POST['pimpinan'];
$website  = $_POST['website'];
$email    = $_POST['email'];

$conn->query("UPDATE instansi SET 
    nama = '$nama',
    alamat = '$alamat',
    telepon = '$telepon',
    pimpinan = '$pimpinan',
    website = '$website',
    email = '$email'
    WHERE id = $id");

header("Location: ../master/instansi.php");
