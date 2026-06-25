<?php
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

// 2. VALIDASI PARAMETER ID SURAT
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<script>alert('ID Surat tidak valid!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$id_surat = (int)$_GET['id'];

// 3. QUERY DATA UTAMA SURAT MASUK
$query_surat = "SELECT no_agenda, no_surat, perihal, asal_surat FROM surat_masuk WHERE id_surat = ?";
$stmt_surat = $conn->prepare($query_surat);
$stmt_surat->bind_param("i", $id_surat);
$stmt_surat->execute();
$res_surat = $stmt_surat->get_result();

if ($res_surat->num_rows === 0) {
    echo "<script>alert('Berkas surat tidak ditemukan!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$surat = $res_surat->fetch_assoc();
$stmt_surat->close();

// 4. QUERY LOG RIWAYAT/MUTASI DISPOSISI SURAT
// Menampilkan riwayat dari tabel log_disposisi / riwayat_disposisi (sesuaikan nama tabel Anda)
$query_log = "SELECT * FROM log_disposisi WHERE id_surat = ? ORDER BY id_log DESC";
$stmt_log = $conn->prepare($query_log);
$stmt_log->bind_param("i", $id_surat);
$stmt_log->execute();
$res_log = $stmt_log->get_result();

require_once '../dashboard/sidebar_admin.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .timeline-steps { display: flex; flex-direction: column; position: relative; padding-left: 20px; }
    .timeline-steps .timeline-step { position: relative; margin-bottom: 25px; padding-left: 25px; }
    .timeline-steps .timeline-step:not(:last-child)::before { content: ""; position: absolute; top: 25px; left: 5px; width: 2px; height: calc(100% + 5px); background-color: #cbd5e1; z-index: 1; }
    .timeline-steps .timeline-content { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; }
    .timeline-icon { position: absolute; left: -5px; top: 2px; width: 22px; height: 22px; background-color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.7rem; z-index: 2; }
</style>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Alur Log & Mutasi Berkas</h4>
            <small class="text-muted">No. Agenda: <span class="fw-bold text-primary"><?= htmlspecialchars($surat['no_agenda'] ?? '-') ?></span></small>
        </div>
        <a href="kelola_surat_masuk.php" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-arrow-left"></i> Kembali ke Log Surat
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block text-uppercase fw-bold text-xs">Nomor Surat Resmi</small>
                    <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($surat['no_surat'] ?? '-') ?></span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block text-uppercase fw-bold text-xs">Asal Satuan / Instansi</small>
                    <span class="fw-bold text-uppercase text-secondary"><?= htmlspecialchars($surat['asal_surat'] ?? '-') ?></span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block text-uppercase fw-bold text-xs">Perihal</small>
                    <span class="text-dark fw-semibold"><?= htmlspecialchars($surat['perihal'] ?? '-') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold py-2 text-xs">
            <i class="bi bi-bezier2 me-1"></i> LOG TIMELINE DISPOSISI & VALIDASI STAF
        </div>
        <div class="card-body p-4">
            <?php if ($res_log->num_rows > 0): ?>
                <div class="timeline-steps">
                    <?php 
                    $no = $res_log->num_rows;
                    while ($log = $res_log->fetch_assoc()): 
                    ?>
                        <div class="timeline-step">
                            <div class="timeline-icon bg-secondary">
                                <span><?= $no-- ?></span>
                            </div>
                            <div class="timeline-content shadow-sm">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                    <div>
                                        <span class="badge bg-primary text-uppercase me-2"><?= htmlspecialchars($log['aktivitas'] ?? 'Mutasi Berkas') ?></span>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= !empty($log['tanggal_log']) ? date('d-m-Y H:i:s', strtotime($log['tanggal_log'])) : '-' ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-xs text-muted d-block">Eksekutif Log:</small>
                                        <span class="badge bg-dark font-monospace text-xs"><?= htmlspecialchars($log['aktor'] ?? $log['user_input'] ?? '-') ?></span>
                                    </div>
                                </div>
                                <div class="row g-2 text-xs">
                                    <div class="col-md-4 border-end">
                                        <span class="text-muted d-block">Dari Posisi / Pengirim:</span>
                                        <strong class="text-danger text-uppercase"><?= htmlspecialchars($log['posisi_lama'] ?? '-') ?></strong>
                                    </div>
                                    <div class="col-md-4 border-end">
                                        <span class="text-muted d-block">Menuju Posisi / Penerima:</span>
                                        <strong class="text-success text-uppercase"><?= htmlspecialchars($log['posisi_baru'] ?? '-') ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <span class="text-muted d-block">Catatan Komando / Keterangan:</span>
                                        <span class="text-dark italic"><?= htmlspecialchars($log['keterangan'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clock-history fs-1 d-block mb-2 text-light"></i>
                    Belum ada rekaman riwayat log mutasi disposisi untuk berkas surat ini.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$stmt_log->close();
$conn->close();
?>
</body>
</html>
