<?php
include '../config/koneksi.php';

$result = $conn->query("SELECT * FROM instansi ORDER BY id DESC");

echo '<table border="1" width="100%" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Instansi</th>
                <th>Alamat</th>
                <th>Telp</th>
                <th>Email</th>
                <th>Website</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
while ($row = $result->fetch_assoc()) {
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

echo '</tbody></table>';
?>
