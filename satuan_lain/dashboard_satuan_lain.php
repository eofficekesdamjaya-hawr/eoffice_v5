<?php
session_start();
require_once '../config/koneksi.php';

// =============================
// PROTEKSI HALAMAN
// =============================
// Menggunakan 'tipe_akses' sesuai standarisasi login sebelumnya
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

// =============================
// DATA SESSION
// =============================
$id_user      = $_SESSION['id_user'];
$email_user   = $_SESSION['email']; 
$nama_satuan  = $_SESSION['satuan'];

// =============================
// SURAT MASUK (DITERIMA)
// =============================
// Mengambil surat yang ditujukan kepada nama satuan ini
$stmtMasuk = $conn->prepare("
    SELECT COUNT(*) as total FROM surat_masuk 
    WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?))
");
$stmtMasuk->bind_param("s", $nama_satuan);
$stmtMasuk->execute();
$totalMasuk = $stmtMasuk->get_result()->fetch_assoc()['total'];

// =============================
// SURAT TERKIRIM (RIWAYAT)
// =============================
$stmtKeluar = $conn->prepare("
    SELECT * FROM surat_masuk 
    WHERE id_user = ? 
    AND role_pengirim = 'satuan_lain' 
    ORDER BY id_surat DESC
");
$stmtKeluar->bind_param("i", $id_user);
$stmtKeluar->execute();
$resultKeluar = $stmtKeluar->get_result();
$totalKeluar  = $resultKeluar->num_rows;

// Hitung Grand Total untuk Notifikasi (Surat Masuk Belum Dibaca)
$stmtNotif = $conn->prepare("SELECT COUNT(*) as total FROM surat_masuk WHERE kepada = ? AND status_baca = 'belum'");
$stmtNotif->bind_param("s", $nama_satuan);
$stmtNotif->execute();
$totalNotifBaru = $stmtNotif->get_result()->fetch_assoc()['total'];

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .card-dashboard {
        transition: all 0.3s ease;
        border-radius: 15px;
        border: none;
    }
    .card-dashboard:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .icon-box {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
</style>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold mb-1">Dashboard Satuan Luar</h3>
            <p class="text-muted small mb-0">
                Instansi: <span class="badge bg-dark"><?= htmlspecialchars($nama_satuan) ?></span> | 
                PIC: <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="kirim_surat_satuan_lain.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-paper-plane me-1"></i> Kirim Surat Baru
            </a>
            <a href="ubah_password_satuan_lain.php" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="fas fa-key me-1"></i> Ubah Password
            </a>
            <a href="logout.php" class="btn btn-outline-danger rounded-pill px-3 shadow-sm" onclick="return confirm('Yakin ingin Logout?')">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <a href="kotak_masuk_satuan_lain.php" class="text-decoration-none">
                <div class="card card-dashboard shadow-sm border-start border-primary border-5 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-envelope-open-text fa-2x text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">SURAT MASUK DITERIMA</small>
                            <h3 class="mb-0 fw-bold text-dark"><?= $totalMasuk ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="riwayat_terkirim_satuan_lain.php" class="text-decoration-none">
                <div class="card card-dashboard shadow-sm border-start border-info border-5 h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 me-3">
                            <i class="fas fa-history fa-2x text-info"></i>
                        </div>
                        <div>
                            <small class="text-muted fw-bold">RIWAYAT SURAT TERKIRIM</small>
                            <h3 class="mb-0 fw-bold text-dark"><?= $totalKeluar ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Ringkasan Surat Terakhir yang Dikirim</h6>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-4">No. Agenda</th>
                        <th>Tujuan</th>
                        <th>No. Surat / Perihal</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($totalKeluar > 0): ?>
                        <?php while($row = $resultKeluar->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($row['no_agenda']) ?></td>
                            <td><?= htmlspecialchars($row['kepada']) ?></td>
                            <td>
                                <div class="small text-muted"><?= htmlspecialchars($row['no_surat']) ?></div>
                                <div class="fw-bold"><?= htmlspecialchars($row['perihal']) ?></div>
                            </td>
                            <td>
                                <?php 
                                $status = $row['status_proses'];
                                $badge = "bg-warning text-dark";
                                if($status == "Selesai") $badge = "bg-success";
                                if($status == "Ditolak") $badge = "bg-danger";
                                ?>
                                <span class="badge rounded-pill <?= $badge ?> px-3"><?= htmlspecialchars($status) ?></span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button onclick="openPreview('../uploads/<?= $row['file_surat'] ?>')" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye">file</i>
                                    </button>
                                    <a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-outline-success" download>
                                        <i class="fas fa-download">download</i>
                                    </a>
                                    <?php if($row['status_proses'] == "Pending"): ?>
                                        <a href="edit_surat_satuan_lain.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit text-white">edit</i>
                                        </a>
                                        <!-- <a href="hapus_surat_satuan_lain.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus surat ini?')">
                                            <i class="fas fa-trash"></i>
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
                                <p>Belum ada riwayat pengiriman surat.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<audio id="notifAudio"><source src="../assets/audio/ada_suratmasuk.mp3" type="audio/mpeg"></audio>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let lastTotal = <?= $totalNotifBaru ?>;
const notifSound = document.getElementById("notifAudio");

function openPreview(src){ window.open(src, '_blank'); }

function checkNewMail() {
    $.ajax({
        url: 'cek_notifikasi_surat_masuk.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.total_notif > lastTotal) {
                // Mainkan suara
                notifSound.play().catch(e => console.log("Interaksi user diperlukan"));

                // Munculkan Alert
                Swal.fire({
                    title: 'Notifikasi Baru!',
                    text: 'Anda menerima surat masuk atau balasan baru dari Setum.',
                    icon: 'info',
                    confirmButtonText: 'Periksa Sekarang',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
                lastTotal = data.total_notif;
            }
        }
    });
}

// Cek setiap 10 detik
setInterval(checkNewMail, 10000);

// Izin Audio Browser
$(document).one('click', function() {
    notifSound.play().then(() => { notifSound.pause(); notifSound.currentTime = 0; });
});
</script>

<?php include '../layout/footer.php'; ?>