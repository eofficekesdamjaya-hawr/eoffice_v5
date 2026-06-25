<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_surat = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM surat_keluar WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$surat = $stmt->get_result()->fetch_assoc();

if (!$surat) {
    echo "<script>alert('Data surat tidak ditemukan!'); window.location='kelola_surat_keluar.php';</script>";
    exit;
}

require_once '../layout/header.php';
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-info text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-eye me-2"></i>Detail Pengajuan Surat Keluar</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <tr><th style="width: 30%;">No Agenda</th><td><?= htmlspecialchars($surat['no_agenda']) ?></td></tr>
                <tr><th>Asal Satuan</th><td><?= htmlspecialchars($surat['asal_satuan']) ?></td></tr>
                <tr><th>Tanggal Input</th><td><?= date('d-m-Y', strtotime($surat['tanggal_input'])) ?></td></tr>
                <tr><th>No Surat</th><td><?= $surat['no_surat'] ? htmlspecialchars($surat['no_surat']) : '<span class="badge bg-warning text-dark">Draft Setum</span>' ?></td></tr>
                <tr><th>Bentuk / Jenis Surat</th><td><?= htmlspecialchars($surat['shapes_surat'] ?? $surat['bentuk_surat']) ?> / <?= htmlspecialchars($surat['jenis_surat']) ?></td></tr>
                <tr><th>Klasifikasi / Derajat</th><td><?= htmlspecialchars($surat['klasifikasi_surat']) ?> / <?= htmlspecialchars($surat['derajat_surat']) ?></td></tr>
                <tr><th>Tujuan Disposisi</th><td><?= htmlspecialchars($surat['tujuan_disposisi']) ?></td></tr>
                <tr><th>Tujuan Utama</th><td><?= htmlspecialchars($surat['tujuan_utama']) ?></td></tr>
                <tr><th>Perihal</th><td><?= nl2br(htmlspecialchars($surat['perihal'])) ?></td></tr>
                <tr><th>Tembusan</th><td><?= nl2br(htmlspecialchars($surat['tembusan'] ?? '-')) ?></td></tr>
                <tr><th>Keterangan</th><td><?= nl2br(htmlspecialchars($surat['keterangan'] ?? '-')) ?></td></tr>
                <tr><th>Status Proses</th><td><span class="badge bg-primary"><?= htmlspecialchars($surat['status_proses']) ?></span></td></tr>
                <tr>
                    <th>File Dokumen</th>
                    <td>
                        <a href="../uploads/surat_keluar/<?= htmlspecialchars($surat['file_surat']) ?>" target="_blank" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> Buka File PDF
                        </a>
                    </td>
                </tr>
            </table>
            <div class="text-end mt-3">
                <a href="kelola_surat_keluar.php" class="btn btn-secondary px-4">Kembali</a>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
