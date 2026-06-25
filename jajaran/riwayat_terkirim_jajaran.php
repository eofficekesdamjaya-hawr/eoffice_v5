<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

$id = $_SESSION['id'];

$stmt = $conn->prepare("
SELECT * FROM surat_masuk
WHERE id_user = ?
AND role_pengirim = 'jajaran'
ORDER BY id_surat DESC
");

$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();

include '../layout/header.php';
?>

<div class="container py-4">
<h4 class="fw-bold">Riwayat Surat Terkirim</h4>

<table class="table table-hover">
<thead>
<tr>
<th>Agenda</th>
<th>Tujuan</th>
<th>Nomor</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['no_agenda'] ?></td>
<td><?= htmlspecialchars($row['kepada']) ?></td>
<td><?= htmlspecialchars($row['no_surat']) ?></td>
<td><?= $row['status_proses'] ?></td>
<td>

<a href="../uploads/<?= $row['file_surat'] ?>" class="btn btn-sm btn-success" download>
Download
</a>

<?php if($row['status_proses']=="Pending"): ?>

<a href="edit_riwayat_terkirim_jajaran.php?id=<?= $row['id_surat'] ?>" 
class="btn btn-warning btn-sm">Edit</a>


<?php endif; ?>

</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

<?php include '../layout/footer.php'; ?>
