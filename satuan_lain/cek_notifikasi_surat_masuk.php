<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Validasi akses
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    echo json_encode(['total_notif' => 0]);
    exit;
}

$nama_satuan = $_SESSION['satuan'];

// Menghitung jumlah surat masuk yang status_baca-nya 'belum'
$query = "SELECT COUNT(*) as total FROM surat_masuk 
          WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?)) 
          AND status_baca = 'belum'";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nama_satuan);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['total_notif' => (int)$result['total']]);
exit;