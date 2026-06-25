<?php
session_start();
require_once '../config/koneksi.php';

// Set header agar output selalu JSON
header('Content-Type: application/json');

// Inisialisasi respon default
$response = [
    'success' => false,
    'total'   => 0,
    'detail'  => [
        'ruangan' => 0,
        'jajaran' => 0,
        'satuan_luar' => 0
    ]
];

// ==========================================
// VALIDASI AKSES (Hanya Admin atau Setum)
// ==========================================
// Kita gunakan 'tipe_akses' sesuai standarisasi file sebelumnya
if (!isset($_SESSION['id_user']) || !in_array($_SESSION['tipe_akses'], ['admin', 'setum'])) {
    echo json_encode($response);
    exit;
}

// ==========================================
// HITUNG SURAT PENDING BERDASARKAN SUMBER
// ==========================================
// Query tunggal dengan CASE WHEN lebih efisien daripada 3 query terpisah
$query = "SELECT 
            COUNT(*) as total_semua,
            SUM(CASE WHEN sumber_surat = 'Ruangan' THEN 1 ELSE 0 END) as total_ruangan,
            SUM(CASE WHEN sumber_surat = 'Jajaran' THEN 1 ELSE 0 END) as total_jajaran,
            SUM(CASE WHEN sumber_surat = 'Satuan Luar' THEN 1 ELSE 0 END) as total_luar
          FROM surat_masuk 
          WHERE status_proses = 'Pending'";

$result = $conn->query($query);

if ($result && $data = $result->fetch_assoc()) {
    $response['success'] = true;
    $response['total']   = (int)$data['total_semua'];
    $response['detail']  = [
        'ruangan'     => (int)($data['total_ruangan'] ?? 0),
        'jajaran'     => (int)($data['total_jajaran'] ?? 0),
        'satuan_luar' => (int)($data['total_luar'] ?? 0)
    ];
}

// Output JSON untuk dibaca oleh JavaScript di dashboard_admin.php
echo json_encode($response);
?>