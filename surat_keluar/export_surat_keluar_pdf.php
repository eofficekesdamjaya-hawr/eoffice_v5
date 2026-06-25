<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

$id_surat = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();

$filename = "EKSPOR_SURAT_" . ($surat['no_agenda'] ?? 'KOSONG') . ".xls";

// Paksa browser melakukan stream download file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <tr><th colspan="2" style="background-color: #007bff; color: white;">DATA EKSPOR SURAT KELUAR</th></tr>
    <tr><th>No Agenda</th><td><?= $surat['no_agenda'] ?></td></tr>
    <tr><th>Asal Satuan</th><td><?= $surat['asal_satuan'] ?></td></tr>
    <tr><th>Tanggal Input</th><td><?= $surat['tanggal_input'] ?></td></tr>
    <tr><th>No Surat</th><td><?= $surat['no_surat'] ?></td></tr>
    <tr><th>Bentuk Surat</th><td><?= $surat['bentuk_surat'] ?></td></tr>
    <tr><th>Jenis Surat</th><td><?= $surat['jenis_surat'] ?></td></tr>
    <tr><th>Perihal</th><td><?= $surat['perihal'] ?></td></tr>
    <tr><th>Status Proses</th><td><?= $surat['status_proses'] ?></td></tr>
</table>
