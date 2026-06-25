<?php
include '../config/koneksi.php';

$query = mysqli_query($conn, "SELECT * FROM master_instansi ORDER BY id_instansi DESC");

echo "<table border='1' cellpadding='5'>
        <tr>
            <th>No</th>
            <th>Nama Instansi</th>
        </tr>";
$no = 1;
while($row = mysqli_fetch_assoc($query)){
    echo "<tr>
            <td>$no</td>
            <td>{$row['nama_instansi']}</td>
          </tr>";
    $no++;
}
echo "</table>";
?>
