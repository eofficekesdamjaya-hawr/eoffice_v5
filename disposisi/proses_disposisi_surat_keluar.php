<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_disposisi'])) {
    header("Location: ../transaksi/kelola_surat_keluar.php");
    exit;
}

// ======================================================
// AMBIL DATA INPUT
// ======================================================
$id_surat          = isset($_POST['id_surat']) ? (int)$_POST['id_surat'] : 0;
$dari_role         = strtolower(mysqli_real_escape_string($conn, $_POST['dari_role'] ?? ''));
$tujuan_utama      = strtolower(mysqli_real_escape_string($conn, $_POST['tujuan_utama'] ?? $_POST['disposisi_pimpinan'] ?? ''));
$catatan           = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
$signature_data    = $_POST['signature_data'] ?? '';
$tembusan_array    = $_POST['disposisi_tembusan'] ?? ['tidak_ada'];
$tembusan_string   = implode(',', $tembusan_array);

// Ambil data sesi yang BENAR
$user_email_aktif  = strtolower($_SESSION['email'] ?? '');

// ======================================================
// PEMETAAN ROLE / JABATAN
// ======================================================
if ($dari_role === 'ruangan' && !empty($user_email_aktif)) {
    $ruanganMap = [
        'dandenkeslap2026@gmail.com'   => 'dandenkeslap',
        'kasiwas@gmail.com'            => 'kasi_was',
        'kasidukkes2026@gmail.com'     => 'kasi_dukkes',
        'kasikesprev2026@gmail.com'    => 'kasi_kesprev',
        'kasirenproggar@gmail.com'     => 'kasi_renproggar',
        'kasiminlogkes2026@gmail.com'  => 'kasi_minlogkes',
        'kasimatkes2026@gmail.com'     => 'kasi_matkes',
        'kasiyankes2026@gmail.com'     => 'kasi_yankes',
        'kaprimkop2026@gmail.com'      => 'ka_primkop',
        'kagudkesrah2026@gmail.com'    => 'kagud_kesrah',
        'pakukesdam2026@gmail.com'     => 'paku_kesdam',
        'smkkesdamjaya@gmail.com'      => 'ka_smk_kesdam',
        'infokes@gmail.com'            => 'kaur_infokes',
        'logkesdam@gmail.com'          => 'kaur_log',
        'juyarkesdam@gmail.com'        => 'juyar',
        'persit@gmail.com'             => 'persit_kck_ranting5',
        'pamkesdam@gmail.com'          => 'kaur_pam',
        'urdalkesdam@gmail.com'        => 'kaur_dal',
        'korpri@gmail.com'             => 'korpri_kesdam',
        'perskesdam@gmail.com'         => 'kaur_pers',
        'perstuud@gmail.com'           => 'pers_tuud'
    ];

    if (array_key_exists($user_email_aktif, $ruanganMap)) {
        $dari_role = $ruanganMap[$user_email_aktif];
    } else {
        $dari_role = 'ruangan';
    }
}

// Validasi input wajib
if ($id_surat <= 0 || empty($tujuan_utama) || empty($catatan)) {
    echo "<script>alert('Gagal! Data disposisi tidak lengkap.'); window.history.back();</script>";
    exit;
}

// ======================================================
// PROSES SIMPAN TANDA TANGAN
// ======================================================
$nama_file_ttd = "";
if (!empty($signature_data)) {
    if (preg_match('/^data:image\/png;base64,/', $signature_data)) {
        $encoded_data = str_replace('data:image/png;base64,', '', $signature_data);
        $encoded_data = str_replace(' ', '+', $encoded_data);
        $decoded_data = base64_decode($encoded_data);

        $target_dir = "../uploads/ttd_disposisi_keluar/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $nama_file_ttd = "ttd_keluar_" . $id_surat . "_" . time() . "_" . uniqid() . ".png";
        $file_target_path = $target_dir . $nama_file_ttd;

        if (!file_put_contents($file_target_path, $decoded_data)) {
            echo "<script>alert('Gagal menyimpan tanda tangan.'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Format tanda tangan tidak valid!'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Tanda tangan wajib diisi!'); window.history.back();</script>";
    exit;
}

// ======================================================
// TRANSAKSI DATABASE
// ======================================================
$conn->begin_transaction();

try {
    // 1. Simpan ke log_disposisi_keluar → 6 parameter = tipe "isssss"
    $query_log = "INSERT INTO log_disposisi_keluar 
                  (id_surat, dari_jabatan, ke_jabatan, tembusan, catatan, file_ttd, tgl_disposisi)  
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt_log = $conn->prepare($query_log);
    if (!$stmt_log) throw new Exception($conn->error);
    // Tipe: i=int, s=string → total 6 = "isssss"
    $stmt_log->bind_param("isssss", $id_surat, $dari_role, $tujuan_utama, $tembusan_string, $catatan, $nama_file_ttd);
    $stmt_log->execute();

    // 2. Tentukan status surat
    if ($tujuan_utama === 'arsip') {
        $status_baru = 'selesai';
    } elseif ($tujuan_utama === 'ruangan_pengirim') {
        $status_baru = 'dikembalikan';
    } else {
        $status_baru = 'diproses';
    }

    // 3. Update surat_keluar → 4 parameter = tipe "sssi"
    $query_update = "UPDATE surat_keluar 
                     SET posisi_surat = ?, status_proses = ?, catatan_terakhir = ? 
                     WHERE id_surat = ?";


    $stmt_update = $conn->prepare($query_update);
    if (!$stmt_update) throw new Exception($conn->error);
    $stmt_update->bind_param("sssi", $tujuan_utama, $status_baru, $catatan, $id_surat);
    $stmt_update->execute();

    // ======================================================
// NOTIFIKASI SYSTEM FULL DUA ARAH
// ======================================================

$tujuan_utama = strtolower(trim($tujuan_utama));
$dari_role    = strtolower(trim($dari_role));

$link = "../disposisi/disposisi_surat_keluar.php?id=$id_surat";

// PESAN
$pesan_forward = "Disposisi surat #$id_surat dari " . strtoupper($dari_role);
$pesan_back    = "Anda menerima disposisi surat #$id_surat dari " . strtoupper($dari_role);

$query_notif = "INSERT INTO notifikasi 
(id_surat, untuk_role, dari_role, pesan, link, status_baca)
VALUES (?, ?, ?, ?, ?, 'belum')";

// ======================================================
// 1. FORWARD (KE TUJUAN UTAMA)
// ======================================================
$stmt1 = $conn->prepare($query_notif);
$stmt1->bind_param("issss", $id_surat, $tujuan_utama, $dari_role, $pesan_forward, $link);
$stmt1->execute();

// ======================================================
// 2. BACKWARD (DUA ARAH KE PENGIRIM)
// ======================================================
$stmt2 = $conn->prepare($query_notif);
$stmt2->bind_param("issss", $id_surat, $dari_role, $tujuan_utama, $pesan_back, $link);
$stmt2->execute();

// ======================================================
// 3. TEMBUSAN (MULTI ROLE)
// ======================================================
if (!in_array('tidak_ada', $tembusan_array)) {

    foreach ($tembusan_array as $role_tembusan) {

        $role_tembusan = strtolower(trim($role_tembusan));

        if (
            !empty($role_tembusan) &&
            $role_tembusan !== $tujuan_utama &&
            $role_tembusan !== $dari_role
        ) {

            $pesan_tembusan = "Tembusan surat #$id_surat dari " . strtoupper($dari_role);

            $stmt3 = $conn->prepare($query_notif);
            $stmt3->bind_param("issss", $id_surat, $role_tembusan, $dari_role, $pesan_tembusan, $link);
            $stmt3->execute();
        }
    }
}

    // 4. Notifikasi tujuan utama → 5 parameter = tipe "issss"
    $pesan = "Disposisi surat keluar #$id_surat dari: " . strtoupper(str_replace('_', ' ', $dari_role));
    $link = "../disposisi/disposisi_surat_keluar.php?id=$id_surat";

    $query_notif = "INSERT INTO notifikasi 
                    (id_surat, untuk_role, dari_role, pesan, link, status_baca) 
                    VALUES (?, ?, ?, ?, ?, 'belum')";
    $stmt_notif = $conn->prepare($query_notif);
    if (!$stmt_notif) throw new Exception($conn->error);
    // Tipe: i + 4 s = "issss"
    $stmt_notif->bind_param("issss", $id_surat, $tujuan_utama, $dari_role, $pesan, $link);
    $stmt_notif->execute();

    // 5. Notifikasi tembusan jika ada
    if (!in_array('tidak_ada', $tembusan_array)) {
        foreach ($tembusan_array as $untuk_tembus) {
            $untuk_tembus = strtolower(trim($untuk_tembus));
            if (!empty($untuk_tembus) && $untuk_tembus !== $tujuan_utama) {
                $stmt_notif->bind_param("issss", $id_surat, $untuk_tembus, $dari_role, $pesan, $link);
                $stmt_notif->execute();
            }
        }
    }

    $conn->commit();
    
    echo "<script>alert('✅ Disposisi berhasil dikirim ke tujuan!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if (!empty($nama_file_ttd) && file_exists($target_dir . $nama_file_ttd)) {
        unlink($target_dir . $nama_file_ttd);
    }
    echo "<script>alert('❌ Gagal: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    exit;
}
?>
