<?php
session_start();
// Menghapus semua data session (karcis masuk)
session_destroy();
// Mengarahkan kembali ke halaman pintu depan (portal)
header("Location: index.php");
exit;
?>
