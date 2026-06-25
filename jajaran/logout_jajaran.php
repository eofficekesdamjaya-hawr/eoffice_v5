<?php
session_start();
require_once "../config/koneksi.php";

/*
========================================
UPDATE STATUS OFFLINE
========================================
*/
if(isset($_SESSION['id_user'])){

    $id_user = (int) $_SESSION['id_user'];

    $update = $conn->prepare("
        UPDATE users 
        SET is_online = 0
        WHERE id = ?
    ");

    $update->bind_param("i", $id_user);
    $update->execute();
}

/*
========================================
HAPUS SESSION
========================================
*/
$_SESSION = [];

session_unset();
session_destroy();

/*
========================================
HAPUS COOKIE SESSION
========================================
*/
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/*
========================================
ANTI CACHE
========================================
*/
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/*
========================================
REDIRECT LOGIN
========================================
*/
header("Location: ../auth/login_jajaran.php");
exit();
?>