<?php
include '../config/koneksi.php';

$id = $_POST['id'];
$nama_unit = $_POST['nama_unit'];

$conn->query("UPDATE unit_kerja SET nama_unit = '$nama_unit' WHERE id = $id");
header("Location: ../master/unit_kerja.php");
