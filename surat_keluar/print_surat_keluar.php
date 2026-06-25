<?php
session_start();
require_once '../config/koneksi.php';

$id_surat = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Lembar Kontrol Surat Keluar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; font-size: 12px; }
        .header { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; text-transform: uppercase; border-bottom: 2px solid black; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; vertical-align: top; }
        .footer { margin-top: 50px; text-align: right; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        KOMANDO DAERAH MILITER JAYA/JAYAKARTA<br>
        KESEHATAN DAERAH MILITER JAYA<br>
        <small style="font-size: 10px; font-weight: normal;">Lembar Pengendali Administrasi Surat Keluar</small>
    </div>

    <table>
        <tr><th style="width: 25%;">No Agenda</th><td><?= htmlspecialchars($surat['no_agenda'] ?? '-') ?></td></tr>
        <tr><th>Asal Satuan</th><td><?= htmlspecialchars($surat['asal_satuan'] ?? '-') ?></td></tr>
        <tr><th>Tanggal Pengajuan</th><td><?= isset($surat['tanggal_input']) ? date('d-m-Y', strtotime($surat['tanggal_input'])) : '-' ?></td></tr>
        <tr><th>Nomor Surat Resmi</th><td><?= htmlspecialchars($surat['no_surat'] ?? 'Belum Diisi Setum') ?></td></tr>
        <tr><th>Bentuk / Jenis</th><td><?= htmlspecialchars($surat['bentuk_surat'] ?? '-') ?> / <?= htmlspecialchars($surat['jenis_surat'] ?? '-') ?></td></tr>
        <tr><th>Perihal</th><td><?= nl2br(htmlspecialchars($surat['perihal'] ?? '-')) ?></td></tr>
        <tr><th>Tujuan Utama</th><td><?= htmlspecialchars($surat['tujuan_utama'] ?? '-') ?></td></tr>
        <tr><th>Status Proses Sekarang</th><td><strong><?= htmlspecialchars($surat['status_proses'] ?? '-') ?></strong></td></tr>
    </table>

    <div class="footer">
        Jakarta, <?= date('d-m-Y') ?><br>
        Petugas Operator Ruangan<br><br><br><br>
        (............................................)
    </div>
</body>
</html>
