<?php
require_once '../config/koneksi.php';

$jenis = $_GET['jenis'];
$tgl_awal = $_GET['tgl_awal'];
$tgl_akhir = $_GET['tgl_akhir'];

// Header untuk memaksa browser men-download file excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_$jenis ($tgl_awal - $tgl_akhir).xls");

if ($jenis == 'surat_masuk') {
    echo "<table><tr><th>No</th><th>No Agenda</th><th>Pengirim</th><th>Perihal</th><th>Tanggal</th></tr>";
    $query = $conn->query("SELECT * FROM surat_masuk WHERE tanggal_diterima BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $no = 1;
    while($row = $query->fetch_assoc()) {
        echo "<tr><td>".$no++."</td><td>".$row['no_agenda']."</td><td>".$row['pengirim']."</td><td>".$row['perihal']."</td><td>".$row['tanggal_diterima']."</td></tr>";
    }
} else {
    echo "<table><tr><th>No</th><th>No Surat</th><th>Tujuan</th><th>Perihal</th><th>Tanggal</th></tr>";
    $query = $conn->query("SELECT * FROM surat_keluar WHERE tanggal_surat BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $no = 1;
    while($row = $query->fetch_assoc()) {
        echo "<tr><td>".$no++."</td><td>".$row['no_surat']."</td><td>".$row['tujuan_surat']."</td><td>".$row['perihal']."</td><td>".$row['tanggal_surat']."</td></tr>";
    }
}
echo "</table>";
?>