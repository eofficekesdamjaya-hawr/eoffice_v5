<?php
session_start();
require_once '../config/koneksi.php';

// Menangkap parameter tanggal dari URL
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Query data surat keluar
$query = "SELECT * FROM surat_keluar WHERE tanggal_surat BETWEEN ? AND ? ORDER BY tanggal_surat DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Surat Keluar</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: #000; }
        .kop { text-align: center; border-bottom: 4px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .kop h2, .kop h3 { margin: 0; text-transform: uppercase; }
        .title { text-align: center; margin-bottom: 20px; text-decoration: underline; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; padding: 6px; font-size: 12px; }
        th { background-color: #eee; text-align: center; }
        .ttd-area { margin-top: 30px; width: 100%; }
        .ttd-box { float: right; width: 250px; text-align: center; }
    </style>
</head>
<body onload="window.print()">
    <div class="kop">
        <h3>KESDAM JAYA</h3>
        <p>Jl. Mayjen Sutoyo No. 11, Jakarta Timur</p>
    </div>

    <div class="title">LAPORAN SURAT KELUAR</div>
    <p>Periode: <?= date('d-m-Y', strtotime($tgl_awal)) ?> s/d <?= date('d-m-Y', strtotime($tgl_akhir)) ?></p>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>No Surat</th>
                <th>Tanggal</th>
                <th>Tujuan</th>
                <th>Perihal</th>
                <th>Status TTE</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td align="center"><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['no_surat'] ?? '-') ?></td>
                <td align="center"><?= date('d/m/Y', strtotime($row['tanggal_surat'])) ?></td>
                <td><?= htmlspecialchars($row['tujuan_surat'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
                <td align="center"><?= $row['status_tte'] ?? '-' ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <div class="ttd-box">
            <p>Jakarta, <?= date('d-m-Y') ?></p>
            <p>Kepala Sekretariat</p>
            <br><br><br>
            <p><b>( .............................. )</b></p>
        </div>
    </div>
</body>
</html>