<?php
session_start();
require_once "../config/koneksi.php";

// Set header agar output berupa JSON
header('Content-Type: application/json');

// 1. Validasi Akses: Hanya untuk user tipe 'jajaran'
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    echo json_encode(['total_notif' => 0, 'error' => 'Unauthorized']);
    exit;
}

// 2. Ambil Nama Satuan dari Session
$nama_jajaran = $_SESSION['nama'];

/**
 * 3. Query Hitung Surat Masuk
 * Menghitung jumlah surat yang ditujukan ke satuan ini 
 * dan belum dibaca oleh satuan ini.
 */
$query = "SELECT COUNT(*) as total FROM surat_masuk 
          WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?)) 
          AND status_baca = 'belum'";

$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("s", $nama_jajaran);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total = (int)$result['total'];

    // 4. Kirim Output ke AJAX Dashboard
    echo json_encode([
        'status'      => 'success',
        'total_notif' => $total,
        'satuan'      => $nama_jajaran
    ]);
} else {
    // Jika query gagal
    echo json_encode([
        'status'      => 'error',
        'total_notif' => 0,
        'message'     => $conn->error
    ]);
}