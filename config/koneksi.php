<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "eoffice_user2";
$pass = "##Kesdamjaya2026##";
$db   = "eoffice_kesdamjayav5";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$koneksi = $conn;
?>
