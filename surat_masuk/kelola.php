<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/koneksi.php";

/* ================= PROTEKSI ================= */
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'ruangan') {
    header("Location: ../auth/login_ruangan.php");
    exit;
}

// Gunakan nama_role dan role_key sekaligus untuk pencocokan data yang fleksibel
$nama_role = $_SESSION['nama_role'] ?? '';
$role_key  = strtolower(trim($_SESSION['role_key'] ?? ''));

/* ================= FILTER ================= */
$filter_status = $_GET['filter_status'] ?? 'belum_dibaca';
$search = $_GET['search'] ?? '';

/* ================= COUNT (Berdasarkan Struktur Asli Tabel) ================= */
$base_count = "FROM disposisi_surat_masuk d 
               WHERE (LOWER(TRIM(d.untuk_role)) = '$role_key' OR LOWER(TRIM(d.untuk_role)) = LOWER(TRIM('$nama_role')))";

$countAll = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total $base_count"))['total'] ?? 0;
$countBelum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total $base_count AND d.status_baca='belum'"))['total'] ?? 0;
$countSudah = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total $base_count AND d.status_baca='sudah'"))['total'] ?? 0;

/* ================= WHERE CLAUSE ================= */
$conditions = ["(LOWER(TRIM(d.untuk_role)) = '$role_key' OR LOWER(TRIM(d.untuk_role)) = LOWER(TRIM('$nama_role')))"];

if ($filter_status == 'belum_dibaca') {
    $conditions[] = "d.status_baca='belum'";
} elseif ($filter_status == 'sudah_dibaca') {
    $conditions[] = "d.status_baca='sudah'";
}

$where = "WHERE " . implode(" AND ", $conditions);

/* ================= FINAL QUERY (100% SESUAI STRUKTUR DATABASE) ================= */
// Menggunakan d.id_surat_masuk = s.id_surat sesuai dengan blueprint tabel disposisi Anda
$sql = "SELECT d.id AS id_disposisi, d.status_baca, d.tanggal_disposisi, d.catatan_disposisi, d.dari_role,
               s.id_surat, s.no_agenda, s.no_surat, s.perihal, s.status_proses, s.file_surat
        FROM disposisi_surat_masuk d
        JOIN surat_masuk s ON d.id_surat_masuk = s.id_surat
        $where ";

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (s.no_surat LIKE '%$search%' OR s.perihal LIKE '%$search%' OR s.asal_surat LIKE '%$search%')";
}

// Urutkan berdasarkan ID disposisi terbesar (terbaru)
$sql .= " ORDER BY d.id DESC";

$query = mysqli_query($conn, $sql);
include '../layout/header.php';
?>

<style>
    .table-container { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); border: 1px solid #dee2e6; }
    .table thead th { background: #f8f9fa; color: #333; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; vertical-align: middle; border: 1px solid #dee2e6 !important; }
    .table tbody td { font-size: 13px; vertical-align: middle; border: 1px solid #dee2e6 !important; }
    .pending-row { background-color: #fffdf2 !important; font-weight: 500; border-left: 4px solid #ffc107 !important; }
    .text-agenda { font-family: 'Courier New', monospace; font-weight: bold; color: #d63384; }
    .btn-xs { padding: 4px 8px; font-size: 11px; border-radius: 4px; }
    .badge-status { min-width: 90px; padding: 6px; }
</style>

<div class="container-fluid mt-4">
    <?php if(isset($_SESSION['notif'])): ?>
        <div class="alert alert-<?= $_SESSION['notif']['type'] ?> alert-dismissible fade show shadow-sm">
            <?= $_SESSION['notif']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['notif']); endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-primary m-0"><i class="bi bi-envelope-paper-fill me-2"></i> Ruang Pengelolaan Disposisi</h4>
            <p class="text-muted small mb-0">Kelola konfirmasi, verifikasi, dan penerusan lembar disposisi internal.</p>
        </div>
        <div class="btn-group shadow-sm">
            <button onclick="window.location.reload()" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
            <a href="../ruangan/dashboard.php" class="btn btn-primary"><i class="bi bi-house-door"></i> Dashboard</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-3">
                <div class="col-md-7">
                    <div class="btn-group w-100">
                        <a href="?filter_status=semua" class="btn btn-sm <?=($filter_status=='semua')?'btn-dark':'btn-outline-dark'?>">Semua (<?=$countAll?>)</a>
                        <a href="?filter_status=belum_dibaca" class="btn btn-sm <?=($filter_status=='belum_dibaca')?'btn-warning':'btn-outline-warning'?>">Belum Dibaca (<?=$countBelum?>)</a>
                        <a href="?filter_status=sudah_dibaca" class="btn btn-sm <?=($filter_status=='sudah_dibaca')?'btn-success':'btn-outline-success'?>">Sudah Dibaca (<?=$countSudah?>)</a>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Cari No. Surat/Perihal..." value="<?=htmlspecialchars($search)?>">
                        <button class="btn btn-primary px-3"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-bordered table-hover m-0">
                <thead class="text-center">
                    <tr>
                        <th width="50">No</th>
                        <th>No. Agenda</th>
                        <th>Waktu Disposisi</th>
                        <th>Asal Instansi</th>
                        <th>Detail Informasi Surat</th>
                        <th>Petunjuk / Instruksi Atasan</th>
                        <th>Status Berkas</th>
                        <th width="220">Aksi Pengelolaan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($query && mysqli_num_rows($query) > 0): $no = 1; while($row = mysqli_fetch_assoc($query)): 
                        $is_unread = $row['status_baca'] == 'belum';
                        $status_p = $row['status_proses'] ?? 'Pending';
                    ?>
                    <tr class="<?= $is_unread ? 'pending-row' : '' ?>">
                        <td class="text-center text-muted fw-bold"><?= $no++ ?></td>
                        <td class="text-center text-agenda"><?= htmlspecialchars($row['no_agenda'] ?: '-') ?></td>
                        <td class="text-center small"><?= date('d/m/Y H:i', strtotime($row['tanggal_disposisi'])) ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars(str_replace('_', ' ', $row['dari_role'])) ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['perihal']) ?></div>
                            <small class="text-primary fw-semibold"><?= htmlspecialchars($row['no_surat']) ?></small>
                        </td>
                        <td>
                            <div class="p-2 bg-light rounded text-dark border-start border-3 border-info" style="font-size: 12px;">
                                "<?= htmlspecialchars($row['catatan_disposisi'] ?: 'Tidak ada catatan khusus') ?>"
                            </div>
                        </td>
                        <td class="text-center">
                            <?php 
                            $b_class = "bg-secondary";
                            if($status_p == 'Diterima') $b_class = "bg-success";
                            elseif($status_p == 'Ditolak') $b_class = "bg-danger";
                            elseif($status_p == 'Proses Disposisi') $b_class = "bg-warning text-dark";
                            ?>
                            <span class="badge badge-status <?= $b_class ?>"><?= $status_p ?></span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column gap-2 align-items-center">
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-dark btn-sm dropdown-toggle shadow-sm py-1" data-bs-toggle="dropdown">
                                        <i class="bi bi-check2-circle me-1"></i> Update Status
                                    </button>
                                    <ul class="dropdown-menu shadow border-0">
                                        <li><a class="dropdown-item small py-2" href="proses_verifikasi_ruangan.php?id=<?= $row['id_disposisi'] ?>&status=Diterima" onclick="return confirm('Terima berkas surat ini?')">✅ Terima Berkas</a></li>
                                        <li><a class="dropdown-item small py-2" href="proses_verifikasi_ruangan.php?id=<?= $row['id_disposisi'] ?>&status=Proses Disposisi">🔄 Teruskan Disposisi</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item small py-2 text-danger fw-bold" href="proses_verifikasi_ruangan.php?id=<?= $row['id_disposisi'] ?>&status=Ditolak" onclick="return confirm('Tolak berkas surat ini?')">❌ Tolak Berkas</a></li>
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-center gap-1 w-100">
                                    <?php if($is_unread): ?>
                                        <a href="proses_verifikasi_ruangan.php?id=<?= $row['id_disposisi'] ?>&mark_read=true" class="btn btn-warning btn-xs text-dark shadow-sm" title="Tandai Sudah Dibaca"><i class="bi bi-eye-fill"></i></a>
                                    <?php endif; ?>
                                    <a href="detail_surat.php?id=<?= $row['id_surat'] ?>" class="btn btn-info btn-xs text-white shadow-sm" title="Detail Surat"><i class="bi bi-info-circle"></i> Info</a>
                                    <a href="disposisi_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-xs shadow-sm" title="Disposisikan Lanjut"><i class="bi bi-send-fill"></i> Kirim</a>
                                    <a href="riwayat_disposisi.php?id=<?= $row['id_surat'] ?>" class="btn btn-dark btn-xs shadow-sm" title="Riwayat Jejak Surat"><i class="bi bi-clock-history"></i> Log</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fa-3x opacity-25 d-block mb-2"></i>
                            Tidak ada surat disposisi yang ditemukan pada kategori ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
