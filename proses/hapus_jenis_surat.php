<?php
include '../config/koneksi.php';

$id = $_GET['id'];
$tipe = $_GET['tipe'];
$table = $tipe === 'keluar' ? 'jenis_surat_keluar' : 'jenis_surat_masuk';

$conn->query("DELETE FROM $table WHERE id = $id");
header("Location: ../master/jenis_surat.php?tipe=$tipe");
