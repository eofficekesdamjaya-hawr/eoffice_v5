<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

$id_user = (int)$_SESSION['id_user'];
$filter = $_POST['filter'] ?? 'hari_ini'; // Default ke hari ini

$draw = intval($_POST['draw']);
$start = intval($_POST['start']);
$length = intval($_POST['length']);
$search = $_POST['search']['value'] ?? '';

// Logika Filter Tanggal
$dateCondition = ($filter === 'hari_ini') 
    ? " AND DATE(tgl_surat) = CURDATE() " 
    : " AND DATE(tgl_surat) < CURDATE() ";

$where = "WHERE created_by=? " . $dateCondition;
$params = [$id_user];
$types = "i";

// Pencarian
if(!empty($search)) {
    $where .= " AND (no_agenda LIKE ? OR no_surat LIKE ? OR perihal LIKE ? OR tujuan_utama LIKE ?)";
    $s = "%{$search}%";
    $params = array_merge($params, [$s, $s, $s, $s]);
    $types .= "ssss";
}

// Total Records (Tanpa filter pencarian, tapi sesuai filter tanggal)
$totalQuery = $conn->prepare("SELECT COUNT(*) total FROM surat_keluar WHERE created_by=? " . $dateCondition);
$totalQuery->bind_param("i", $id_user);
$totalQuery->execute();
$totalRecords = $totalQuery->get_result()->fetch_assoc()['total'];

// Filtered Records
$filteredQuery = $conn->prepare("SELECT COUNT(*) total FROM surat_keluar $where");
$filteredQuery->bind_param($types, ...$params);
$filteredQuery->execute();
$totalFiltered = $filteredQuery->get_result()->fetch_assoc()['total'];

// Data Query
$sql = "SELECT * FROM surat_keluar $where ORDER BY id_surat DESC LIMIT ?,?";
$paramsData = array_merge($params, [$start, $length]);
$typesData = $types . "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($typesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$nomor = $start + 1;

while($row = $result->fetch_assoc()) {
    $fileBtn = !empty($row['file_surat']) ? '<a target="_blank" class="btn btn-primary btn-sm" href="../uploads/'.urlencode($row['file_surat']).'">File</a> ' : '';
    
    $aksi = $fileBtn . '
        <a href="edit_surat_keluar.php?id='.$row['id_surat'].'" class="btn btn-warning btn-sm">Edit</a>
        <a href="kirim_surat_keluar.php?id='.$row['id_surat'].'" class="btn btn-info btn-sm">Kirim</a>
        <a href="print_surat_keluar.php?id='.$row['id_surat'].'" class="btn btn-secondary btn-sm">Print</a>
        <a href="export_surat_keluar.php?id='.$row['id_surat'].'" class="btn btn-success btn-sm">Export</a>
        <a onclick="return confirm(\'Yakin hapus?\')" href="hapus_surat_keluar.php?id='.$row['id_surat'].'" class="btn btn-danger btn-sm">Hapus</a>';

    $data[] = [
        'no'            => $nomor++,
        'no_agenda'     => $row['no_agenda'],
        'arsip_no'      => $row['asal_satuan'] . '<br><b>' . ($row['no_surat'] ?: 'SETUM') . '</b>',
        'tgl_data'      => $row['tgl_surat'] . '<br><small>' . $row['tgl_kirim'] . '</small>',
        'bentuk_jenis'  => $row['bentuk_surat'] . '<br>' . $row['jenis_surat'],
        'klas_derajat'  => $row['klasifikasi_surat'] . '<br>' . $row['derajat_surat'],
        'tujuan_full'   => $row['tujuan_utama'] . '<br><small>' . $row['tujuan_disposisi'] . '</small>',
        'perihal'       => $row['perihal'],
        'tembusan'      => $row['tembusan'],
        'status'        => '<span class="badge bg-secondary">' . $row['status_dokumen'] . '</span>',
        'keterangan'    => $row['keterangan'],
        'aksi'          => $aksi
    ];
}

header('Content-Type: application/json');
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => (int)$totalRecords,
    "recordsFiltered" => (int)$totalFiltered,
    "data" => $data
]);
