<?php
require_once "../config/session.php";
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'kosong', 'pesan' => 'User belum login']);
    exit;
}

// Menangkap role_key user ruangan yang aktif (misal: kasi_matkes)
$user_role = isset($_SESSION['role_key']) ? strtolower(trim($_SESSION['role_key'])) : '';

if (empty($user_role)) {
    echo json_encode(['status' => 'kosong', 'pesan' => 'Session role_key kosong']);
    exit;
}

// QUERY UTAMA: Mencari data notifikasi belum terbaca yang ditujukan ke role user saat ini.
// Filter tambahan (pesan LIKE) opsional jika Anda ingin mengkhususkan bunyi alert untuk tipe kata tertentu.
$query = "SELECT id_notif, pesan, link FROM notifikasi 
          WHERE untuk_role = ? AND status_baca = 'belum' 
          ORDER BY id_notif DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_notif = $row['id_notif'];
    
    // UPDATE LANGSUNG KE 'SUDAH': Supaya polling AJAX di detik berikutnya tidak memicu suara berulang-ulang
    $update_query = "UPDATE notifikasi SET status_baca = 'sudah' WHERE id_notif = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $id_notif);
    $update_stmt->execute();
    $update_stmt->close();

    echo json_encode([
        'status' => 'baru',
        'id'     => $id_notif,
        'pesan'  => $row['pesan'],
        'link'   => "dashboard.php" // Memaksa halaman tetap di dashboard agar aman dari 'akses_ditolak'
    ]);
} else {
    echo json_encode(['status' => 'kosong']);
}

$stmt->close();
$conn->close();
exit;
?>
