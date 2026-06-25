<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'tipe_akses' => $_SESSION['tipe_akses'] ?? 'kosong',
    'role' => $_SESSION['role'] ?? 'kosong',
    'role_key' => $_SESSION['role_key'] ?? 'kosong'
]);
