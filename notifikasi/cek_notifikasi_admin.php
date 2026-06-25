<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json');

// Proteksi: Pastikan hanya role admin/setum/pimpinan yang bisa memicu file ini
$user_role = strtolower(trim($_SESSION['tipe_akses'] ?? ''));
$allowed = ['admin', 'setum', 'superadmin', 'kasi_tuud', 'kakesdam_jaya', 'wakakesdam_jaya', 'spri_pimpinan'];

if (!in_array($user_role, $allowed)) {
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Hitung total surat keluar saat ini untuk pembanding real-time
$query = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar");
$row = mysqli_fetch_assoc($query);

echo json_encode([
    'total_keluar_sekarang' => (int)($row['total'] ?? 0)
]);
exit;
