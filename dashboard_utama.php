<?php
session_start();


header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// CSP TELAH DIPERBARUI: Mengizinkan unpkg.com (Leaflet) dan *.tile.openstreetmap.org (Ubin Gambar Peta)
header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com https://unpkg.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data: https://*.tile.openstreetmap.org https://unpkg.com; media-src 'self';");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi E-Office Kesdam Jaya/Jayakarta untuk pengelolaan surat masuk dan keluar secara digital.">
    <meta name="keywords" content="E-Office Kesdam Jaya, Surat Digital Kodam Jaya, Administrasi Kesdam">
    <meta name="author" content="IT Kesdam Jaya/Jayakarta">
    <title>E-Office Kesdam Jaya/Jayakarta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        /* ==========================================================================
           1. CORE SECTIONS & BASE STYLES
           ========================================================================== */
        body { background-color: #f5f7fa; }

        .header-section {
            background: #ffffff;
            padding: 15px 0;
            border-bottom: 3px solid #1a5928;
        }

        .header-section h2 { margin-bottom: 5px; }
        .header-section p { margin-bottom: 3px; font-size: 0.9rem; }

        .logo-header {
            width: 85px;
            transition: all 0.4s ease;
            cursor: pointer;
            animation: logoPulse 3s ease-in-out infinite;
        }

        .logo-header:hover {
            transform: scale(1.08);
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.25));
            animation-play-state: paused; 
        }

        @keyframes logoPulse {
            0% {
                opacity: 1;
                filter: drop-shadow(0 2px 5px rgba(0,0,0,0.1));
            }
            50% {
                opacity: 0.75;
                filter: drop-shadow(0 8px 15px rgba(26, 89, 40, 0.4));
            }
            100% {
                opacity: 1;
                filter: drop-shadow(0 2px 5px rgba(0,0,0,0.1));
            }
        }

        .hero-section { text-align: center; padding: 50px 0; }

        /* ==========================================================================
           2. CARDS MANAGEMENT (PEJABAT & JAJARAN)
           ========================================================================== */
        .pejabat-container {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .pejabat-card {
            background: linear-gradient(145deg, #ffffff, #f1f1f1);
            padding: 20px;
            width: 220px;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        }

        .pejabat-card img {
            width: 180px;
            height: 230px;
            object-fit: cover;
            border-radius: 15px;
            border: 4px solid #1a5928;
            padding: 5px;
            background: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2), 0 0 0 6px #f8f9fa;
            transition: all 0.4s ease;
        }

        .pejabat-card img:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 12px 25px rgba(0,0,0,0.3), 0 0 0 6px #d4edda;
        }

        .nama { font-weight: bold; font-size: 1rem; }

        .jajaran-card {
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .jajaran-card img {
            height: 120px;
            width: 100%;
            object-fit: cover;
            transition: 0.3s;
        }

        .jajaran-card img:hover {
            transform: scale(1.07);
            border: 2px solid #1a5928;
        }

        /* ==========================================================================
           3. PREMIUM CAROUSEL OPTIMIZATION
           ========================================================================== */
        #heroCarousel {
            overflow: hidden;
            border: 4px solid #ffffff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
        }

        .carousel-item img {
            height: 500px;
            object-fit: cover;
            transform: scale(1);
            transition: transform 3s ease-in-out;
        }

        .carousel-item.active img {
            transform: scale(1.05);
        }

        .carousel-inner::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 120px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0));
            pointer-events: none;
            z-index: 1;
        }

        .carousel-indicators {
            z-index: 2;
            margin-bottom: 1.5rem;
        }

        .carousel-indicators [data-bs-target] {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.6);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .carousel-indicators .active {
            background-color: #1a5928;
            width: 30px;
            border-radius: 10px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 8%;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 2;
        }

        #heroCarousel:hover .carousel-control-prev,
        #heroCarousel:hover .carousel-control-next {
            opacity: 1;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(26, 89, 40, 0.85);
            padding: 22px;
            border-radius: 50%;
            background-size: 55%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        /* ==========================================================================
           4. BUTTONS & FOOTER
           ========================================================================== */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 30px 0;
        }

        .btn-primary {
            background-color: #1a5928;
            border-color: #1a5928;
        }

        .btn-primary:hover { background-color: #14461f; }

        .btn-play-modern {
            background: linear-gradient(135deg, #00c853, #00e676);
            color: white;
            border: none;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .btn-play-modern:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.25);
            background: linear-gradient(135deg, #00e676, #00c853);
        }

        .btn-play-modern i { margin-right: 6px; font-size: 18px; }

        @media (min-width:1600px){ .container{ max-width:1450px; } .hero-section{ padding:70px 0; } .carousel-item img{ height:650px; } .pejabat-card{ width:260px; } .pejabat-card img{ width:210px; height:280px; } .room-card img, .jajaran-card img{ height:150px; object-fit:cover; } } /* ================================ LAPTOP NORMAL ================================ */ @media (max-width:1200px){ .carousel-item img{ height:420px; } .pejabat-container{ gap:30px; } } /* ================================ TABLET ================================ */ @media (max-width:992px){ .header-section{ padding:12px 10px; } .logo-header{ width:70px; } .header-section h2{ font-size:1.5rem; } .hero-section{ padding:35px 15px; } .hero-section h1{ font-size:2rem; } .hero-section h4{ font-size:1.2rem; } .carousel-item img{ height:350px; } .pejabat-container{ gap:25px; } .pejabat-card{ width:200px; } .pejabat-card img{ width:160px; height:210px; } } /* ================================ ANDROID & iPHONE ================================ */ @media (max-width:768px){ body{ overflow-x:hidden; } .header-section{ padding:10px; } .logo-header{ width:60px; } .header-section h2{ font-size:1.2rem; } .header-section p{ font-size:0.75rem; padding:0 10px; } .hero-section{ padding:25px 10px; } .hero-section h1{ font-size:1.6rem; } .hero-section h4{ font-size:1rem; } .pejabat-container{ flex-direction:column; align-items:center; gap:20px; } .pejabat-card{ width:100%; max-width:260px; padding:15px; } .pejabat-card img{ width:150px; height:190px; } .carousel-item img{ height:230px; } .carousel-control-prev-icon, .carousel-control-next-icon{ padding:16px; } .btn-play-modern{ width:100%; max-width:320px; font-size:14px; } .room-card img, .jajaran-card img{ height:95px; object-fit:cover; } .room-card p, .jajaran-card p{ font-size:11px; } .footer{ padding:20px 10px; } } /* ================================ MOBILE KECIL ================================ */ @media (max-width:576px){ .carousel-item img{ height:180px; } .header-section h2{ font-size:1rem; } .hero-section h1{ font-size:1.3rem; } .hero-section h4{ font-size:0.9rem; } .pejabat-card{ border-radius:15px; } .pejabat-card img{ width:130px; height:170px; } .btn{ font-size:12px; } }
    @media (max-width: 768px) {
        #map { height: 300px !important; }
    }
    </style>
</head>
<body>

<div class="header-section text-center">
    <img src="assets/img/logo1.png" 
         alt="Logo" 
         id="logo-admin-trigger" 
         class="logo-header mb-2">

    <h2 class="fw-bold">Kesdam Jaya/Jayakarta</h2>
    <p class="mb-1">Jl. Mayor Jendral Sutoyo No.5 Cawang, Jakarta Timur</p>
    <p>
        Email: <a href="mailto:kesdamjayakarta2026@gmail.com">kesdamjayakarta2026@gmail.com</a> |
        HP: 0852-1745-4660
    </p>
</div>

<section class="hero-section container">
    <h1 class="fw-bold">Selamat Datang</h1>
    <h4>Di Aplikasi E-Office Kesdam Jaya/Jayakarta</h4>

    <div class="pejabat-container">

        <div class="pejabat-card text-center">
    <a href="auth/login_admin.php" style="text-decoration: none; color: inherit;">
        <img src="assets/img/foto_kakesdamjaya.png"
         alt="Foto Kakesdam Jaya">
        <p class="nama">Kolonel Ckm <br>dr. Sumanta Sembiring, Sp.B</p>
        <p class="jabatan">Kakesdam Jaya/Jayakarta</p>
    </a>
</div>

<div class="pejabat-card text-center">
    <a href="auth/login_admin.php" style="text-decoration: none; color: inherit;">
        <img src="assets/img/foto_wakakesdamjaya.png"
         alt="Foto Waka Kesdam Jaya">
        <p class="nama">Letkol Ckm <br>dr. Hengki Irawan, M.Biomed, Sp.An, S.H, M.A.R.S</p>
        <p class="jabatan">Waka Kesdam Jaya/Jayakarta</p>
    </a>
</div>
        
    </div>

    <div class="text-center mb-4">
        <audio id="welcomeAudio" autoplay>
            <source src="assets/audio/selamat_datang.mp3" type="audio/mpeg">
        </audio>
        <button id="playAudioBtn" class="btn-play-modern">
            <i class="bi bi-play-circle"></i> Putar Ulang Sambutan
        </button>
    </div>

    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="auth/login_satuan_lain.php" class="btn btn-primary btn-lg shadow text-white">
            <i class="bi bi-globe"></i> Kirim Surat (Satuan Luar)
        </a>
    </div>

    <div class="mt-4">
        <p class="fw-bold">Saran & Pengaduan:</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="https://wa.me/6285217454660" target="_blank" rel="noopener noreferrer" class="btn btn-success">
                <i class="bi bi-whatsapp"></i> Chat Admin Setum
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="bi bi-box-arrow-right"></i> Keluar Portal
            </a>
        </div>
    </div>
</section>

<div id="heroCarousel" class="carousel slide carousel-fade container shadow rounded mt-5" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false">
    <div class="carousel-indicators">
        <?php for($i=0;$i<5;$i++): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i==0 ? 'active' : '' ?>" aria-label="Slide <?= $i+1 ?>"></button>
        <?php endfor; ?>
    </div>
    <div class="carousel-inner">
        <?php for($i=1;$i<=5;$i++): ?>
        <div class="carousel-item <?php if($i==1) echo 'active'; ?>">
            <img src="assets/img/carosel<?= $i ?>.jpg" class="d-block w-100" alt="Dokumentasi <?= $i ?>">
        </div>
        <?php endfor; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<section class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body p-5 text-center">
            <h3 class="fw-bold text-success mb-4">Tentang Kesdam Jaya/Jayakarta</h3>
            <p style="text-align: justify; line-height: 1.8;">
                Kesdam Jaya/Jayakarta merupakan Satuan Pelaksana di tingkat Kodam Jaya yang bertuyen menyelenggarakan dukungan dan pelayanan kesehatan bagi prajurit TNI AD, PNS, serta keluarganya dalam rangka mendukung tugas pokok Kodam Jaya/Jayakarta. Melalui inovasi digital seperti aplikasi E-Office ini, Kesdam Jaya/Jayakarta berkomitmen meningkatkan kualitas pelayanan administrasi dan tata kelola persuratan secara modern, efektif, dan transparan.
            </p>
        </div>
    </div>
</section>

<section class="container mt-5">
    <h3 class="text-center fw-bold mb-4">RUANGAN MAKESDAM JAYA/JAYAKARTA</h3>
    <div class="row g-4 justify-content-center">
        <?php
        $daftar_intern = [
            ["nama" => "Denkeslap", "foto" => "Denkeslap.jpg"],
            ["nama" => "Was", "foto" => "Was.jpg"],
            ["nama" => "Dukkes", "foto" => "dukkes.jpg"],
            ["nama" => "Kesprev", "foto" => "Kesprev.jpg"],
            ["nama" => "Renproggar", "foto" => "Renproggar.jpg"],         
            ["nama" => "Minlogkes", "foto" => "Minlogkes.jpg"],          
            ["nama" => "Matkes", "foto" => "Matkes.jpg"],
            ["nama" => "Yankes", "foto" => "Yankes.jpg"],
            ["nama" => "Primkop", "foto" => "Primkop.jpg"],
            ["nama" => "Kagudkesrah", "foto" => "gudkesrah.jpg"],
            ["nama" => "Paku Kesdam", "foto" => "paku_kesdam.jpg"],
            ["nama" => "infokes", "foto" => "Infokes.jpg"],
            ["nama" => "Pers", "foto" => "Pers.jpg"],
            ["nama" => "Log", "foto" => "Log.jpg"],
            ["nama" => "Urdal", "foto" => "Pam.jpg"],
            ["nama" => "Pam", "foto" => "Pam.jpg"],
            ["nama" => "Juryar", "foto" => "Pam.jpg"],           
            ["nama" => "Persit", "foto" => "persit.jpg"],
            ["nama" => "Korpri", "foto" => "korpri.jpg"],
            ["nama" => "SMK Kesdam", "foto" => "smk.jpg"]    
        ];

        foreach ($daftar_intern as $intern): 
        ?>
        <div class="col-lg-2 col-md-3 col-6 text-center">
            <div class="room-card text-dark">
                <img src="assets/jajaran/<?= $intern['foto']; ?>" class="img-fluid rounded shadow-sm" alt="Ruang <?= $intern['nama']; ?>">
                <p class="mt-2 fw-bold small"><?= $intern['nama']; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="container mt-5 mb-5">
    <h3 class="text-center fw-bold mb-4">SATUAN JAJARAN INTERNAL KESDAM JAYA/JAYAKARTA</h3>
    <div class="row g-4 justify-content-center">
        <?php
        $daftar_jajaran = [
            ["nama" => "Rumkit Tk II MRM", "foto" => "rumkit_mrm1.jpg"],
            ["nama" => "Rumkit Tk IV Cijantung", "foto" => "cijantungok.jpg"],
            ["nama" => "Rumkit Tk IV Daan Mogot", "foto" => "daan_mogot.jpg"],
            ["nama" => "Rumkitban 00.08.01 JS", "foto" => "rumkitban_js.jpg"],
            ["nama" => "Poliklinik Induk Cijantung", "foto" => "poliklinik_cijantung.jpg"],
            ["nama" => "Poliklinik Induk Tangerang", "foto" => "poliklinik_tangerang.jpg"],
            ["nama" => "Polkes 00.09.01 JP", "foto" => "polkes_jp.jpg"],
            ["nama" => "Polkes 00.09.02 JU", "foto" => "polkes_ju.jpg"],
            ["nama" => "Polkes 00.09.03 JB", "foto" => "polkes_jb.jpg"],
            ["nama" => "Polkes 00.09.04 JT", "foto" => "polkes_jt.jpg"],
            ["nama" => "Polkes 00.09.06 Bekasi", "foto" => "polkes_bekasi.jpg"],
            ["nama" => "Polkes 00.09.07 Depok", "foto" => "polkes_depok.jpg"],
            ["nama" => "Polma Kodam Jaya", "foto" => "polma.jpg"],
            ["nama" => "Polkes Rindam Jaya", "foto" => "rindam.jpg"],
            ["nama" => "Ka SMK Kesdam Jaya", "foto" => "smk.jpg"]
        ];

        foreach ($daftar_jajaran as $item):
        ?>
        <div class="col-lg-2 col-md-3 col-6 text-center">
            <div class="jajaran-card text-dark">
                <img src="assets/jajaran/<?= $item['foto']; ?>" class="img-fluid rounded shadow-sm" alt="Satuan <?= $item['nama']; ?>">
                <p class="mt-2 fw-bold small"><?= $item['nama']; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>


<div class="container text-center mb-5">
    <h5 class="fw-bold mb-3">Media Sosial Kesdam Jaya/Jayakarta</h5>
    <a href="https://www.youtube.com/@infokeskesdamjaya" class="btn btn-danger btn-sm m-1" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i> YouTube</a>
    <a href="#" class="btn btn-primary btn-sm m-1"><i class="bi bi-facebook"></i> Facebook</a>
    <a href="https://www.instagram.com/kesdamjayakarta?igsh=eHM0ZjFqeW00b3Vo" class="btn btn-warning btn-sm m-1" target="_blank" rel="noopener noreferrer"><i class="bi bi-instagram"></i> Instagram</a>
    <a href="#" class="btn btn-dark btn-sm m-1"><i class="bi bi-tiktok"></i> TikTok</a>
</div>




<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map { height: 450px !important; width: 100%; display: block; }
    .card-body { padding: 0 !important; }
</style>

<div class="card shadow-sm mb-4 container p-0">
    <div class="card-header bg-primary text-white" style="padding: 1rem !important;">
        <h5 class="m-0"><i class="bi bi-map"></i> Peta Lokasi Fasilitas</h5>
    </div>
    <div class="p-2 bg-light border-bottom">
        <input type="text" id="mapSearch" class="form-control" placeholder="🔍 Cari lokasi (Contoh: RS Tk. II)...">
    </div>
    <div class="card-body">
        <div id="map"></div>
    </div>
</div>

<footer class="footer text-center">
    <p><a href="https://www.kesdamjayakarta.web.id/" target="_blank" rel="noopener noreferrer" style="color:white;text-decoration:none;">Website Resmi Kesdam Jaya/Jayakarta</a></p>
    <p style="color:#aaa;"><span>&copy; IT Kesdam Jaya/Jayakarta 2026 - Azam</span></p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. INISIALISASI PETA
    var map = L.map('map').setView([-6.2625, 106.8837], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var locations = [
        {lat: -6.2625, lng: 106.8837, title: "Kesdam Jaya/Jayakarta"},
        {lat: -6.2917, lng: 106.8825, title: "RS Tk. II Moh Ridwan Meuraksa"},
        {lat: -6.3134, lng: 106.8656, title: "RS Tk. IV Cijantung"}
    ];

    locations.forEach(function(loc) {
        L.marker([loc.lat, loc.lng]).addTo(map).bindPopup(loc.title);
    });

    setTimeout(() => { map.invalidateSize(); }, 800);

    // 2. FILTER PENCARIAN (DITAMBAHKAN CEK NULL AGAR AMAN)
    const searchInput = document.getElementById('mapSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    const title = layer.getPopup().getContent().toLowerCase();
                    layer.setOpacity(title.includes(filter) ? 1 : 0.2);
                }
            });
        });
    }
    
    // 2. AUDIO SAMBUTAN
    const audio = document.getElementById("welcomeAudio");
    const btn = document.getElementById("playAudioBtn");
    if (audio) {
        audio.play().catch(() => {});
        audio.addEventListener("ended", function () { audio.pause(); });
    }
    if (btn && audio) {
        btn.addEventListener("click", function () { audio.currentTime = 0; audio.play(); });
    }

    // 3. CAROUSEL
    var myCarousel = document.querySelector('#heroCarousel');
    if (myCarousel) {
        new bootstrap.Carousel(myCarousel, { interval: 3000, ride: 'carousel' });
    }

    // 4. PINTU RAHASIA (DIGABUNGKAN)
    const setupSecretLogin = (triggerSelector, targetUrl, clickCountLimit, timerLimit) => {
        document.querySelectorAll(triggerSelector).forEach(item => {
            let count = 0; let timer;
            item.addEventListener('click', () => {
                count++; clearTimeout(timer);
                if (count === clickCountLimit) { count = 0; window.location.href = targetUrl; }
                else { timer = setTimeout(() => { count = 0; }, timerLimit); }
            });
        });
    };

    // Eksekusi Pintu Rahasia
const logoTrigger = document.getElementById('logo-admin-trigger');
if (logoTrigger) {
    let count = 0; let timer;
    logoTrigger.addEventListener('click', () => {
        count++; clearTimeout(timer);
        if (count === 5) { count = 0; window.location.href = "auth/login_admin.php"; }
        else { timer = setTimeout(() => { count = 0; }, 1200); }
    });
}

// Panggil fungsi dengan benar
setupSecretLogin('.room-card', 'auth/login_ruangan.php', 5, 1200);
setupSecretLogin('.jajaran-card', 'auth/login_jajaran.php', 7, 1500);

});
</script>
</body>
</html>
