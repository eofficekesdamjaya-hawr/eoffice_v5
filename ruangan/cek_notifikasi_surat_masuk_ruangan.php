<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'kosong', 'pesan' => 'User belum login']);
    exit;
}

$user_role = isset($_SESSION['role_key']) ? strtolower(trim($_SESSION['role_key'])) : '';

if (empty($user_role)) {
    echo json_encode(['status' => 'kosong', 'pesan' => 'Session role_key kosong']);
    exit;
}

// Ambil data notifikasi
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
    
    // Update status baca agar tidak berbunyi berulang-ulang
    $update_query = "UPDATE notifikasi SET status_baca = 'sudah' WHERE id_notif = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $id_notif);
    $update_stmt->execute();
    $update_stmt->close();

    // =========================================================================
    // FORCE LINK BYPASS: Alihkan paksa ke halaman yang bisa dibuka oleh 'ruangan'
    // =========================================================================
    // Kita arahkan ke halaman dashboard itu sendiri atau halaman daftar surat masuk ruangan
    // Jika Anda punya file khusus seperti 'surat_masuk.php' di folder ruangan, ganti ke file tersebut.
    $link_aman = "dashboard.php"; 

    echo json_encode([
        'status' => 'baru',
        'id'     => $id_notif,
        'pesan'  => $row['pesan'],
        'link'   => $link_aman
    ]);
} else {
    echo json_encode(['status' => 'kosong']);
}

$stmt->close();
$conn->close();
exit;
?>
