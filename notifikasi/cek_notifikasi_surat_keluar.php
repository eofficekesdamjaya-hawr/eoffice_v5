<?php
require_once "../config/session.php";
require_once __DIR__ . '/../config/koneksi.php';

$user_role = strtolower(trim($_SESSION['tipe_akses'] ?? ''));
$allowed_roles = ['admin', 'setum', 'superadmin', 'kasi_tuud', 'kakesdam_jaya', 'wakakesdam_jaya', 'spri_pimpinan'];

if (!in_array($user_role, $allowed_roles)) {
    echo json_encode(['total' => 0, 'error' => 'Akses ditolak']);
    exit();
}

// Filter berdasarkan role yang sedang login
if ($user_role === 'setum' || $user_role === 'admin' || $user_role === 'superadmin') {
   $query = "SELECT COUNT(*) as total FROM surat_keluar WHERE status_proses = 'diproses'";
} else if ($user_role === 'kasi_tuud') {
    $query = "SELECT COUNT(*) as total FROM surat_keluar WHERE status_proses = 'Paraf Kasi TUUD'";
} else if ($user_role === 'kakesdam_jaya' || $user_role === 'wakakesdam_jaya') {
    $query = "SELECT COUNT(*) as total FROM surat_keluar WHERE status_proses = 'Menunggu TTD Pimpinan'";
} else {
    $query = "SELECT 0 as total";
}

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

echo json_encode(['total' => (int)($data['total'] ?? 0)]);
exit();
