<?php
session_start();

// 1. Hapus semua data session
$_SESSION = [];

// 2. Jika menggunakan cookie session, hapus juga cookie-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session secara total
session_destroy();

// 4. Arahkan kembali ke halaman login satuan lain dengan pesan sukses
// Gunakan JavaScript agar bisa menampilkan pesan alert sebelum pindah halaman
echo "<script>
        alert('Anda telah berhasil keluar dari sistem.');
        window.location.href = '../auth/login_satuan_lain.php';
      </script>";
exit;
?>