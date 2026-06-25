<?php
include '../config/koneksi.php';

$nama_unit = $_POST['nama_unit'];
$conn->query("INSERT INTO unit_kerja (nama_unit) VALUES ('$nama_unit')");

header("Location: ../master/unit_kerja.php");
