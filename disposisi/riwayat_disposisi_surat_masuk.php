<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/koneksi.php";

// 1. Ambil ID & Query Data Surat Utama
$id_surat_get = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_surat_get <= 0) {
    header("Location: ../transaksi/kelola_surat_masuk.php");
    exit;
}

$query_tampil = "SELECT * FROM surat_masuk WHERE id_surat = ?";
$stmt_t = $conn->prepare($query_tampil);
$stmt_t->bind_param("i", $id_surat_get);
$stmt_t->execute();
$surat = $stmt_t->get_result()->fetch_assoc();

if (!$surat) {
    echo "<script>alert('Data surat tidak ditemukan!'); window.location='../transaksi/kelola_surat_masuk.php';</script>";
    exit;
}

// 2. Load Auth Config (Variabel $role, $dari, $is_admin_setum otomatis tersedia)
require_once "../config/auth_config.php";

// Validasi Login Utama
if (empty($_SESSION['id_user'])) {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}

$user_role = $role; // Diambil dari auth_config.php

// 3. Ambil Seluruh Linimasa / Riwayat Disposisi Terkait
$query_riwayat = "SELECT * FROM disposisi_surat WHERE id_surat = ? AND jenis_surat = 'masuk' ORDER BY id_disposisi ASC";
$stmt_r = $conn->prepare($query_riwayat);
$stmt_r->bind_param("i", $id_surat_get);
$stmt_r->execute();
$riwayat_res = $stmt_r->get_result();

// Format pemetaan nama peran agar tampilan lebih rapi dan resmi
$nama_peran_map = [
    'kakesdam_jaya'      => 'Kakesdam Jaya',
    'wakakesdam_jaya'    => 'Wakakesdam Jaya',
    'dandenkeslap'       => 'Dandenkeslap',
    'setum'              => 'Staf Umum (Setum)',
    'kasi_tuud'          => 'Kasi Tuud',
    'kasi_was'           => 'Kasi Was',
    'kasi_dukkes'        => 'Kasi Dukkes',
    'kasi_kesprev'       => 'Kasi Kesprev',
    'kasi_renproggar'    => 'Kasi Renproggar',
    'kasi_minlogkes'     => 'Kasi Minlogkes',
    'kasi_matkes'        => 'Kasi Matkes',
    'kasi_yankes'        => 'Kasi Yankes',
    'ka_primkop'         => 'Ka Primkop',
    'kagud_kesrah'       => 'Kagud Kesrah',
    'pers_tuud'          => 'Pers Tuud',
    'kaur_infokes'       => 'Kaur Infokes',
    'kaur_pers'          => 'Kaur Pers',
    'kaur_log'           => 'Kaur Log',
    'kaur_dal'           => 'Kaurdal',
    'kaur_pam'           => 'Kaurpam',
    'juyar'              => 'Juyar',
    'paku_kesdam'        => 'Paku Kesdam',
    'ka_smk_kesdam'      => 'Ka SMK Kesdam Jaya',
    'persit_kck_ranting5'=> 'Persit',
    'korpri_kesdam'      => 'Korpri',
    'spri_pimpinan'      => 'Spri Pimpinan'
];

function formatPeran($role, $map) {
    return $map[$role] ?? ucwords(str_replace('_', ' ', $role));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Disposisi Surat Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .bg-kesdam-dark { background-color: #1e293b !important; }
        .text-kesdam-dark { color: #1e293b !important; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .timeline-item { position: relative; padding-left: 2.5rem; border-left: 3px solid #cbd5e1; margin-bottom: 1.5rem; }
        .timeline-item:last-child { border-left: 3px solid transparent; margin-bottom: 0; }
        .timeline-icon { position: absolute; left: -14px; top: 0; width: 25px; height: 25px; border-radius: 50%; background-color: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; border: 3px solid #fff; box-shadow: 0 0 0 3px #cbd5e1; }
        .timeline-item:first-child .timeline-icon { background-color: #10b981; box-shadow: 0 0 0 3px #a7f3d0; }
        .ttd-img { max-height: 70px; object-fit: contain; background-color: #fff; border: 1px dashed #cbd5e1; padding: 4px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- TOMBOL KEMBALI -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <a href="../transaksi/kelola_surat_masuk.php" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-pill fw-medium">
                    <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali ke Kelola Surat
                </a>
                <span class="badge bg-kesdam-dark px-3 py-2 rounded-pill">E-Office Kesdam Jaya</span>
            </div>

            <!-- RINGKASAN SURAT MASUK -->
            <div class="card card-custom mb-4 overflow-hidden">
                <div class="card-header bg-kesdam-dark text-white p-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-warning"></i> Ringkasan Berkas Surat</h5>
                    <span class="badge bg-light text-dark fw-bold">ID #<?= $surat['id_surat'] ?></span>
                </div>
                <div class="card-body bg-white p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 35%;">No. Agenda</td>
                                    <td>: <strong class="text-dark"><?= htmlspecialchars($surat['no_agenda'] ?? '-') ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Surat</td>
                                    <td>: <?= htmlspecialchars($surat['no_surat'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal Surat</td>
                                    <td>: <?= !empty($surat['tgl_surat']) ? date('d-m-Y', strtotime($surat['tgl_surat'])) : '-' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 35%;">Asal Surat</td>
                                    <td>: <span class="fw-medium text-dark"><?= htmlspecialchars($surat['asal_surat'] ?? '-') ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Perihal</td>
                                    <td>: <span class="text-kesdam-dark fw-semibold"><?= htmlspecialchars($surat['perihal'] ?? '-') ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Proses</td>
                                    <td>: <span class="badge bg-info text-dark px-2 rounded-1 fw-bold"><?= htmlspecialchars($surat['status_proses'] ?? 'Baru') ?></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LINIMASA RIWAYAT DISPOSISI -->
            <div class="card card-custom">
                <div class="card-header bg-white border-bottom p-3">
                    <h5 class="mb-0 fw-bold text-kesdam-dark"><i class="bi bi-diagram-3-fill text-primary me-2"></i> Runtutan Alokasi Lembar Disposisi</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if ($riwayat_res->num_rows === 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x text-muted display-4"></i>
                            <p class="text-muted mt-3 mb-0">Belum ada riwayat alokasi disposisi untuk surat ini.</p>
                            <a href="disposisi_surat_masuk.php?id=<?= $surat['id_surat'] ?>" class="btn btn-sm btn-success mt-3 fw-bold">
                                <i class="bi bi-reply-all-fill me-1"></i> Mulai Disposisi Pertama
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php 
                            $no = 1;
                            while ($row = $riwayat_res->fetch_assoc()): 
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <small style="font-size: 0.65rem; font-weight: bold; line-height: 19px;"><?= $no++ ?></small>
                                    </div>
                                    <div class="card bg-light border-0 p-3 rounded-3 shadow-sm mb-2">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-2">
                                            <div>
                                                <span class="fw-bold text-primary"><i class="bi bi-person-fill"></i> Dari: <?= htmlspecialchars($row['dari']) ?></span> 
                                                <span class="text-muted mx-1"> menuju </span>
                                                <span class="fw-bold text-success"><i class="bi bi-arrow-right-circle-fill"></i> Ke: <?= formatPeran($row['ke'], $nama_peran_map) ?></span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                    <i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?> WIB
                                                </small>
                                            </div>
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-md-9">
                                                <p class="mb-2 text-dark" style="font-size: 0.95rem; white-space: pre-line;">
                                                    <strong>Catatan / Instruksi:</strong><br><?= htmlspecialchars($row['catatan'] ?? '-') ?>
                                                </p>
                                                
                                                <?php if (!empty($row['tembusan_kasi']) && $row['tembusan_kasi'] !== 'tidak_ada'): ?>
                                                    <div class="mt-2 pt-2 border-top">
                                                        <span class="text-muted small d-block"><strong><i class="bi bi-info-circle"></i> Tembusan Ditujukan Kepada:</strong></span>
                                                        <?php 
                                                        $arr_t = explode(',', $row['tembusan_kasi']);
                                                        foreach ($arr_t as $t_role) {
                                                            echo '<span class="badge bg-secondary me-1 my-1 px-2 py-1">' . formatPeran(trim($t_role), $nama_peran_map) . '</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- KOLOM TANDA TANGAN -->
                                            <div class="col-md-3 text-md-end text-start border-start-md border-top-sm pt-2 pt-md-0 d-flex flex-column align-items-md-end justify-content-center">
                                                <span class="text-muted small d-block mb-1">Otorisasi Digital</span>
                                                <?php if (!empty($row['ttd_disposisi']) && $row['ttd_disposisi'] !== 'no_signature'): ?>
                                                    <img src="<?= $row['ttd_disposisi'] ?>" alt="Tanda Tangan" class="ttd-img img-fluid">
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1 small"><i class="bi bi-exclamation-triangle"></i> Tanpa TTD</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- CARD FOOTER -->
                <div class="card-footer bg-light p-3 text-end">
                    <a href="disposisi_surat_masuk.php?id=<?= $surat['id_surat'] ?>" class="btn btn-sm btn-success fw-bold px-3">
                        <i class="bi bi-reply-fill fs-6 align-middle"></i> Teruskan / Jawab Disposisi Baru
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt_r->close();
$stmt_t->close();
$conn->close();
?>
