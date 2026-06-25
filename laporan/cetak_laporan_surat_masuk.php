<?php
session_start();
require_once '../config/koneksi.php';

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$query = "SELECT * FROM surat_masuk WHERE tanggal_diterima BETWEEN ? AND ? ORDER BY tanggal_diterima DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Surat Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .kop { text-align: center; border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body onload="window.print()">
    <div class="kop">
        <h3>KESDAM JAYA</h3>
        <p>Laporan Surat Masuk Periode <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Agenda</th>
                <th>Pengirim</th>
                <th>Perihal</th>
                <th>Tanggal Terima</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['pengirim'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
                <td><?= date('d/m/Y', strtotime($row['tanggal_diterima'])) ?></td>
                <td><?= $row['status_proses'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>