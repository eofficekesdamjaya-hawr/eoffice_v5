<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// 1. Ambil data role user dari session
$user_role = strtolower(trim($_SESSION['tipe_akses'] ?? ''));
$allowed_roles = ['admin', 'setum', 'superadmin', 'kasi_tuud', 'kakesdam_jaya', 'wakakesdam_jaya', 'spri_pimpinan'];

if (!in_array($user_role, $allowed_roles)) {
    echo json_encode(['total' => 0, 'error' => 'Akses ditolak']);
    exit();
}

// 2. LOGIKA DISTRIBUSI ALAMAT SURAT MASUK
// Kita hitung surat masuk baru/pending berdasarkan siapa yang berhak memproses saat itu
if ($user_role === 'setum' || $user_role === 'admin' || $user_role === 'superadmin') {
    $query = "SELECT COUNT(*) as total FROM surat_masuk WHERE status_proses = 'Pending'";
} else if ($user_role === 'kakesdam_jaya' || $user_role === 'wakakesdam_jaya') {
    // UBAH status_surat menjadi status_proses
    $query = "SELECT COUNT(*) as total FROM surat_masuk WHERE status_proses = 'Menunggu Disposisi Pimpinan'";
} else if ($user_role === 'kasi_tuud' || $user_role === 'spri_pimpinan') {
    // UBAH status_surat menjadi status_proses
    $query = "SELECT COUNT(*) as total FROM surat_masuk WHERE status_proses = 'Agenda TUUD'";
} else {
    $query = "SELECT 0 as total";
}

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

echo json_encode([
    'total' => (int)($data['total'] ?? 0)
]);
exit();
