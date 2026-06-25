<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

if (!isset($_SESSION['role'])) {
    exit("Unauthorized");
}

$role = $_SESSION['role'];

// Ambil ID Surat jika ada untuk spesifik, jika tidak ada maka update semua milik role tersebut
if (isset($_GET['id'])) {
    $id_surat = intval($_GET['id']);
    $where_clause = "AND id_surat_masuk = '$id_surat'";
} else {
    $where_clause = "";
}

// Update status baca di tabel disposisi
$conn->query("UPDATE disposisi_surat_masuk 
              SET status_baca = 'sudah' 
              WHERE untuk_role = '$role' 
              AND status_baca = 'belum' $where_clause");

// Update status baca di tabel tembusan
$conn->query("UPDATE tembusan_surat_masuk 
              SET status_baca = 'sudah' 
              WHERE untuk_role = '$role' 
              AND status_baca = 'belum' $where_clause");

// Kembali ke dashboard
header("Location: ../dashboard/dashboard_admin.php");
exit();
?>