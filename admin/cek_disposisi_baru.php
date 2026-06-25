<?php
session_start();
require_once "../config/koneksi.php";

$role = $_SESSION['role'];

// Hitung total disposisi atau tembusan yang belum dibaca untuk role ini
$sql = "SELECT 
        (SELECT COUNT(*) FROM disposisi_surat_masuk WHERE untuk_role = '$role' AND status_baca = 'belum') +
        (SELECT COUNT(*) FROM tembusan_surat_masuk WHERE untuk_role = '$role' AND status_baca = 'belum') 
        as total";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(['jumlah' => $row['total']]);
?>