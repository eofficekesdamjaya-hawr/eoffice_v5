<?php
require_once "../config/session.php";

echo json_encode([
    "session_role" => $_SESSION['role'] ?? 'TIDAK ADA SESSION'
]);
exit;

require_once "../config/koneksi.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode([
        "surat" => 0,
        "disposisi" => 0
    ]);
    exit;
}

$role = strtolower(trim($_SESSION['role']));

// ===========================
// HITUNG SURAT MASUK PENDING
// ===========================
$qSurat = mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM surat_masuk
    WHERE status_proses='Pending'
");

$dataSurat = mysqli_fetch_assoc($qSurat);
$totalSurat = $dataSurat['total'] ?? 0;


// ===========================
// HITUNG DISPOSISI BELUM BACA (FIX LOWERCASE MATCH)
// ===========================
$qDisposisi = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM notifikasi_surat_masuk
    WHERE LOWER(TRIM(untuk_role)) = '$role'
    AND status_baca='belum'
");

$dataDisposisi = mysqli_fetch_assoc($qDisposisi);
$totalDisposisi = $dataDisposisi['total'] ?? 0;

echo json_encode([
    "surat" => (int)$totalSurat,
    "disposisi" => (int)$totalDisposisi
]);