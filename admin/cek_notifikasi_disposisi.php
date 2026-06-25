<?php
session_start();
require_once "../config/koneksi.php";

// Set header agar output selalu JSON
header('Content-Type: application/json');

// Inisialisasi respon default
$response = [
    'success' => false,
    'jumlah'  => 0,
    'detail'  => [
        'disposisi' => 0,
        'tembusan'  => 0
    ]
];

// 1. VALIDASI SESI (Pastikan user sudah login)
if (!isset($_SESSION['role']) || !isset($_SESSION['id_user'])) {
    echo json_encode($response);
    exit;
}

$role = $_SESSION['role'];

// 2. QUERY OPTIMASI (Menggunakan Prepared Statement untuk keamanan)
// Kita hitung dua sumber notifikasi: Disposisi Utama dan Tembusan
$sql = "SELECT 
            (SELECT COUNT(*) FROM disposisi_surat_masuk WHERE untuk_role = ? AND status_baca = 'belum') as total_disposisi,
            (SELECT COUNT(*) FROM tembusan_surat_masuk WHERE untuk_role = ? AND status_baca = 'belum') as total_tembusan";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $role, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($data = $result->fetch_assoc()) {
    $dispo = (int)$data['total_disposisi'];
    $tembus = (int)$data['total_tembusan'];
    
    $response['success'] = true;
    $response['jumlah']  = $dispo + $tembus;
    $response['detail']  = [
        'disposisi' => $dispo,
        'tembusan'  => $tembus
    ];
}

// 3. OUTPUT JSON
echo json_encode($response);
?>