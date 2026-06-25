<?php
session_start();
require_once '../config/koneksi.php';

$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$sql = "SELECT * FROM surat_masuk 
        WHERE tanggal_diterima BETWEEN '$tgl_awal' AND '$tgl_akhir' 
        ORDER BY no_agenda ASC";
$query = mysqli_query($conn, $sql);

include '../layout/header.php';
?>

<div class="main-content p-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Filter Buku Agenda</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="small text-muted">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Daftar Agenda Surat Masuk</h6>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Laporan
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="tabelAgenda">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No. Agenda</th>
                            <th>Tgl Terima</th>
                            <th>Asal Surat</th>
                            <th>Nomor & Tgl Surat</th>
                            <th>Perihal</th>
                            <th>Penerima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $row['no_agenda'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal_diterima'])) ?></td>
                            <td><?= $row['asal_surat'] ?></td>
                            <td>
                                <small><?= $row['no_surat'] ?></small><br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($row['tanggal_surat'])) ?></small>
                            </td>
                            <td><?= $row['perihal'] ?></td>
                            <td><?= $row['kepada'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .btn, .card-header, form, .nav-header { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
}
</style>

<?php include '../layout/footer.php'; ?>