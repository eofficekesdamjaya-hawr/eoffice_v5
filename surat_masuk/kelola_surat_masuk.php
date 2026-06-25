<?php
session_start();
require_once "../config/koneksi.php";

// 1. PROTEKSI AKSES HALAMAN

if (empty($_SESSION['id_user'])) {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

// Menangani tipe akses baik berupa string maupun array
$tipe_akses_raw = $_SESSION['tipe_akses'] ?? '';
$tipe_akses = is_array($tipe_akses_raw) ? ($tipe_akses_raw[0] ?? '') : $tipe_akses_raw;

$id_user = (int) $_SESSION['id_user'];

// Menangkap role akun dari array session
$role_raw = $_SESSION['username_ruangan'] ?? ($_SESSION['role'] ?? ($_SESSION['username'] ?? ''));
if (is_array($role_raw)) {
    $role_string = $role_raw[0] ?? ($role_raw['role'] ?? ($role_raw['username'] ?? ''));
} else {
    $role_string = $role_raw;
}
$role_ruangan = strtolower(trim($role_string));

$asalMap = [
    'kakesdam_jaya'      => 'Kakesdam Jaya',
    'wakakesdam_jaya'    => 'Wakakesdam Jaya',
    'kasi_tuud'          => 'Kasi Tuud',
    'dandenkeslap'       => 'Dandenkeslap',
    'kasi_was'           => 'Kasi Was',
    'kasi_dukkes'        => 'Kasi Dukkes',
    'kasi_kesprev'       => 'Kasi Kesprev',
    'kasi_renproggar'    => 'Kasi Renproggar',
    'kasi_minlogkes'     => 'Kasi Minlogkes',
    'kasi_matkes'        => 'Kasi Matkes',
    'kasi_yankes'        => 'Kasi Yankes',
    'setum'              => 'SETUM Kesdam Jaya'
];

// ====================================================================
// SQL QUERY: MEMISAHKAN DATA HARI INI DAN TANGGAL SEBELUMNYA
// ====================================================================

if (in_array($tipe_akses, ['admin', 'setum', 'superadmin'])) {
    // 1. Query Hari Ini (Admin/Setum)
    $stmtToday = $conn->prepare("
        SELECT sm.*, sm.status_proses AS status_sub, '' AS catatan_disp 
        FROM surat_masuk sm
        WHERE DATE(sm.tanggal_input) = CURDATE()
        ORDER BY sm.id_surat DESC
    ");
    
    // 2. Query Kemarin & Tanggal Sebelumnya (Admin/Setum)
    $stmtOlder = $conn->prepare("
        SELECT sm.*, sm.status_proses AS status_sub, '' AS catatan_disp 
        FROM surat_masuk sm
        WHERE DATE(sm.tanggal_input) < CURDATE()
        ORDER BY sm.id_surat DESC
    ");
} else {
    // 1. Query Hari Ini (Ruangan)
    $stmtToday = $conn->prepare("
        SELECT sm.*, 
               IFNULL(MAX(ds.status_disposisi), sm.status_proses) AS status_sub, 
               MAX(ds.catatan) AS catatan_disp
        FROM surat_masuk sm
        LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
        WHERE DATE(sm.tanggal_input) = CURDATE() AND (
            LOWER(sm.tujuan_disposisi) = ? 
            OR LOWER(sm.tujuan_utama) = ? 
            OR LOWER(ds.ke) = ? 
            OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
            OR ? = ''
        )
        GROUP BY sm.id_surat
        ORDER BY sm.id_surat DESC
    ");
    $stmtToday->bind_param("sssss", $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan);

    // 2. Query Kemarin & Tanggal Sebelumnya (Ruangan)
    $stmtOlder = $conn->prepare("
        SELECT sm.*, 
               IFNULL(MAX(ds.status_disposisi), sm.status_proses) AS status_sub, 
               MAX(ds.catatan) AS catatan_disp
        FROM surat_masuk sm
        LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
        WHERE DATE(sm.tanggal_input) < CURDATE() AND (
            LOWER(sm.tujuan_disposisi) = ? 
            OR LOWER(sm.tujuan_utama) = ? 
            OR LOWER(ds.ke) = ? 
            OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
            OR ? = ''
        )
        GROUP BY sm.id_surat
        ORDER BY sm.id_surat DESC
    ");
    $stmtOlder->bind_param("sssss", $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan);
}

// Eksekusi Query Hari Ini
$stmtToday->execute();
$resSuratToday = $stmtToday->get_result();

// Eksekusi Query Kemarin & Sebelumnya
$stmtOlder->execute();
$resSuratOlder = $stmtOlder->get_result();

function renderBadgeStatusMasuk($status_raw) {
    $status = strtolower(trim($status_raw ?? 'baru'));
    switch ($status) {
        case 'pending':
        case 'baru':
            return "<span class='badge bg-warning text-dark'><i class='fas fa-clock me-1'></i> Baru</span>";
        case 'proses':
            return "<span class='badge bg-primary text-white'><i class='fas fa-spinner me-1'></i> Proses</span>";
        case 'selesai':
            return "<span class='badge bg-success text-white'><i class='fas fa-check-double me-1'></i> Selesai</span>";
        default:
            return "<span class='badge bg-secondary'>".ucfirst($status)."</span>";
    }
}

require_once '../layout/header.php';
?>

<div class="d-flex" id="wrapper">
    <?php 
    if (in_array($tipe_akses, ['admin', 'setum', 'superadmin'])) {
        include '../dashboard/sidebar_admin.php'; 
    } else {
        include '../ruangan/sidebar.php'; 
    }
    ?>

    <div id="page-content-wrapper" class="w-100 bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm">
            <button class="btn btn-outline-dark btn-sm rounded" id="menu-toggle"><i class="fas fa-bars"></i></button>
            <span class="ms-3 fw-semibold text-secondary">E-Office Kesdam Jaya - Panel Surat Masuk</span>
        </nav>

        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-envelope text-danger me-2"></i>Daftar Surat Masuk</h4>
                    <small class="text-muted">Kelola berkas dan lembar disposisi masuk.</small>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-2"></i> Surat Masuk Hari Ini</h6>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-hover table-striped align-top mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 1%;">No</th>
                                <th style="width: 15%;">No Agenda & Asal</th>
                                <th style="width: 12%;">No Surat</th>
                                <th style="width: 10%;">Tgl Surat</th>
                                <th style="width: 25%;">Perihal & Catatan</th>
                                <th style="width: 4%;">File</th>
                                <th style="width: 4%;">Status</th>
                                <th style="width: 4%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $no = 1;
                        if ($resSuratToday->num_rows > 0):
                            while ($row = $resSuratToday->fetch_assoc()): 
                                $asalRaw = trim($row['asal_surat'] ?? '');
                                $asalSatuan = $asalMap[$asalRaw] ?? $asalRaw;
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span><br>
                                    <small class="text-danger fw-bold"><?= htmlspecialchars($asalSatuan) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['no_surat'] ?? '-') ?></td>
                                <td class="text-center"><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                                <td>
                                    <p class="mb-1 fw-bold text-dark"><?= htmlspecialchars($row['perihal'] ?? '-') ?></p>
                                    <?php if(!empty($row['catatan_disp'])): ?>
                                        <small class="text-muted d-block"><strong>Disposisi Note:</strong> <?= htmlspecialchars($row['catatan_disp']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['file_surat'])) : ?>
                                        <a href="../uploads/surat_masuk/<?= urlencode($row['file_surat']) ?>" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= renderBadgeStatusMasuk($row['status_sub']) ?></td>
                                <td class="text-center">
                                    <a href="detail_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-info text-white btn-sm"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data surat masuk untuk hari ini.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> Surat Masuk Kemarin & Tanggal Sebelumnya</h6>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-hover table-striped align-top mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 1%;">No</th>
                                <th style="width: 15%;">No Agenda & Asal</th>
                                <th style="width: 12%;">No Surat</th>
                                <th style="width: 10%;">Tgl Input / Surat</th>
                                <th style="width: 25%;">Perihal & Catatan</th>
                                <th style="width: 4%;">File</th>
                                <th style="width: 4%;">Status</th>
                                <th style="width: 4%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $noOlder = 1;
                        if ($resSuratOlder->num_rows > 0):
                            while ($rowOlder = $resSuratOlder->fetch_assoc()): 
                                $asalRawOlder = trim($rowOlder['asal_surat'] ?? '');
                                $asalSatuanOlder = $asalMap[$asalRawOlder] ?? $asalRawOlder;
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $noOlder++ ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($rowOlder['no_agenda'] ?? '-') ?></span><br>
                                    <small class="text-danger fw-bold"><?= htmlspecialchars($asalSatuanOlder) ?></small>
                                </td>
                                <td><?= htmlspecialchars($rowOlder['no_surat'] ?? '-') ?></td>
                                <td class="text-center">
                                    <small class="text-muted d-block">In: <?= !empty($rowOlder['tanggal_input']) ? date('d-m-Y', strtotime($rowOlder['tanggal_input'])) : '-' ?></small>
                                    <span class="badge bg-light text-dark border"><?= !empty($rowOlder['tanggal_surat']) ? date('d-m-Y', strtotime($rowOlder['tanggal_surat'])) : '-' ?></span>
                                </td>
                                <td>
                                    <p class="mb-1 fw-bold text-dark"><?= htmlspecialchars($rowOlder['perihal'] ?? '-') ?></p>
                                    <?php if(!empty($rowOlder['catatan_disp'])): ?>
                                        <small class="text-muted d-block"><strong>Disposisi Note:</strong> <?= htmlspecialchars($rowOlder['catatan_disp']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($rowOlder['file_surat'])) : ?>
                                        <a href="../uploads/surat_masuk/<?= urlencode($rowOlder['file_surat']) ?>" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= renderBadgeStatusMasuk($rowOlder['status_sub']) ?></td>
                                <td class="text-center">
                                    <a href="detail_surat_masuk.php?id=<?= $rowOlder['id_surat'] ?>" class="btn btn-info text-white btn-sm"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data arsip surat masuk dari tanggal sebelumnya.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById("menu-toggle").addEventListener("click", function(e) {
    e.preventDefault();
    document.getElementById("wrapper").classList.toggle("toggled");
});
</script>
<?php include '../layout/footer.php'; ?>
