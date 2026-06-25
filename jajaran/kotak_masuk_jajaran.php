<?php
require_once "../config/session.php";
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$nama = $_SESSION['nama'];

$stmt = $conn->prepare("
    SELECT * FROM surat_masuk_jajaran
    WHERE TRIM(LOWER(kepada)) = TRIM(LOWER(?))
    ORDER BY id_surat DESC
");
$stmt->bind_param("s", $nama);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<div class="container py-4">
<h4 class="fw-bold mb-3">Kotak Masuk Jajaran</h4>

<a href="dashboard_jajaran.php" class="btn btn-secondary mb-3">← Kembali</a>

<div class="card shadow-sm">
<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
<th>Agenda</th>
<th>Pengirim</th>
<th>Nomor</th>
<th>Perihal</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['no_agenda'] ?></td>
<td><?= htmlspecialchars($row['asal_surat']) ?></td>
<td><?= htmlspecialchars($row['no_surat']) ?></td>
<td><?= htmlspecialchars($row['perihal']) ?></td>
<td>
<a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-success" download>
Download
</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>
</div>
</div>

<?php include '../layout/footer.php'; ?>
