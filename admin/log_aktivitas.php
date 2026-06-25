<?php
session_start();
require_once '../config/koneksi.php';
require_once '../auth/auth_middleware.php';

// Hanya superadmin/admin yang bisa melihat log
requireRole(['admin']);

include '../layout/header.php';
?>

<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Sistem</h4>
        <button class="btn btn-danger btn-sm" onclick="return confirm('Kosongkan log?')">
            <i class="bi bi-trash me-1"></i> Bersihkan Log
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi / Aktivitas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= date('d/m/Y H:i') ?></td>
                            <td><span class="badge bg-primary">Admin Utama</span></td>
                            <td>Melakukan Verifikasi Surat #AGENDA-001</td>
                            <td><span class="text-success"><i class="bi bi-check-circle-fill"></i> Sukses</span></td>
                        </tr>
                        <?php
                        $queryLog = mysqli_query($conn, "SELECT * FROM log_aktivitas ORDER BY tanggal DESC LIMIT 20");
                        while($log = mysqli_fetch_assoc($queryLog)):
                        ?>
                        <tr>
                            <td><?= $log['waktu'] ?></td>
                            <td><?= $log['username'] ?></td>
                            <td><?= $log['aksi'] ?></td>
                            <td><span class="text-success small">Logged</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>