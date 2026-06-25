<?php
require_once('../config/koneksi.php');
require_once('../config/session_check.php');

check_access(['ruangan']);

// --- SINKRONISASI: Ambil data sesi sesuai kelola_surat_masuk.php ---
$tipe_akses_raw = $_SESSION['tipe_akses'] ?? '';
$tipe_akses = is_array($tipe_akses_raw) ? ($tipe_akses_raw[0] ?? '') : $tipe_akses_raw;

// ID User bernilai angka (152), aman untuk query stat surat keluar
$id_user = $_SESSION['id_user'] ?? ''; 

// Mengambil nama asli langsung dari session yang terbukti valid ('Dandenkeslap')
$nama_user = $_SESSION['nama_user'] ?? ($_SESSION['nama'] ?? 'Personel Ruangan');

// Sinkronisasi data role/nama ruangan untuk kebutuhan parameter query sql di bawah
$role_string = $nama_user; 
$role_ruangan = strtolower(trim($role_string));

// ======================================================
// DISUNTIKKAN DI SINI: PETA ASAL SURAT SUPER LENGKAP
// ======================================================
$asalMap = [
    'kakesdam_jaya'      => 'Kakesdam Jaya',
    'wakakesdam_jaya'    => 'Wakakesdam Jaya',
    'dandenkeslap'       => 'Dandenkeslap',
    'kasi_tuud'          => 'Kasi Tuud',
    'kasi_was'           => 'Kasi Was',
    'kasi_dukkes'        => 'Kasi Dukkes',
    'kasi_kesprev'       => 'Kasi Kesprev',
    'kasi_renproggar'    => 'Kasi Renproggar',
    'kasi_minlogkes'     => 'Kasi Minlogkes',
    'kasi_matkes'        => 'Kasi Matkes',
    'kasi_yankes'        => 'Kasi Yankes',
    'ka_primkop'         => 'Ka Primkop',
    'kagud_kesrah'       => 'Gudang Kesrah',
    'paku_kesdam'        => 'Paku Kesdam',
    'ka_smk_kesdam'      => 'Ka SMK Kesdam Jaya',
    'kaur_infokes'       => 'Kaur Infokes',
    'kaur_log'           => 'Kaur Log',
    'juyar'              => 'Juyar',
    'persit_kck_ranting5'=> 'Persit',
    'kaur_pam'           => 'Kaurpam',
    'kaur_dal'           => 'Kaurdal',
    'korpri_kesdam'      => 'Korpri',
    'kaur_pers'          => 'Kaur Pers',
    'pers_tuud'          => 'Pers Tuud',
    'setum'              => 'SETUM Kesdam Jaya'
];

$myFeatures = ['kirim', 'disposisi'];

// =========================
// SURAT MASUK - Filter SESUAI kelola_surat_masuk.php
// =========================
$total_masuk_hari_ini = 0;
$total_masuk_sebelumnya = 0;

// Query filter yang SAMA persis
$stmt = $conn->prepare("
    SELECT 
        sm.id_surat,
        DATE(sm.tanggal_input) AS tgl_input
    FROM surat_masuk sm
    LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
    WHERE (
        LOWER(sm.tujuan_disposisi) = ? 
        OR LOWER(sm.tujuan_utama) = ? 
        OR LOWER(ds.ke) = ? 
        OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
        OR ? = ''
    )
    GROUP BY sm.id_surat
");

$stmt->bind_param("sssss", $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    if ($row['tgl_input'] === date('Y-m-d')) {
        $total_masuk_hari_ini++;
    } else {
        $total_masuk_sebelumnya++;
    }
}
$stmt->close();

$total_masuk = $total_masuk_hari_ini + $total_masuk_sebelumnya;


// =========================
// FUNCTION STAT
// =========================
function getStats($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}


// =========================
// SURAT KELUAR
// =========================
$total_keluar = getStats($conn,
    "SELECT COUNT(*) as total FROM surat_keluar WHERE created_by = ? OR LOWER(tujuan_disposisi) = LOWER(?)",
    [$id_user, $role_string], "is"
);

$total_keluar_hari_ini = getStats($conn,
    "SELECT COUNT(*) as total FROM surat_keluar WHERE (created_by = ? OR LOWER(tujuan_disposisi) = LOWER(?)) AND DATE(tanggal_input) = CURDATE()",
    [$id_user, $role_string], "is"
);

$total_keluar_sebelumnya = $total_keluar - $total_keluar_hari_ini;


// =========================
// DISPOSISI
// =========================
$total_belum_disposisi = getStats($conn,
    "SELECT COUNT(DISTINCT sm.id_surat) as total 
     FROM surat_masuk sm
     LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
     WHERE (
        LOWER(sm.tujuan_disposisi) = ? 
        OR LOWER(sm.tujuan_utama) = ? 
        OR LOWER(ds.ke) = ? 
        OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
        OR ? = ''
     )
     AND IFNULL(ds.status_disposisi, sm.status_proses) IN ('baru', 'pending')",
    [$role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan], "sssss"
);

$total_selesai_disposisi = getStats($conn,
    "SELECT COUNT(DISTINCT sm.id_surat) as total 
     FROM surat_masuk sm
     LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
     WHERE (
        LOWER(sm.tujuan_disposisi) = ? 
        OR LOWER(sm.tujuan_utama) = ? 
        OR LOWER(ds.ke) = ? 
        OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
        OR ? = ''
     )
     AND IFNULL(ds.status_disposisi, sm.status_proses) = 'selesai'",
    [$role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan], "sssss"
);


// =========================
// 5 DATA TERBARU - Filter SESUAI & Tampilan SAMA
// =========================
$stmtRecent = $conn->prepare("
    SELECT 
        sm.id_surat,
        sm.no_agenda,
        sm.asal_surat,
        sm.no_surat,
        sm.perihal,
        sm.sifat_surat,
        sm.tanggal_surat,
        sm.tanggal_input,
        IFNULL(MAX(ds.status_disposisi), sm.status_proses) AS status_sub,
        MAX(ds.catatan) AS catatan_disp
    FROM surat_masuk sm
    LEFT JOIN disposisi_surat ds ON sm.id_surat = ds.id_surat
    WHERE (
        LOWER(sm.tujuan_disposisi) = ? 
        OR LOWER(sm.tujuan_utama) = ? 
        OR LOWER(ds.ke) = ? 
        OR FIND_IN_SET(?, ds.tembusan_kasi) > 0
        OR ? = ''
    )
    GROUP BY sm.id_surat
    ORDER BY sm.id_surat DESC
    LIMIT 5
");

$stmtRecent->bind_param("sssss", $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan, $role_ruangan);
$stmtRecent->execute();
$recentInbox = $stmtRecent->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtRecent->close();


// --- Fungsi Status yang SAMA persis ---
function renderBadgeStatusMasuk($status_raw) {
    $status = strtolower(trim($status_raw ?? 'baru'));
    switch ($status) {
        case 'pending':
        case 'baru':
            return "<span class='badge bg-warning text-dark'><i class='fas fa-clock me-1'></i> Baru</span>";
        case 'proses':
            return "<span class='badge bg-primary text-white'><i class='fas fa-spinner me-1'></i> Proses</span>";
        case 'selesai':
            return "<span class='badge bg-success text-white'><i class='fas fa-check-double me-1'></i> Selesai</span>";
        default:
            return "<span class='badge bg-secondary'>".ucfirst($status)."</span>";
    }
}

require_once "../layout/header.php";
?>

<div class="d-flex" id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="page-content-wrapper" class="w-100 bg-light" style="overflow-x: hidden;">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm">
            <button class="btn btn-outline-dark btn-sm rounded" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <span class="ms-3 fw-semibold text-secondary">Sistem Informasi E-Office Kesdam Jaya</span>
        </nav>

        <div class="container-fluid py-4 px-4">
            <?php if($total_masuk_hari_ini > 0): ?>
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-bell me-2"></i> <strong>Notifikasi!</strong> Ada <?= $total_masuk_hari_ini ?> surat masuk baru hari ini.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-0">Dashboard Ruangan</h3>
                    <p class="text-muted mb-0">Selamat bekerja, <strong><?= htmlspecialchars($nama_user ?? '') ?></strong> (<?= strtoupper(htmlspecialchars($role_ruangan)) ?>)</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="location.reload(); this.querySelector('i').classList.add('fa-spin');" class="btn btn-white border shadow-sm btn-sm fw-bold text-secondary px-3">
                        <i class="fas fa-sync-alt me-1 text-primary"></i> Refresh Data
                    </button>
                    <div class="text-muted small fw-semibold bg-white border px-3 py-2 rounded shadow-sm">
                        <i class="fas fa-calendar-alt me-1 text-danger"></i> <?= date('d M Y') ?>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card p-3 border-start border-primary border-4 shadow-sm"><small class="text-uppercase text-muted">Total Surat Masuk</small><h4 class="fw-bold"><?= $total_masuk ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-info border-4 shadow-sm"><small class="text-uppercase text-muted">Masuk Hari Ini</small><h4 class="fw-bold"><?= $total_masuk_hari_ini ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-secondary border-4 shadow-sm"><small class="text-uppercase text-muted">Masuk Lampau</small><h4 class="fw-bold"><?= $total_masuk_sebelumnya ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-warning border-4 shadow-sm"><small class="text-uppercase text-muted">Belum Disposisi</small><h4 class="fw-bold"><?= $total_belum_disposisi ?></h4></div></div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card p-3 border-start border-success border-4 shadow-sm"><small class="text-uppercase text-muted">Total Surat Keluar</small><h4 class="fw-bold"><?= $total_keluar ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-success border-4 shadow-sm"><small class="text-uppercase text-muted">Keluar Hari Ini</small><h4 class="fw-bold"><?= $total_keluar_hari_ini ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-success border-4 shadow-sm"><small class="text-uppercase text-muted">Keluar Lampau</small><h4 class="fw-bold"><?= $total_keluar_sebelumnya ?></h4></div></div>
                <div class="col-md-3"><div class="card p-3 border-start border-success border-4 shadow-sm"><small class="text-uppercase text-muted">Selesai Disposisi</small><h4 class="fw-bold"><?= $total_selesai_disposisi ?></h4></div></div>
            </div>
                
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bell text-danger me-2"></i>5 Atensi Disposisi Terkini</h5>
                        <span class="badge bg-light text-dark border">Ruangan</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">No Agenda</th>
                                        <th>Asal / No Surat</th>
                                        <th>Perihal & Catatan</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentInbox)): ?>
                                        <?php foreach ($recentInbox as $row):
                                            $asalSatuan = $asalMap[$row['asal_surat'] ?? ''] ?? ($row['asal_surat'] ?? '-');
                                        ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></td>
                                            <td>
                                                <span class="d-block fw-semibold"><?= htmlspecialchars($asalSatuan) ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($row['no_surat'] ?? '-') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger mb-1"><?= htmlspecialchars($row['sifat_surat'] ?? '-') ?></span>
                                                <p class="mb-1 text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['perihal'] ?? '-') ?></p>
                                                <?php if(!empty($row['catatan_disp'])): ?>
                                                    <small class="text-muted d-block"><em><?= htmlspecialchars($row['catatan_disp']) ?></em></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= renderBadgeStatusMasuk($row['status_sub']) ?></td>
                                            <td class="text-center">
                                                <a href="../surat_masuk/detail_surat_masuk.php?id=<?= $row['id_surat'] ?>" class="btn btn-sm btn-dark"><i class="fas fa-eye"></i>detail</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada disposisi surat masuk.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="notifAudio" preload="auto">
    <source src="../assets/audio/ada_suratmasuk.mp3" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function cekNotifikasiRuangan() {
    fetch('cek_notifikasi_surat_masuk_ruangan.php')
        .then(response => {
            if (!response.ok) {
                throw new Error("Gagal memuat file cek notifikasi (Status: " + response.status + ")");
            }
            return response.json();
        })
        .then(data => {
            console.log("Hasil Polling Notifikasi:", data); 

            if (data.status === 'baru') {
                // Play Audio otomatis
                let audioTag = document.getElementById('notifAudio');
                if (audioTag) {
                    audioTag.currentTime = 0; 
                    audioTag.play().catch(e => {
                        console.log("Audio ditahan browser. Butuh izin akses suara atau klik user.");
                    });
                }

                // Tampilkan SweetAlert2 modal pop-up
                Swal.fire({
                    title: 'Disposisi Surat Baru!',
                    text: data.pesan,
                    icon: 'info',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Buka Halaman Surat',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = data.link;
                    }
                });
            }
        })
        .catch(err => console.error('Polling error:', err));
}

// PERBAIKAN 2: Menghapus baris ganda setInterval() agar request tidak tabrakan berkali-kali
setInterval(cekNotifikasiRuangan, 5000);

function cekNotifikasiSuratKeluar() {
    fetch('cek_notifikasi_surat_keluar_ruangan.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'baru') {
                // Bunyikan audio yang sama atau audio berbeda (jika ada file mp3 lain)
                let audioTag = document.getElementById('notifAudio');
                if (audioTag) {
                    audioTag.currentTime = 0; 
                    audioTag.play().catch(e => console.log("Audio ditahan browser"));
                }

                // Tampilkan Swal khusus surat keluar
                Swal.fire({
                    title: 'Notifikasi Surat Keluar!',
                    text: data.pesan,
                    icon: 'success', // Warna hijau untuk membedakan dengan surat masuk
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Lihat Dashboard',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = data.link;
                    }
                });
            }
        });
}

// Daftarkan di polling interval (Jalankan bergantian setiap 5 detik)
setInterval(cekNotifikasiSuratKeluar, 5000);

document.addEventListener("DOMContentLoaded", function() {
    // TRIK BYPASS AUTOPLAY BROWSER
    const pemicuAudioOtomatis = function() {
        let audioTag = document.getElementById('notifAudio');
        if (audioTag) {
            audioTag.play().then(() => {
                audioTag.pause();
                audioTag.currentTime = 0;
                console.log("Gembok Autoplay Browser Berhasil Dibuka!");
            }).catch(error => {
                console.log("Menunggu klik user untuk mengaktifkan suara...");
            });
        }
        document.body.removeEventListener('click', pemicuAudioOtomatis);
    };

    document.body.addEventListener('click', pemicuAudioOtomatis);
    
    // Jalankan pengecekan pertama saat halaman dimuat
    cekNotifikasiRuangan();
});
</script>

<?php include '../layout/footer.php'; ?>
