<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login_admin.php");
    exit;
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $del = mysqli_query($conn, "DELETE FROM pejabat WHERE id='$id'");
    if($del){
        $_SESSION['sukses'] = "Data pejabat berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data pejabat!";
    }
}
header("Location: master_pejabat.php");
exit;
