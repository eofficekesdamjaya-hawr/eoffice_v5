<?php
require_once "../config/session.php";
include "../config/koneksi.php";
include '../layout/header.php';

$id = $_GET['id'];
// Kita asumsikan ini khusus untuk detail Jajaran 
// (untuk Internal biasanya Anda sudah punya detail_surat.php)
$query = mysqli_query($conn, "SELECT * FROM surat_masuk_jajaran WHERE id_surat = '$id'");
$d = mysqli_fetch_assoc($query);
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Detail Surat Jajaran</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th width="200">Asal Satuan</th><td><?= $d['asal_surat'] ?></td></tr>
                <tr><th>No. Surat</th><td><?= $d['no_surat'] ?></td></tr>
                <tr><th>Perihal</th><td><?= $d['perihal'] ?></td></tr>
                <tr><th>Nama Pengirim</th><td><?= $d['nama_pengirim'] ?> (<?= $d['jabatan_pengirim'] ?>)</td></tr>
                <tr><th>Status Dokumen</th><td><?= $d['status_dokumen'] ?></td></tr>
                <tr><th>Keterangan</th><td><?= $d['keterangan'] ?></td></tr>
            </table>
            <a href="kelola_surat_jajaran.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>