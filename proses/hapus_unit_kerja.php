<?php
include '../config/koneksi.php';
$id = $_GET['id'];

$conn->query("DELETE FROM unit_kerja WHERE id = $id");
header("Location: ../master/unit_kerja.php");
