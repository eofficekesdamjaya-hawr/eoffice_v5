<?php
session_start();
require_once "../config/koneksi.php";

// 1. Proteksi Akses: Hanya user 'ruangan' yang bisa akses
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'ruangan') {
    header("Location: ../auth/login_ruangan.php");
    exit();
}

// 2. Ambil ID Surat dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'ID Surat tidak valid.'];
    header("Location: kelola_surat_masuk.php");
    exit();
}

$id_surat = intval($_GET['id']);
$role_user = $_SESSION['nama_role']; // Role ruangan saat ini (misal: 'Kasi Yankes')

/* 3. PROSES HAPUS (Logical/Physical Delete)
   Kita hanya menghapus baris di tabel disposisi yang ditujukan ke ruangan ini.
   Ini memastikan ruangan lain yang mendapat disposisi surat yang sama tidak kehilangan datanya.
*/

$stmt = $conn->prepare("DELETE FROM disposisi_surat_masuk WHERE id_surat_masuk = ? AND untuk_role = ?");
$stmt->bind_param("is", $id_surat, $role_user);

if ($stmt->execute()) {
    // Jika baris yang terhapus > 0
    if ($stmt->affected_rows > 0) {
        $_SESSION['notif'] = [
            'type' => 'success', 
            'message' => 'Surat berhasil dihapus dari daftar masuk ruangan Anda.'
        ];
    } else {
        $_SESSION['notif'] = [
            'type' => 'warning', 
            'message' => 'Data tidak ditemukan atau Anda tidak memiliki akses menghapus surat ini.'
        ];
    }
} else {
    $_SESSION['notif'] = [
        'type' => 'danger', 
        'message' => 'Gagal menghapus data: ' . $conn->error
    ];
}

$stmt->close();
$conn->close();

// 4. Kembali ke halaman utama
header("Location: kelola_surat_masuk.php");
exit();