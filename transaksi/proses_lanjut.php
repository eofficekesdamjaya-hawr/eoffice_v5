<?php
// 1. Inisialisasi Sesi & Koneksi Database
require_once "../config/session.php";
require_once '../config/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['username']) && !isset($_SESSION['status'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Tangkap Parameter ID dan Jenis dari URL
$id_surat = isset($_GET['id']) ? intval($_GET['id']) : 0;
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

if ($id_surat === 0 || empty($jenis)) {
    echo "<script>
        alert('Parameter data tidak valid!');
        window.location.href = 'kelola_surat.php';
    </script>";
    exit;
}

// 3. Tentukan Tabel dan Kolom Status Berdasarkan Jenis Surat (Sesuai Struktur DB Anda)
if ($jenis === 'masuk') {
    $tabel = 'surat_masuk';
    $kolom_status = 'status_proses'; // Sesuai kolom di DESC surat_masuk
    $nilai_status = 'Proses';        // Karena enum di DB Anda tipenya: 'Pending','Proses','Selesai','Ditolak'
} else {
    $tabel = 'surat_keluar';
    $kolom_status = 'status_surat';  // Sesuai kolom di SHOW COLUMNS surat_keluar
    $nilai_status = 'Proses';        // Menyesuaikan varchar(50) bawaan surat keluar
}

// Kunci Utama (Primary Key) kedua tabel Anda sudah pasti 'id_surat'
$nama_kolom_pk = 'id_surat';

// 4. Eksekusi Perubahan Status Surat
$query_update = "UPDATE $tabel SET $kolom_status = '$nilai_status' WHERE $nama_kolom_pk = $id_surat";
$eksekusi = mysqli_query($koneksi, $query_update);

// 5. Penanganan Output Hasil Proses
if ($eksekusi) {
    echo "<script>
        alert('Surat Berhasil diproses ke tahap selanjutnya!');
        window.location.href = 'kelola_surat.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal memproses surat: " . mysqli_error($koneksi) . "');
        window.location.href = 'detail_surat.php?id=$id_surat&jenis=$jenis';
    </script>";
}
?>
