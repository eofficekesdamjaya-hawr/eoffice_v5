<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../config/koneksi.php';

if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

/* ==========================
   VALIDASI LOGIN
========================== */
if (
    !isset($_SESSION['sudah_masuk_portal']) ||
    $_SESSION['sudah_masuk_portal'] !== true
) {
    echo json_encode([
        'success' => false,
        'total'   => 0
    ]);
    exit;
}

/* ==========================
   ROLE USER LOGIN
========================== */
$role = strtolower(trim($_SESSION['role_key'] ?? ''));

$total = 0;

/* ==========================
   HITUNG DISPOSISI AKTIF
========================== */

/* ==========================
   HITUNG DISPOSISI AKTIF
========================== */

$sql = "
SELECT COUNT(*) AS total
FROM disposisi_surat_masuk
WHERE untuk_role = ? 
AND status_baca = 'belum'
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    // Karena hanya ada satu tanda tanya (?), maka gunakan satu "s" dan satu variabel $role
    mysqli_stmt_bind_param($stmt, "s", $role);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $total = (int)$row['total'];
    }

    mysqli_stmt_close($stmt);
}

echo json_encode([
    'success' => true,
    'total'   => $total
]);

exit;
