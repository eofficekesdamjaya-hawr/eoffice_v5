<?php
require_once __DIR__.'/../config/session.php';
$message = '';
require_once '../config/koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

// 1. Ambil ID dari session
$id_user = (int)($_SESSION['id_user'] ?? 0);

// 2. Ambil data satuan spesifik untuk user yang sedang login
// Kita gunakan 'id' karena itulah nama primary key di tabel users Anda
$queryUser = $conn->prepare("SELECT satuan FROM users WHERE id = ?"); 
$queryUser->bind_param("i", $id_user);
$queryUser->execute();
$resultUser = $queryUser->get_result()->fetch_assoc();

// 3. Masukkan ke variabel $role_nama
$role_nama = $_SESSION['nama_role'] ?? 'Unit Pengirim';


/* 1. GENERATE NOMOR AGENDA OTOMATIS */
$stmtAgenda = $conn->prepare("SELECT MAX(id_surat) AS terakhir FROM surat_keluar");
$stmtAgenda->execute();
$dataAgenda = $stmtAgenda->get_result()->fetch_assoc();
$nextNumber = ((int)($dataAgenda['terakhir'] ?? 0)) + 1;
$no_agenda  = "SK/" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT) . "/" . date('Y') . "/" . date('m');

/* 2. PROSES SIMPAN */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $target_dir = "../uploads/surat_keluar/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);

    $file = $_FILES['file_surat'];
    $new_name = "SRT_OUT_" . date('Ymd_HisA') . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file['name']);

    if (move_uploaded_file($file['tmp_name'], $target_dir . $new_name)) {
        
        // Definisikan status operasional untuk tracking alur
        $status_pengiriman = 'Terkirim';
        $status_proses     = 'Baru'; // Mengisi kolom wajib agar tidak error NOT NULL

        // Query dengan 18 Kolom (Menambahkan status_proses) & 18 Tanda Tanya (?)
        $stmt = $conn->prepare("
            INSERT INTO surat_keluar (
                no_agenda, kode_arsip, asal_satuan, bentuk_surat, jenis_surat, 
                klasifikasi_surat, derajat_surat, tujuan_disposisi, tujuan_utama, 
                perihal, tembusan, status_dokumen, file_surat, keterangan, 
                created_by, status_pengiriman, status_proses, tanggal_input
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Bind_param dengan 18 huruf "s" dan 18 variabel yang berurutan pas
        $stmt->bind_param("ssssssssssssssssss", 
            $no_agenda, 
            $_POST['kode'], 
            $role_nama, 
            $_POST['shapes_surat'], 
            $_POST['jenis_surat'], 
            $_POST['klasifikasi_surat'], 
            $_POST['derajat_surat'], 
            $_POST['tujuan_disposisi'], 
            $_POST['tujuan_utama'], 
            $_POST['perihal'], 
            $_POST['tembusan'], 
            $_POST['status_dokumen'], 
            $new_name, 
            $_POST['keterangan'], 
            $id_user, 
            $status_pengiriman,
            $status_proses, // Masuk ke kolom status_proses
            $_POST['tanggal_input']
        );

        $stmt->execute();
        $id_surat_baru = $stmt->insert_id;
        
        // 1. Simpan ke riwayat_surat
        // 1. Simpan ke riwayat_surat menggunakan Prepared Statement yang aman
$stmtRiwayat = $conn->prepare("INSERT INTO riwayat_surat (id_user, id_surat, jenis, status, created_at) VALUES (?, ?, 'surat_keluar', 'terkirim', NOW())");
$stmtRiwayat->bind_param("ii", $id_user, $id_surat_baru);
$stmtRiwayat->execute();
// ==========================================
// 2. Kirim Notifikasi ke SETUM (SUDAH DIPERBAIKI)
// ==========================================
$pesan_notif = "Surat Keluar Baru dari " . $role_nama . " Perihal: " . $_POST['perihal'];
$link_tujuan = "transaksi/kelola_surat_keluar.php"; 
$untuk_role  = "SETUM";
$status_baca = "belum";

// Total 6 kolom dinamis dengan tanda tanya (?), tanggal menggunakan NOW() langsung di SQL
$stmtNotif = $conn->prepare("
    INSERT INTO notifikasi (
        id_surat, untuk_role, dari_role, pesan, link, status_baca, tanggal
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())
");

// 6 huruf s/i disesuaikan dengan 6 tanda tanya di atas
$stmtNotif->bind_param("isssss", $id_surat_baru, $untuk_role, $role_nama, $pesan_notif, $link_tujuan, $status_baca);
$stmtNotif->execute();

 // ====================================================================
        // PENGALIHAN RUTU SECARA DINAMIS (KASI TUUD vs RUANGAN)
        // ====================================================================
        $current_email = $_SESSION['id_user'] ?? '';
        $tipe_akses    = $_SESSION['tipe_akses'] ?? 'ruangan';

        // Jika email yang login adalah Kasi Tuud, atau tipe aksesnya admin, arahkan ke dashboard admin
        if ($current_email === 'kasituud2026@gmail.com' || $tipe_akses === 'superadmin' || $tipe_akses === 'admin') {
            $url_redirect = '../dashboard/dashboard_admin.php';
        } else {
            $url_redirect = '../ruangan/dashboard.php';
        }

        // PERBAIKAN: Menambahkan titik koma (;) di akhir script dan perintah exit;
        echo "<script>
            alert('Surat berhasil dikirim ke Setum'); 
            window.location='" . $url_redirect . "';
        </script>";
        exit; 
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
    <?php include '../ruangan/sidebar.php'; ?>

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
                                        <input type="text" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($role_nama ?? '') ?>" readonly>
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
                                    <div class="col-md-12">
            <label class="form-label small fw-bold">Tanggal Input (Mundur/Maju) <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
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

                </div> </div> </div> </div> </div> <script>
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