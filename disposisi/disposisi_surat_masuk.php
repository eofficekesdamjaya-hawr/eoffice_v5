<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/koneksi.php";

// 1. Ambil ID & Query Data Surat
$id_surat_get = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_surat_get <= 0) {
    header("Location: ../transaksi/kelola_surat_masuk.php");
    exit;
}

$query_tampil = "SELECT * FROM surat_masuk WHERE id_surat = ?";
$stmt_t = $conn->prepare($query_tampil);
$stmt_t->bind_param("i", $id_surat_get);
$stmt_t->execute();
$surat = $stmt_t->get_result()->fetch_assoc();

// 2. Load Auth Config (Sekarang variabel $role, $dari, $is_admin_setum tersedia)
require_once "../config/auth_config.php";

// 3. Penyesuaian Nama User & Role
$nama_user = $_SESSION['nama_user'] ?? $_SESSION['nama'] ?? 'User';
$user_role = $role; // Mengambil dari auth_config.php

// Validasi Login Utama
if (empty($_SESSION['id_user'])) {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}

// ==========================================
// 1. PROSES SIMPAN DATA (JIKA FORM DISUBMIT)
// ==========================================
if (isset($_POST['submit_disposisi_masuk'])) {
    $id_surat          = (int)$_POST['id_surat'];
   $dari_role = $role;
    $tujuan_disposisi  = trim($_POST['tujuan_disposisi']);
    $catatan_disposisi = trim($_POST['catatan_disposisi']);
    $signature_data    = $_POST['signature_data'] ?? ''; 
    
    // Aturan Otomatis: Jika ruangan yang menjawab, tujuan mutlak ke 'setum'
    if ($tipe_akses_login === 'ruangan' || $status_user_login === 'ruangan') {
        $tujuan_disposisi = 'setum'; 
    }

    // Ambil data tembusan baru dari input: disposisi_tembusan[]
    $tembusan_array  = isset($_POST['disposisi_tembusan']) ? $_POST['disposisi_tembusan'] : ['tidak_ada'];
    $tembusan_string = implode(',', $tembusan_array);

    if ($id_surat <= 0 || empty($tujuan_disposisi) || empty($catatan_disposisi)) {
        echo "<script>alert('Data disposisi tidak lengkap!'); window.history.back();</script>";
        exit;
    }

    // Simpan string Base64 tanda tangan digital ke database
    $isi_ttd_db = !empty($signature_data) ? $signature_data : "no_signature";

    // --- SINKRONISASI QUERY INSERT SESUAI DATABASE KESDAM ---
    $query_disp = "INSERT INTO disposisi_surat (id_surat, dari_role, tujuan_role, jenis_surat, dari, ke, catatan, status_disposisi, tembusan_kasi, created_by, ttd_disposisi, status_baca) 
                   VALUES (?, ?, ?, 'masuk', ?, ?, ?, 'Proses', ?, ?, ?, 'belum')";
    
    $stmt_disp = $conn->prepare($query_disp);
    $stmt_disp->bind_param("issssssis", $id_surat, $user_role, $tujuan_disposisi, $nama_user, $tujuan_disposisi, $catatan_disposisi, $tembusan_string, $id_user, $isi_ttd_db);
    
    if ($stmt_disp->execute()) {
        // Ambil info surat untuk kebutuhan sistem notifikasi
        $query_surat = "SELECT no_agenda, perihal, file_surat FROM surat_masuk WHERE id_surat = ?";
        $stmt_s = $conn->prepare($query_surat);
        $stmt_s->bind_param("i", $id_surat);
        $stmt_s->execute();
        $res_s = $stmt_s->get_result()->fetch_assoc();
        $no_agenda  = $res_s['no_agenda'] ?? '-';
        $perihal    = $res_s['perihal'] ?? 'Surat Kedinasan Baru';
        $file_surat = $res_s['file_surat'] ?? '';

        $pesan_error_berkas = "";
        if (!empty($file_surat)) {
            $path_berkas = "../uploads/" . $file_surat; 
            if (!file_exists($path_berkas)) {
                $pesan_error_berkas = "\\n\\n⚠️ PERINGATAN: File berkas digital (" . $file_surat . ") tidak ditemukan di folder uploads/.";
            }
        }

        $pesan_notif = "Disposisi Masuk Baru! No. Agenda: " . $no_agenda . " - Perihal: " . $perihal;
        $status_baca = "belum";
        $link_target = "dashboard.php"; 

        // --- SISTEM NOTIFIKASI AMAN (TRY-CATCH) ---
        try {
            // 1. Notifikasi Penerima Utama
            if ($tujuan_disposisi !== 'tidak_ada') {
                $query_notif = "INSERT INTO notifikasi (untuk_role, pesan, link, status_baca) VALUES (?, ?, ?, ?)";
                $stmt_n = $conn->prepare($query_notif);
                $stmt_n->bind_param("ssss", $tujuan_disposisi, $pesan_notif, $link_target, $status_baca);
                $stmt_n->execute();
                $stmt_n->close();
            }

            // 2. Notifikasi Tembusan Berulang (Looping)
            foreach ($tembusan_array as $tembusan_role) {
                if ($tembusan_role !== 'tidak_ada') {
                    $pesan_tembusan = "[TEMBUSAN] " . $pesan_notif;
                    $query_notif_t = "INSERT INTO notifikasi (untuk_role, pesan, link, status_baca) VALUES (?, ?, ?, ?)";
                    $stmt_nt = $conn->prepare($query_notif_t);
                    $stmt_nt->bind_param("ssss", $tembusan_role, $pesan_tembusan, $link_target, $status_baca);
                    $stmt_nt->execute();
                    $stmt_nt->close();
                }
            }
        } catch (Exception $e) {
            // Bypass jika struktur kolom tabel notifikasi berbeda
        }

        // Sinkronisasi status di tabel induk surat_masuk
        $query_update_status = "UPDATE surat_masuk SET status_proses = 'Proses Disposisi' WHERE id_surat = ?";
        $stmt_up = $conn->prepare($query_update_status);
        $stmt_up->bind_param("i", $id_surat);
        $stmt_up->execute();
        $stmt_up->close();

        $stmt_disp->close();
        $conn->close();

        echo "<script>
                alert('Disposisi Surat Masuk Berhasil Diproses & Didistribusikan!" . $pesan_error_berkas . "'); 
                window.location='../transaksi/kelola_surat_masuk.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan data disposisi.'); window.history.back();</script>";
        exit;
    }
}

// ==========================================
// 2. TAMPILAN HALAMAN FORM (JIKA DIKLIK GET)
// ==========================================
$id_surat_get = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_surat_get <= 0) {
    header("Location: ../transaksi/kelola_surat_masuk.php");
    exit;
}

$query_tampil = "SELECT * FROM surat_masuk WHERE id_surat = ?";
$stmt_t = $conn->prepare($query_tampil);
$stmt_t->bind_param("i", $id_surat_get);
$stmt_t->execute();
$surat = $stmt_t->get_result()->fetch_assoc();

if (!$surat) {
    echo "<script>alert('Data surat tidak ditemukan!'); window.location='../transaksi/kelola_surat_masuk.php';</script>";
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Jawab Disposisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        #signature-pad { border: 2px dashed #ccc; background-color: #fafafa; border-radius: 5px; cursor: crosshair; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-dark text-white p-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-reply-all-fill text-success me-2"></i> Form Jawab Disposisi Surat</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-sm table-striped mb-4 text-muted" style="font-size: 0.85rem;">
                        <tr><td style="width: 25%;">No. Agenda</td><td>: <strong><?= htmlspecialchars($surat['no_agenda'] ?? '-') ?></strong></td></tr>
                        <tr><td>Asal Surat</td><td>: <?= htmlspecialchars($surat['asal_surat'] ?? '-') ?></td></tr>
                        <tr><td>Perihal</td><td>: <?= htmlspecialchars($surat['perihal'] ?? '-') ?></td></tr>
                    </table>

                    <form action="disposisi_surat_masuk.php" method="POST" id="formDisposisi">
                        <input type="hidden" name="id_surat" value="<?= $surat['id_surat'] ?>">
                        <input type="hidden" name="dari_role" value="<?= htmlspecialchars($nama_user) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Diteruskan Kepada / Tujuan Disposisi</label>
                            <?php if (!$is_admin_setum && $user_role === 'ruangan'): ?>
                                <input type="text" class="form-control bg-light" name="tujuan_disposisi" value="setum" readonly>
                                <div class="form-text text-danger">* Akses Ruangan otomatis menjawab kembali ke Staf Umum (Setum).</div>
                            <?php else: ?>
                                <select class="form-select border-primary" name="tujuan_disposisi" required>
                                    <option value="">-- Pilih Unit Tujuan Utama --</option>
                                    <option value="kakesdam_jaya">Kakesdam Jaya</option>
                                    <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
                                    <option value="dandenkeslap">Dandenkeslap</option>
                                    <option value="setum">setum</option>
                                    <option value="kasi_tuud">Kasi Tuud</option>
                                    <option value="kasi_was">Kasi Was</option>
                                    <option value="kasi_dukkes">Kasi Dukkes</option>
                                    <option value="kasi_kesprev">Kasi Kesprev</option>
                                    <option value="kasi_renproggar">Kasi Renproggar</option>
                                    <option value="kasi_minlogkes">Kasi Minlogkes</option>
                                    <option value="kasi_matkes">Kasi Matkes</option>
                                    <option value="kasi_yankes">Kasi Yankes</option>
                                    <option value="ka_primkop">Ka Primkop</option>
                                    <option value="kagud_kesrah">Kagud Kesrah</option>
                                    <option value="pers_tuud">Pers Tuud</option>
                                    <option value="kaur_infokes">Kaur Infokes</option>
                                    <option value="kaur_pers">Kaur Pers</option>
                                    <option value="kaur_log">Kaur Log</option>
                                    <option value="kaur_dal">Kaurdal</option>
                                    <option value="kaur_pam">Kaurpam</option>
                                    <option value="juyar">Juyar</option>
                                    <option value="paku_kesdam">Paku Kesdam</option>
                                    <option value="ka_smk_kesdam">Ka SMK Kesdam Jaya</option>
                                    <option value="persit_kck_ranting5">Persit</option>
                                    <option value="korpri_kesdam">Korpri</option>
                                    <option value="spri_pimpinan">spri pimpinan</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Tembusan Disposisi (Bisa Pilih Lebih dari Satu)</label>
                            <select name="disposisi_tembusan[]" class="form-select border-primary-subtle" multiple style="height:130px;">
                                <option value="tidak_ada" selected>-- Tidak Ada Tembusan (-) --</option>
                                <option value="kakesdam_jaya">Kakesdam Jaya</option>
                                <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
                                <option value="dandenkeslap">Dandenkeslap</option>
                                <option value="kasi_tuud">Kasi Tuud</option>
                                <option value="kasi_was">Kasi Was</option>
                                <option value="kasi_dukkes">Kasi Dukkes</option>
                                <option value="kasi_kesprev">Kasi Kesprev</option>
                                <option value="kasi_renproggar">Kasi Renproggar</option>
                                <option value="kasi_minlogkes">Kasi Minlogkes</option>
                                <option value="kasi_matkes">Kasi Matkes</option>
                                <option value="kasi_yankes">Kasi Yankes</option>
                                <option value="ka_primkop">Ka Primkop</option>
                                <option value="kagud_kesrah">Kagud Kesrah</option>
                                <option value="pers_tuud">Pers Tuud</option>
                                <option value="kaur_infokes">Kaur Infokes</option>
                                <option value="kaur_pers">Kaur Pers</option>
                                <option value="kaur_log">Kaur Log</option>
                                <option value="kaur_dal">Kaurdal</option>
                                <option value="kaur_pam">Kaurpam</option>
                                <option value="juyar">Juyar</option>
                                <option value="paku_kesdam">Paku Kesdam</option>
                                <option value="ka_smk_kesdam">Ka SMK Kesdam Jaya</option>
                                <option value="persit_kck_ranting5">Persit</option>
                                <option value="korpri_kesdam">Korpri</option>
                            </select>
                            <div class="form-text text-muted">* Tahan tombol <code>Ctrl</code> (Windows) atau <code>Command</code> (Mac) untuk memilih beberapa tembusan sekaligus.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Isi Catatan / Instruksi Disposisi</label>
                            <textarea class="form-control" name="catatan_disposisi" rows="4" placeholder="Tuliskan tanggapan atau catatan disposisi di sini..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Tanda Tangan Digital</label>
                            <canvas id="signature-pad" width="400" height="150"></canvas>
                            <input type="hidden" name="signature_data" id="signature_data">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-sig"><i class="bi bi-trash"></i> Bersihkan TTD</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="../transaksi/kelola_surat_masuk.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                            <button type="submit" name="submit_disposisi_masuk" class="btn btn-success fw-bold"><i class="bi bi-send-check"></i> Kirim Jawaban Disposisi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    ctx.strokeStyle = "#1e293b";
    ctx.lineWidth = 3;
    ctx.lineCap = "round";

    function getMousePos(canvasDom, touchOrMouseEvent) {
        const rect = canvasDom.getBoundingClientRect();
        return {
            x: (touchOrMouseEvent.clientX || touchOrMouseEvent.touches[0].clientX) - rect.left,
            y: (touchOrMouseEvent.clientY || touchOrMouseEvent.touches[0].clientY) - rect.top
        };
    }

    ['mousedown', 'touchstart'].forEach(evt => {
        canvas.addEventListener(evt, (e) => {
            drawing = true;
            const pos = getMousePos(canvas, e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            if(evt === 'touchstart') e.preventDefault();
        });
    });

    ['mousemove', 'touchmove'].forEach(evt => {
        canvas.addEventListener(evt, (e) => {
            if (!drawing) return;
            const pos = getMousePos(canvas, e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if(evt === 'touchmove') e.preventDefault();
        });
    });

    ['mouseup', 'touchend'].forEach(evt => {
        canvas.addEventListener(evt, () => drawing = false);
    });

    document.getElementById('clear-sig').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signature_data').value = '';
    });

    document.getElementById('formDisposisi').addEventListener('submit', function(e) {
        const dataUrl = canvas.toDataURL('image/png');
        document.getElementById('signature_data').value = dataUrl;
    });
</script>
</body>
</html>
