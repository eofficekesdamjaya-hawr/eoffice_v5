<?php
// Panggil master akses terlebih dahulu
require_once '../config/hak_akses.php';
require_once '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$currentFile = basename($_SERVER['PHP_SELF']);

function active($key) {
    return (strpos($_SERVER['REQUEST_URI'], $key) !== false) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrasi SETUM KESDAM JAYA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-active: #0284c7;
            --sidebar-hover: rgba(255,255,255,0.07);
            --nav-text: #94a3b8;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #334155;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            flex-shrink: 0;
            padding: 20px 16px;
            background: rgba(0,0,0,0.25);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-align: center;
        }

        /* Scrollable nav area */
        .sidebar-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px 0 16px;
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }
        .sidebar-body::-webkit-scrollbar { width: 4px; }
        .sidebar-body::-webkit-scrollbar-track { background: transparent; }
        .sidebar-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        /* Nav items */
        .nav-link {
            color: var(--nav-text);
            font-size: 13.5px;
            font-weight: 500;
            margin: 1px 10px;
            padding: 9px 12px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .nav-link i { font-size: 16px; flex-shrink: 0; }
        .nav-link:hover { background: var(--sidebar-hover); color: #f8fafc; }
        .nav-link.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(2,132,199,0.35);
        }

        /* Section headers (collapsible) */
        .nav-section-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            padding: 14px 22px 4px;
            cursor: pointer;
            background: none;
            border: none;
            width: 100%;
            transition: color 0.15s;
        }
        .nav-section-btn:hover { color: #94a3b8; }
        .nav-section-btn .chevron {
            font-size: 11px;
            transition: transform 0.25s;
        }
        .nav-section-btn.collapsed .chevron { transform: rotate(-90deg); }

        /* Logout area */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 10px 10px 14px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        /* ── OVERLAY (mobile) ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1039;
        }
        .sidebar-overlay.show { display: block; }

        /* ── TOPBAR (mobile toggle) ── */
        .topbar {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 56px;
            background: var(--sidebar-bg);
            align-items: center;
            padding: 0 16px;
            z-index: 1038;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .topbar .brand { color: #fff; font-weight: 700; font-size: 15px; margin-left: 12px; }
        .topbar .toggle-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            padding: 4px;
            cursor: pointer;
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding: 32px 28px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .topbar { display: flex; }
            .main-content { margin-left: 0; padding: 72px 16px 24px; }
        }
    </style>
</head>
<body>

<!-- Mobile topbar -->
<div class="topbar">
    <button class="toggle-btn" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    <span class="brand">E-OFFICE · SETUM KESDAM JAYA</span>
</div>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-header">
        <div class="fw-bold text-white" style="font-size:15px;">E-OFFICE</div>
        <div class="text-info fw-semibold" style="font-size:10px;letter-spacing:1px;">SETUM KESDAM JAYA</div>
    </div>

    <!-- Scrollable nav -->
    <div class="sidebar-body">
        <ul class="nav flex-column">

            <!-- Dashboard -->
            <li>
                <a href="/dashboard/dashboard_admin.php"
                   class="nav-link <?= ($currentFile == 'dashboard_admin.php') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard Center</span>
                </a>
            </li>

            <!-- ── Transaksi Surat ── -->
            <?php if ($can_A || $can_B || $can_C || $can_D || $can_G): ?>
            <button class="nav-section-btn" data-bs-toggle="collapse" data-bs-target="#secTransaksi" aria-expanded="true">
                Transaksi Surat <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="collapse show" id="secTransaksi">
                <ul class="nav flex-column">

                    <?php if ($can_G): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_masuk.php"
                           class="nav-link <?= ($currentFile == 'kelola_surat_masuk.php') ? 'active' : '' ?>">
                            <i class="bi bi-envelope-arrow-down-fill"></i> <span>Surat Masuk</span>
                        </a>
                    </li>
                    <li>
                        <a href="../surat_masuk/tambah_surat_masuk.php"
                           class="nav-link <?= ($currentFile == 'tambah_surat_masuk.php') ? 'active' : '' ?>">
                            <i class="bi bi-plus-circle-fill"></i> <span>Tambah Surat Masuk</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_G || $user_email === 'kasituud2026@gmail.com'): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php"
                           class="nav-link <?= ($currentFile == 'kelola_surat_keluar.php') ? 'active' : '' ?>">
                            <i class="bi bi-envelope-arrow-up-fill"></i> <span>Surat Keluar</span>
                        </a>
                    </li>
                    <li>
                        <a href="../surat_keluar/tambah_surat_keluar.php"
                           class="nav-link <?= ($currentFile == 'tambah_surat_keluar.php') ? 'active' : '' ?>">
                            <i class="bi bi-plus-circle"></i> <span>Tambah Surat Keluar</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_A): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_masuk.php?filter=disposisi"
                           class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'kelola_surat_masuk.php') !== false && isset($_GET['filter']) && $_GET['filter'] == 'disposisi') ? 'active' : '' ?>">
                            <i class="bi bi-envelope-open"></i> <span>Disposisi Masuk</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_B): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_masuk.php?filter=riwayat"
                           class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'filter=riwayat') !== false && strpos($_SERVER['REQUEST_URI'], 'surat_masuk') !== false) ? 'active' : '' ?>">
                            <i class="bi bi-clock-history"></i> <span>Riwayat Disp. Masuk</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_C): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php?filter=disposisi"
                           class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'kelola_surat_keluar.php') !== false && isset($_GET['filter']) && $_GET['filter'] == 'disposisi') ? 'active' : '' ?>">
                            <i class="bi bi-envelope-arrow-up"></i> <span>Disposisi Keluar</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_D): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php?filter=riwayat"
                           class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'filter=riwayat') !== false && strpos($_SERVER['REQUEST_URI'], 'surat_keluar') !== false) ? 'active' : '' ?>">
                            <i class="bi bi-clock-history"></i> <span>Riwayat Disp. Keluar</span>
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
            <?php endif; ?>

            <!-- ── Monitoring & TTE ── -->
            <?php if ($can_E || $can_F || $can_G): ?>
            <button class="nav-section-btn" data-bs-toggle="collapse" data-bs-target="#secMonitoring" aria-expanded="true">
                Monitoring & TTE <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="collapse show" id="secMonitoring">
                <ul class="nav flex-column">

                    <?php if ($can_E): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php?filter=belum_ttd"
                           class="nav-link <?= active('filter=belum_ttd') ?>">
                            <i class="bi bi-pen-fill"></i> <span>Belum TTD / TTE</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_F): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php?filter=sudah_ttd"
                           class="nav-link <?= active('filter=sudah_ttd') ?>">
                            <i class="bi bi-patch-check-fill"></i> <span>Sudah TTD / TTE</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($can_G): ?>
                    <li>
                        <a href="../transaksi/kelola_surat_keluar.php?filter=arsip"
                           class="nav-link <?= active('filter=arsip') ?>">
                            <i class="bi bi-archive-fill"></i> <span>Arsip Terpadu</span>
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </div>
            <?php endif; ?>

            <!-- ── Laporan ── -->
            <?php if ($can_G): ?>
            <button class="nav-section-btn" data-bs-toggle="collapse" data-bs-target="#secLaporan" aria-expanded="true">
                Laporan <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="collapse show" id="secLaporan">
                <ul class="nav flex-column">
                    <li>
                        <a href="../laporan/laporan_surat_masuk.php"
                           class="nav-link <?= active('laporan_surat_masuk.php') ?>">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i> <span>Laporan Masuk</span>
                        </a>
                    </li>
                    <li>
                        <a href="../laporan/laporan_surat_keluar.php"
                           class="nav-link <?= active('laporan_surat_keluar.php') ?>">
                            <i class="bi bi-file-earmark-spreadsheet-fill"></i> <span>Laporan Keluar</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- ── Admin Khusus ── -->
            <?php if ($can_G): ?>
            <button class="nav-section-btn" data-bs-toggle="collapse" data-bs-target="#secAdmin" aria-expanded="true">
                Admin Khusus <i class="bi bi-chevron-down chevron"></i>
            </button>
            <div class="collapse show" id="secAdmin">
                <ul class="nav flex-column">
                    <?php if ($user_email === 'superadmin@gmail.com'): ?>
                    <li>
                        <a href="../pengaturan/manajemen_user.php"
                           class="nav-link <?= active('manajemen_user.php') ?>">
                            <i class="bi bi-people-fill"></i> <span>Manajemen User</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="../pengaturan/profil.php"
                           class="nav-link <?= active('profil.php') ?>">
                            <i class="bi bi-gear-fill"></i> <span>Profil Sistem</span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>

        </ul>
    </div><!-- /sidebar-body -->

    <!-- Logout pinned di bawah -->
    <div class="sidebar-footer">
        <a href="../auth/logout.php"
           class="nav-link text-danger bg-danger bg-opacity-10"
           onclick="return confirm('Keluar dari sistem E-Office Kesdam Jaya?')">
            <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
        </a>
    </div>

</div><!-- /sidebar -->

<div class="main-content">
<!-- konten halaman di sini -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar mobile
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); }

    toggleBtn?.addEventListener('click', () =>
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar()
    );
    overlay.addEventListener('click', closeSidebar);

    // Chevron rotate sync dengan Bootstrap collapse
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
        const target = document.querySelector(btn.dataset.bsTarget);
        target?.addEventListener('show.bs.collapse', () => btn.classList.remove('collapsed'));
        target?.addEventListener('hide.bs.collapse', () => btn.classList.add('collapsed'));
    });
</script>
