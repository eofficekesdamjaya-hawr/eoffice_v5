<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$role_nama = $_SESSION['nama_role'] ?? $_SESSION['nama'] ?? 'Petugas';
$hari_ini  = date('Y-m-d');

// 1. QUERY UNTUK SURAT KELUAR HARI INI (Milik satuan yang login)
$query_hari_ini = "SELECT * FROM surat_keluar WHERE created_by = ? AND DATE(tanggal_input) = ? ORDER BY id_surat DESC";
$stmt1 = $conn->prepare($query_hari_ini);
$stmt1->bind_param("is", $id_user, $hari_ini);
$stmt1->execute();
$result_hari_ini = $stmt1->get_result();

// 2. QUERY UNTUK SURAT KELUAR SEBELUMNYA (Milik satuan yang login)
$query_sebelumnya = "SELECT * FROM surat_keluar WHERE created_by = ? AND DATE(tanggal_input) < ? ORDER BY id_surat DESC";
$stmt2 = $conn->prepare($query_sebelumnya);
$stmt2->bind_param("is", $id_user, $hari_ini);
$stmt2->execute();
$result_sebelumnya = $stmt2->get_result();

require_once '../layout/header.php';
?>

<div class="d-flex" id="wrapper">
    <?php include '../ruangan/sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm">
            <button class="btn btn-outline-dark btn-sm rounded" id="menu-toggle"><i class="fas fa-bars"></i></button>
            <span class="ms-3 fw-semibold text-secondary">E-Office Kesdam Jaya - Monitoring Surat Keluar</span>
        </nav>

        <div class="container-fluid py-4 px-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark mb-0"><i class="fas fa-folder-open me-2 text-primary"></i>Kelola Data Surat Keluar</h4>
                <a href="tambah_surat_keluar.php" class="btn btn-primary rounded shadow-sm">
                    <i class="fas fa-plus me-2"></i>Tambah Surat Keluar
                </a>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-2"></i>Daftar Pengajuan Surat Keluar - Hari Ini (<?= date('d-m-Y') ?>)</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" style="font-size: 0.85rem;">
                            <thead class="table-dark text-center">
                                <?php include 'komponen_header_tabel.php'; ?>
                            </thead>
                            <tbody>
                                <?php if ($result_hari_ini->num_rows === 0): ?>
                                    <tr><td colspan="13" class="text-center text-muted py-4">Belum ada pengajuan surat keluar untuk hari ini.</td></tr>
                                <?php else: $no = 1; while ($row = $result_hari_ini->fetch_assoc()): ?>
                                    <?php include 'komponen_baris_tabel.php'; ?>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-secondary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Arsip Pengajuan Surat Keluar - Sebelum Hari Ini</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" style="font-size: 0.85rem;">
                            <thead class="table-dark text-center">
                                <?php include 'komponen_header_tabel.php'; ?>
                            </thead>
                            <tbody>
                                <?php if ($result_sebelumnya->num_rows === 0): ?>
                                    <tr><td colspan="13" class="text-center text-muted py-4">Tidak ada arsip pengajuan surat keluar sebelumnya.</td></tr>
                                <?php else: $no = 1; while ($row = $result_sebelumnya->fetch_assoc()): ?>
                                    <?php include 'komponen_baris_tabel.php'; ?>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById("menu-toggle").addEventListener("click", function(e) {
    e.preventDefault();
    document.getElementById("wrapper").classList.toggle("toggled");
});
</script>

<?php include '../layout/footer.php'; ?>
