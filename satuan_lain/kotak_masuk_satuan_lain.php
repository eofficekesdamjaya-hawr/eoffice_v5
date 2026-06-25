<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

$nama_satuan = $_SESSION['satuan'];

// ============================
// LOGIKA BACA SURAT (UPDATE STATUS)
// ============================
if (isset($_GET['read_id'])) {
    $read_id = $_GET['read_id'];
    $stmtUpdate = $conn->prepare("UPDATE surat_masuk SET status_baca = 'sudah' WHERE id_surat = ? AND kepada = ?");
    $stmtUpdate->bind_param("is", $read_id, $nama_satuan);
    $stmtUpdate->execute();
}

// ============================
// AMBIL SURAT MASUK
// ============================
$stmt = $conn->prepare("
    SELECT * FROM surat_masuk
    WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?))
    ORDER BY id_surat DESC
");
$stmt->bind_param("s", $nama_satuan);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .unread-row {
        background-color: #f0f7ff !important;
        font-weight: bold;
    }
    .badge-dot {
        height: 10px;
        width: 10px;
        background-color: #0d6efd;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
</style>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-inbox me-2 text-primary"></i>
                Kotak Masuk Satuan Luar
            </h4>
            <small class="text-muted">Surat yang dikirimkan oleh Kesdam Jaya kepada <strong><?= htmlspecialchars($nama_satuan) ?></strong></small>
        </div>
        <a href="dashboard_satuan_lain.php" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">No. Agenda</th>
                        <th>Pengirim (Kesdam Jaya)</th>
                        <th>No. Surat / Tgl</th>
                        <th>Perihal</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $is_unread = ($row['status_baca'] == 'belum');
                    ?>
                    <tr class="<?= $is_unread ? 'unread-row' : '' ?>">
                        <td class="ps-4">
                            <?php if($is_unread): ?>
                                <span class="badge-dot" title="Belum Dibaca"></span>
                            <?php endif; ?>
                            <?= htmlspecialchars($row['no_agenda']) ?>
                        </td>
                        <td>
                            <i class="fas fa-university me-1 text-muted"></i>
                            <?= htmlspecialchars($row['asal_surat']) ?>
                        </td>
                        <td>
                            <div class="small fw-bold"><?= htmlspecialchars($row['no_surat']) ?></div>
                            <div class="x-small text-muted"><?= date('d M Y', strtotime($row['tanggal_diterima'])) ?></div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 250px;">
                                <?= htmlspecialchars($row['perihal']) ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $status = $row['status_proses'] ?? 'Terkirim';
                            $bg = "bg-info";
                            if($status == "Selesai") $bg = "bg-success";
                            if($status == "Ditolak") $bg = "bg-danger";
                            ?>
                            <span class="badge rounded-pill <?= $bg ?>">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="?read_id=<?= $row['id_surat'] ?>" 
                                   onclick="previewFile('../uploads/<?= htmlspecialchars($row['file_surat']) ?>')" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                                <a href="../uploads/<?= htmlspecialchars($row['file_surat']) ?>" 
                                   class="btn btn-sm btn-outline-success" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-envelope-open fa-3x mb-3 opacity-25"></i>
                            <p>Belum ada surat masuk untuk satuan Anda.</p>
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function previewFile(src){
    // Membuka file di tab baru
    window.open(src, '_blank');
    // Reload halaman setelah jeda sedikit agar status 'belum' menjadi 'sudah' (karena parameter read_id di URL)
    setTimeout(function(){
        location.reload();
    }, 1000);
}
</script>

<?php include '../layout/footer.php'; ?>