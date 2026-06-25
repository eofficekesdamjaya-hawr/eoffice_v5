<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if (isset($_POST['submit_disposisi_masuk'])) {
    $id_surat          = (int)$_POST['id_surat'];
    $dari_role         = trim($_POST['dari_role'] ?? 'setum'); 
    $tujuan_disposisi  = trim($_POST['tujuan_disposisi'] ?? ''); 
    $catatan_disposisi = trim($_POST['catatan_disposisi'] ?? '');
    $signature_data    = $_POST['signature_data'] ?? ''; 
    $id_user_login     = $_SESSION['id_user'] ?? null;
    
    $tembusan_array = isset($_POST['tembusan_pimpinan']) ? $_POST['tembusan_pimpinan'] : ['tidak_ada'];
    $tembusan_string = implode(',', $tembusan_array);

    if ($id_surat <= 0 || empty($catatan_disposisi)) {
        echo "<script>alert('Data disposisi tidak lengkap!'); window.history.back();</script>";
        exit;
    }

    // --- JURUS SAKTI: Ambil data backup dari surat_masuk jika input form kosong/tidak_ada ---
    $query_surat = "SELECT no_agenda, perihal, tujuan_disposisi, tujuan_utama FROM surat_masuk WHERE id_surat = ?";
    $stmt_s = $conn->prepare($query_surat);
    $stmt_s->bind_param("i", $id_surat);
    $stmt_s->execute();
    $res_s = $stmt_s->get_result()->fetch_assoc();
    
    $no_agenda = $res_s['no_agenda'] ?? '-';
    $perihal   = $res_s['perihal'] ?? 'Surat Kedinasan Baru';
    
    if (empty($tujuan_disposisi) || $tujuan_disposisi === 'tidak_ada') {
        $tujuan_disposisi = !empty($res_s['tujuan_disposisi']) ? $res_s['tujuan_disposisi'] : $res_s['tujuan_utama'];
    }

    // --- PROSES SAVE TANDA TANGAN DIGITAL ---
    $nama_file_ttd = "no_signature.png";
    if (!empty($signature_data)) {
        $filteredData = explode(',', $signature_data);
        if (isset($filteredData[1])) {
            $unencodedData = base64_decode($filteredData[1]);
            $nama_file_ttd = "ttd_masuk_" . $id_surat . "_" . time() . ".png";
            $target_dir_ttd = "../uploads/tanda_tangan/";
            
            if (!file_exists($target_dir_ttd)) {
                mkdir($target_dir_ttd, 0755, true);
            }
            
            file_put_contents($target_dir_ttd . $nama_file_ttd, $unencodedData);
        }
    }

    // --- INSERT DISPOSISI SURAT ---
    $jenis_surat     = 'masuk';
    $status_disp     = 'Proses';
    $status_baca_disp = 'belum';

    $query_disp = "INSERT INTO disposisi_surat 
                   (id_surat, dari_role, jenis_surat, dari, ke, catatan, tanggal_disposisi, status_disposisi, tembusan_kasi, created_by, ttd_disposisi, status_baca) 
                   VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";
    
    $stmt_disp = $conn->prepare($query_disp);
    $stmt_disp->bind_param(
        "isssssssiss", 
        $id_surat, 
        $dari_role, 
        $jenis_surat, 
        $dari_role,        
        $tujuan_disposisi, 
        $catatan_disposisi, 
        $status_disp, 
        $tembusan_string,  
        $id_user_login,    
        $nama_file_ttd,    
        $status_baca_disp
    );
    
    if ($stmt_disp->execute()) {
        
        $pesan_notif = "Disposisi Masuk Baru! No. Agenda: " . $no_agenda . " - Perihal: " . $perihal;
        $status_baca = "belum";
        $link_target = "dashboard.php"; 

        // 1. Tembakkan Notifikasi Utama ke Ruangan Tujuan
        if ($tujuan_disposisi !== 'tidak_ada' && $tujuan_disposisi !== 'setum') {
            $query_notif = "INSERT INTO notifikasi (id_surat, untuk_role, dari_role, pesan, link, status_baca) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_n = $conn->prepare($query_notif);
            $stmt_n->bind_param("isssss", $id_surat, $tujuan_disposisi, $dari_role, $pesan_notif, $link_target, $status_baca);
            $stmt_n->execute();
            $stmt_n->close();
        }

        // 2. Tembakkan Notifikasi ke Unit Tembusan
        foreach ($tembusan_array as $tembusan_role) {
            if ($tembusan_role !== 'tidak_ada') {
                $pesan_tembusan = "[TEMBUSAN] " . $pesan_notif;
                $query_notif_t = "INSERT INTO notifikasi (id_surat, untuk_role, dari_role, pesan, link, status_baca) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_nt = $conn->prepare($query_notif_t);
                $stmt_nt->bind_param("isssss", $id_surat, $tembusan_role, $dari_role, $pesan_tembusan, $link_target, $status_baca);
                $stmt_nt->execute();
                $stmt_nt->close();
            }
        }

        $stmt_disp->close();
        $conn->close();

        echo "<script>
                alert('Disposisi Surat Masuk Berhasil Diproses & Didistribusikan!'); 
                window.location='../transaksi/kelola_surat_masuk.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan data disposisi: " . addslashes($stmt_disp->error) . "'); window.history.back();</script>";
        $stmt_disp->close();
        $conn->close();
        exit;
    }
} else {
    header("Location: ../transaksi/kelola_surat_masuk.php");
    exit;
}
?>
