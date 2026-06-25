<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI HALAMAN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

// ============================
// DATA SESSION
// ============================
$id_jajaran   = $_SESSION['id_user'];
$nama_jajaran = $_SESSION['nama'];
$email_login  = $_SESSION['email'];
$tahun        = date('Y');

// ============================
// FUNCTION CLEAN
// ============================
function clean($data)
{
    return htmlspecialchars(trim($data));
}

// ============================
// NOMOR AGENDA OTOMATIS
// ============================
$stmtAgenda = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM surat_masuk
    WHERE role_pengirim = 'jajaran'
    AND YEAR(tanggal_diterima) = ?
");

$stmtAgenda->bind_param("i", $tahun);
$stmtAgenda->execute();

$resultAgenda = $stmtAgenda->get_result();
$dataAgenda   = $resultAgenda->fetch_assoc();

$startNumber    = 500;
$nextNumber     = ($dataAgenda['total'] ?? 0) + $startNumber;
$no_agenda_auto = "AG-JAJ/" . $tahun . "/" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);

// ============================
// PROSES SIMPAN
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ============================
    // AMBIL DATA FORM
    // ============================
    $kode               = clean($_POST['kode']);
    $no_agenda          = clean($_POST['no_agenda']);
    $asal_satuan        = clean($_POST['asal_satuan']);
    $nama_pengirim      = clean($_POST['nama_pengirim']);
    $jabatan_pengirim   = clean($_POST['jabatan_pengirim']);
    $no_hp_pengirim     = clean($_POST['no_hp_pengirim']);
    $no_surat           = clean($_POST['no_surat']);
    $tanggal_surat      = clean($_POST['tanggal_surat']);
    $perihal            = clean($_POST['perihal']);
    $tujuan_disposisi = clean($_POST['tujuan_disposisi']);
$tujuan_utama     = clean($_POST['tujuan_utama']);
    $tembusan           = clean($_POST['tembusan']);
    $keterangan         = clean($_POST['keterangan']);

    $bentuk_surat       = clean($_POST['bentuk_surat']);
    $jenis_surat        = clean($_POST['jenis_surat']);
    $klasifikasi        = clean($_POST['klasifikasi_surat']);
    $sifat_surat        = clean($_POST['sifat_surat']);
    $tujuan_disposisi   = clean($_POST['tujuan_disposisi']);

    // ============================
    // STATUS DEFAULT
    // ============================
    $status_dokumen = "Asli";
    $status_proses  = "Pending";
    $status_baca = "belum";
$current_role = "setum";
$last_sender = $nama_jajaran;

    // ============================
    // VALIDASI NOMOR SURAT
    // ============================
    $cekSurat = $conn->prepare("
        SELECT id_surat
        FROM surat_masuk
        WHERE no_surat = ?
    ");

    $cekSurat->bind_param("s", $no_surat);
    $cekSurat->execute();

    if ($cekSurat->get_result()->num_rows > 0) {

        $error_msg = "Nomor surat sudah pernah digunakan!";

    }

    // ============================
    // VALIDASI FILE
    // ============================
    if (!isset($error_msg)) {

        if (!isset($_FILES['file_surat']) || $_FILES['file_surat']['error'] != 0) {

            $error_msg = "File surat wajib diupload!";

        } else {

            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

            $file = $_FILES['file_surat'];

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // ============================
            // VALIDASI FORMAT FILE
            // ============================
            if (!in_array($ext, $allowed_ext)) {

                $error_msg = "Format file tidak diizinkan! Gunakan PDF/JPG/PNG.";

            }
            // ============================
            // VALIDASI UKURAN FILE
            // ============================
            elseif ($file['size'] > 100 * 1024 * 1024) {

                $error_msg = "Ukuran file maksimal 100MB!";

            } else {

                // ============================
                // GENERATE NAMA FILE
                // ============================
                $new_name = "JAJ_" . time() . "_" . uniqid() . "." . $ext;

                $upload_dir  = "../uploads/";
                $upload_path = $upload_dir . $new_name;

                // ============================
                // BUAT FOLDER JIKA BELUM ADA
                // ============================
                if (!is_dir($upload_dir)) {

                    mkdir($upload_dir, 0777, true);

                }

                // ============================
                // UPLOAD FILE
                // ============================
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {

                    // ============================
                    // INSERT DATABASE
                    // ============================
                    $sql = "INSERT INTO surat_masuk (
                        id_user,
                        kode,
                        no_agenda,
                        asal_surat,
                        nama_pengirim,
                        jabatan_pengirim,
                        no_hp_pengirim,
                        no_surat,
                        tanggal_surat,
                        bentuk_surat,
                        klasifikasi_surat,
                        sifat_surat,
                        tujuan_disposisi,
tujuan_utama,
status_baca,
current_role,
last_sender,
                        tembusan,
                        perihal,
                        status_dokumen,
                        file_surat,
                        keterangan,
                        status_proses,
                        tujuan_disposisi,
                        jenis_surat,
                        sumber_surat,
                        role_pengirim
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Jajaran', 'jajaran'
                    )";

                    $stmt = $conn->prepare($sql);

                    // ============================
                    // VALIDASI PREPARE
                    // ============================
                    if (!$stmt) {

                        $error_msg = "Prepare statement gagal : " . $conn->error;

                    } else {

                      $stmt->bind_param(
    "issssssssssssssssssss",
    $id_jajaran,
    $kode,
    $no_agenda,
    $asal_satuan,
    $nama_pengirim,
    $jabatan_pengirim,
    $no_hp_pengirim,
    $no_surat,
    $tanggal_surat,
    $bentuk_surat,
    $klasifikasi,
    $sifat_surat,
    $tujuan_disposisi,
$tujuan_utama,
$status_baca,
$current_role,
$last_sender,
    $tembusan,
    $perihal,
    $status_dokumen,
    $new_name,
    $keterangan,
    $status_proses,
    $tujuan_disposisi,
    $jenis_surat
);
                        // ============================
                        // EKSEKUSI QUERY
                        // ============================
                        if ($stmt->execute()) {

                            echo "
                            <script>
                                alert('Surat berhasil dikirim ke Setum Kesdam Jaya!');
                                window.location='dashboard_jajaran.php';
                            </script>
                            ";

                            exit;

                        } else {

                            $error_msg = "Gagal menyimpan data : " . $stmt->error;

                        }
                    }

                } else {

                    $error_msg = "Gagal upload file ke folder uploads!";

                }
            }
        }
    }
}

include '../layout/header.php';
?>

<div class="container mt-4 mb-5">

    <!-- HEADER -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0">
                <i class="fas fa-paper-plane me-2"></i>
                Form Pengiriman Surat Jajaran
            </h4>
        </div>
    </div>

    <!-- ALERT -->
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $error_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- IDENTITAS -->
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-tag me-2"></i>
                    Identitas Pengirim
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">User Penginput</label>

                        <input type="text"
                               class="form-control bg-light"
                               value="<?= htmlspecialchars($email_login) ?>"
                               readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">No Agenda</label>

                        <input type="text"
                               name="no_agenda"
                               class="form-control bg-light text-primary fw-bold"
                               value="<?= $no_agenda_auto ?>"
                               readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Kode Arsip</label>

                        <input type="text"
                               name="kode"
                               class="form-control"
                               placeholder="Contoh : PERS / LOG"
                               required>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Asal Satuan</label>

                        <input type="text"
                               name="asal_satuan"
                               class="form-control"
                               value="<?= htmlspecialchars($nama_jajaran) ?>"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Pengirim</label>

                        <input type="text"
                               name="nama_pengirim"
                               class="form-control"
                               required>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jabatan Pengirim</label>

                        <input type="text"
                               name="jabatan_pengirim"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">No HP / WhatsApp</label>

                        <input type="text"
                               name="no_hp_pengirim"
                               class="form-control"
                               placeholder="0812xxxx">
                    </div>

                </div>

            </div>
        </div>

        <!-- DETAIL SURAT -->
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    Detail Surat
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nomor Surat</label>

                        <input type="text"
                               name="no_surat"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Surat</label>

                        <input type="date"
                               name="tanggal_surat"
                               class="form-control"
                               value="<?= date('Y-m-d') ?>"
                               required>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Bentuk</label>

                        <select name="bentuk_surat" class="form-select" required>
                            <option>Aplikasi</option>
                            <option>Email</option>
                            <option>Whatsapp</option>
                            <option>Fisik</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Jenis Surat</label>

                        <select name="jenis_surat" class="form-select" required>
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

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">Klasifikasi</label>

                        <select name="klasifikasi_surat"
                                id="klasifikasi_surat"
                                class="form-select"
                                onchange="cekRahasia()"
                                required>

                            <option>Terbuka</option>
                            <option>Terbatas</option>
                            <option>Rahasia</option>
                            <option>Sangat Rahasia</option>

                        </select>

                        <div id="peringatan_rahasia"
                             class="alert alert-danger mt-2 p-2"
                             style="display:none;font-size:12px;">

                            <i class="fas fa-lock me-1"></i>
                            Hanya Kakesdam yang dapat membuka file klasifikasi rahasia.

                        </div>

                    </div>

                    <div class="col-md-3 mb-3">

                        <label class="form-label fw-bold">Sifat Surat</label>

                        <select name="sifat_surat" class="form-select" required>
                            <option>Biasa</option>
                            <option>Segera</option>
                            <option>Sangat Segera</option>
                            <option>Kilat</option>
                        </select>

                    </div>

                </div>

                <div class="row">

    <div class="col-md-6 mb-3">

        <label class="form-label fw-bold">
            Tujuan Disposisi
        </label>

        <input type="text"
               class="form-control bg-light fw-bold"
               value="SETUM"
               readonly>

        <input type="hidden"
               name="tujuan_disposisi"
               value="setum">

        <div class="alert alert-info mt-2 mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Surat otomatis masuk ke Setum Kesdam Jaya.
        </div>

    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label fw-bold">
            Tujuan Utama
        </label>

        <select name="tujuan_utama"
                class="form-select"
                required>

            <option value="">-- Pilih Tujuan --</option>

            <option value="kakesdam_jaya">Kakesdam Jaya</option>
            <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
            <option value="kasi_tuud">Kasi Tuud</option>
            <option value="dandenkeslap">Dandenkeslap</option>
            <option value="kaprimkop">Kaprimkop</option>
            <option value="kasi_matkes">Kasi Matkes</option>
            <option value="kasi_yankes">Kasi Yankes</option>
            <option value="kasi_minlogkes">Kasi Minlogkes</option>
            <option value="kasi_was">Kasi Was</option>
            <option value="kasi_kesprev">Kasi Kesprev</option>
            <option value="kasi_dukkes">Kasi Dukkes</option>
            <option value="kasi_renproggar">Kasi Renproggar</option>
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

</div>

                    <label class="form-label fw-bold">Tembusan</label>

                    <textarea name="tembusan"
                              rows="5"
                              class="form-control">1.
2.
3.</textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label fw-bold">Perihal</label>

                    <textarea name="perihal"
                              rows="2"
                              class="form-control"
                              required></textarea>

                </div>

            </div>
        </div>

        <!-- FILE -->
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-warning py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-file-upload me-2"></i>
                    Unggah Dokumen
                </h6>
            </div>

            <div class="card-body p-4">

                <div class="mb-3">

                    <label class="form-label small fw-bold">
                        File Surat (Scan PDF/Gambar)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="file"
                           name="file_surat"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required>

                    <div class="form-text text-danger small mt-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Pastikan file terbaca jelas, format PDF/JPG/PNG dengan ukuran maksimal 100MB (Mendukung dokumen banyak halaman).
                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label small fw-bold">
                        Catatan Tambahan
                    </label>

                    <textarea name="keterangan"
                              class="form-control"
                              rows="2"
                              placeholder="Catatan opsional untuk Setum..."></textarea>

                </div>

            </div>
        </div>

        <!-- BUTTON -->
        <div class="text-center mb-5">

            <a href="dashboard_jajaran.php"
               class="btn btn-outline-dark btn-lg rounded-pill px-4 shadow-sm me-2">

                <i class="fas fa-arrow-left me-2"></i>
                Dashboard
            </a>

            <button type="submit"
                    class="btn btn-primary btn-lg rounded-pill px-5 shadow">

                <i class="fas fa-paper-plane me-2"></i>
                KIRIM KE SETUM
            </button>

            <a href="../auth/logout.php"
               class="btn btn-outline-danger btn-lg rounded-pill px-4 shadow-sm ms-2"
               onclick="return confirm('Yakin ingin logout?')">

                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </a>

        </div>

    </form>
</div>

<script>
function cekRahasia() {

    const klasifikasi = document.getElementById("klasifikasi_surat").value;

    const warning = document.getElementById("peringatan_rahasia");

    warning.style.display =
        (klasifikasi === "Rahasia" || klasifikasi === "Sangat Rahasia")
        ? "block"
        : "none";
}

// otomatis cek saat halaman dibuka
window.onload = cekRahasia;
</script>

<?php include '../layout/footer.php'; ?>