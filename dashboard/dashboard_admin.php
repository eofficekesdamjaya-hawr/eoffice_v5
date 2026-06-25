<?php
// Cukup panggil master akses, otomatis session start dan proteksi email berjalan di background
require_once '../config/hak_akses.php';
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Ambil data nama pengguna untuk tampilan navbar & ucapan selamat tugas
$stmt = $conn->prepare("SELECT nama FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $user_email); 
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$nama_user = $user['nama'] ?? 'Pengguna';

// Penamaan Otoritas Berdasarkan Email untuk Variabel Navbar
$role_mapping = [
    'superadmin@gmail.com' => 'SUPERADMIN',
    'setum@gmail.com' => 'SETUM',
    'admin@gmail.com' => 'ADMIN IT',
    'kasituud2026@gmail.com' => 'KASI TUUD',
    'kakesdamjaya2026@gmail.com' => 'KAKESDAM JAYA',
    'wakakesdamjaya2026@gmail.com' => 'WAKAKESDAM JAYA',
    'spripimpinan2026@gmail.com' => 'SPRI PIMPINAN'
];
$user_role = $role_mapping[$user_email] ?? 'STAF EKSEKUTIF';

// 2. AMBIL DATA PENGGUNA & SETTING NAMA OTORITAS UNTUK TAMPILAN NAVBAR
// KODE BARU (PENCARIAN BERDASARKAN EMAIL):
$stmt = $conn->prepare("SELECT nama FROM users WHERE email = ? LIMIT 1"); // <-- Ganti 'email' sesuai nama kolom di DB Anda jika berbeda
$stmt->bind_param("s", $_SESSION['id_user']); // <-- Menggunakan "s" karena data berupa string email
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$nama_user = $user['nama'] ?? 'Pengguna';

// Pemetaan Nama Otoritas Berdasarkan Email untuk Variabel Navbar
$role_mapping = [
    'superadmin@gmail.com' => 'SUPERADMIN',
    'setum@gmail.com' => 'SETUM',
    'admin@gmail.com' => 'ADMIN IT',
    'kasituud2026@gmail.com' => 'KASI TUUD',
    'kakesdamjaya2026@gmail.com' => 'KAKESDAM JAYA',
    'wakakesdamjaya2026@gmail.com' => 'WAKAKESDAM JAYA',
    'spripimpinan2026@gmail.com' => 'SPRI PIMPINAN'
];
$user_role = $role_mapping[$user_email] ?? 'STAF EKSEKUTIF';

/* ====================================================================
   3. EKSEKUSI AGREGASI DATA METRIK (8 Indikator Utama)
   ==================================================================== */
// [Metrik 1] Total Surat Masuk Seluruhnya
$q1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_masuk");
$total_masuk = mysqli_fetch_assoc($q1)['total'] ?? 0;

// [Metrik 2] Total Surat Masuk Hari Ini
$q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_masuk WHERE DATE(created_at) = CURDATE()");
$total_masuk_hari_ini = mysqli_fetch_assoc($q2)['total'] ?? 0;

// [Metrik 3] Total Surat Masuk Sebelumnya / Kemarin
$q3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_masuk WHERE DATE(created_at) < CURDATE()");
$total_masuk_sebelumnya = mysqli_fetch_assoc($q3)['total'] ?? 0;

// [Metrik 4] Total Surat Keluar Seluruhnya
$q4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar");
$total_keluar = mysqli_fetch_assoc($q4)['total'] ?? 0;

// [Metrik 5] Total Surat Keluar Hari Ini
$q5 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar WHERE DATE(created_at) = CURDATE()");
$total_keluar_hari_ini = mysqli_fetch_assoc($q5)['total'] ?? 0;

// [Metrik 6] Total Surat Keluar Sebelumnya / Kemarin
$q6 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar WHERE DATE(created_at) < CURDATE()");
$total_keluar_sebelumnya = mysqli_fetch_assoc($q6)['total'] ?? 0;

// [Metrik 7] Total Menunggu Verifikasi & Disposisi
$q7 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar WHERE status_proses = 'Menunggu Verifikasi' OR status_proses = 'Pending'");
$total_belum_disposisi = mysqli_fetch_assoc($q7)['total'] ?? 0;

// [Metrik 8] Total Selesai Disposisi (Sudah final, TTE, Stempel/Arsip)
$q8 = mysqli_query($conn, "SELECT COUNT(*) as total FROM surat_keluar WHERE status_proses = 'Selesai' OR status_proses = 'Disetujui'");
$total_selesai_disposisi = mysqli_fetch_assoc($q8)['total'] ?? 0;

$myFeatures = ['dashboard']; 
require_once '../layout/header.php';
?>

<style>
#wrapper { overflow-x: hidden; }
#wrapper.toggled #sidebar-wrapper { margin-left: -260px; transition: margin 0.25s ease-out; }
#sidebar-wrapper { transition: margin 0.25s ease-out; }
.metric-card { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border: none; }
.metric-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.12) !important; }
.bg-gradient-masuk { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; }
.bg-gradient-keluar { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
.bg-gradient-alert { background: linear-gradient(135deg, #e52d27, #b31217); color: white; }
.bg-gradient-success { background: linear-gradient(135deg, #1d976c, #93f9b9); color: white; }
</style>

<div class="d-flex" id="wrapper">
    
    <?php include 'sidebar_admin.php'; ?>

    <div id="page-content-wrapper" class="w-100 bg-light">
        
 <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm position-relative">
    <button class="btn btn-outline-dark btn-sm rounded position-absolute" id="menu-toggle" style="left: 1rem; z-index: 10;">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="mx-auto text-center d-flex flex-column">
        <span class="fw-bold text-dark">Komando Pengendali Transaksi Surat</span>
        <small class="text-muted font-monospace text-xs text-uppercase">
            Akses Otoritas: <?= htmlspecialchars($user_role) ?> (Kesdam Jaya)
        </small>
    </div>
</nav>

        <div class="container-fluid py-4 px-4">


<div class="alert alert-white border-start border-4 border-primary shadow-sm rounded-3 p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-column flex-md-row">
        <div class="text-center text-md-start mb-2 mb-md-0">
            <h5 class="fw-bold mb-0 text-dark">Selamat Tugas, <?= htmlspecialchars($nama_user) ?></h5>
            <p class="mb-0 text-muted small">Sistem mendeteksi aktivitas jaringan berjalan optimal. Seluruh parameter komunikasi dua arah aktif.</p>
        </div>
        
        <div>
            <button onclick="rotateAndRefresh(this)" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2 shadow-sm px-3 py-2 fw-semibold">
                <i class="bi bi-arrow-clockwise fs-6 transition-transform" id="refresh-icon"></i> Refresh Data
            </button>
        </div>
    </div>
    
    <hr class="my-2 opacity-10">
    
    <div class="text-center font-monospace text-xs text-secondary fw-bold">
        <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y') ?> | <i class="bi bi-clock me-1"></i> <span id="clock">00:00:00</span> WIB
    </div>
</div>

            <div id="liveAlertPlaceholder" class="mb-4"></div>

            <h6 class="fw-bold text-uppercase text-secondary tracking-wider mb-3 text-center">
    <i class="fas fa-chart-pie me-2"></i>Summary Lembar Kerja Masuk & Keluar
</h6>
            
            <div class="row g-3 mb-4">
                
                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-gradient-masuk h-100 shadow-sm rounded-3" onclick="window.location.href='../transaksi/kelola_surat_masuk.php';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-white-50">Total Surat Masuk</span>
                                    <h2 class="display-5 fw-bold mb-0 mt-1"><?= $total_masuk ?></h2>
                                </div>
                                <i class="fas fa-envelope-open-text fa-3x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-white h-100 shadow-sm rounded-3 border-start border-4 border-info" onclick="window.location.href='../transaksi/kelola_surat_masuk.php?filter=today';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted">Masuk Hari Ini</span>
                                    <h2 class="fw-bold text-info mb-0 mt-1"><?= $total_masuk_hari_ini ?></h2>
                                </div>
                                <span class="badge bg-info-subtle text-info p-3 rounded-circle"><i class="fas fa-bell fs-4"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-white h-100 shadow-sm rounded-3 border-start border-4 border-secondary" onclick="window.location.href='../transaksi/kelola_surat_masuk.php?filter=old';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted">Masuk Sebelum Hari Ini</span>
                                    <h2 class="fw-bold text-secondary mb-0 mt-1"><?= $total_masuk_sebelumnya ?></h2>
                                </div>
                                <span class="badge bg-light text-secondary p-3 rounded-circle"><i class="fas fa-history fs-4"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-gradient-keluar h-100 shadow-sm rounded-3" onclick="window.location.href='../transaksi/kelola_surat_keluar.php';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-white-50">Total Surat Keluar</span>
                                    <h2 class="display-5 fw-bold mb-0 mt-1"><?= $total_keluar ?></h2>
                                </div>
                                <i class="fas fa-paper-plane fa-3x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-white h-100 shadow-sm rounded-3 border-start border-4 border-success" onclick="window.location.href='../transaksi/kelola_surat_keluar.php?filter=today';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted">Keluar Hari Ini</span>
                                    <h2 class="fw-bold text-success mb-0 mt-1"><?= $total_keluar_hari_ini ?></h2>
                                </div>
                                <span class="badge bg-success-subtle text-success p-3 rounded-circle"><i class="fas fa-calendar-check fs-4"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="card metric-card bg-white h-100 shadow-sm rounded-3 border-start border-4 border-muted" onclick="window.location.href='../transaksi/kelola_surat_keluar.php?filter=old';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted">Keluar Sebelum Hari Ini</span>
                                    <h2 class="fw-bold text-dark mb-0 mt-1"><?= $total_keluar_sebelumnya ?></h2>
                                </div>
                                <span class="badge bg-light text-dark p-3 rounded-circle"><i class="fas fa-archive fs-4"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6">
                    <div class="card metric-card bg-gradient-alert h-100 shadow-sm rounded-3" onclick="window.location.href='../transaksi/kelola_surat_keluar.php?status=pending';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-white-50">Menunggu Verifikasi & Disposisi</span>
                                    <h2 class="fw-bold text-white mb-0 mt-1"><?= $total_belum_disposisi ?> Berkas</h2>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-3x text-white-50 animate-pulse"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6">
                    <div class="card metric-card bg-gradient-success h-100 shadow-sm rounded-3" onclick="window.location.href='../transaksi/kelola_surat_keluar.php?status=done';">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small fw-bold text-white-50">Selesai Disposisi / Finalisasi</span>
                                    <h2 class="fw-bold text-white mb-0 mt-1"><?= $total_selesai_disposisi ?> Berkas</h2>
                                </div>
                                <i class="fas fa-check-double fa-3x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div> </div> </div> </div> 

<audio id="notifAudio" preload="auto">
    <source src="assets/audio/ada_suratmasuk.mp3" type="audio/mpeg">
</audio>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function rotateAndRefresh(btn) {
    const icon = btn.querySelector('#refresh-icon');
    // Tambahkan efek berputar lewat CSS injeksi sesaat
    icon.style.transform = 'rotate(360deg)';
    icon.style.transition = 'transform 0.5s ease';
    
    // Nonaktifkan tombol sementara biar tidak di-klik berulang-ulang
    btn.disabled = true;
    
    // Reload halaman setelah efek putar selesai (500ms)
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

document.addEventListener("DOMContentLoaded", function () {

    let lastCountMasuk = -1;
    let lastCountKeluar = -1;
    let originalTitle = document.title;
    let blinkInterval = null;
    const audio = document.getElementById("notifAudio");

    /* =========================================
       1. LOGIKA UTILS (Blink & Sound)
       ========================================= */
    function startTitleBlink(text) {
        if (blinkInterval) clearInterval(blinkInterval);
        blinkInterval = setInterval(() => {
            document.title = document.title === originalTitle ? "🔴 " + text : originalTitle;
        }, 1000);
    }

    function stopTitleBlink() {
        clearInterval(blinkInterval);
        blinkInterval = null;
        document.title = originalTitle;
    }

    function playNotifSound() {
        if (!audio) return;
        audio.pause();
        audio.currentTime = 0;
        audio.play().catch(err => console.log("Audio diblokir browser sebelum klik:", err));
    }

    /* =========================================
       2. CEK REALTIME SURAT MASUK
       ========================================= */
    function checkSuratMasuk() {
        fetch('cek_notifikasi_surat_masuk.php?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
            let count = parseInt(data.total || 0);
            
            // Update badge di menu Surat Masuk (jika ada elemennya)
            const badgeMasuk = document.getElementById('badgePendingMasuk');
            if (badgeMasuk) {
                badgeMasuk.innerText = count;
                if (count <= 0) badgeMasuk.classList.add('d-none');
                else badgeMasuk.classList.remove('d-none');
            }

            if (count > 0) {
                startTitleBlink("MENUNGGU DISPOSISI");
                if (count !== lastCountMasuk && count > lastCountMasuk) {
                    playNotifSound();
                    Swal.fire({
                        title: 'SURAT MASUK BARU',
                        html: `<div style="font-size:15px">Ada <b>${count}</b> Surat Masuk membutuhkan tindak lanjut / disposisi.</div>`,
                        icon: 'info',
                        confirmButtonColor: '#0284c7',
                        confirmButtonText: 'Periksa Surat Masuk'
                    }).then((res) => { if (res.isConfirmed) window.location.href = "../transaksi/kelola_surat_masuk.php"; });
                }
            }
            lastCountMasuk = count;
        }).catch(err => console.log("Eror checkSuratMasuk:", err));
    }

    /* =========================================
       3. CEK REALTIME SURAT KELUAR
       ========================================= */
    function checkSuratKeluar() {
        fetch('../notifikasi/cek_notifikasi_surat_keluar.php?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
            if (data.error) return;
            let count = parseInt(data.total || 0);

            // Update badge di menu Surat Keluar (jika ada elemennya)
            const badgeKeluar = document.getElementById('badgePendingKeluar');
            if (badgeKeluar) {
                badgeKeluar.innerText = count;
                if (count <= 0) badgeKeluar.classList.add('d-none');
                else badgeKeluar.classList.remove('d-none');
            }

            if (count > 0) {
                startTitleBlink("MENUNGGU PERSETUJUAN");
                if (count !== lastCountKeluar && count > lastCountKeluar) {
                    playNotifSound();
                    Swal.fire({
                        title: 'SURAT KELUAR BARU',
                        html: `<div style="font-size:15px">Ada <b>${count}</b> draf surat keluar membutuhkan persetujuan / TTD Anda.</div>`,
                        icon: 'warning',
                        confirmButtonColor: '#f59e0b',
                        confirmButtonText: 'Periksa Surat Keluar'
                    }).then((res) => { if (res.isConfirmed) window.location.href = "../transaksi/kelola_surat_keluar.php"; });
                }
            }
            lastCountKeluar = count;
        }).catch(err => console.log("Eror checkSuratKeluar:", err));
    }

    /* =========================================
       4. ENGINE AUTOMATION EXECUTION
       ========================================= */
    // Jalankan pengecekan pertama kali halaman dibuka
    checkSuratMasuk();
    setTimeout(checkSuratKeluar, 2000); // Dijeda 2 detik agar request ke server bergantian

    // Jalankan interval looping setiap 6 detik secara berkala
    setInterval(() => {
        checkSuratMasuk();
        setTimeout(checkSuratKeluar, 3000); // Bergantian agar server tidak syok
    }, 6000);

    /* =========================================
       5. UNLOCK AUDIO INTERACTION RULES
       ========================================= */
    document.addEventListener('click', function () {
        if (!audio) return;
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            console.log("NOTIF ENGINE: AUDIO UNLOCKED");
        }).catch(() => {});
    }, { once: true });

});


</script>
<?php include '../layout/footer.php'; ?>
