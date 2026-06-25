<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// 1. Ambil data identitas unik dari session (berupa email atau username string)
$user_identity = $_SESSION['id_user'] ?? ($_SESSION['username'] ?? ($_SESSION['email'] ?? ''));
$status_login  = $_SESSION['status'] ?? '';

// 2. Validasi Utama: Harus login dan identitas tidak boleh kosong
if (empty($user_identity) || $status_login !== "login") {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}

// 3. Ambil data terbaru user berdasarkan email / username dari session
$user_identity_clean = mysqli_real_escape_string($conn, $user_identity);

// Sistem akan mendeteksi apakah mencari lewat kolom username atau email di database Anda
// --- GANTI MENJADI SEPERTI INI ---
$query_user = "SELECT * FROM users WHERE email = '$user_identity_clean' LIMIT 1";
$res_user   = mysqli_query($conn, $query_user);
$data_user  = mysqli_fetch_assoc($res_user);

if (!$data_user) {
    echo "Data pengguna dengan identitas ($user_identity) tidak ditemukan di database.";
    exit();
}

// --- SINKRONISASI KRITIKAL KOLOM DATABASE ---
$user_id_row       = $data_user['id'] ?? 0;
$user_actual_role  = $data_user['status_user'] ?? ($data_user['role'] ?? ($data_user['level'] ?? 'ruangan'));
$user_actual_name  = $data_user['nama'] ?? ($data_user['nama_lengkap'] ?? 'Personel');
$user_actual_uname = $data_user['username'] ?? ($data_user['user'] ?? ($data_user['uname'] ?? '-'));
$user_id_ruangan   = $data_user['id_ruangan'] ?? ($data_user['ruangan_id'] ?? '');

$tipe_akses = $_SESSION['tipe_akses'] ?? '';

// Peta Nama Ruangan Kesdam Jaya
$ruanganMap = [
    1  => 'Pers Tuud', 2  => 'Seksi Was', 3  => 'Seksi Dukkes', 4  => 'Seksi Kesprev', 
    5  => 'Seksi Renproggar', 6  => 'Seksi Minlogkes', 7  => 'Seksi Matkes', 8  => 'Seksi Yankes', 
    9  => 'Gudang Kesrah', 10 => 'SMK Kesdam Jaya', 11 => 'Dandenkeslap', 12 => 'Ka Primkop', 
    13 => 'Paku Kesdam', 14 => 'Kaur Infokes', 15 => 'Kaur Log', 16 => 'Juyar', 
    17 => 'Persit', 18 => 'Kaurpam', 19 => 'Kaurdal', 20 => 'Korpri', 21 => 'Kaur Pers', 22 => 'Kasi tuud'
];

$pesan = '';
$status_pesan = '';

// --- PROSES UPDATE PROFIL & PASSWORD ---
if (isset($_POST['submit_profil'])) {
    $nama_baru     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $password_baru = $_POST['password_baru'];
    $konfirmasi_pw = $_POST['konfirmasi_password'];
    
    $kolom_nama = isset($data_user['nama']) ? 'nama' : (isset($data_user['nama_lengkap']) ? 'nama_lengkap' : 'nama');

    if (!empty($password_baru)) {
        if ($password_baru !== $konfirmasi_pw) {
            $pesan = "Gagal! Konfirmasi kata sandi baru tidak cocok.";
            $status_pesan = "danger";
        } else {
            $password_md5 = md5($password_baru);
            $query_upd = "UPDATE users SET $kolom_nama = '$nama_baru', password = '$password_md5' WHERE id = $user_id_row";
            
            if (mysqli_query($conn, $query_upd)) {
                $_SESSION['nama_user'] = $nama_baru;
                $_SESSION['nama'] = $nama_baru;
                $user_actual_name = $nama_baru;
                $pesan = "Berhasil! Profil dan kata sandi baru Anda telah diperbarui.";
                $status_pesan = "success";
            } else {
                $pesan = "Gagal memperbarui data: " . mysqli_error($conn);
                $status_pesan = "danger";
            }
        }
    } else {
        $query_upd = "UPDATE users SET $kolom_nama = '$nama_baru' WHERE id = $user_id_row";
        if (mysqli_query($conn, $query_upd)) {
            $_SESSION['nama_user'] = $nama_baru;
            $_SESSION['nama'] = $nama_baru;
            $user_actual_name = $nama_baru;
            $pesan = "Berhasil! Nama profil Anda telah diperbarui.";
            $status_pesan = "success";
        } else {
            $pesan = "Gagal memperbarui nama: " . mysqli_error($conn);
            $status_pesan = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .profile-card { border-top: 4px solid #16a34a; }
        .form-label { font-size: 0.85rem; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex" style="min-height: 100vh;">

    <?php 
    if ($user_actual_role === 'ruangan' || $tipe_akses === 'ruangan') {
        if(file_exists('../ruangan/sidebar.php')) { require_once '../ruangan/sidebar.php'; }
    } else {
        if(file_exists('../dashboard/sidebar_admin.php')) { require_once '../dashboard/sidebar_admin.php'; }
    }
    ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0"><i class="bi bi-person-bounding-box text-success me-2"></i>Pengaturan Akun & Profil</h4>
                <small class="text-muted">Kelola kredensial mandiri demi keamanan siber e-Office</small>
            </div>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="alert alert-<?= $status_pesan ?> alert-dismissible fade show shadow-sm text-xs" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 profile-card text-center p-4 bg-white">
                    <div class="mb-3">
                        <i class="bi bi-person-circle text-secondary" style="font-size: 4.5rem;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars((string)$user_actual_name) ?></h5>
                    <p class="font-monospace text-muted text-xs mb-3">@<?= htmlspecialchars((string)$user_actual_uname) ?></p>
                    <span class="badge bg-dark text-uppercase mb-2 py-2 px-3 text-xs"><i class="bi bi-shield-lock me-1"></i> Otoritas: <?= htmlspecialchars((string)$user_actual_role) ?></span>
                    <div class="border-top pt-3 mt-3 text-start">
                        <small class="text-muted d-block text-uppercase fw-bold text-xs mb-1">Penempatan:</small>
                        <span class="text-success fw-bold text-sm"><i class="bi bi-building me-1"></i><?= ($user_actual_role === 'ruangan' || !empty($user_id_ruangan)) ? ($ruanganMap[$user_id_ruangan] ?? 'Ruangan / Unit') : 'Akses Pengendali Global' ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-3 bg-white">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Formulir Pembaruan Kredensial</h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="profil.php" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label fw-bold mb-1 text-dark">Username Akun / Email:</label>
                               <input type="text" class="form-control bg-light font-monospace" value="<?= htmlspecialchars((string)$user_actual_uname) ?>" readonly>

<input type="text" class="form-control bg-light font-monospace" value="<?= htmlspecialchars((string)$user_identity) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold mb-1 text-dark">Nama Lengkap Personel:</label>
                                <input type="text" class="form-control border-secondary-subtle" name="nama" value="<?= htmlspecialchars((string)$user_actual_name) ?>" required>
                            </div>
                            <hr class="my-4 text-muted">
                            <div class="bg-light p-3 rounded-3 border border-warning-subtle mb-3">
                                <h6 class="fw-bold text-warning-emphasis text-sm mb-2"><i class="bi bi-key-fill me-1"></i>Ganti Kata Sandi (Optional)</h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold mb-1">Kata Sandi Baru:</label>
                                    <input type="password" class="form-control bg-white" name="password_baru" placeholder="Masukkan kata sandi baru...">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold mb-1">Ulangi Kata Sandi Baru:</label>
                                    <input type="password" class="form-control bg-white" name="konfirmasi_password" placeholder="Ulangi kata sandi baru...">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="reset" class="btn btn-outline-secondary btn-sm px-4 fw-bold">Reset</button>
                                <button type="submit" name="submit_profil" class="btn btn-success btn-sm px-4 fw-bold shadow-sm"><i class="bi bi-check-circle me-1"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
