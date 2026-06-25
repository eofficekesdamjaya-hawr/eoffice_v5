<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$nama_jajaran = $_SESSION['nama'];

// Update status_baca menjadi 'sudah' ketika halaman ini dibuka
$updateRead = $conn->prepare("UPDATE surat_masuk SET status_baca = 'sudah' WHERE kepada = ?");
$updateRead->bind_param("s", $nama_jajaran);
$updateRead->execute();

// Ambil data surat masuk
$query = "SELECT * FROM surat_masuk WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?)) ORDER BY id_surat DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $nama_jajaran);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Kotak Masuk Surat</h3>
            <p class="text-muted small">Daftar surat yang ditujukan untuk <strong><?= htmlspecialchars($nama_jajaran) ?></strong></p>
        </div>
        <a href="dashboard_jajaran.php" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success text-dark">
                    <tr>
                        <th width="50">No</th>
                        <th>Pengirim</th>
                        <th>No. Surat</th>
                        <th>Perihal</th>
                        <th>Tanggal Terima</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['role_pengirim']) ?></td>
                            <td><small><?= htmlspecialchars($row['no_surat']) ?></small></td>
                            <td><?= htmlspecialchars($row['perihal']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-primary rounded-pill px-3" target="_blank">
                                    <i class="fas fa-eye me-1"></i> Lihat
                                </a>
                                <a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-success rounded-pill px-3" download>
                                    <i class="fas fa-download me-1"></i> Unduh
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada surat masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>