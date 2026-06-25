<?php
require_once "../config/session.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../config/koneksi.php');

/* =========================================
   PROTEKSI AKSES
========================================= */
if (
    !isset($_SESSION['id_user']) ||
    !in_array($_SESSION['tipe_akses'], ['admin', 'setum', 'superadmin'])
) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'] ?? 'SETUM';

/* =========================================
   GENERATE NOMOR AGENDA
========================================= */
$tahun = date('Y');
$stmtAgenda = $conn->prepare("SELECT COUNT(*) AS total FROM surat_masuk WHERE YEAR(tanggal_diterima) = ?");
$stmtAgenda->bind_param("i", $tahun);
$stmtAgenda->execute();
$dataAgenda = $stmtAgenda->get_result()->fetch_assoc();
$nextNumber = ($dataAgenda['total'] ?? 0) + 1;
$no_agenda = "AG/SETUM/" . $tahun . "/" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);

$message = "";

/* =========================================
   PROSES SIMPAN
========================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function clean($data) { return htmlspecialchars(trim($data)); }

    $kode               = clean($_POST['kode']);
    $asal_surat         = clean($_POST['asal_surat']);
    $tanggal_input      = clean($_POST['tanggal_input']); 
    $no_surat           = clean($_POST['no_surat']);
    $tanggal_surat      = clean($_POST['tanggal_surat']);
    $tanggal_diterima   = clean($_POST['tanggal_diterima']);
    $bentuk_surat       = clean($_POST['bentuk_surat']);
    $jenis_surat        = clean($_POST['jenis_surat']);
    $klasifikasi_surat  = clean($_POST['klasifikasi_surat']);
    $sifat_surat        = clean($_POST['sifat_surat']);
    $tujuan_disposisi   = clean($_POST['tujuan_disposisi']);
    $tujuan_utama       = clean($_POST['tujuan_utama']);
    $tembusan           = clean($_POST['tembusan']);
    $perihal            = clean($_POST['perihal']);
    $status_dokumen     = clean($_POST['status_dokumen']);
    $keterangan         = clean($_POST['keterangan']);

    $file = $_FILES['file_surat'];
    if ($file['error'] != 0) {
        $message = "<div class='alert alert-danger shadow-sm'>File surat wajib diupload.</div>";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            $message = "<div class='alert alert-danger shadow-sm'>Format file harus PDF/JPG/PNG.</div>";
        } else {
            $new_name = "SM_" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($file['tmp_name'], "../uploads/" . $new_name)) {
                
                $sumber_surat   = "Setum";
                $role_pengirim  = "setum";
                $status_baca    = "belum";
                $status_proses  = "Pending";
                $last_sender    = $nama_user;
                $current_role   = ($tujuan_disposisi == 'kasi_tuud') ? 'kasituud' : $tujuan_disposisi;
                $tahap_disposisi = ($klasifikasi_surat == "Rahasia" || $klasifikasi_surat == "Sangat Rahasia") ? 4 : 1;

                $stmt = $conn->prepare("
                    INSERT INTO surat_masuk (
                        created_by, sumber_surat, role_pengirim, kode, no_agenda, asal_surat, tanggal_input, 
                        no_surat, tanggal_surat, tanggal_diterima, bentuk_surat, jenis_surat, klasifikasi_surat, 
                        sifat_surat, tujuan_disposisi, tujuan_utama, status_baca, tembusan, perihal, 
                        status_dokumen, file_surat, keterangan, tahap_disposisi, status_proses, current_role, last_sender
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param("isssssssssssssssssssssssss", 
                    $id_user, $sumber_surat, $role_pengirim, $kode, $no_agenda, $asal_surat, $tanggal_input, 
                    $no_surat, $tanggal_surat, $tanggal_diterima, $bentuk_surat, $jenis_surat, $klasifikasi_surat, 
                    $sifat_surat, $tujuan_disposisi, $tujuan_utama, $status_baca, $tembusan, $perihal, 
                    $status_dokumen, $new_name, $keterangan, $tahap_disposisi, $status_proses, $current_role, $last_sender
                );

                if ($stmt->execute()) {
                    $_SESSION['notif'] = ['type' => 'success', 'message' => 'Surat masuk berhasil ditambahkan.'];
                    header("Location: ../transaksi/kelola_surat_masuk.php"); 
                    exit;
                } else {
                    $message = "<div class='alert alert-danger'>Database Error: {$stmt->error}</div>";
                }
            }
        }
    }
}

include '../layout/header.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary">
                    <i class="bi bi-envelope-plus-fill me-2"></i>
                    Tambah Surat Masuk
                </h4>
                <a href="kelola_surat_masuk.php" class="btn btn-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Kembali
                </a>
            </div>

            <?= $message ?>

            <form id="form-surat" method="POST" enctype="multipart/form-data">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>
                            <i class="bi bi-person-badge me-2"></i> Informasi Registrasi Surat
                        </strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">No Agenda</label>
                                <input type="text" class="form-control bg-light fw-bold text-primary" value="<?= $no_agenda ?>" readonly>
                                <input type="hidden" name="no_agenda" value="<?= $no_agenda ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Kode Arsip</label>
                                <input type="text" name="kode" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Asal Surat</label>
                                <input type="text" name="asal_surat" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Input</label>
                                <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white">
                        <strong>
                            <i class="bi bi-envelope-paper me-2"></i> Detail Surat
                        </strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor Surat</label>
                                <input type="text" name="no_surat" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Surat</label>
                                <input type="date" name="tanggal_surat" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Diterima</label>
                                <input type="date" name="tanggal_diterima" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Bentuk Surat</label>
                                <select name="bentuk_surat" class="form-select">
                                    <option>Fisik</option>
                                    <option>Email</option>
                                    <option>Whatsapp</option>
                                    <option>Aplikasi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Jenis Surat</label>
                                <select name="jenis_surat" class="form-select">
                                    <option>Biasa</option>
                                    <option>Berita Telepon</option>
                                    <option>DISPOSISI PANGDAM</option>
                                    <option>Rahasia</option>
                                    <option>Surat Keputusan</option>
                                    <option>Nota Dinas</option>
                                    <option>Surat Edaran</option>
                                    <option>Surat Izin</option>
                                    <option>Surat Izin Jalan</option>
                                    <option>Surat Keterangan</option>
                                    <option>Surat Perintah</option>
                                    <option>Surat Telegram</option>
                                    <option>Surat Telegram Rahasia</option>
                                    <option>Berita Faksimile</option>
                                    <option>Surat Pernyataan</option>
                                    <option>Surat Cuti</option>
                                    <option>Berita Acara</option>
                                    <option>Surat Perjalanan Dinas</option>
                                    <option>Surat Tugas</option>
                                    <option>Surat Undangan</option>
                                    <option>Surat Kuasa</option>
                                    <option>Sertifikat</option>
                                    <option>Laporan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Klasifikasi</label>
                                <select name="klasifikasi_surat" class="form-select">
                                    <option>Terbuka</option>
                                    <option>Terbatas</option>
                                    <option>Rahasia</option>
                                    <option>Sangat Rahasia</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Sifat Surat</label>
                                <select name="sifat_surat" class="form-select">
                                    <option value="Biasa">Biasa</option>
                                    <option value="Segera">Segera</option>
                                    <option value="Kilat">Kilat</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tujuan Disposisi</label>
                                <select name="tujuan_disposisi" class="form-select" required>
                                    <option value="">-- Pilih Tujuan Disposisi --</option>
                                    <option value="kakesdam_jaya">Kakesdam Jaya</option>
                                    <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
                                    <option value="kasi_tuud">Kasi Tuud</option>
                                    <option value="kasi_tuud">setum</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tujuan Utama</label>
                                <select name="tujuan_utama" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="kakesdam_jaya">Kakesdam Jaya</option>
                                    <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
                                    <option value="kasi_tuud">Kasi Tuud</option>
                                    <option value="dandenkeslap">Dandenkeslap</option>
                                    <option value="kasi_was">Kasi Was</option>
                                    <option value="kasi_dukkes">Kasi Dukkes</option>
                                    <option value="kasi_kesprev">Kasi Kesprev</option>
                                    <option value="kasi_renproggar">Kasi Renproggar</option>
                                    <option value="kasi_minlogkes">Kasi Minlogkes</option>
                                    <option value="kasi_matkes">Kasi Matkes</option>
                                    <option value="kasi_yankes">Kasi Yankes</option>
                                    <option value="kaprimkop">Kaprimkop</option>
                                    <option value="kagud_kesrah">Kagud Kesrah</option>
                                    <option value="kaur_infokes">Kaur Infokes</option>
                                    <option value="kaur_log">Kaur Log</option>
                                    <option value="juyar">Juyar</option>
                                    <option value="kaur_pam">Kaur Pam</option>
                                    <option value="kaurdal">Kaurdal</option>
                                    <option value="kaur_pers">Kaur Pers</option>
                                    <option value="perstuud">Perstuud</option>
                                    <option value="paku_kesdam">Paku Kesdam</option>
                                    <option value="ka_smk_kesdam_jaya">Ka SMK Kesdam Jaya</option>
                                    <option value="korpri_kesdam_jaya">Korpri Kesdam Jaya</option>
                                    <option value="persit">Persit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Dokumen</label>
                                <select name="status_dokumen" class="form-select">
                                    <option value="Asli">Asli</option>
                                    <option value="Tembusan">Tembusan</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Perihal</label>
                                <textarea name="perihal" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Tembusan</label>
                                <textarea name="tembusan" class="form-control" rows="3">1.&#10;2.&#10;3.</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning">
                        <strong>
                            <i class="bi bi-file-earmark-arrow-up me-2"></i> Upload Dokumen
                        </strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">File Surat</label>
                            <input type="file" name="file_surat" class="form-control" required>
                            <small class="text-muted">Format PDF/JPG/PNG maksimal 100MB.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-5">
                    <button type="submit" id="btn-simpan-kirim" class="btn btn-success btn-lg px-5 rounded-pill shadow">
                        <i class="bi bi-save me-2"></i> KIRIM & SIMPAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('form-surat').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        return;
    }
    e.preventDefault();
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menyimpan dan mengirim surat ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});
</script>

<?php include '../layout/footer.php'; ?>
