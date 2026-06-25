<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'ruangan') {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

$nama_role = $_SESSION['nama_role'];
$role_key  = strtolower(trim($_SESSION['role_key'])); 

// ============================
// SINKRONISASI NOTIFIKASI
// ============================
// Menggunakan pencocokan nama_role atau role_key sesuai standar database Anda
$update = $conn->prepare("
    UPDATE surat_masuk
    SET status_baca = 'sudah'
    WHERE LOWER(TRIM(current_role)) = ?
");

$update->bind_param("s", $role_key);
$update->execute();

// ============================
// AMBIL DATA SURAT MASUK & DISPOSISI (FIXED QUERY ON CLAUSE)
// ============================
// Kita hubungkan menggunakan 'no_agenda' sebagai jembatan relasi antar tabel
$query = "
    SELECT *
    FROM surat_masuk
    WHERE LOWER(TRIM(current_role)) = ?
    ORDER BY id_surat DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $role_key);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-uppercase">
                <i class="fas fa-inbox me-2 text-primary"></i>Kotak Masuk Ruangan
            </h3>
            <p class="text-muted mb-0">Surat dan Lembar Disposisi yang ditujukan kepada: <span class="badge bg-secondary"><?= htmlspecialchars($nama_role) ?></span></p>
        </div>
        <a href="ruangan/dashboard.php" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark text-nowrap">
                    <tr>
                        <th class="py-3 px-4 text-center" width="5%">No</th>
                        <th width="15%">No. Agenda / Arsip</th>
                        <th width="20%">Asal Surat</th>
                        <th width="35%">No. Surat / Perihal / Catatan</th>
                        <th width="15%">Tgl Disposisi</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold small text-secondary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></div>
                                <span class="badge bg-info text-dark" style="font-size: 10px;"><?= htmlspecialchars($row['kode'] ?? '-') ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['asal_surat'] ?? '-') ?></div>
                                <div class="small text-muted" style="font-size: 11px;"><i class="fas fa-user me-1"></i><?= htmlspecialchars($row['nama_pengirim'] ?? '-') ?></div>
                            </td>
                            <td>
                                <div class="fw-bold small text-primary mb-1"><?= htmlspecialchars($row['no_surat'] ?? '-') ?></div>
                                <div class="text-dark fw-semibold" style="font-size: 13px;"><?= htmlspecialchars($row['perihal'] ?? '-') ?></div>
                                
                                <?php if (!empty($row['catatan_disposisi'])): ?>
                                    <div class="mt-1 p-2 bg-light border-start border-warning rounded" style="font-size: 11px;">
                                        <strong><i class="fas fa-comment-medical text-warning me-1"></i>Petunjuk Atasan:</strong> 
                                        <span class="text-muted italic"><?= htmlspecialchars($row['catatan_disposisi']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small fw-bold">
                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                    <?= (!empty($row['tanggal_disposisi']) && $row['tanggal_disposisi'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['tanggal_disposisi'])) : date('d/m/Y') ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if (!empty($row['file_surat'])): ?>
                                        <button onclick="openPreview('../uploads/<?= htmlspecialchars($row['file_surat']) ?>')" 
                                                class="btn btn-sm btn-outline-primary px-2" title="Lihat Berkas">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="../uploads/<?= htmlspecialchars($row['file_surat']) ?>" 
                                           class="btn btn-sm btn-outline-success px-2" download title="Unduh Berkas">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-envelope-open fa-3x text-secondary opacity-25 mb-3 d-block"></i>
                                <h5 class="text-muted fw-normal">Kotak masuk kosong</h5>
                                <p class="text-muted small">Belum ada lembar disposisi atau surat yang diteruskan ke ruangan ini.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function openPreview(src) {
    if (!src || src.includes('undefined') || src.endsWith('/')) {
        alert('Berkas surat tidak ditemukan.');
        return;
    }
    window.open(src, '_blank');
}
</script>

<?php include '../layout/footer.php'; ?>
