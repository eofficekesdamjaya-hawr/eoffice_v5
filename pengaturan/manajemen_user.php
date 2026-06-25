<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// Validasi Utama: Hanya Superadmin & Admin yang boleh mengelola user
if (empty($_SESSION['id_user']) || ($_SESSION['status'] ?? '') !== "login") {
    header("Location: ../auth/login_admin.php?pesan=akses_ditolak");
    exit();
}

$user_role = $_SESSION['status_user'] ?? ($_SESSION['role'] ?? 'admin');
if (!in_array($user_role, ['superadmin', 'admin'])) {
    header("Location: ../dashboard/dashboard_admin.php?pesan=hak_akses_terbatas");
    exit();
}

$nama_user  = $_SESSION['nama_user'] ?? ($_SESSION['nama'] ?? 'Administrator'); 
$tipe_akses = $_SESSION['tipe_akses'] ?? '';

// Peta Nama Ruangan (Sesuai dengan Master Ruangan Kesdam Jaya)
$ruanganMap = [
    1  => 'Pers Tuud', 2  => 'Seksi Was', 3  => 'Seksi Dukkes', 4  => 'Seksi Kesprev', 
    5  => 'Seksi Renproggar', 6  => 'Seksi Minlogkes', 7  => 'Seksi Matkes', 8  => 'Seksi Yankes', 
    9  => 'Gudang Kesrah', 10 => 'SMK Kesdam Jaya', 11 => 'Dandenkeslap', 12 => 'Ka Primkop', 
    13 => 'Paku Kesdam', 14 => 'Kaur Infokes', 15 => 'Kaur Log', 16 => 'Juyar', 
    17 => 'Persit', 18 => 'Kaurpam', 19 => 'Kaurdal', 20 => 'Korpri', 21 => 'Kaur Pers', 22 => 'Kasi tuud'
];

// --- PROSES ACTION (TAMBAH, EDIT, HAPUS) ---
$pesan = '';
$status_pesan = '';

// 1. Tambah User
if (isset($_POST['submit_tambah'])) {
    $username   = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password   = md5($_POST['password']); // Menggunakan MD5 sesuai standard v5 (Bisa diganti password_hash jika diperlukan)
    $nama       = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $role       = mysqli_real_escape_string($conn, $_POST['role']);
    $id_ruangan = ($role === 'ruangan') ? (int)$_POST['id_ruangan'] : 'NULL';

    // Cek apakah username sudah ada
    $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Gagal! Username <strong>$username</strong> sudah terdaftar.";
        $status_pesan = "danger";
    } else {
        $tipe_akses_input = ($role === 'ruangan') ? 'ruangan' : 'admin';
        $query_ins = "INSERT INTO users (username, password, nama, status_user, tipe_akses, id_ruangan) 
                      VALUES ('$username', '$password', '$nama', '$role', '$tipe_akses_input', $id_ruangan)";
        
        if (mysqli_query($conn, $query_ins)) {
            $pesan = "Berhasil menambahkan user baru: <strong>$nama</strong>.";
            $status_pesan = "success";
        } else {
            $pesan = "Gagal menyimpan ke database: " . mysqli_error($conn);
            $status_pesan = "danger";
        }
    }
}

// 2. Edit User
if (isset($_POST['submit_edit'])) {
    $id_user_edit = (int)$_POST['id_user'];
    $nama         = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $role         = mysqli_real_escape_string($conn, $_POST['role']);
    $id_ruangan   = ($role === 'ruangan') ? (int)$_POST['id_ruangan'] : 'NULL';
    $password     = $_POST['password'];

    $tipe_akses_input = ($role === 'ruangan') ? 'ruangan' : 'admin';
    
    // Jika password diisi, maka update password juga
    if (!empty($password)) {
        $password_md5 = md5($password);
        $query_upd = "UPDATE users SET nama='$nama', status_user='$role', tipe_akses='$tipe_akses_input', id_ruangan=$id_ruangan, password='$password_md5' WHERE id=$id_user_edit";
    } else {
        $query_upd = "UPDATE users SET nama='$nama', status_user='$role', tipe_akses='$tipe_akses_input', id_ruangan=$id_ruangan WHERE id=$id_user_edit";
    }

    if (mysqli_query($conn, $query_upd)) {
        $pesan = "Data user berhasil diperbarui.";
        $status_pesan = "success";
    } else {
        $pesan = "Gagal memperbarui data: " . mysqli_error($conn);
        $status_pesan = "danger";
    }
}

// 3. Hapus User
if (isset($_GET['action']) && $_GET['action'] === 'hapus') {
    $id_hapus = (int)$_GET['id'];
    // Proteksi: jangan biarkan akun yang sedang login menghapus dirinya sendiri
    if ($id_hapus === (int)$_SESSION['id_user']) {
        $pesan = "Aksi ditolak! Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif.";
        $status_pesan = "warning";
    } else {
        if (mysqli_query($conn, "DELETE FROM users WHERE id = $id_hapus")) {
            $pesan = "User berhasil dihapus permanen dari sistem.";
            $status_pesan = "success";
        } else {
            $pesan = "Gagal menghapus user.";
            $status_pesan = "danger";
        }
    }
}

// Query mengambil semua data user
$sqlUsers = "SELECT * FROM users ORDER BY id DESC";
$resUsers = mysqli_query($conn, $sqlUsers);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Operator & User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .table th { vertical-align: middle; text-align: center; font-size: 0.8rem; text-transform: uppercase; background-color: #1e293b !important; color: #fff; border: 1px solid #334155; }
        .table td { font-size: 0.85rem; vertical-align: middle; color: #334155; }
        .section-header { border-left: 4px solid #0284c7; padding-left: 12px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex" style="min-height: 100vh;">

    <?php require_once '../dashboard/sidebar_admin.php'; ?>

    <div class="container-fluid p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-info me-2"></i>Manajemen Akun Personel & Ruangan</h4>
                <small class="text-muted">Pengendali Otoritas Sistem e-Office Kesdam Jaya</small>
            </div>
            <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah User Baru
            </button>
        </div>

        <?php if (!empty($pesan)): ?>
            <div class="alert alert-<?= $status_pesan ?> alert-dismissible fade show shadow-sm text-xs" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="section-header text-dark fs-5 shadow-sm p-2 bg-white rounded-3">
            <i class="bi bi-shield-lock-fill text-primary me-2"></i>Daftar Pengguna Aktif Sistem
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0 table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Nama Lengkap</th>
                            <th style="width: 15%;">Username</th>
                            <th style="width: 15%;">Role Jabatan</th>
                            <th style="width: 25%;">Penempatan Ruangan / Unit</th>
                            <th style="width: 20%;">Aksi Pengaturan</th>
                        </tr>
                    </thead>
                    <tbody>

<?php 
                        $no = 1;
                        if (mysqli_num_rows($resUsers) > 0):
                            while ($user = mysqli_fetch_assoc($resUsers)): 
                                // --- SINKRONISASI KRITIKAL KOLOM DATABASE (ANTI NULL / UNDEFINED) ---
                                $user_actual_role = $user['status_user'] ?? ($user['role'] ?? ($user['level'] ?? 'ruangan'));
                                $user_actual_name = $user['nama'] ?? ($user['nama_lengkap'] ?? 'Tanpa Nama');
                                $user_actual_uname = $user['username'] ?? ($user['user'] ?? ($user['uname'] ?? '-'));
                                $user_id_ruangan   = $user['id_ruangan'] ?? ($user['ruangan_id'] ?? '');

                                // Penentuan warna badge berdasarkan role jabatan
                                $badge_role = 'bg-secondary';
                                if($user_actual_role === 'superadmin') $badge_role = 'bg-danger';
                                elseif($user_actual_role === 'admin') $badge_role = 'bg-warning text-dark';
                                elseif($user_actual_role === 'setum') $badge_role = 'bg-success';
                                elseif($user_actual_role === 'kasituud') $badge_role = 'bg-primary';
                                elseif($user_actual_role === 'ruangan') $badge_role = 'bg-info text-dark';
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars((string)$user_actual_name) ?></td>
                                <td class="font-monospace text-secondary"><?= htmlspecialchars((string)$user_actual_uname) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $badge_role ?> text-uppercase text-xs"><?= htmlspecialchars((string)$user_actual_role) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    if ($user_actual_role === 'ruangan' || !empty($user_id_ruangan)) {
                                        echo '<span class="fw-bold text-success"><i class="bi bi-building"></i> ' . ($ruanganMap[$user_id_ruangan] ?? 'Internal Staff / Ruangan') . '</span>';
                                    } else {
                                        echo '<span class="text-muted italic"><i class="bi bi-shield-shaded"></i> Akses Kontrol Global</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm fw-bold text-xs shadow-sm me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?= $user['id'] ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="manajemen_user.php?action=hapus&id=<?= $user['id'] ?>" 
                                       class="btn btn-danger btn-sm text-xs shadow-sm" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus user <?= htmlspecialchars((string)$user_actual_name) ?> secara permanen?')">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modalEdit<?= $user['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <form action="manajemen_user.php" method="POST" class="modal-content">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-pencil-square text-warning"></i> Sunting Akun Pengguna</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <input type="hidden" name="id_user" value="<?= $user['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Username (Tidak Dapat Diubah):</label>
                                                <input type="text" class="form-control bg-light font-monospace" value="<?= htmlspecialchars((string)$user_actual_uname) ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Nama Lengkap Personel:</label>
                                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars((string)$user_actual_name) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Kata Sandi Baru (Kosongkan jika tidak diganti):</label>
                                                <input type="password" class="form-control" name="password" placeholder="Masukan password baru saja...">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Role Otoritas Jabatan:</label>
                                                <select class="form-select text-uppercase" name="role" required>
                                                    <option value="superadmin" <?= $user_actual_role == 'superadmin' ? 'selected':'' ?>>Superadmin</option>
                                                    <option value="admin" <?= $user_actual_role == 'admin' ? 'selected':'' ?>>Admin</option>
                                                    <option value="setum" <?= $user_actual_role == 'setum' ? 'selected':'' ?>>Setum</option>
                                                    <option value="kasituud" <?= $user_actual_role == 'kasituud' ? 'selected':'' ?>>Kasituud</option>
                                                    <option value="ruangan" <?= $user_actual_role == 'ruangan' ? 'selected':'' ?>>Ruangan / Unit</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Jika Role Unit, Pilih Ruangan Penempatan:</label>
                                                <select class="form-select" name="id_ruangan">
                                                    <option value="">-- Bukan Ruangan / Pilih Unit --</option>
                                                    <?php foreach ($ruanganMap as $id_ru => $nama_ru): ?>
                                                        <option value="<?= $id_ru ?>" <?= $user_id_ruangan == $id_ru ? 'selected':'' ?>><?= $nama_ru ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="submit_edit" class="btn btn-warning btn-sm fw-bold text-dark">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        <?php 
                            endwhile;


                        else: 
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted bg-white">Belum ada data operator/user terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="manajemen_user.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Registrasi Akun Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="mb-3">
                    <label class="form-label fw-bold mb-1">Username Akses (Gunakan huruf kecil/angka):</label>
                    <input type="text" class="form-control font-monospace" name="username" placeholder="cth: setum_kesdam" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-1">Nama Lengkap Personel / Pemilik Akun:</label>
                    <input type="text" class="form-control" name="nama" placeholder="cth: Serma Budi Santoso" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-1">Kata Sandi Default:</label>
                    <input type="password" class="form-control" name="password" placeholder="******" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-1">Role Otoritas Jabatan:</label>
                    <select class="form-select" name="role" required>
                        <option value="">-- Pilih Hak Otoritas --</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="setum">Setum</option>
                        <option value="kasituud">Kasituud</option>
                        <option value="ruangan">Ruangan / Unit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-1">Jika Role Unit, Pilih Ruangan Penempatan:</label>
                    <select class="form-select" name="id_ruangan">
                        <option value="">-- Pilih Penempatan Ruangan --</option>
                        <?php foreach ($ruanganMap as $id_ru => $nama_ru): ?>
                            <option value="<?= $id_ru ?>"><?= $nama_ru ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="submit_tambah" class="btn btn-primary btn-sm fw-bold">Daftarkan Akun</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
