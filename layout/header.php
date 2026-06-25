<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "https://eofficekesdamjaya.com";

/*
====================================================
RESPONSIVE CONFIG SAFE LOAD
====================================================
*/
if (file_exists(__DIR__ . "/../config/responsive_config.php")) {
    require_once __DIR__ . "/../config/responsive_config.php";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>E-Office Kesdam Jaya/Jayakarta</title>

    <!-- BOOTSTRAP (ONLY ONCE SAFE LOAD) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- FONT AWESOME (backup dashboard lama Anda) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .topbar {
            background: #0d6efd;
            color: white;
            padding: 12px 10px;
            text-align: center;
        }

        .topbar img {
            height: 55px;
        }
    </style>
</head>

<body>

<div class="topbar">
    <img src="<?= $base_url ?>/assets/img/logo1.png" alt="Logo">
    <div><strong>E-Office Kesdam Jaya/Jayakarta</strong></div>
    <small>Digital Disposisi & Surat Terintegrasi</small>
</div>


<div class="container-fluid py-3">
