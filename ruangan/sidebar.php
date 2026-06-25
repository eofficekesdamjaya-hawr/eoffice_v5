<?php
// File: /var/www/eoffice_kesdamjayav5/ruangan/sidebar.php

$current_tipe_akses = $_SESSION['tipe_akses'] ?? '';
$current_role       = $_SESSION['role'] ?? '';
$current_role_key   = $_SESSION['role_key'] ?? '';
$current_nama = trim(
    $_SESSION['nama_user']
    ?? $_SESSION['nama']
    ?? 'User'
);
$current_nama_role  = trim($_SESSION['nama_role'] ?? 'Guest');
?>

<div class="bg-dark text-white p-3 shadow" id="sidebar-wrapper" style="min-width: 260px; min-height: 100vh;">
    <div class="sidebar-heading text-center py-3 border-bottom border-secondary">
        <h5 class="fw-bold mb-0 text-warning">E-OFFICE</h5>
        <small class="text-white-50 text-uppercase font-monospace" style="font-size: 0.7rem; letter-spacing: 1px;">Kesdam Jaya v5</small>
    </div>
    
    <div class="text-center py-3 my-3 bg-secondary bg-opacity-10 rounded px-2 border border-secondary border-opacity-25">
        <i class="fas fa-user-circle fa-2.5x text-info mb-2"></i>
        <div class="fw-bold text-truncate text-white" style="font-size: 0.85rem;" title="<?= htmlspecialchars($current_nama) ?>"><?= htmlspecialchars($current_nama) ?></div>
        <span class="badge bg-light text-dark px-2 py-1 mt-1 text-uppercase font-monospace" style="font-size: 0.7rem;"><?= htmlspecialchars($current_nama_role) ?></span>
    </div>

    <div class="list-group list-group-flush mt-2" id="sidebar-menu">
        <p class="text-muted fw-bold text-uppercase px-3 mb-1 mt-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">Menu Utama</p>
        
        <?php if ($current_tipe_akses === 'ruangan'): ?>
            <a href="../ruangan/dashboard.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-tachometer-alt me-2 text-warning"></i> Dashboard</a>
        <?php elseif ($current_role === 'jajaran'): ?>
            <a href="../jajaran/dashboard.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-tachometer-alt me-2 text-warning"></i> Dashboard</a>
        <?php else: ?>
            <a href="../dashboard/dashboard_admin.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-tachometer-alt me-2 text-warning"></i> Dashboard</a>
        <?php endif; ?>

        <?php if (in_array($current_role_key, ['admin', 'setum', 'kasituud', 'kakesdamjaya', 'wakakesdamjaya', 'spripimpinan'])): ?>
            <p class="text-muted fw-bold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Manajemen Surat</p>
            <a href="../surat_masuk/kelola_surat_masuk.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-envelope me-2 text-success"></i> Kelola Surat Masuk</a>
            <a href="../surat_keluar/kelola_surat_keluar.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-paper-plane me-2 text-primary"></i> Kelola Surat Keluar</a>
            <a href="../surat_masuk/kelola_surat_masuk.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-tasks me-2 text-info"></i> Disposisi Surat Masuk</a>
        <?php endif; ?>

<?php if ($current_tipe_akses === 'ruangan'): ?>
            <p class="text-muted fw-bold text-uppercase px-3 mt-3 mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Layanan Ruangan</p>
            <a href="../surat_keluar/tambah_surat_keluar.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Surat Keluar</a>
            
            <a href="../transaksi/kelola_surat_keluar.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2 d-flex justify-content-between align-items-center">
                <span><i class="fas fa-folder-open me-2 text-success"></i> Kelola Surat Keluar</span>
                <span class="badge bg-danger d-none" id="badgePendingMasuk">0</span>
            </a>
            
            <a href="../transaksi/kelola_surat_masuk.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-inbox me-2 text-warning"></i> Kelola Surat Masuk</a>
            <a href="../transaksi/kelola_surat_masuk.php?filter=disposisi" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-tasks me-2 text-info"></i> Disposisi Surat Masuk</a>
        <?php endif; ?>

        <p class="text-muted fw-bold text-uppercase px-3 mt-4 mb-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Akun Anda</p>
        <a href="../auth/ubah_password.php" class="list-group-item list-group-item-action bg-dark text-white border-0 px-3 py-2"><i class="fas fa-key me-2 text-muted"></i> Ubah Password</a>
        
        <?php 
            $logout_param = $current_tipe_akses ?: ($current_role ?: 'total'); 
        ?>
        <a href="../auth/logout.php?dari=<?= urlencode($logout_param) ?>" class="list-group-item list-group-item-action bg-dark text-danger border-0 px-3 py-2 fw-bold">
            <i class="fas fa-sign-out-alt me-2"></i> Keluar Aplikasi
        </a>
    </div>
</div>

<style>
#sidebar-menu .list-group-item-action { transition: all 0.2s ease-in-out; }
#sidebar-menu .list-group-item-action:hover {
    background-color: rgba(255, 255, 255, 0.08) !important;
    color: #f59e0b !important;
    border-radius: 4px;
    padding-left: 20px !important;
}
.fa-2.5x { font-size: 2.3rem; }
</style>
