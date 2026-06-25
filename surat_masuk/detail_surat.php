<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

/* ================= PROTEKSI ================= */
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'ruangan') {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Surat tidak ditemukan!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$id = intval($_GET['id']);

/* ================= AMBIL DATA SURAT ================= */
$stmt = $conn->prepare("SELECT * FROM surat_masuk WHERE id_surat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='kelola_surat_masuk.php';</script>";
    exit();
}

$file = $data['file_surat'];
$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));

include '../layout/header.php';
?>

<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-file-earmark-text text-primary me-2"></i> Detail Surat Masuk
            </h4>
            <small class="text-muted">Melihat rincian informasi dan dokumen lampiran</small>
        </div>
        <a href="kelola_surat_masuk.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold py-3">
                    <i class="bi bi-info-circle me-2"></i> Informasi Surat
                </div>

                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">No Surat</label>
                            <div class="fs-5 fw-bold text-dark"><?= htmlspecialchars($data['no_surat']) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Status Proses</label>
                            <div>
                                <span class="badge bg-info text-dark px-3"><?= strtoupper($data['status_proses']) ?></span>
                            </div>
                        </div>
                        <hr class="my-2 opacity-50">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Asal Surat</label>
                            <div class="text-dark"><?= htmlspecialchars($data['asal_surat']) ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Tanggal Surat</label>
                            <div class="text-dark"><?= date('d F Y', strtotime($data['tanggal_surat'])) ?></div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="fw-bold small text-muted text-uppercase">Perihal</label>
                            <div class="p-3 bg-light rounded border">
                                <p class="mb-0 fw-bold"><?= htmlspecialchars($data['perihal']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white fw-bold py-3">
                    <i class="bi bi-paperclip me-2"></i> File Lampiran
                </div>
                <div class="card-body bg-light text-center p-4">
                    <?php if(!empty($file)): ?>
                        
                        <?php if(in_array($ext, ['jpg','jpeg','png'])): ?>
                            <div class="mb-3">
                                <img src="../uploads/surat_masuk/<?= $file ?>" class="img-fluid rounded shadow border" style="max-height: 800px;">
                            </div>
                            <a href="../uploads/surat_masuk/<?= $file ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-fullscreen me-1"></i> Lihat Gambar Penuh
                            </a>

                        <?php elseif($ext == 'pdf'): ?>
                            <div class="ratio ratio-16x9 mb-3 shadow-sm border rounded overflow-hidden">
                                <embed src="../uploads/surat_masuk/<?= $file ?>#toolbar=0" type="application/pdf" width="100%" height="600px" />
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="../uploads/surat_masuk/<?= $file ?>" target="_blank" class="btn btn-danger">
                                    <i class="bi bi-file-pdf me-1"></i> Buka PDF di Tab Baru
                                </a>
                                <a href="../uploads/surat_masuk/<?= $file ?>" download class="btn btn-outline-dark">
                                    <i class="bi bi-download me-1"></i> Unduh File
                                </a>
                            </div>

                        <?php else: ?>
                            <div class="py-5">
                                <i class="bi bi-file-earmark-arrow-down text-secondary" style="font-size: 4rem;"></i>
                                <div class="mt-3">
                                    <p class="mb-2 text-muted">Format file (<?= strtoupper($ext) ?>) tidak dapat dipratinjau.</p>
                                    <a href="../uploads/surat_masuk/<?= $file ?>" download class="btn btn-success">
                                        <i class="bi bi-download me-1"></i> Unduh Dokumen
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="py-5 text-muted">
                            <i class="bi bi-slash-circle" style="font-size: 3rem;"></i>
                            <p class="mt-2">File lampiran tidak tersedia atau belum diunggah.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Mengatur body agar konten bisa diletakkan di tengah secara vertikal */
    body, html {
        height: 100%;
        margin: 0;
        background-color: #f8f9fa;
    }

    /* Container utama detail */
    .main-content {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center; /* Mengetengahkan secara vertikal */
        align-items: center;     /* Mengetengahkan secara horizontal */
        padding: 40px 15px;
    }

    /* Mengatur lebar box agar tidak terlalu lebar di layar besar */
    .detail-container {
        width: 100%;
        max-width: 900px; /* Batas lebar maksimal box detail */
    }

    .card {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        margin-bottom: 25px;
    }

    /* Styling tambahan untuk judul */
    .header-box {
        text-align: center;
        margin-bottom: 30px;
    }

    .header-box h4 {
        font-size: 1.5rem;
        color: #0d6efd;
    }

    /* Responsive untuk tampilan mobile */
    @media (max-width: 768px) {
        .main-content {
            justify-content: flex-start; /* Pada mobile biarkan scroll dari atas */
            padding-top: 20px;
        }
    }
    
    /* Styling tambahan untuk kerapihan */
    .card { overflow: hidden; }
    .badge { font-weight: 500; font-size: 0.85rem; }
    label { letter-spacing: 0.5px; }
    embed { border-radius: 8px; }
    .main-content { background-color: #f8f9fa; min-height: 100vh; }
</style>

<?php include '../layout/footer.php'; ?>