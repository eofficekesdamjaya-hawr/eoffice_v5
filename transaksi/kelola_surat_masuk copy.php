<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
require_once "../config/auth_config.php"; 
require_once "../config/hak_akses.php";   

// PETA IDENTITAS UNTUK LOGIKA RUANGAN
$id_user      = $_SESSION['id_user'] ?? ''; 
$nama_user    = $_SESSION['nama_user'] ?? ($_SESSION['nama'] ?? 'Personel Ruangan'); 
$tipe_akses   = $_SESSION['tipe_akses'] ?? ''; 
$role_ruangan = strtolower(trim($nama_user));

// --- PROTEKSI OTORITAS HALAMAN (SINKRON DENGAN HAK_AKSES.PHP) ---
$allowed_roles = ['superadmin', 'setum', 'admin', 'kasi_tuud', 'kakesdam_jaya', 'wakakesdam_jaya', 'spri_pimpinan', 'ruangan'];

if (!in_array($user_role, $allowed_roles) && $tipe_akses !== 'ruangan') {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki otoritas pengendali surat masuk.'); window.location.href='../dashboard/dashboard_admin.php';</script>";
    exit();
}

// Peta Nama Ruangan Komando Lengkap
$ruanganMap = [
    1  => 'Pers Tuud', 2  => 'Seksi Was', 3  => 'Seksi Dukkes', 
    4  => 'Seksi Kesprev', 5  => 'Seksi Renproggar', 6  => 'Seksi Minlogkes', 
    7  => 'Seksi Matkes', 8  => 'Seksi Yankes', 9  => 'Gudang Kesrah', 
    10 => 'SMK Kesdam Jaya', 11 => 'Dandenkeslap', 12 => 'Ka Primkop',
    13 => 'Paku Kesdam', 14 => 'Kaur Infokes', 15 => 'Kaur Log',
    16 => 'Juyar', 17 => 'Persit', 18 => 'Kaurpam', 19 => 'Kaurdal',
    20 => 'Korpri', 21 => 'Kaur Pers', 22 => 'Kasi tuud'
];

$filter = $_GET['filter'] ?? '';
$additional_query = "";

// Kondisi Logika Penyaringan Surat Masuk Komando
if ($filter === 'disposisi') {
    $additional_query = " AND (sm.status_proses = 'Proses Disposisi' OR sm.status_proses = 'Baru' OR sm.status_proses = 'Pending')";
} elseif ($filter === 'riwayat') {
    $additional_query = " AND (sm.status_proses = 'Proses Disposisi' OR sm.status_proses = 'Disposisi Selesai' OR sm.status_proses = 'Selesai')";
}

// PROTEKSI AKSES ABSOLUT DATA RUANGAN
if ($user_role === 'ruangan' || $tipe_akses === 'ruangan') {
    $safe_role = mysqli_real_escape_string($conn, $role_ruangan);
    $safe_uid  = mysqli_real_escape_string($conn, $id_user);
    
    $additional_query .= " AND (
        LOWER(sm.tujuan_disposisi) = '$safe_role' 
        OR LOWER(sm.tujuan_utama) = '$safe_role' 
        OR LOWER(ds.ke) = '$safe_role'
        OR FIND_IN_SET('$safe_role', ds.tembusan_kasi) > 0
        OR sm.created_by = '$safe_uid'
    )";
}

// --- LOGIKA OTOMATIS FILTER UNTUK PIMPINAN (Kakesdam, Wakakesdam, Spri) ---
$pimpinan_roles = ['kakesdam_jaya', 'wakakesdam_jaya', 'spri_pimpinan'];
if (in_array($user_role, $pimpinan_roles) && empty($filter)) {
    $additional_query .= " AND (sm.status_proses = 'Proses Disposisi' OR sm.status_proses = 'Pending' OR sm.status_proses = 'Baru')";
}

// QUERY DATA SURAT MASUK
$sqlHariIni = "SELECT sm.*, u.nama AS nama_pembuat 
               FROM surat_masuk sm 
               LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
               LEFT JOIN users u ON sm.created_by = u.id 
               WHERE DATE(sm.tanggal_input) = CURDATE() $additional_query 
               GROUP BY sm.id_surat 
               ORDER BY sm.id_surat DESC";
$resHariIni = mysqli_query($conn, $sqlHariIni);

$sqlSebelumnya = "SELECT sm.*, u.nama AS nama_pembuat 
                  FROM surat_masuk sm 
                  LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
                  LEFT JOIN users u ON sm.created_by = u.id 
                  WHERE DATE(sm.tanggal_input) < CURDATE() $additional_query 
                  GROUP BY sm.id_surat 
                  ORDER BY sm.id_surat DESC";
$resSebelumnya = mysqli_query($conn, $sqlSebelumnya);

function renderBadgeAlurMasuk($status) {
    $status_clean = trim(strtolower($status ?? ''));
    switch ($status_clean) {
        case 'baru':
        case 'pending': return "<span class='badge bg-warning text-dark'><i class='bi bi-clock'></i> Baru / Pending</span>";
        case 'di terima': 
        case 'diterima': return "<span class='badge bg-info text-white'><i class='bi bi-check-circle'></i> Diterima</span>";
        case 'ditolak': return "<span class='badge bg-danger text-white'><i class='bi bi-x-circle'></i> Ditolak</span>";
        case 'proses disposisi':
        case 'disposisi selesai': return "<span class='badge bg-primary text-white'><i class='bi bi-shuffle'></i> Terdisposisi</span>";
        case 'selesai': return "<span class='badge bg-success text-white'><i class='bi bi-check-all'></i> Selesai</span>";
        default: return "<span class='badge bg-secondary text-white'>".$status."</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Surat Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table th { vertical-align: middle; text-align: center; font-size: 0.78rem; text-transform: uppercase; background-color: #0f172a !important; color: #fff; border: 1px solid #1e293b; }
        .table td { font-size: 0.85rem; vertical-align: top; color: #1e293b; }
        .section-divider { border-left: 4px solid #16a34a; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }
        .section-divider-old { border-left: 4px solid #64748b; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }
        .dropdown-menu { z-index: 1050 !important; }
    </style>
</head>
<body class="bg-light text-dark">

<div class="d-flex" style="min-height: 100vh;">

<?php 
if ($user_role === 'ruangan' || $tipe_akses === 'ruangan') {
    require_once '../ruangan/sidebar.php'; 
} else {
    require_once '../dashboard/sidebar_admin.php'; 
}
?>

    <div class="p-4 flex-grow-1" style="overflow-x: hidden;">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-envelope-arrow-down-fill text-success me-2"></i>Otoritas Pengendali Surat Masuk Kesdam Jaya</h4>
                    <small class="text-muted">Log Aktivitas Sebagai: <span class="badge bg-danger text-uppercase"><?= htmlspecialchars($nama_user) ?></span></small>
                    <?php if(!empty($filter)): ?>
                        <span class="badge bg-primary ms-2"><i class="bi bi-funnel-fill"></i> MODE FILTER: <?= strtoupper(str_replace('_', ' ', $filter)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if (in_array($user_role, ['superadmin', 'setum', 'admin', 'kasi_tuud'])): ?>
                        <a href="../surat_masuk/tambah_surat_masuk.php" class="btn btn-success fw-bold shadow-sm"><i class="bi bi-plus-lg"></i> Tambah Surat Masuk</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-start mb-3">
                <a href="?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-sm btn-secondary text-white fw-bold shadow-sm rounded px-3">
                    <i class="bi bi-arrow-clockwise me-1"></i> SEGARKAN DATA
                </a>
            </div>

            <div class="section-divider text-success fs-5 mt-2">
                <i class="bi bi-calendar-check-fill me-2"></i>Berkas Surat Masuk Hari Ini
            </div>
            
            <?php displayTableSuratMasuk($resHariIni, $user_role, $tipe_akses, $ruanganMap); ?>

            <div class="section-divider-old text-secondary fs-5 mt-4">
                <i class="bi bi-clock-history me-2"></i>Surat Masuk Sebelum Hari Ini (Kemarin / Lampau)
            </div>
            
            <?php displayTableSuratMasuk($resSebelumnya, $user_role, $tipe_akses, $ruanganMap); ?>
        </div>
    </div> 
</div>

<?php 
function displayTableSuratMasuk($result, $user_role, $tipe_akses, $ruanganMap) {
    global $user_email; 
?>
<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-hover table-striped align-top mb-0">
            <thead>
                <tr>
                    <th style="width: 1%;">No</th>
                    <th style="width: 12%;">No Agenda<br>Asal Satuan</th>
                    <th style="width: 10%;">No Surat</th>
                    <th style="width: 8%;">Tgl Surat</th>
                    <th style="width: 8%;">Tgl Terima</th>
                    <th style="width: 8%;">Bentuk / Jenis</th>
                    <th style="width: 8%;">Klasifikasi / Sifat</th>
                    <th style="width: 14%;">Tujuan Disposisi &<br>Tujuan Utama</th>
                    <th style="width: 18%;">Perihal &<br>Tembusan</th>
                    <th style="width: 3%;">File</th>
                    <th style="width: 5%;">Status</th>
                    <th style="width: 10%;">Aksi Komando</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)): 
                    $asal_ruangan = !empty($row['asal_surat']) ? $row['asal_surat'] : ($ruanganMap[$row['created_by']] ?? 'Internal Kesdam');
            ?>
                <tr>
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td>
                        <span class="fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span><br>
                        <small class="text-muted fw-bold text-uppercase"><?= htmlspecialchars($asal_ruangan) ?></small>
                    </td>
                    <td class="font-monospace text-xs fw-bold text-secondary"><?= htmlspecialchars($row['no_surat'] ?? 'Belum Diisi Setum') ?></td>
                    <td class="text-center"><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                    <td class="text-center"><?= !empty($row['tanggal_diterima']) ? date('d-m-Y', strtotime($row['tanggal_diterima'])) : '-' ?></td>
                    <td>
                        <span class="badge bg-secondary text-xs"><?= htmlspecialchars($row['shapes_surat'] ?? ($row['bentuk_surat'] ?? 'Fisik')) ?></span><br>
                        <small class="text-xs text-secondary"><?= htmlspecialchars($row['jenis_surat'] ?? '-') ?></small>
                    </td>
                    <td>
                        <span class="text-danger fw-bold text-xs d-block text-uppercase"><?= htmlspecialchars($row['klasifikasi_surat'] ?? 'Biasa') ?></span>
                        <span class="text-warning fw-bold text-xs d-block text-uppercase"><?= htmlspecialchars($row['sifat_surat'] ?? 'Biasa') ?></span>
                    </td>
                    <td>
                        <div class="mb-1 text-xs">Disp: <span class="badge bg-light text-primary border"><?= htmlspecialchars($row['tujuan_disposisi'] ?? '-') ?></span></div>
                        <div class="text-xs">Utama: <span class="fw-bold text-success"><?= htmlspecialchars($row['tujuan_utama'] ?? '-') ?></span></div>
                    </td>
                    <td>
                        <p class="mb-1 fw-bold text-dark text-wrap"><?= htmlspecialchars($row['perihal'] ?? '-') ?></p>
                        <small class="text-muted d-block border-top pt-1 text-xs">Tembusan: <?= htmlspecialchars($row['tembusan'] ?? '-') ?></small>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($row['file_surat'])) : ?>
                            <a href="../uploads/<?= urlencode($row['file_surat']) ?>" target="_blank" class="btn btn-sm btn-outline-danger p-1"><i class="bi bi-file-pdf fs-5"></i></a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="text-center"><?= renderBadgeAlurMasuk($row['status_proses'] ?? 'Pending') ?></td>
                    
                    <td>
                        <div class="d-flex flex-column gap-1">
                            
                            <?php 
                            // GRUP 1: Otoritas Penuh & Pengendali Utama Komando (Superadmin, Kakesdam, Wakakesdam, Kasi TUUD)
                            if (in_array($user_role, ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya', 'kasi_tuud'])): 
                            ?>
                                <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#verifModalMasuk<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
                                <a href="../disposisi/disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
                                <a href="../tte/ttd_surat.php?id=<?= $row['id_surat'] ?>&action=tte" class="btn btn-success btn-sm text-xs fw-bold"><i class="bi bi-pen-fill"></i> TTD Surat</a>
                                <a href="hapus_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Permanen Berkas Surat Masuk?')"><i class="bi bi-trash"></i> Hapus</a>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-xs" href="detail_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                                        <li><a class="dropdown-item text-xs" href="../disposisi/riwayat_disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                                        <li><button type="button" class="dropdown-item text-xs" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_surat'] ?>"><i class="bi bi-pencil"></i> Edit No/Tgl/Surat</button></li>
                                    </ul>
                                </div>

                            <?php 
                            // GRUP 2: Staf Sekretariat / Pengelola Berkas (Setum & Admin)
                            elseif (in_array($user_role, ['setum', 'admin'])): 
                            ?>
                                <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#verifModalMasuk<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
                                <a href="../disposisi/disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
                                
                                <?php if ($user_email === 'admin@gmail.com'): ?>
                                    <a href="hapus_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Berkas?')"><i class="bi bi-trash"></i> Hapus</a>
                                <?php endif; ?>

                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-xs" href="detail_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                                        <li><a class="dropdown-item text-xs" href="../disposisi/riwayat_disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                                        <li><button type="button" class="dropdown-item text-xs" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_surat'] ?>"><i class="bi bi-pencil-square"></i> Edit No/Tgl Surat</button></li>
                                    </ul>
                                </div>

                            <?php 
                            // GRUP 3: Spri Pimpinan (Akses Terbatas, Tidak Ada Tombol TTD & Hapus)
                            elseif ($user_role === 'spri_pimpinan'): 
                            ?>
                                <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#verifModalMasuk<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
                                <a href="../disposisi/disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
                                
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item text-xs" href="detail_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                                        <li><a class="dropdown-item text-xs" href="../disposisi/riwayat_disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                                    </ul>
                                </div>

                            <?php 
                            // GRUP DEFAULT: Ruangan / User Lain (Hanya bisa revisi/jawab)
                            else: 
                            ?>
                                <a href="../disposisi/disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-reply-all-fill"></i> Jawab / Revisi</a>
                            <?php endif; ?>

                        </div>

                        <div class="modal fade" id="verifModalMasuk<?= $row['id_surat'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="../transifikasi/proses_verifikasi_surat.php" method="POST" class="modal-content">
                                    <div class="modal-header bg-warning text-dark">
                                        <h5 class="modal-title fw-bold fs-6"><i class="bi bi-shield-check"></i> Lembar Verifikasi No: <?= htmlspecialchars($row['no_agenda'] ?? '-') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <input type="hidden" name="id_surat" value="<?= $row['id_surat'] ?>">
                                        <input type="hidden" name="jenis_tabel" value="masuk">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-xs mb-1">Status Verifikasi Berkas Masuk:</label>
                                            <select class="form-select border-primary" name="status_proses" required>
                                                <option value="Baru" <?= ($row['status_proses'] ?? '') == 'Baru' ? 'selected':'' ?>>Baru</option>
                                                <option value="Pending" <?= ($row['status_proses'] ?? '') == 'Pending' ? 'selected':'' ?>>Pending</option>
                                                <option value="Diterima" <?= ($row['status_proses'] ?? '') == 'Diterima' ? 'selected':'' ?>>Diterima</option>
                                                <option value="Ditolak" <?= ($row['status_proses'] ?? '') == 'Ditolak' ? 'selected':'' ?>>Ditolak</option>
                                                <option value="Proses Disposisi" <?= ($row['status_proses'] ?? '') == 'Proses Disposisi' ? 'selected':'' ?>>Proses Disposisi</option>
                                                <option value="Selesai" <?= ($row['status_proses'] ?? '') == 'Selesai' ? 'selected':'' ?>>Selesai</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-xs mb-1">Catatan Verifikator:</label>
                                            <textarea class="form-control text-xs" name="catatan_verif" rows="3" placeholder="Tulis instruksi koreksi berkas..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="submit_verif" class="btn btn-warning btn-sm fw-bold">Simpan Keputusan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="modal fade" id="editModal<?= $row['id_surat'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="proses_edit_setum.php" method="POST" enctype="multipart/form-data" class="modal-content">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-pencil-square"></i> Kamar Sunting Berkas</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start text-xs">
                                        <input type="hidden" name="id_surat" value="<?= $row['id_surat'] ?>">
                                        
                                        <div class="mb-2">
                                            <label class="form-label fw-bold mb-1">Nomor Surat Resmi:</label>
                                            <input type="text" class="form-control form-control-sm font-monospace" name="no_surat" value="<?= htmlspecialchars($row['no_surat'] ?? '') ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label fw-bold mb-1">Tanggal Surat Resmi:</label>
                                            <input type="date" class="form-control form-control-sm" name="tanggal_surat" value="<?= $row['tanggal_surat'] ?? '' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label fw-bold mb-1">Tanggal Terima:</label>
                                            <input type="date" class="form-control form-control-sm" name="tanggal_diterima" value="<?= !empty($row['tanggal_diterima']) ? date('Y-m-d', strtotime($row['tanggal_diterima'])) : '' ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label fw-bold mb-1">Ganti / Perbarui Berkas Scan Surat Masuk (PDF):</label>
                                            <input type="file" class="form-control form-control-sm" name="file_surat" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="submit_edit_setum" class="btn btn-primary btn-sm w-100 fw-bold">Simpan Perubahan Berkas</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </td>
                </tr>
            <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted bg-white">Belum ada rekaman log berkas surat masuk.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php 
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>