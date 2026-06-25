<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_surat = $_GET['id'] ?? 0;

// Ambil info surat
$stmtSurat = $conn->prepare("SELECT no_agenda, perihal FROM surat_keluar WHERE id_surat = ?");
$stmtSurat->bind_param("i", $id_surat);
$stmtSurat->execute();
$surat = $stmtSurat->get_result()->fetch_assoc();

// Ganti baris query ini di file riwayat_surat_keluar.php Anda
$stmtLog = $conn->prepare("
    SELECT r.*, u.nama AS nama_user 
    FROM riwayat_surat r 
    LEFT JOIN users u ON r.id_user = u.id 
    WHERE r.id_surat = ? AND r.jenis = 'surat_keluar' 
    ORDER BY r.id_riwayat DESC
");
$stmtLog->bind_param("i", $id_surat);
$stmtLog->execute();
$riwayat = $stmtLog->get_result();
require_once '../layout/header.php';
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-warning text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Log Riwayat Perjalanan Surat</h5>
        </div>
        <div class="card-body">
            <h6 class="fw-bold text-muted mb-3">No Agenda: <?= htmlspecialchars($surat['no_agenda'] ?? '-') ?></h6>
            <p class="text-secondary small mb-4">Perihal: <?= htmlspecialchars($surat['perihal'] ?? '-') ?></p>

            <div class="timeline-steps">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 25%;">Waktu Operasi</th>
                            <th style="width: 25%;">Pelaku (User)</th>
                            <th>Aktivitas / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($riwayat->num_rows === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada riwayat aktivitas log.</td></tr>
                        <?php else: $no = 1; while ($row = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= date('d-m-Y H:i:s', strtotime($row['created_at'])) ?> WIB</td>
                                <td><?= htmlspecialchars($row['nama_user'] ?? 'Sistem') ?></td>
                                <td>
                                    <span class="badge bg-success"><?= strtoupper(htmlspecialchars($row['status'])) ?></span>
                                </td>
                            </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="kelola_surat_keluar.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
