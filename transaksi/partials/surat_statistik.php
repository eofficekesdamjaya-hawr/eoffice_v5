<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_statistik.php
 * ==========================================================
 */

if (!isset($conn)) {
    die("Koneksi database belum tersedia.");
}

/* ==========================================================
   TOTAL SURAT MASUK
========================================================== */

$totalSuratMasuk = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
"))['total'] ?? 0;

/* ==========================================================
   TOTAL SURAT KELUAR
========================================================== */

$totalSuratKeluar = 0;

if(mysqli_num_rows(mysqli_query($conn,"
    SHOW TABLES LIKE 'surat_keluar'
")) > 0){

    $totalSuratKeluar = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM surat_keluar
    "))['total'] ?? 0;
}

/* ==========================================================
   TOTAL SURAT SEMUA
========================================================== */

$totalSurat = $totalSuratMasuk + $totalSuratKeluar;

/* ==========================================================
   SUMBER SURAT MASUK
========================================================== */

$totalRuangan = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE role_pengirim='ruangan'
"))['total'] ?? 0;

$totalJajaran = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE role_pengirim='jajaran'
"))['total'] ?? 0;

$totalSatuanLain = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE role_pengirim='satuan_lain'
"))['total'] ?? 0;

/* ==========================================================
   BERDASARKAN TUJUAN / ROLE
========================================================== */
$totalKakesdam = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE
        kepada='kakesdam_jaya'
        OR tujuan_disposisi='kakesdam_jaya'
"))['total'] ?? 0;

$totalWakakesdam = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE
        kepada='wakakesdam_jaya'
        OR tujuan_disposisi='wakakesdam_jaya'
"))['total'] ?? 0;

$totalSpri = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE
        kepada='spri_pimpinan'
        OR tujuan_disposisi='spri_pimpinan'
"))['total'] ?? 0;

/* ==========================================================
   SURAT HARI INI
========================================================== */

$totalHariIni = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE DATE(tanggal_diterima)=CURDATE()
"))['total'] ?? 0;

/* ==========================================================
   SURAT SEBELUMNYA
========================================================== */

$totalSebelumnya = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE DATE(tanggal_diterima)<CURDATE()
"))['total'] ?? 0;

/* ==========================================================
   STATUS PROSES
========================================================== */

$totalDisetujui = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE status_proses='Disetujui'
"))['total'] ?? 0;

$totalDiterima = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE status_proses='Diterima'
"))['total'] ?? 0;

$totalSelesai = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM surat_masuk
WHERE file_surat_ttd IS NOT NULL
AND file_surat_ttd<>''
"))['total'] ?? 0;

/* ==========================================================
   TTD DIGITAL
========================================================== */

$totalSudahTTD = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE file_surat_ttd IS NOT NULL
    AND file_surat_ttd <> ''
"))['total'] ?? 0;

$totalBelumTTD = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM surat_masuk
    WHERE file_surat_ttd IS NULL
    OR file_surat_ttd=''
"))['total'] ?? 0;

/* ==========================================================
   DISPOSISI
========================================================== */

$totalDisposisi = 0;

if(mysqli_num_rows(mysqli_query($conn,"
    SHOW TABLES LIKE 'disposisi_surat_masuk'
")) > 0){

    $totalDisposisi = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM disposisi_surat_masuk
    "))['total'] ?? 0;
}

/* ==========================================================
   ARRAY STATISTIK V5
========================================================== */

$STATISTIK_V5 = [

'total_surat'        => $totalSurat,
'total_surat_masuk'  => $totalSuratMasuk,
'total_surat_keluar' => $totalSuratKeluar,

'ruangan'            => $totalRuangan,
'jajaran'            => $totalJajaran,
'satuan_lain'        => $totalSatuanLain,

'hari_ini'           => $totalHariIni,
'sebelumnya'         => $totalSebelumnya,

'disetujui'          => $totalDisetujui,
'diterima'           => $totalDiterima,
'selesai'            => $totalSelesai,

'sudah_ttd'          => $totalSudahTTD,
'belum_ttd'          => $totalBelumTTD,

'disposisi'          => $totalDisposisi
];
