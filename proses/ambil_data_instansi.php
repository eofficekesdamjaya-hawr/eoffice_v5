<?php
include '../config/koneksi.php';

$result = mysqli_query($conn, "SELECT * FROM instansi ORDER BY id DESC");

echo "<table border='1' width='100%' cellspacing='0' cellpadding='5'>
<tr>
    <th>No</th>
    <th>Nama Instansi</th>
    <th>Alamat</th>
    <th>Telepon</th>
    <th>Email</th>
    <th>Website</th>
</tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$no}</td>
        <td>{$row['nama_instansi']}</td>
        <td>{$row['alamat']}</td>
        <td>{$row['telp']}</td>
        <td>{$row['email']}</td>
        <td>{$row['website']}</td>
    </tr>";
    $no++;
}

echo "</table>";
?>
