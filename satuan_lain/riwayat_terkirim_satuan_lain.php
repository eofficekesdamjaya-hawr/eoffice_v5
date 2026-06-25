<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN (SINKRON DASHBOARD)
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

$id_user     = $_SESSION['id_user'];
$nama_satuan = $_SESSION['satuan'];

// ============================
// AMBIL DATA RIWAYAT TERKIRIM
// ============================
$stmt = $conn->prepare("
    SELECT * FROM surat_masuk
    WHERE id_user = ?
    AND role_pengirim = 'satuan_lain'
    ORDER BY id_surat DESC
");

$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<div class="container py-4">

<h4 class="fw-bold mb-3">
    <i class="fas fa-history me-2 text-info"></i>
    Riwayat Surat Terkirim
</h4>

<a href="dashboard_satuan_lain.php" class="btn btn-secondary mb-3">
    ← Kembali ke Dashboard
</a>

<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
<th>No Agenda</th>
<th>Tujuan</th>
<th>No Surat / Perihal</th>
<th>Status</th>
<th class="text-center">Aksi</th>
</tr>
</thead>

<tbody>

<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td class="fw-bold"><?= htmlspecialchars($row['no_agenda'] ?: '-') ?></td>
        <td><?= htmlspecialchars($row['kepada']) ?></td>
        <td>
            <div class="small text-muted"><?= htmlspecialchars($row['no_surat']) ?></div>
            <div class="fw-semibold"><?= htmlspecialchars($row['perihal']) ?></div>
        </td>

        <td>
            <?php
            $status = $row['status_proses'] ?: 'Pending';
            $badge = "bg-warning text-dark";

            if($status == "Selesai") $badge = "bg-success";
            elseif($status == "Ditolak") $badge = "bg-danger";
            elseif($status == "Proses Disposisi") $badge = "bg-info text-white";
            elseif($status == "Diterima Setum") $badge = "bg-primary";
            ?>

            <span class="badge rounded-pill <?= $badge ?> px-3">
                <?= htmlspecialchars($status) ?>
            </span>
        </td>

        <td class="text-center">
            <div class="btn-group">

<?php if(!empty($row['file_surat'])): ?>

<a href="lihat_surat_terkirim_satuan_lain.php?id=<?= $row['id_surat'] ?>" 
   class="btn btn-sm btn-outline-primary">
   <i class="fas fa-eye"></i> preview
</a>

<a href="download_surat_terkirim_satuan_lain.php?id=<?= $row['id_surat'] ?>" 
   class="btn btn-sm btn-outline-success">
   <i class="fas fa-download"></i> download
</a>

<?php endif; ?>

                <?php if($status == "Pending"): ?>
                    <a href="edit_surat_satuan_lain.php?id=<?= $row['id_surat'] ?>" 
                       class="btn btn-sm btn-warning">
                       <i class="fas fa-edit text-white"></i>edit
                    </a>

                    <!-- <a href="hapus_surat_satuan_lain.php?id=<?= $row['id_surat'] ?>" 
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Hapus surat ini?')">
                        <i class="fas fa-trash"></i>hapus
                    </a> -->
                <?php endif; ?>

            </div>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center text-muted py-5">
            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
            <p>Belum ada riwayat surat terkirim.</p>
        </td>
    </tr>
<?php endif; ?>

</tbody>
</table>

</div>
</div>

</div>


<?php include '../layout/footer.php'; ?>