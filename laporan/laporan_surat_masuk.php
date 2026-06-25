<?php
session_start();
require_once '../config/koneksi.php';
// Tambahkan validasi session di sini

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01'); // Default awal bulan
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$query = "SELECT * FROM surat_masuk WHERE tanggal_diterima BETWEEN ? AND ? ORDER BY tanggal_diterima DESC";
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

   <a href="cetak_laporan_surat_masuk.php?tgl_awal=<?= $tgl_awal ?>&tgl_akhir=<?= $tgl_akhir ?>" 
   target="_blank" 
   class="btn btn-dark shadow-sm">
   <i class="bi bi-printer me-1"></i> Cetak Laporan
</a>

    <!-- Filter Form -->
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
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Filter Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th>No Agenda</th>
                        <th>Pengirim</th>
                        <th>Perihal</th>
                        <th>Tanggal Terima</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                       <td><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></td>
<td><?= htmlspecialchars($row['asal_surat'] ?? '-') ?></td>
<td><?= htmlspecialchars($row['perihal'] ?? '-') ?></td>
<td><?= isset($row['tanggal_diterima']) ? date('d/m/Y', strtotime($row['tanggal_diterima'])) : '-' ?></td>
                        <td><span class="badge bg-success"><?= $row['status_proses'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../layout/footer.php'; ?>
