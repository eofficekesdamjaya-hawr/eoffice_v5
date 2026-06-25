<?php
session_start();
require_once '../config/koneksi.php';

// Proteksi Login
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id_jajaran = $_SESSION['id_user'];

// Ambil data surat yang dikirim oleh user ini
$query = "SELECT * FROM surat_masuk WHERE id_user = ? AND role_pengirim = 'jajaran' ORDER BY id_surat DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_jajaran);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-success">Riwayat Surat Terkirim</h3>
            <p class="text-muted small">Daftar seluruh surat yang telah Anda kirimkan.</p>
        </div>
        <div>
            <a href="kirim_surat_jajaran.php" class="btn btn-success rounded-pill px-3 me-2">
                <i class="fas fa-paper-plane me-1"></i> Kirim Baru
            </a>
            <a href="dashboard_jajaran.php" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Agenda</th>
                        <th>Ditujukan Ke</th>
                        <th>No. Surat</th>
                        <th>Perihal</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?= $row['no_agenda'] ?></td>
                            <td><?= htmlspecialchars($row['kepada']) ?></td>
                            <td><small><?= htmlspecialchars($row['no_surat']) ?></small></td>
                            <td><?= htmlspecialchars($row['perihal']) ?></td>
                            <td>
                                <?php if($row['status_proses'] == 'Pending'): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-white"><i class="fas fa-check me-1"></i><?= $row['status_proses'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="../uploads/<?= $row['file_surat'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                    <?php if($row['status_proses'] == 'Pending'): ?>
                                        <a href="edit_surat_jajaran.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <!-- <a href="hapus_surat_jajaran.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus surat ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a> -->
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada riwayat pengiriman.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>