<?php
include '../config/koneksi.php';

$query = "SELECT * FROM instansi ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>No</th><th>Nama</th><th>Alamat</th><th>Telp</th><th>Email</th><th>Website</th></tr>";
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
} else {
    echo "<p>Belum ada data instansi.</p>";
}
?>
