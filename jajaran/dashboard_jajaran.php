<?php 
session_start();
require_once '../config/koneksi.php';

// Proteksi Login Jajaran
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id_jajaran    = $_SESSION['id_user'];
$nama_jajaran  = $_SESSION['nama'];
$email_jajaran = $_SESSION['email'];

/* =========================
   SURAT MASUK (DITERIMA JAJARAN)
========================= */
$stmtMasuk = $conn->prepare("
    SELECT COUNT(*) as total FROM surat_masuk
    WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?))
    AND status_baca = 'belum'
");
$stmtMasuk->bind_param("s", $nama_jajaran);
$stmtMasuk->execute();
$totalMasuk = $stmtMasuk->get_result()->fetch_assoc()['total'] ?? 0;

/* =========================
   SURAT TERKIRIM (DIKIRIM JAJARAN)
========================= */
$stmtKeluar = $conn->prepare("
    SELECT * FROM surat_masuk
    WHERE id_user = ?
    AND role_pengirim = 'jajaran'
    ORDER BY id_surat DESC
    LIMIT 10
");
$stmtKeluar->bind_param("i", $id_jajaran);
$stmtKeluar->execute();
$resultKeluar = $stmtKeluar->get_result();
$totalKeluar  = $resultKeluar->num_rows;

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    .card-hover { transition: transform 0.2s; cursor: pointer; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .btn-action { font-size: 0.8rem; padding: 0.25rem 0.6rem; }
</style>

<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0 text-success">Dashboard Jajaran</h3>
            <span class="badge bg-success shadow-sm">
                <i class="fas fa-building me-1"></i> Satuan: <?= htmlspecialchars($nama_jajaran) ?>
            </span>
        </div>

        <div class="d-flex gap-2">
            <a href="kirim_surat_jajaran.php" class="btn btn-success rounded-pill px-3 shadow-sm">
                <i class="fas fa-paper-plane me-1"></i> Kirim Surat Baru
            </a>
            <a href="ubah_password_jajaran.php" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="fas fa-key me-1"></i> ubah Password
            </a>
           <a href="logout_jajaran.php" class="btn btn-danger rounded-pill px-3 shadow-sm">Logout
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <a href="surat_masuk_jajaran.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-primary border-4 h-100 card-hover">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold">TOTAL SURAT MASUK</small>
                            <h2 class="fw-bold mb-0 text-dark" id="txtTotalMasuk"><?= $totalMasuk ?></h2>
                        </div>
                        <i class="fas fa-envelope-open-text fa-3x text-primary opacity-25"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6">
            <a href="surat_terkirim_jajaran.php" class="text-decoration-none">
                <div class="card shadow-sm border-start border-success border-4 h-100 card-hover">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold">TOTAL SURAT TERKIRIM</small>
                            <h2 class="fw-bold mb-0 text-dark"><?= $totalKeluar ?></h2>
                        </div>
                        <i class="fas fa-history fa-3x text-success opacity-25"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">
            <i class="fas fa-list me-2"></i>Riwayat Pengiriman Terbaru
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Agenda</th>
                        <th>Tujuan</th>
                        <th>No. Surat</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($totalKeluar > 0): ?>
                        <?php while($row = $resultKeluar->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($row['no_agenda']) ?></td>
                            <td><?= htmlspecialchars($row['kepada']) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($row['no_surat']) ?></small></td>
                            <td>

<?php
$status = strtolower($row['status_proses']);

$badgeClass = 'bg-secondary';

if (str_contains($status, 'pending')) {
    $badgeClass = 'bg-warning text-dark';
} elseif (str_contains($status, 'setum')) {
    $badgeClass = 'bg-primary';
} elseif (str_contains($status, 'tuud')) {
    $badgeClass = 'bg-info text-dark';
} elseif (str_contains($status, 'wakakesdam')) {
    $badgeClass = 'bg-success';
} elseif (str_contains($status, 'kakesdam')) {
    $badgeClass = 'bg-danger';
} elseif (str_contains($status, 'selesai')) {
    $badgeClass = 'bg-dark';
}
?>

<span class="badge <?= $badgeClass ?> px-3 rounded-pill">
    <i class="fas fa-clock me-1"></i>
    <?= htmlspecialchars($row['status_proses']) ?>
</span>

                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                   <?php if(!empty($row['file_surat'])): ?>

<button onclick="openPreview('../uploads/<?= $row['file_surat'] ?>')" class="btn btn-sm btn-primary btn-action">
    

<?php endif; ?>
                                        <i class="fas fa-eye me-1"></i> Preview
                                    </button>
                                    
                                    <a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-success btn-action" download>
                                        <i class="fas fa-download me-1"></i> Unduh
                                    </a>

                                    <?php if($row['status_proses'] == "Pending"): ?>
                                        <a href="edit_riwayat_terkirim_jajaran.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-warning btn-action">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="hapus_riwayat_terkirim_jajaran.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Hapus surat ini?')">
                                            <i class="fas fa-trash me-1"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                                Belum ada riwayat pengiriman surat.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<audio id="notifAudio" src="../assets/audio/ada_suratmasuk.mp3" preload="auto"></audio>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentTotal = <?= $totalMasuk ?>;
const audio = document.getElementById("notifAudio");

function openPreview(src){
    window.open(src, '_blank');
}

function checkUpdates() {
    $.ajax({
        url: 'cek_notifikasi_surat_masuk.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.total_notif > currentTotal) {
                // Mainkan suara
                audio.play().catch(e => console.log("Menunggu interaksi user..."));

                // SweetAlert
                Swal.fire({
                    title: 'Surat Masuk Baru!',
                    text: 'Terdapat surat baru yang ditujukan untuk satuan Anda.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Periksa Sekarang',
                    cancelButtonText: 'Nanti Saja',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'surat_masuk_jajaran.php';
                    }
                });
                currentTotal = res.total_notif;
                $('#txtTotalMasuk').text(res.total_notif);
            }
        }
    });
}

// Cek setiap 10 detik
setInterval(checkUpdates, 10000);

// Unblock audio policy (kebijakan browser)
$(document).one('click', function() {
    audio.play().then(() => { audio.pause(); audio.currentTime = 0; });
});
</script>

<?php include '../layout/footer.php'; ?>