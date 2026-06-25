<?php
include '../config/koneksi.php';

$tipe = $_POST['tipe'];
$nama = $_POST['nama_jenis'];
$table = $tipe === 'keluar' ? 'jenis_surat_keluar' : 'jenis_surat_masuk';

$conn->query("INSERT INTO $table (nama_jenis) VALUES ('$nama')");
header("Location: ../master/jenis_surat.php?tipe=$tipe");
