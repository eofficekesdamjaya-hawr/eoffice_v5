<?php
include '../config/koneksi.php';
$id = $_GET['id'];
$conn->query("DELETE FROM instansi WHERE id = $id");
header("Location: ../master/instansi.php");
