<?php
require_once "../config/session.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ====================================================================
   1. PROTEKSI HALAMAN MULTI-ROLE (ADMIN, SETUM, & RUANGAN)
   ==================================================================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login_ruangan.php");
    exit;
}

$id_user    = $_SESSION['id_user'];
$role_nama  = $_SESSION['nama_role'] ?? $_SESSION['nama'] ?? 'Petugas';
$tipe_akses = strtolower($_SESSION['tipe_akses'] ?? '');

$akses_diizinkan = ['ruangan', 'admin', 'superadmin', 'setum'];

if (!in_array($tipe_akses, $akses_diizinkan)) {
    echo "<script>
            alert('Akses Ditolak! Akun Anda ($tipe_akses) tidak memiliki hak akses membuat surat keluar.'); 
            window.location.href = '../ruangan/dashboard.php';
          </script>";
    exit;
}

$message = "";
$tahun   = date('Y');
$bulan   = date('m'); 

// Set variable pembantu agar menu sidebar ruangan aktif secara presisi
$myFeatures = ['kirim', 'disposisi'];

/* ====================================================================
   2. GENERATE NOMOR AGENDA OTOMATIS
   ==================================================================== */
$stmtAgenda = $conn->prepare("SELECT MAX(id_surat) AS terakhir FROM surat_keluar");
$stmtAgenda->execute();
$dataAgenda = $stmtAgenda->get_result()->fetch_assoc();

$nextNumber = ((int)($dataAgenda['terakhir'] ?? 0)) + 1;
$no_agenda  = "SK/" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT) . "/" . $tahun . "/" . $bulan;

/* ====================================================================
   3. PROSES SIMPAN SURAT
   ==================================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    function clean($data) { return htmlspecialchars(trim($data)); }

    $no_surat      = NULL;
    $tanggal_surat = NULL;
    $tanggal_kirim = NULL;

    $file = $_FILES['file_surat'];
    if ($file['error'] !== 0) {
        $message = "<div class='alert alert-danger'>Gagal mengunggah file. Pastikan file valid!</div>";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','jpg','jpeg','png'];

        if(!in_array($ext,$allowed)){
            $message = "<div class='alert alert-danger'>Format file harus PDF, JPG atau PNG.</div>";
        } elseif($file['size'] > 100 * 1024 * 1024){
            $message = "<div class='alert alert-danger'>Ukuran file maksimal 100 MB.</div>";
        } else {
            $clean_filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $file['name']);
            $new_name = "SRT_OUT_" . date('Ymd_His') . "_" . $clean_filename;

            if (move_uploaded_file($file['tmp_name'], "../uploads/surat_keluar/" . $new_name)) {
                
                $stmt = $conn->prepare("
                    INSERT INTO surat_keluar (
                        no_agenda, kode_arsip, asal_satuan, no_surat, tanggal_surat, 
                        tanggal_kirim, bentuk_surat, jenis_surat, klasifikasi_surat, derajat_surat, 
                        tujuan_disposisi, tujuan_utama, perihal, tembusan, status_dokumen, 
                        file_surat, keterangan, created_by, status_pengiriman
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");

                $status_pengiriman = 'Terkirim';
                $kode_arsip        = clean($_POST['kode']);
                $asal_satuan       = $role_nama;
                $bentuk_surat      = clean($_POST['shapes_surat'] ?? 'Fisik');
                $jenis_surat       = clean($_POST['jenis_surat']);
                $klasifikasi_surat = clean($_POST['klasifikasi_surat']);
                $derajat_surat     = clean($_POST['derajat_surat']);
                $tujuan_disposisi  = clean($_POST['tujuan_disposisi']);
                $tujuan_utama      = clean($_POST['tujuan_utama']);
                $perihal           = clean($_POST['perihal']);
                $tembusan          = clean($_POST['tembusan']);
                $status_dokumen    = clean($_POST['status_dokumen']);
                $keterangan        = clean($_POST['keterangan']);

                $stmt->bind_param(
                    "sssssssssssssssssis",
                    $no_agenda, $kode_arsip, $asal_satuan, $no_surat, $tanggal_surat,
                    $tanggal_kirim, $bentuk_surat, $jenis_surat, $klasifikasi_surat, $derajat_surat,
                    $tujuan_disposisi, $tujuan_utama, $perihal, $tembusan, $status_dokumen,
                    $new_name, $keterangan, $id_user, $status_pengiriman
                );

                $stmt->execute();
                $id_surat_baru = $stmt->insert_id;

                $stmtRiwayat = $conn->prepare("INSERT INTO riwayat_surat (id_user, id_surat, jenis, status, created_at) VALUES (?, ?, 'surat_keluar', 'terkirim', NOW())");
                $stmtRiwayat->bind_param("ii", $id_user, $id_surat_baru);
                $stmtRiwayat->execute();

                echo "<script>alert('Surat berhasil dikirim ke Setum'); window.location='../ruangan/dashboard.php';</script>";
                exit;
            }
        }
    }
}

require_once '../layout/header.php';
?>

<style>
input[type="file"]::file-selector-button {
    background-color: #0d6efd;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}
#wrapper { overflow-x: hidden; }
#wrapper.toggled #sidebar-wrapper { margin-left: -260px; transition: margin 0.25s ease-out; }
#sidebar-wrapper { transition: margin 0.25s ease-out; }
</style>

<div class="d-flex" id="wrapper">
    
    <?php include '../layout/sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 bg-light" style="overflow-x: hidden;">
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm">
            <button class="btn btn-outline-dark btn-sm rounded" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <span class="ms-3 fw-semibold text-secondary">E-Office Kesdam Jaya - Pembuatan Draft</span>
        </nav>

        <div class="container-fluid py-4 px-4">
            <div class="row justify-content-center">
                <div class="col-lg-11">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold text-dark mb-0"><i class="fas fa-paper-plane me-2 text-primary"></i>Buat Pengajuan Surat Keluar</h4>
                        <a href="../ruangan/dashboard.php" class="btn btn-sm btn-secondary rounded px-3 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

                    <?= $message ?>

                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="card shadow-sm mb-4 border-0 rounded-3">
                            <div class="card-header bg-dark text-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2 text-warning"></i>Identitas Pengirim & Arsip</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Unit Pengirim</label>
                                        <input type="text" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($role_nama) ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">No. Agenda (Otomatis)</label>
                                        <input type="text" name="no_agenda" value="<?= htmlspecialchars($no_agenda) ?>" class="form-control bg-light fw-bold text-primary" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Kode Arsip <span class="text-danger">*</span></label>
                                        <input type="text" name="kode" class="form-control" placeholder="Contoh: KES" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4 border-0 rounded-3">
                            <div class="card-header bg-dark text-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-envelope-open-text me-2 text-info"></i>Detail Informasi Surat</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Nomor Surat Draft</label>
                                        <input type="text" class="form-control bg-light" value="Diisi oleh Setum" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Tanggal Surat</label>
                                        <input type="text" class="form-control bg-light" value="Diisi oleh Setum" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Tanggal Kirim</label>
                                        <input type="text" class="form-control bg-light" value="Diisi oleh Setum" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Bentuk Surat</label>
                                        <select name="shapes_surat" class="form-select">
                                            <option value="Fisik">Fisik</option>
                                            <option value="Aplikasi">Aplikasi</option>
                                            <option value="Email">Email</option>
                                            <option value="Whatsapp">Whatsapp</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Jenis Surat</label>
                                        <select name="jenis_surat" class="form-select">
                                            <option value="Biasa">Biasa</option>
                                            <option value="Keputusan">Keputusan</option>
                                            <option value="Surat Keputusan">Surat Keputusan</option>
                                            <option value="Surat Perintah">Surat Perintah</option>
                                            <option value="Surat Keterangan">Surat Keterangan / Pernyataan</option>
                                            <option value="Surat Izin">Surat Izin</option>
                                            <option value="Nota Dinas">Nota Dinas</option>
                                            <option value="Laporan">Laporan</option>
                                            <option value="Lain-lain">Lain-lain</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Klasifikasi</label>
                                        <select name="klasifikasi_surat" id="klasifikasi_surat" class="form-select" onchange="cekRahasia()">
                                            <option value="Biasa">Biasa</option>
                                            <option value="Rahasia">Rahasia</option>
                                            <option value="Sangat Rahasia">Sangat Rahasia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Derajat Surat</label>
                                        <select name="derajat_surat" class="form-select">
                                            <option value="Biasa">Biasa</option>
                                            <option value="Segera">Segera</option>
                                            <option value="Kilat">Kilat</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Status Dokumen</label>
                                        <select name="status_dokumen" class="form-select">
                                            <option value="Asli">Asli</option>
                                            <option value="Tembusan">Tembusan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-muted">Tujuan Disposisi Internal</label>
                                        <input type="text" class="form-control bg-light fw-bold text-success" value="SETUM" readonly>
                                        <input type="hidden" name="tujuan_disposisi" value="SETUM">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Tujuan Utama Target <span class="text-danger">*</span></label>
                                        <input type="text" name="tujuan_utama" class="form-control" placeholder="Contoh: Kakesdam Jaya" required>
                                    </div>

                                    <div class="col-12" id="peringatan_rahasia" style="display:none;">
                                        <div class="alert alert-warning py-2 mb-0 shadow-sm border-start border-warning border-3">
                                            <i class="fas fa-shield-alt me-2 text-danger"></i> <strong>Sifat Rahasia:</strong> Dokumen rahasia ini otomatis diprioritaskan pemeriksaannya.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Perihal <span class="text-danger">*</span></label>
                                        <textarea name="perihal" class="form-control" rows="2" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Tembusan</label>
                                        <textarea name="tembusan" class="form-control font-monospace text-sm" rows="3"><?= "1. \n2. \n3. " ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4 border-0 rounded-3">
                            <div class="card-header bg-dark text-white py-3">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-file-upload me-2 text-primary"></i>Unggah Dokumen Lampiran</h6>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">File Surat (Scan PDF / File Gambar) <span class="text-danger">*</span></label>
                                    <input type="file" name="file_surat" class="form-control" required>
                                    <div id="namaFile" class="small text-success mt-2 fw-semibold"></div>
                                    <div class="form-text text-muted small mt-2">
                                        <i class="fas fa-info-circle me-1 text-danger"></i> Ekstensi diizinkan: PDF, JPG, JPEG, PNG. Maksimal file 100MB.
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Catatan Pengirim</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan internal untuk petugas Setum jika ada..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-5">
                            <button type="submit" class="btn btn-primary btn-lg rounded shadow px-5" onclick="return confirm('Yakin surat pengajuan akan dikirim ke Setum?')">
                                <i class="fas fa-paper-plane me-2"></i> KIRIM KE SETUM
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div> </div> </div> <script>
document.getElementById("menu-toggle").addEventListener("click", function(e) {
    e.preventDefault();
    document.getElementById("wrapper").classList.toggle("toggled");
});

document.querySelector('input[name="file_surat"]').addEventListener('change', function(){
    const preview = document.getElementById('namaFile');
    if(this.files.length > 0){
        const file = this.files[0];
        preview.innerHTML = '<i class="fas fa-check-circle me-1"></i> Ready: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    }
});

function cekRahasia() {
    const klasifikasi = document.getElementById("klasifikasi_surat").value;
    const warning = document.getElementById("peringatan_rahasia");
    warning.style.display = (klasifikasi === "Rahasia" || klasifikasi === "Sangat Rahasia") ? "block" : "none";
}
window.onload = cekRahasia;
</script>

<?php include '../layout/footer.php'; ?>
