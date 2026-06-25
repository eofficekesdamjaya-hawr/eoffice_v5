<?php
session_start();
require_once '../config/koneksi.php';

// Menangkap filter tanggal
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Query data surat keluar
$query = "SELECT * FROM surat_keluar WHERE tanggal_surat BETWEEN ? AND ? ORDER BY tanggal_surat DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $tgl_awal, $tgl_akhir);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<?php
// tampilkan sidebar hanya jika user sudah login
if (isset($_SESSION['id_user'])) {
    include __DIR__ . '/../dashboard/sidebar_admin.php';
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold"><i class="bi bi-file-earmark-arrow-up-fill text-dark me-2"></i>Rekap Surat Keluar</h4>
            <p class="text-muted small">Manajemen laporan data surat keluar resmi.</p>
        </div>
        <a href="cetak_laporan_surat_keluar.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" 
           target="_blank" 
           class="btn btn-dark shadow-sm">
           <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="small fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tampilkan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th>No Surat</th>
                        <th>Tanggal Surat</th>
                        <th>Tujuan</th>
                        <th>Perihal</th>
                        <th>Status TTE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['no_surat'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_surat'])) ?></td>
                        <td><?= htmlspecialchars($row['tujuan_surat'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
                        <td>
                            <?php if(($row['status_tte'] ?? '') == 'Selesai'): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
