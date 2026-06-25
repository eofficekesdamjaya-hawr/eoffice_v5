<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// Proteksi Akses Berbasis Email
$user_email  = $_SESSION['id_user'] ?? '';
$allowed_emails = [
    'superadmin@gmail.com',
    'setum@gmail.com',
    'admin@gmail.com',
    'kasituud2026@gmail.com',
    'kakesdamjaya2026@gmail.com',
    'wakakesdamjaya2026@gmail.com',
    'spripimpinan2026@gmail.com'
];

if (empty($_SESSION['id_user']) || ($_SESSION['status'] ?? '') !== "login" || !in_array($user_email, $allowed_emails)) {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}

$role_mapping = [
    'superadmin@gmail.com' => 'superadmin',
    'setum@gmail.com' => 'setum',
    'admin@gmail.com' => 'admin',
    'kasituud2026@gmail.com' => 'kasituud',
    'kakesdamjaya2026@gmail.com' => 'kakesdam',
    'wakakesdamjaya2026@gmail.com' => 'wakakesdam',
    'spripimpinan2026@gmail.com' => 'spri'
];
$user_role = $role_mapping[$user_email] ?? 'ruangan';

// LOGIKA RENDER BADGE STATUS ALUR
if (!function_exists('renderBadgeAlur')) {
    function renderBadgeAlur($status) {
        $status_clean = trim(strtolower($status ?? ''));
        switch ($status_clean) {
            case 'pending': return "<span class='badge bg-warning text-dark'><i class='bi bi-clock'></i> Pending</span>";
            case 'di terima': 
            case 'diterima': return "<span class='badge bg-info text-white'><i class='bi bi-check-circle'></i> Di terima</span>";
            case 'ditolak': return "<span class='badge bg-danger text-white'><i class='bi bi-x-circle'></i> Ditolak</span>";
            case 'proses disposisi': return "<span class='badge bg-primary text-white'><i class='bi bi-shuffle'></i> Proses Disposisi</span>";
            case 'selesai': return "<span class='badge bg-success text-white'><i class='bi bi-check-all'></i> Selesai</span>";
            default: return "<span class='badge bg-secondary text-white'>".ucfirst($status)."</span>";
        }
    }
}

// Kondisi filter pimpinan berdasarkan kolom status_proses murni dari skema surat_keluar
$query_kondisi = "";
if (in_array($user_role, ['kakesdam', 'wakakesdam'])) {
    $query_kondisi = " AND status_proses NOT IN ('Proses Disposisi', 'Pending', 'Baru')";
}

// Fetch Data murni tanpa JOIN eksternal
$sqlRiwayat = "SELECT * FROM surat_keluar WHERE 1=1 $query_kondisi ORDER BY id_surat DESC";
$resRiwayat = mysqli_query($conn, $sqlRiwayat) or die(mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Disposisi Surat Keluar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="d-flex" style="min-height: 100vh;">

    <?php require_once '../dashboard/sidebar_admin.php'; ?>

    <div class="p-4 flex-grow-1" style="overflow-x: hidden;">
        <style>
            .table th { vertical-align: middle; text-align: center; font-size: 0.78rem; text-transform: uppercase; background-color: #0f172a !important; color: #fff; border: 1px solid #1e293b; }
            .table td { font-size: 0.85rem; vertical-align: top; color: #334155; }
            .section-divider-history { border-left: 4px solid #64748b; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }
        </style>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Riwayat Disposisi Surat Keluar</h4>
                    <small class="text-muted">Hak Akses Peninjauan: <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($user_role) ?></span></small>
                </div>
            </div>

            <div class="d-flex justify-content-start mb-3">
                <a href="" class="btn btn-sm btn-secondary text-white fw-bold shadow-sm rounded px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i> REFRESH LOG DATA
                </a>
            </div>

            <div class="section-divider-history text-secondary fs-5 mt-2">
                <i class="bi bi-folder-check me-2"></i>Daftar Berkas Selesai Disposisi / Ditindaklanjuti
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-hover table-striped align-top mb-0">
                        <thead>
                            <tr>
                                <th style="width: 1%;">No</th>
                                <th style="width: 15%;">No Agenda &<br>Asal Ruangan (Seksi)</th>
                                <th style="width: 12%;">No Surat</th>
                                <th style="width: 8%;">Tgl Surat</th>
                                <th style="width: 8%;">Tgl Kirim</th>
                                <th style="width: 10%;">Bentuk / Jenis</th>
                                <th style="width: 15%;">Tujuan Disposisi &<br>Tujuan Utama</th>
                                <th style="width: 20%;">Perihal &<br>Tembusan</th>
                                <th style="width: 4%;">File</th>
                                <th style="width: 5%;">Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($resRiwayat) > 0):
                            while ($row = mysqli_fetch_assoc($resRiwayat)): 
                                // KUNCIAN UTAMA: Mengambil data nama seksi murni dari kolom asal_satuan database
                                $asal_ruangan = (!empty($row['asal_satuan'])) ? $row['asal_satuan'] : 'Internal Kesdam';
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span><br>
                                    <small class="text-dark fw-bold bg-warning bg-opacity-25 px-1 rounded"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($asal_ruangan) ?></small>
                                </td>
                                <td class="font-monospace text-xs fw-bold text-secondary"><?= htmlspecialchars($row['no_surat'] ?? 'Belum Diisi Setum') ?></td>
                                <td class="text-center"><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                                <td class="text-center"><?= !empty($row['tanggal_kirim']) ? date('d-m-Y', strtotime($row['tanggal_kirim'])) : '-' ?></td>
                                <td>
                                    <span class="badge bg-secondary text-xs"><?= htmlspecialchars($row['shapes_surat'] ?? $row['bentuk_surat'] ?? 'Fisik') ?></span><br>
                                    <small class="text-xs text-secondary"><?= htmlspecialchars($row['jenis_surat'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <div class="mb-1 text-xs">Disp Ke: <span class="fw-bold text-primary"><?= htmlspecialchars($row['tujuan_disposisi'] ?? '-') ?></span></div>
                                    <div class="text-xs">Utama: <span class="fw-bold text-success"><?= htmlspecialchars($row['tujuan_utama'] ?? '-') ?></span></div>
                                </td>
                                <td>
                                    <p class="mb-1 fw-bold text-dark text-wrap"><?= htmlspecialchars($row['perihal'] ?? '-') ?></p>
                                    <small class="text-muted d-block border-top pt-1 text-xs">Tembusan: <?= htmlspecialchars($row['tembusan'] ?? '-') ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['file_surat'])) : ?>
                                        <a href="../uploads/surat_keluar/<?= urlencode($row['file_surat']) ?>" target="_blank" class="btn btn-sm btn-outline-danger p-1"><i class="bi bi-file-pdf fs-5"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= renderBadgeAlur($row['status_proses'] ?? 'Pending') ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted bg-white">Belum ada riwayat arsip disposisi surat keluar yang terproses.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
    </div> 
</div> 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
