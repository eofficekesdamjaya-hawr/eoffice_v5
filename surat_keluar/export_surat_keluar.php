<?php
session_start();

require_once "../config/koneksi.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (
    empty($_SESSION['id_user']) ||
    $_SESSION['tipe_akses'] !== 'ruangan'
) {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

$id_user  = (int)$_SESSION['id_user'];
$id_surat = (int)($_GET['id'] ?? 0);

/*
|--------------------------------------------------------------------------
| AMBIL DATA SURAT
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT *
FROM surat_keluar
WHERE id_surat = ?
AND created_by = ?
LIMIT 1
");

$stmt->bind_param("ii", $id_surat, $id_user);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Data surat tidak ditemukan.");
}

/*
|--------------------------------------------------------------------------
| HTML PDF
|--------------------------------------------------------------------------
*/

$html = '
<html>
<head>
<meta charset="UTF-8">

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:11px;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    width:220px;
    text-align:left;
    background:#f2f2f2;
}

th,td{
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
}

.footer{
    margin-top:30px;
    text-align:right;
}

</style>

</head>

<body>

<h2>DATA SURAT KELUAR</h2>

<table>

<tr>
<th>No Agenda</th>
<td>'.$data['no_agenda'].'</td>
</tr>

<tr>
<th>Kode Arsip</th>
<td>'.$data['kode_arsip'].'</td>
</tr>

<tr>
<th>Asal Satuan</th>
<td>'.$data['asal_satuan'].'</td>
</tr>

<tr>
<th>No Surat</th>
<td>'.(!empty($data['no_surat']) ? $data['no_surat'] : 'Belum Diisi Setum').'</td>
</tr>

<tr>
<th>Tanggal Surat</th>
<td>'.(!empty($data['tanggal_surat']) ? $data['tanggal_surat'] : '-').'</td>
</tr>

<tr>
<th>Tanggal Kirim</th>
<td>'.(!empty($data['tanggal_kirim']) ? $data['tanggal_kirim'] : '-').'</td>
</tr>

<tr>
<th>Bentuk Surat</th>
<td>'.$data['bentuk_surat'].'</td>
</tr>

<tr>
<th>Jenis Surat</th>
<td>'.$data['jenis_surat'].'</td>
</tr>

<tr>
<th>Klasifikasi Surat</th>
<td>'.$data['klasifikasi_surat'].'</td>
</tr>

<tr>
<th>Derajat Surat</th>
<td>'.$data['derajat_surat'].'</td>
</tr>

<tr>
<th>Tujuan Disposisi</th>
<td>'.$data['tujuan_disposisi'].'</td>
</tr>

<tr>
<th>Tujuan Utama</th>
<td>'.$data['tujuan_utama'].'</td>
</tr>

<tr>
<th>Perihal</th>
<td>'.$data['perihal'].'</td>
</tr>

<tr>
<th>Tembusan</th>
<td>'.nl2br($data['tembusan']).'</td>
</tr>

<tr>
<th>Keterangan</th>
<td>'.nl2br($data['keterangan']).'</td>
</tr>

<tr>
<th>Status Pengiriman</th>
<td>'.$data['status_pengiriman'].'</td>
</tr>

<tr>
<th>Status Proses</th>
<td>'.$data['status_proses'].'</td>
</tr>

<tr>
<th>Status Verifikasi</th>
<td>'.$data['status_verifikasi'].'</td>
</tr>

<tr>
<th>Status TTD</th>
<td>'.$data['status_tte'].'</td>
</tr>

<tr>
<th>Penandatangan</th>
<td>'.$data['penandatangan'].'</td>
</tr>

<tr>
<th>Tanggal TTD</th>
<td>'.(!empty($data['tgl_tte']) ? $data['tgl_tte'] : '-').'</td>
</tr>

<tr>
<th>Dibuat</th>
<td>'.$data['created_at'].'</td>
</tr>

</table>

<div class="footer">
Dicetak : '.date('d-m-Y H:i:s').'
</div>

</body>
</html>
';

/*
|--------------------------------------------------------------------------
| GENERATE PDF
|--------------------------------------------------------------------------
*/

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

/*
|--------------------------------------------------------------------------
| DOWNLOAD
|--------------------------------------------------------------------------
*/

$filename =
"Surat_Keluar_" .
preg_replace('/[^A-Za-z0-9]/','_',$data['no_agenda']) .
".pdf";

$dompdf->stream(
    $filename,
    ["Attachment" => true]
);

exit;
