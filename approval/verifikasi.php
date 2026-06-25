<?php
session_start();
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

/*
========================================
CEK LOGIN RUANGAN
========================================
*/
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['tipe_akses']) ||
    $_SESSION['tipe_akses'] !== 'ruangan'
) {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

$id_surat = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_surat <= 0) {
    echo "ID surat tidak valid.";
    exit();
}

/*
========================================
AMBIL DATA SURAT
========================================
*/
$stmt = $conn->prepare("SELECT * FROM surat_masuk WHERE id_surat = ?");
$stmt->bind_param("i", $id_surat);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Data surat tidak ditemukan.";
    exit();
}

$data = $result->fetch_assoc();

/*
========================================
PROSES VERIFIKASI
========================================
*/
if (isset($_POST['submit_verifikasi'])) {

    $status = $_POST['status_proses'];
    $verified_by = $_SESSION['nama_role'];
    $tanggal = date("Y-m-d H:i:s");

    $update = $conn->prepare("
        UPDATE surat_masuk 
        SET status_proses = ?, 
            verified_by = ?, 
            tanggal_verifikasi = ?, 
            status_baca = 'sudah'
        WHERE id_surat = ?
    ");

    $update->bind_param("sssi", $status, $verified_by, $tanggal, $id_surat);
    $update->execute();

    echo "<script>
        alert('Verifikasi berhasil!');
        window.location='kelola_surat_masuk.php';
    </script>";
    exit();
}

include '../layout/header.php';
?>

<div class="main-content p-4">
    <h4 class="fw-bold mb-4">
        <i class="bi bi-check-circle text-warning me-2"></i>
        Verifikasi Surat Masuk
    </h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <table class="table table-bordered">
                <tr>
                    <th width="200">No Surat</th>
                    <td><?= htmlspecialchars($data['no_surat']) ?></td>
                </tr>
                <tr>
                    <th>Asal Surat</th>
                    <td><?= htmlspecialchars($data['asal_surat']) ?></td>
                </tr>
                <tr>
                    <th>Perihal</th>
                    <td><?= htmlspecialchars($data['perihal']) ?></td>
                </tr>
                <tr>
                    <th>Status Saat Ini</th>
                    <td>
                        <span class="badge bg-info">
                            <?= htmlspecialchars($data['status_proses']) ?>
                        </span>
                    </td>
                </tr>
            </table>

            <hr>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Ubah Status</label>
                    <select name="status_proses" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="Diterima">Diterima</option>
                        <option value="Proses Disposisi">Proses Disposisi</option>
                        <option value="Ditolak">Ditolak</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>

                <button type="submit" name="submit_verifikasi" class="btn btn-warning">
                    <i class="bi bi-check2-circle me-1"></i>
                    Simpan Verifikasi
                </button>

                <a href="kelola_surat_masuk.php" class="btn btn-secondary">
                    Kembali
                </a>
            </form>

        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>