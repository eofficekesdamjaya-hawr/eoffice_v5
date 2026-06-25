<?php

session_start();
require_once "../config/koneksi.php";

$id_user = (int)$_SESSION['id_user'];

$draw = intval($_POST['draw']);
$start = intval($_POST['start']);
$length = intval($_POST['length']);
$search = $_POST['search']['value'] ?? '';

$where = "WHERE created_by=?";

$params = [$id_user];
$types = "i";

if(!empty($search))
{
    $where .= "
    AND (
        no_agenda LIKE ?
        OR no_surat LIKE ?
        OR perihal LIKE ?
        OR tujuan_utama LIKE ?
    )";

    $s = "%{$search}%";

    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;

    $types .= "ssss";
}

$totalQuery = $conn->prepare("
SELECT COUNT(*) total
FROM surat_keluar
WHERE created_by=?
");

$totalQuery->bind_param("i",$id_user);
$totalQuery->execute();

$totalRecords =
$totalQuery
->get_result()
->fetch_assoc()['total'];

$filteredQuery = $conn->prepare("
SELECT COUNT(*) total
FROM surat_keluar
$where
");

$filteredQuery->bind_param($types,...$params);
$filteredQuery->execute();

$totalFiltered =
$filteredQuery
->get_result()
->fetch_assoc()['total'];

$sql = "
SELECT *
FROM surat_keluar
$where
ORDER BY id_surat DESC
LIMIT ?,?
";

$paramsData = $params;
$paramsData[] = $start;
$paramsData[] = $length;

$typesData = $types."ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($typesData,...$paramsData);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

$nomor = $start + 1;

while($row = $result->fetch_assoc())
{

$status = '<span class="badge bg-secondary">'
        .$row['status_dokumen']
        .'</span>';

$fileBtn = '';

if(!empty($row['file_surat']))
{
    $fileBtn =
    '<a target="_blank"
    class="btn btn-primary btn-sm"
    href="../uploads/'.urlencode($row['file_surat']).'">
    File
    </a>';
}

$aksi='

'.$fileBtn.'

<a href="edit_surat_keluar.php?id='.$row['id_surat'].'"
class="btn btn-warning btn-sm">
Edit
</a>

<a href="kirim_surat_keluar.php?id='.$row['id_surat'].'"
class="btn btn-info btn-sm">
Kirim
</a>

<a href="print_surat_keluar.php?id='.$row['id_surat'].'"
class="btn btn-secondary btn-sm">
Print
</a>

<a href="export_surat_keluar.php?id='.$row['id_surat'].'"
class="btn btn-success btn-sm">
Export
</a>

<a
onclick="return confirm(\'Yakin hapus?\')"
href="hapus_surat_keluar.php?id='.$row['id_surat'].'"
class="btn btn-danger btn-sm">
Hapus
</a>

';

$data[] = [

'no'=>$nomor++,

'no_agenda'=>$row['no_agenda'],

'arsip'=>

$row['asal_satuan']
.' / '.
$row['kode_arsip']

.'<br><b>'.

($row['no_surat'] ?: 'SETUM')

.'</b>',

'jenis'=>

$row['bentuk_surat']
.' / '.
$row['jenis_surat'],

'klasifikasi'=>

$row['klasifikasi_surat']
.' / '.
$row['derajat_surat'],

'tujuan'=>$row['tujuan_utama'] ?: '-',

'perihal'=>$row['perihal'],

'status'=>$status,

'aksi'=>$aksi

];

}

echo json_encode([

"draw"=>$draw,

"recordsTotal"=>$totalRecords,

"recordsFiltered"=>$totalFiltered,

"data"=>$data

]);
