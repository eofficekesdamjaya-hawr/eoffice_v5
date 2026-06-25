<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Filter tanggal
$tgl_awal = $_GET['tgl_awal'] ?? date('Y-m-01'); 
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d'); 
$filter_sumber = $_GET['sumber'] ?? ''; 

// Pastikan filter tanggal mencakup akhir hari (23:59:59) jika kolomnya DATETIME
$sql = "SELECT * FROM surat_masuk 
        WHERE tanggal_diterima >= '$tgl_awal 00:00:00' 
        AND tanggal_diterima <= '$tgl_akhir 23:59:59'";

// Tambahkan filter sumber jika dipilih
if ($filter_sumber != '') {
    $sql .= " AND sumber_surat = '$filter_sumber'";
}

$sql .= " ORDER BY tanggal_diterima DESC";
$query = mysqli_query($conn, $sql);
include '../layout/header.php';
?>

<div class="main-content p-4">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope-paper-fill me-2 text-success"></i>Rekapitulasi Surat Masuk</h4>
            <p class="text-muted small">Laporan surat masuk dari Ruangan, Jajaran dan Satuan Lain</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Sumber Surat</label>

<select name="sumber" class="form-select">
    <option value="">Semua Sumber</option>
    <option value="Ruangan" <?= $filter_sumber == 'Ruangan' ? 'selected' : '' ?>>Ruangan</option>
    <option value="Jajaran" <?= $filter_sumber == 'Jajaran' ? 'selected' : '' ?>>Jajaran</option>
    <option value="Satuan Lain" <?= $filter_sumber == 'Satuan Lain' ? 'selected' : '' ?>>Satuan Lain</option>
</select>

                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center ps-3">No</th>
                            <th>No. Agenda / Surat</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th>Sumber</th>
                            <th>Status</th>
                            <th>Tgl Terima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($query) > 0):
                            while($s = mysqli_fetch_assoc($query)): 
                                // Warna Badge Status
                                $statusClass = [
                                    'Pending' => 'bg-warning text-dark',
                                    'Diterima' => 'bg-info text-white',
                                    'Proses Disposisi' => 'bg-primary text-white',
                                    'Selesai' => 'bg-success text-white',
                                    'Ditolak' => 'bg-danger text-white'
                                ][$s['status_proses']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td class="text-center ps-3"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['no_agenda'] ?: '-') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($s['no_surat']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($s['nama_pengirim']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($s['asal_surat']) ?></div>
                                </td>
                                <td style="max-width: 250px;" class="small"><?= htmlspecialchars($s['perihal']) ?></td>
                                
<td>
<?php
$sumber = $s['sumber_surat'];

if($sumber == 'Ruangan'){
    $badge = 'background-color:#6f42c1; color:white;';
}elseif($sumber == 'Jajaran'){
    $badge = 'background-color:#198754; color:white;';
}elseif($sumber == 'Satuan Lain'){
    $badge = 'background-color:#fd7e14; color:white;';
}else{
    $badge = 'background-color:#6c757d; color:white;';
}
?>
<span class="badge shadow-sm" style="<?= $badge ?>">
    <?= htmlspecialchars($sumber) ?>
</span>
</td>

                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= $s['status_proses'] ?></span>
                                </td>
                                <td class="small"><?= date('d/m/Y H:i', strtotime($s['tanggal_diterima'])) ?></td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-search fs-2 d-block mb-3"></i>
                                    Data surat tidak ditemukan pada periode ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .sidebar, .btn, .card-body form, .nav-header { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .card { border: none !important; shadow: none !important; }
        .table { width: 100% !important; border: 1px solid #000 !important; }
    }
</style>

<?php include '../layout/footer.php'; ?>