<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

$email_aktif = $_SESSION['email'] ?? '';
$akses_riwayat = [
    'superadmin@gmail.com', 'setum@gmail.com', 'admin@gmail.com', 
    'kasituud2026@gmail.com', 'kakesdamjaya2026@gmail.com', 
    'wakakesdamjaya2026@gmail.com', 'spripimpinan2026@gmail.com', 'ruangan'
];

if (!in_array($email_aktif, $akses_riwayat) && strpos($email_aktif, 'ruangan') === false) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='kelola_surat_keluar.php';</script>";
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT no_surat, perihal, keterangan FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Aktivitas Surat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0" style="border-radius:15px;">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Log Riwayat Perjalanan Surat</h5>
        </div>
        <div class="card-body">
            <h6><strong>No Surat:</strong> <?= $surat['no_surat'] ?? '-' ?></h6>
            <p class="text-muted small"><strong>Perihal:</strong> <?= $surat['perihal'] ?? '-' ?></p>
            <hr>
            <label class="fw-bold text-uppercase small text-secondary d-block mb-2">Kronologi Perubahan Status</label>
            <div class="bg-dark text-warning p-3 rounded font-monospace" style="white-space: pre-wrap; font-size: 13px; max-height: 400px; overflow-y: auto;">
                <?= !empty($surat['keterangan']) ? htmlspecialchars($surat['keterangan']) : "Belum ada riwayat aktivitas yang tercatat." ?>
            </div>
        </div>
        <div class="card-footer bg-white text-end">
            <a href="kelola_surat_keluar.php" class="btn btn-sm btn-secondary">Tutup</a>
        </div>
    </div>
</div>
</body>
</html>