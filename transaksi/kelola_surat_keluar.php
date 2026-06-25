<?php

require_once __DIR__.'/../config/session.php';
require_once "../config/koneksi.php";
require_once "../config/auth_config.php";
require_once "../config/hak_akses.php";  

// SINKRONISASI DENGAN STRUKTUR SESSION ANDA
$id_user     = $_SESSION['id_user'] ?? '';
$nama_user   = $_SESSION['nama_user'] ?? 'Personel Ruangan';
$tipe_akses  = $_SESSION['tipe_akses'] ?? '';

// Menentukan user_role untuk kebutuhan filter (diambil dari tipe_akses session)
$user_role   = $tipe_akses;
$user_email  = $_SESSION['email'] ?? ''; 

$ruanganMap = [
    1 => 'Seksi Tuud', 2 => 'Seksi Was', 3 => 'Seksi Dukkes',
    4 => 'Seksi Kesprev', 5 => 'Seksi Renproggar', 6 => 'Seksi Minlogkes',
    7 => 'Seksi Matkes', 8 => 'Seksi Yankes', 9 => 'Gudang Kesrah', 10 => 'SMK Kesdam Jaya'
];

$filter = $_GET['filter'] ?? '';
$additional_query = "";

// Kondisi Logika Penyaringan Surat Komando
if ($filter === 'disposisi') {
    $additional_query = " AND (status_proses = 'Proses Disposisi' OR status_proses = 'Pending')";
} elseif ($filter === 'belum_ttd') {
    $additional_query = " AND status_proses = 'Di terima'";
} elseif ($filter === 'sudah_ttd') {
    $additional_query = " AND status_proses = 'Selesai'";
}

// MENYESUAIKAN ROLE: Jika pimpinan/ruangan membuka halaman utama tanpa filter khusus
if ($user_role === 'pimpinan' && empty($filter)) {
    $additional_query .= " AND (status_proses = 'Proses Disposisi' OR status_proses = 'Pending')";
}

// PROTEKSI DATA: Jika tipe aksesnya ruangan, batasi hanya melihat surat miliknya sendiri
if ($user_role === 'ruangan') {
    $safe_creator = mysqli_real_escape_string($conn, $id_user);
    $additional_query .= " AND (created_by = '$safe_creator' OR status_proses = 'Selesai')";
}

// QUERY DATA SINKRON
$sqlHariIni = "SELECT * FROM surat_keluar WHERE DATE(created_at) = CURDATE() $additional_query ORDER BY id_surat DESC";
$resHariIni = mysqli_query($conn, $sqlHariIni);

$sqlSebelumnya = "SELECT * FROM surat_keluar WHERE DATE(created_at) < CURDATE() $additional_query ORDER BY id_surat DESC";
$resSebelumnya = mysqli_query($conn, $sqlSebelumnya);

function renderBadgeAlur($status) {
    $status_clean = trim(strtolower($status ?? ''));
    switch ($status_clean) {
        case 'pending': return "<span class='badge bg-warning text-dark'><i class='bi bi-clock'></i> Pending</span>";
        case 'di terima':
        case 'diterima': return "<span class='badge bg-info text-white'><i class='bi bi-check-circle'></i> Di terima</span>";
        case 'ditolak': return "<span class='badge bg-danger text-white'><i class='bi bi-x-circle'></i> Ditolak</span>";
        case 'proses disposisi': return "<span class='badge bg-primary text-white'><i class='bi bi-shuffle'></i> Proses Disposisi</span>";
        case 'selesai': return "<span class='badge bg-success text-white'><i class='bi bi-check-all'></i> Selesai</span>";
        default: return "<span class='badge bg-secondary text-white'>".htmlspecialchars($status)."</span>";
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="d-flex bg-light text-dark" style="min-height: 100vh; width: 100%;">

<?php
if ($user_role === 'ruangan' || $user_role === 'stranger') {
    require_once '../ruangan/sidebar.php';
} else {
    require_once '../dashboard/sidebar_admin.php';
}
?>
    <style>
    .table-responsive {
        overflow: visible !important;
        position: relative;
    }
    .dropdown-menu { 
        z-index: 1060 !important; 
    }
    .table th { vertical-align: middle; text-align: center; font-size: 0.78rem; text-transform: uppercase; background-color: #1e293b !important; color: #fff; border: 1px solid #334155; }
    .table td { font-size: 0.85rem; vertical-align: top; color: #334155; }
    .section-divider { border-left: 4px solid #0284c7; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }
    .section-divider-old { border-left: 4px solid #64748b; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }
    </style>

    <div class="container-fluid py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0"><i class="bi bi-envelope-arrow-up-fill text-info me-2"></i>Otoritas Pengendali Surat Keluar Kesdam Jaya</h4>
                <small class="text-muted">Log Aktivitas Sebagai: <span class="badge bg-danger text-uppercase"><?= htmlspecialchars($nama_user) ?></span></small>
                <?php if(!empty($filter)): ?>
                    <span class="badge bg-primary ms-2"><i class="bi bi-funnel-fill"></i> MODE FILTER: <?= strtoupper(str_replace('_', ' ', $filter)) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-start mb-3">
            <a href="?filter=<?= urlencode($filter) ?>" class="btn btn-sm btn-info text-white fw-bold shadow-sm rounded px-3" style="border: 1px solid #0284c7;">
                <i class="bi bi-arrow-clockwise me-1"></i> SEGARKAN DATA (REFRESH)
            </a>
        </div>

        <div class="section-divider text-info fs-5 mt-2">
            <i class="bi bi-calendar-check-fill me-2"></i>Draf Surat Keluar Masuk Hari Ini
        </div>
        
        <?php displayTableSurat($resHariIni, $user_role, $ruanganMap, $user_email); ?>

        <div class="section-divider-old text-secondary fs-5 mt-4">
            <i class="bi bi-clock-history me-2"></i>Draf Masuk Sebelum Hari Ini (Kemarin / Lampau)
        </div>
        
        <?php displayTableSurat($resSebelumnya, $user_role, $ruanganMap, $user_email); ?>
    </div>
</div>

<?php
// DEKLARASI FUNGSI KONTEN UTAMA - STRUKTUR FIXED
function displayTableSurat($result, $user_role, $ruanganMap, $user_email) {
?>
<div class="card shadow-sm border-0 rounded-3 mb-4 mx-1">
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-hover table-striped align-top mb-0">
            <thead>
                <tr>
                    <th style="width: 1%;">No</th>
                    <th style="width: 12%;">No Agenda<br>Asal Satuan</th>
                    <th style="width: 10%;">No Surat</th>
                    <th style="width: 8%;">Tgl Surat</th>
                    <th style="width: 8%;">Tgl Kirim</th>
                    <th style="width: 8%;">Bentuk / Jenis</th>
                    <th style="width: 8%;">Klasifikasi / Derajat</th>
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
                    $asalMap = [
                        'kakesdam_jaya'      => 'Kakesdam Jaya',
                        'wakakesdam_jaya'    => 'Wakakesdam Jaya',
                        'dandenkeslap'       => 'Dandenkeslap',
                        'setum'              => 'Setum',
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

                    $kode_asal = trim($row['created_by'] ?? '');
                    $asal_ruangan = isset($asalMap[$kode_asal]) ? $asalMap[$kode_asal] : ($row['asal_satuan'] ?? 'Ruangan');
            ?>
                <tr>
                    <td class="text-center fw-bold"><?= $no++ ?></td>
                    <td>
                        <span class="fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span><br>
                        <small class="text-muted fw-bold"><?= htmlspecialchars($asal_ruangan) ?></small>
                    </td>
                    <td class="font-monospace text-xs fw-bold text-secondary"><?= htmlspecialchars($row['no_surat'] ?? 'Belum Diisi Setum') ?></td>
                    <td class="text-center"><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
                    <td class="text-center"><?= !empty($row['tanggal_kirim']) ? date('d-m-Y', strtotime($row['tanggal_kirim'])) : '-' ?></td>
                    <td>
                        <span class="badge bg-secondary text-xs"><?= htmlspecialchars($row['shapes_surat'] ?? $row['bentuk_surat'] ?? 'Fisik') ?></span><br>
                        <small class="text-xs text-secondary"><?= htmlspecialchars($row['jenis_surat'] ?? '-') ?></small>
                    </td>
                    <td>
                        <span class="text-danger fw-bold text-xs d-block"><?= htmlspecialchars($row['klasifikasi_surat'] ?? 'Biasa') ?></span>
                        <span class="text-warning fw-bold text-xs d-block"><?= htmlspecialchars($row['derajat_surat'] ?? 'Biasa') ?></span>
                    </td>
                    <td>
                        <div class="mb-1 text-xs">Disp: <span class="fw-bold text-primary"><?= htmlspecialchars($row['tujuan_disposisi'] ?? '-') ?></span></div>
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
                    <td>


                    <div class="d-flex flex-column gap-1">
    <?php if (trim(strtolower($row['status_proses'] ?? '')) === 'selesai'): ?>
        <span class="btn btn-success btn-sm fw-bold disabled w-100">
            <i class="bi bi-patch-check-fill"></i> Sudah TTD
        </span>
        
        <?php if (in_array($user_role, ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya', 'kasi_tuud'])): ?>
            <a href="../tte/hapus_ttd_aksi.php?id=<?= $row['id_surat'] ?>&jenis=keluar" 
               class="btn btn-outline-danger btn-sm text-xs" 
               onclick="return confirm('Batalkan status TTD berkas ini?')">
               <i class="bi bi-x-circle"></i> Batal TTD
            </a>
        <?php endif; ?>

    <?php else: ?>
        <?php if (in_array($user_role, ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya', 'kasi_tuud'])): ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm custom-modal-btn" data-target-modal="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
            <a href="../tte/ttd_surat.php?id=<?= $row['id_surat'] ?>&action=tte" class="btn btn-success btn-sm text-xs fw-bold"><i class="bi bi-pen-fill"></i> TTD Surat</a>
            <a href="../surat_keluar/hapus_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Permanen Berkas?')"><i class="bi bi-trash"></i> Hapus</a>

        <?php elseif (in_array($user_role, ['setum', 'admin'])): ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold custom-modal-btn" data-target-modal="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
            <?php if ($user_email === 'admin@gmail.com'): ?>
                <a href="../surat_keluar/hapus_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Berkas?')"><i class="bi bi-trash"></i> Hapus</a>
            <?php endif; ?>

        <?php elseif ($user_role === 'spri_pimpinan'): ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold custom-modal-btn" data-target-modal="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>

        <?php else: ?>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-reply-all-fill"></i> Jawab / Revisi</a>
        <?php endif; ?>
    <?php endif; ?>

    <div class="dropdown custom-dropdown mt-1">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle-btn w-100 text-xs" type="button">Menu Lainnya</button>
        <ul class="dropdown-menu-list bg-white border rounded shadow-sm p-2 d-none position-absolute" style="z-index:1090; min-width:160px; list-style:none;">
            <li class="mb-1"><a class="text-decoration-none text-dark d-block p-1 text-xs" href="detail_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
            <li class="mb-1"><a class="text-decoration-none text-dark d-block p-1 text-xs" href="riwayat_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
            <?php if (in_array($user_email, ['superadmin@gmail.com', 'setum@gmail.com', 'admin@gmail.com'])): ?>
                <li><button type="button" class="border-0 bg-transparent text-dark w-100 text-start p-1 text-xs custom-modal-btn" data-target-modal="#modalEditNoTgl<?= $row['id_surat'] ?>"><i class="bi bi-pencil-square text-warning"></i> Edit No/Tgl Surat</button></li>
            <?php endif; ?>
        </ul>

                        <div class="modal fade" id="verifModal<?= $row['id_surat'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="../transifikasi/proses_verifikasi_surat.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $row['id_surat'] ?>">
                                        <div class="modal-header bg-warning text-dark">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-shield-check"></i> Verifikasi Berkas Surat</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <p class="mb-3">Tentukan status verifikasi draf surat dengan nomor agenda <strong><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></strong>:</p>
                                            <div class="mb-3">
                                                <label for="statusSelect<?= $row['id_surat'] ?>" class="form-label fw-bold text-secondary">Pilih Status Berkas:</label>
                                                <select class="form-select" id="statusSelect<?= $row['id_surat'] ?>" name="status" required>
                                                    <option value="" disabled selected>-- Pilih Status --</option>
                                                    <option value="Di terima">✅ Diterima</option>
                                                    <option value="Ditolak">❌ Ditolak</option>
                                                    <option value="Proses Disposisi">📝 Proses Disposisi</option>
                                                    <option value="Sudah Didisposisikan">📌 Sudah Didisposisikan</option>
                                                    <option value="Dalam Proses">⚙️ Dalam Proses</option>
                                                    <option value="Ditindaklanjuti">📤 Ditindaklanjuti/Dijawab</option>
                                                    <option value="Selesai">📂 Selesai & Diarsipkan</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success fw-bold"><i class="bi bi-check-lg"></i> Simpan Verifikasi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php if (in_array($user_role, ['superadmin', 'setum', 'admin']) || in_array($user_email, ['superadmin@gmail.com', 'setum@gmail.com', 'admin@gmail.com'])): ?>
                        <div class="modal fade" id="modalEditNoTgl<?= $row['id_surat'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form method="POST" action="proses_edit_no_tgl.php">
                                    <div class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning"></i> Ubah Nomor & Tanggal Surat</h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <input type="hidden" name="id_surat" value="<?= $row['id_surat'] ?>">
<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Nomor Surat Baru</label>
    <input type="text" name="no_surat" class="form-control" value="<?= htmlspecialchars($row['no_surat'] ?? '') ?>" required>
</div>
<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Tanggal Surat Baru</label>
    <input type="date" name="tgl_surat" class="form-control" value="<?= $row['tanggal_surat'] ?? '' ?>" required>
</div>
<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Tanggal Kirim Baru</label>
    <input type="date" name="tgl_kirim" class="form-control" value="<?= $row['tanggal_kirim'] ?? '' ?>" required>
</div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm btn-success fw-bold">Simpan Perubahan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted bg-white">Belum ada rekaman log draf berkas surat keluar.</td>
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
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Fungsi Dropdown Manual
    var dropButtons = document.querySelectorAll('.dropdown-toggle-btn');
    dropButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var currentMenu = this.nextElementSibling;
            
            document.querySelectorAll('.dropdown-menu-list').forEach(function(m) {
                if(m !== currentMenu) m.classList.add('d-none');
            });
            
            currentMenu.classList.toggle('d-none');
        });
    });

    // 2. Menutup menu dropdown jika klik di luar area
    document.addEventListener('click', function (e) {
        if (e.target.closest('.modal') || e.target.closest('.custom-modal-btn')) {
            return;
        }
        document.querySelectorAll('.dropdown-menu-list').forEach(function(m) {
            m.classList.add('d-none'); 
        });
    });

    // 3. Fungsi Pemicu Modal Mandiri
    var modalTriggerBtns = document.querySelectorAll('.custom-modal-btn');
    modalTriggerBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation(); 
            
            var selectorId = this.getAttribute('data-target-modal');
            var targetedModalEl = document.querySelector(selectorId);
            
            if (targetedModalEl) {
                document.querySelectorAll('.dropdown-menu-list').forEach(function(m) {
                    m.classList.add('d-none'); 
                });

                var modalObj = bootstrap.Modal.getInstance(targetedModalEl) || new bootstrap.Modal(targetedModalEl);
                modalObj.show();
            } else {
                alert("Struktur modal " + selectorId + " belum ditambahkan di sistem.");
            }
        });
    });
});
</script>