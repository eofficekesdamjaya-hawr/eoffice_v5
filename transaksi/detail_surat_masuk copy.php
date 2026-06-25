<?php
// ======================================================
// SAFE SESSION & CONFIG
// ======================================================
require_once "../config/session.php";
require_once "../config/koneksi.php";
require_once "../config/session_check.php";

// 1. PROTEKSI AKSES MULTI-ROLE
$allowed_roles = ['superadmin', 'admin', 'setum', 'kasituud', 'kakesdam', 'wakakesdam', 'spri', 'ruangan'];
$user_role = strtolower($_SESSION['tipe_akses'] ?? '');

if (empty($_SESSION['id_user']) || !in_array($user_role, $allowed_roles)) {
    header("Location: ../auth/login_admin.php");
    exit();
}

// 2. VALIDASI PARAMETER ID SURAT (Sintaks Telah Diperbaiki)
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<script>alert('ID Surat tidak valid!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$id_surat = (int)$_GET['id'];

// 3. QUERY DATA DETAIL SURAT MASUK
$query = "SELECT * FROM surat_masuk WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Berkas surat tidak ditemukan!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

require_once '../dashboard/sidebar_admin.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .info-label { font-weight: 600; color: #475569; font-size: 0.85rem; background-color: #f8fafc; width: 30%; }
    .info-value { color: #1e293b; font-size: 0.88rem; }
    .card-header-custom { background-color: #1e293b; color: #ffffff; }
    .signature-box { border: 1px dashed #cbd5e1; padding: 10px; text-align: center; background: #fff; border-radius: 6px; }
    .signature-img { max-height: 80px; object-fit: contain; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Lembar Kendali & Detail Surat Masuk</h4>
            <small class="text-muted">No. Agenda: <span class="fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span></small>
        </div>
        <a href="kelola_surat_masuk.php" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left"></i> Kembali ke Log Surat
        </a>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header card-header-custom fw-bold py-2 text-uppercase text-xs tracking-wider">
                    <i class="bi bi-info-circle me-1"></i> Identitas Klasifikasi Surat
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 align-middle">
                        <tr>
                            <td class="info-label">Asal Satuan / Surat</td>
                            <td class="info-value fw-bold text-uppercase"><?= htmlspecialchars($row['asal_surat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Nomor Surat Resmi</td>
                            <td class="info-value font-monospace fw-bold"><?= htmlspecialchars($row['no_surat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Tanggal Surat</td>
                            <td class="info-value"><?= !empty($row['tanggal_surat']) ? date('d F Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Tanggal Diterima (Setum)</td>
                            <td class="info-value fw-bold text-danger"><?= !empty($row['tanggal_diterima']) ? date('d F Y H:i', strtotime($row['tanggal_diterima'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Bentuk / Jenis</td>
                            <td class="info-value">
                                <span class="badge bg-secondary"><?= htmlspecialchars($row['bentuk_surat'] ?? 'Fisik') ?></span> / 
                                <small class="fw-bold"><?= htmlspecialchars($row['jenis_surat'] ?? '-') ?></small>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Klasifikasi / Sifat</td>
                            <td class="info-value">
                                <span class="text-danger fw-bold text-uppercase me-2"><?= htmlspecialchars($row['klasifikasi_surat'] ?? 'Biasa') ?></span>|
                                <span class="text-warning fw-bold text-uppercase ms-2"><?= htmlspecialchars($row['sifat_surat'] ?? 'Biasa') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Perihal</td>
                            <td class="info-value fw-bold text-dark"><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Tembusan Utama</td>
                            <td class="info-value text-muted text-xs"><?= htmlspecialchars($row['tembusan'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light border-bottom fw-bold py-2 text-dark text-xs">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Tracking Posisi & Validasi Alur
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 align-middle">
                        <tr>
                            <td class="info-label">Status Proses</td>
                            <td class="info-value">
                                <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($row['status_proses'] ?? 'Baru') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Posisi Berkas Terakhir</td>
                            <td class="info-value fw-bold text-success text-uppercase"><?= htmlspecialchars($row['posisi_terakhir'] ?? 'Setum') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Pengirim Terakhir</td>
                            <td class="info-value font-monospace text-xs"><?= htmlspecialchars($row['last_sender'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label">Tahap Disposisi Aktif</td>
                            <td class="info-value"><span class="badge bg-dark">Iterasi Ke-<?= htmlspecialchars($row['tahap_disposisi'] ?? '0') ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4 border-start border-3 border-success">
                <div class="card-header bg-success text-white fw-bold py-2 text-xs">
                    <i class="bi bi-card-checklist me-1"></i> Data Intisari Lembar Disposisi Pimpinan
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-xs text-muted d-block fw-bold mb-1">Diteruskan Kepada (Tujuan Disposisi):</label>
                            <div class="p-2 border rounded bg-light font-monospace text-primary fw-bold text-xs"><?= htmlspecialchars($row['tujuan_disposisi'] ?? '-') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-xs text-muted d-block fw-bold mb-1">Tembusan Pimpinan:</label>
                            <div class="p-2 border rounded bg-light text-xs font-monospace"><?= htmlspecialchars($row['tembusan_pimpinan'] ?? '-') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="text-xs text-muted d-block fw-bold mb-1">Isi Instruksi / Catatan Disposisi:</label>
                            <div class="p-2 border rounded bg-white text-dark text-xs text-wrap border-primary" style="min-height:60px; white-space: pre-line;"><?= htmlspecialchars($row['catatan_disposisi'] ?? 'Belum ada catatan instruksi.') ?></div>
                        </div>
                    </div>

                    <div class="row mt-3 g-2">
                        <div class="col-md-4">
                            <div class="signature-box">
                                <small class="text-xs text-muted d-block border-bottom pb-1 mb-1">TTD Kakesdam</small>
                                <?php if (!empty($row['ttd_kakesdam'])): ?>
                                    <img src="../uploads/ttd_disposisi_masuk/<?= $row['ttd_kakesdam'] ?>" class="signature-img" alt="TTD Kakesdam">
                                <?php else: ?>
                                    <span class="text-muted text-xs d-block py-3">Kosong</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="signature-box">
                                <small class="text-xs text-muted d-block border-bottom pb-1 mb-1">TTD Wakakesdam</small>
                                <?php if (!empty($row['ttd_wakakesdam'])): ?>
                                    <img src="../uploads/ttd_disposisi_masuk/<?= $row['ttd_wakakesdam'] ?>" class="signature-img" alt="TTD Wakakesdam">
                                <?php else: ?>
                                    <span class="text-muted text-xs d-block py-3">Kosong</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="signature-box">
                                <small class="text-xs text-muted d-block border-bottom pb-1 mb-1">Paraf SPRI / Admin</small>
                                <?php if (!empty($row['ttd_spri_image'])): ?>
                                    <img src="../uploads/ttd_disposisi_masuk/<?= $row['ttd_spri_image'] ?>" class="signature-img" alt="Paraf SPRI">
                                <?php else: ?>
                                    <span class="text-muted text-xs d-block py-3">Kosong</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 10;">
                <div class="card-header bg-danger text-white fw-bold py-2 text-xs d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-pdf-fill me-1"></i> Dokumen Lampiran Digital (PDF)</span>
                    <?php if (!empty($row['file_surat'])): ?>
                        <a href="../uploads/surat_masuk/<?= urlencode($row['file_surat']) ?>" target="_blank" class="btn btn-xs btn-light py-0 px-2 fw-bold text-danger text-xs">Buka Tab Baru</a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0 bg-dark" style="height: 680px;">
                    <?php if (!empty($row['file_surat']) && file_exists("../uploads/surat_masuk/" . $row['file_surat'])): ?>
                        <embed src="../uploads/surat_masuk/<?= urlencode($row['file_surat']) ?>#toolbar=1&navpanes=0&scrollbar=1" type="application/pdf" width="100%" height="100%">
                    <?php else: ?>
                        <div class="d-flex flex-column justify-content-center align-items-center text-white h-100 p-4 text-center">
                            <i class="bi bi-file-earmark-pdf text-muted mb-2" style="font-size: 3rem;"></i>
                            <span class="text-xs text-muted">Berkas fisik master PDF tidak ditemukan atau belum diunggah oleh Operator Setum.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
