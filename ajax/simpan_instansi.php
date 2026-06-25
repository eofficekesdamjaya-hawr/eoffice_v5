<?php
include '../config/koneksi.php';

if (isset($_POST['nama_instansi'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_instansi']);
    mysqli_query($conn, "INSERT INTO master_instansi (nama_instansi) VALUES ('$nama')");
}
?>
