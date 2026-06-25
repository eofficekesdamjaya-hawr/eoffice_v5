<?php
session_start();
include "../config/koneksi.php";

$id = $_GET['id'];

mysqli_query($conn,"DELETE FROM user_internal WHERE id='$id'");
header("Location: master_user_internal.php");
exit;