<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'satuan_lain') {
    header("Location: ../auth/login_satuan_lain.php");
    exit;
}

$id_user = $_SESSION['id_user'];

if(!isset($_GET['id'])){
    die("ID surat tidak ditemukan");
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("
SELECT file_surat 
FROM surat_masuk 
WHERE id_surat=? AND id_user=? AND role_pengirim='satuan_lain'
");

$stmt->bind_param("ii",$id,$id_user);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Surat tidak ditemukan");
}

$data = $result->fetch_assoc();

$file = "../uploads/".$data['file_surat'];

if(!file_exists($file)){
    die("File tidak ditemukan");
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=".$data['file_surat']);

readfile($file);
exit;