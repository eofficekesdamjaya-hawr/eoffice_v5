<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN
// ============================
// Menggunakan 'id_user' dan 'tipe_akses' sesuai standarisasi login sebelumnya
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

// ============================
// DATA SESSION
// ============================
$id_user      = $_SESSION['id_user'];
$nama_satuan  = $_SESSION['satuan'];
$email_login  = $_SESSION['email'];
$tahun        = date('Y');

// ============================
// HITUNG NO AGENDA OTOMATIS
// ============================
$stmtAgenda = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM surat_masuk 
    WHERE role_pengirim='satuan_lain'
    AND YEAR(tanggal_diterima) = ?
");
$stmtAgenda->bind_param("i", $tahun);
$stmtAgenda->execute();
$resultAgenda = $stmtAgenda->get_result();
$dataAgenda   = $resultAgenda->fetch_assoc();

$startNumber = 1000;
$nextNumber  = ($dataAgenda['total'] ?? 0) + $startNumber;
$no_agenda_auto = "AG-SL/" . $tahun . "/" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);

// ============================
// PROSES SIMPAN
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    function clean($data){
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    $kode             = clean($_POST['kode'] ?? '');
    $no_agenda        = clean($_POST['no_agenda'] ?? '');
    $asal_satuan      = clean($_POST['asal_satuan'] ?? '');
    $nama_pengirim    = clean($_POST['nama_pengirim'] ?? '');
    $jabatan_pengirim = clean($_POST['jabatan_pengirim'] ?? '');
    $no_hp_pengirim   = clean($_POST['no_hp_pengirim'] ?? '');
    $no_surat         = clean($_POST['no_surat'] ?? '');
    $tanggal_surat    = clean($_POST['tanggal_surat'] ?? '');
$perihal          = clean($_POST['perihal'] ?? '');
$tujuan_disposisi = clean($_POST['tujuan_disposisi'] ?? 'setum');
$tujuan_utama     = clean($_POST['tujuan_utama'] ?? '');
    $tembusan         = clean($_POST['tembusan'] ?? '');
    $status_dokumen   = "Masuk";
    $keterangan       = clean($_POST['keterangan'] ?? '');

    // Validasi Dasar
    if (empty($no_surat) || empty($tanggal_surat) || empty($kepada) || empty($perihal)) {
        $error_msg = "Data wajib bertanda bintang (*) belum lengkap!";
    } else {
        // ============================
        // VALIDASI FILE
        // ============================
        if (!isset($_FILES['file_surat']) || $_FILES['file_surat']['error'] !== 0) {
            $error_msg = "File lampiran wajib diunggah!";
        } else {
            $allowed_ext = ['pdf','jpg','jpeg','png'];
            $file = $_FILES['file_surat'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) {
                $error_msg = "Format file tidak diizinkan! (Gunakan PDF/JPG/PNG)";
           } elseif ($file['size'] > 100 * 1024 * 1024) {
    $error_msg = "Ukuran file terlalu besar! Maksimal 100MB.";

            } else {
                // Proses Upload
                $new_name = "SL_" . time() . "_" . uniqid() . "." . $ext;
                $upload_path = "../uploads/" . $new_name;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // ============================
                    // INSERT DATABASE
                    // ============================
$sql = "INSERT INTO surat_masuk (
        kode, no_agenda, no_surat, tanggal_diterima, asal_surat, 
        nama_pengirim, jabatan_pengirim, no_hp_pengirim, perihal,
        tujuan_disposisi,
        tujuan_utama, tembusan, status_dokumen, file_surat, id_user,
        role_pengirim, status_proses, keterangan, status_baca
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'satuan_lain', 'Pending', ?, 'belum')";

                    $stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssssis", 
    $kode, 
    $no_agenda, 
    $no_surat, 
    $tanggal_surat, 
    $asal_satuan, 
    $nama_pengirim, 
    $jabatan_pengirim, 
    $no_hp_pengirim, 
    $perihal,
$tujuan_disposisi,
$tujuan_utama,
    $tembusan, 
    $status_dokumen, 
    $new_name, 
    $id_user, 
    $keterangan
);

                    if ($stmt->execute()) {
                        echo "<script>
                                alert('Surat berhasil dikirim ke Setum Kesdam Jaya!');
                                window.location='dashboard_satuan_lain.php';
                              </script>";
                        exit;
                    } else {
                        $error_msg = "Gagal menyimpan ke database: " . $conn->error;
                    }
                } else {
                    $error_msg = "Gagal mengunggah file ke server.";
                }
            }
        }
    }
}

include '../layout/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark mb-0"><i class="fas fa-paper-plane me-2 text-primary"></i>Kirim Surat Baru</h4>
                <a href="dashboard_satuan_lain.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Batal
                </a>
            </div>

            <?php if(isset($error_msg)): ?>
                <div class="alert alert-danger shadow-sm border-start border-5 border-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="card shadow-sm mb-4 border-0 rounded-4">
                    <div class="card-header bg-info text-white rounded-top-4 py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-user-tag me-2"></i>Identitas Pengirim & Arsip</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Email Akun</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($email_login) ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">No. Agenda (Otomatis)</label>
                                <input type="text" name="no_agenda" value="<?= $no_agenda_auto ?>" class="form-control bg-light fw-bold text-primary" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label small fw-bold">Kode Arsip</label>
                                <input type="text" name="kode" class="form-control" placeholder="Contoh: Pers Kodam jaya">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nama Instansi Asal *</label>
                                <input type="text" name="asal_satuan" class="form-control" value="<?= htmlspecialchars($nama_satuan) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nama Pengirim (PIC) *</label>
                                <input type="text" name="nama_pengirim" class="form-control" placeholder="Nama Lengkap" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Jabatan *</label>
                                <input type="text" name="jabatan_pengirim" class="form-control" placeholder="Contoh: Baurpers Kodam Jaya" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">No. HP / WA Pengirim</label>
                                <input type="text" name="no_hp_pengirim" class="form-control" placeholder="0812xxxx">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0 rounded-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2"></i>Detail Informasi Surat</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Nomor Surat Resmi *</label>
                                <input type="text" name="no_surat" class="form-control" placeholder="B/Kodamjaya/07/III/2026" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Tanggal Surat *</label>
                                <input type="date" name="tanggal_surat" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
<div class="mb-3">

<div class="col-12 mb-3">
    <label class="form-label small fw-bold">
        Tujuan Disposisi
    </label>

    <input 
        type="text"
        class="form-control bg-light fw-bold"
        value="SETUM"
        readonly
    >

    <input 
        type="hidden"
        name="tujuan_disposisi"
        value="setum"
    >

    <small class="text-muted">
        Semua surat wajib masuk melalui Setum terlebih dahulu.
    </small>
</div>
</div>

                           <div class="col-12 mb-3">
    <label class="form-label small fw-bold">
        Tujuan Utama *
    </label>

    <select name="tujuan_utama" class="form-select border-primary" required>

        <option value="">-- Pilih Tujuan Utama --</option>

        <option value="kakesdam">Kakesdam Jaya</option>
        <option value="wakakesdam">Waka Kesdam Jaya</option>
        <option value="kasi tuud">Kasi Tuud</option>
        <option value="dandenkeslap">Dandenkeslap</option>
        <option value="kaprimkop">Kaprimkop</option>
        <option value="kasi matkes">Kasi Matkes</option>
        <option value="kasi yankes">Kasi Yankes</option>
        <option value="kasi minlogkes">Kasi Minlogkes</option>
        <option value="kasi was">Kasi Was</option>
        <option value="kasi kesprev">Kasi Kesprev</option>
        <option value="kasi dukkes">Kasi Dukkes</option>
        <option value="kasi renproggar">Kasi Renproggar</option>
        <option value="kagudkesrah">KagudKesrah</option>
        <option value="kaur infokes">Kaur Infokes</option>
        <option value="paku kesdam">Paku Kesdam</option>
        <option value="korpri">Korpri</option>
        <option value="persit">Persit</option>

    </select>

    <small class="text-muted">
        Tujuan utama hanya sebagai informasi disposisi Setum.
        Surat tidak langsung masuk ke dashboard pejabat tujuan.
    </small>
</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Perihal *</label>
                            <textarea name="perihal" class="form-control" rows="2" placeholder="Isi perihal surat" required></textarea>
                        </div>

<div class="mb-0">
    <label class="form-label small fw-bold">Tembusan (Jika Ada)</label>
    <textarea name="tembusan" class="form-control" rows="6" placeholder="1. ...&#10;2. ...&#10;3. ...&#10;4. ...&#10;5. ..."><?php echo "1. \n2. \n3. \n4. \n5. "; ?></textarea>
</div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0 rounded-4">
                    <div class="card-header bg-warning py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-paperclip me-2"></i>Lampiran & Catatan</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-danger">Upload File Surat (Scan) *</label>
                                <input type="file" name="file_surat" class="form-control border-danger" required>
                               <small class="text-muted">
    Format: <strong>PDF/JPG/PNG</strong>. 
    Maksimal <strong>100MB</strong> 
    (mendukung dokumen banyak halaman).
</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Keterangan Tambahan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada"></textarea>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Surat Sekarang
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>