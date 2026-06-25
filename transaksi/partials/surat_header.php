<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_header.php
 * ==========================================================
 */

require_once "../../config/session.php";

/* ==========================================================
   KONEKSI DATABASE
========================================================== */

require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../responsive_config.php';

/* ==========================================================
   VALIDASI LOGIN
========================================================== */

if (!isset($_SESSION['id_user'])) {

    header("Location: ../auth/login_admin.php");
    exit;
}

/* ==========================================================
   DATA USER LOGIN
========================================================== */

$id_user      = $_SESSION['id_user'] ?? 0;
$nama_user    = $_SESSION['nama_user'] ?? '';
$username     = $_SESSION['username'] ?? '';
$tipe_akses   = strtolower(trim($_SESSION['tipe_akses'] ?? 'guest'));
$jabatan      = $_SESSION['jabatan'] ?? '';
$foto_user    = $_SESSION['foto'] ?? '';

/* ==========================================================
   DAFTAR ROLE SISTEM V5
========================================================== */

$ROLE_ADMIN         = ['admin','superadmin'];
$ROLE_SETUM         = ['setum'];
$ROLE_KASI_TUUD     = ['kasi_tuud'];
$ROLE_KAKESDAM      = ['kakesdam_jaya'];
$ROLE_WAKAKESDAM    = ['wakakesdam_jaya'];
$ROLE_SPRI          = ['spri_pimpinan'];

/* ==========================================================
   HAK AKSES
========================================================== */

$isAdmin       = in_array($tipe_akses, $ROLE_ADMIN);
$isSetum       = in_array($tipe_akses, $ROLE_SETUM);
$isKasiTuud    = in_array($tipe_akses, $ROLE_KASI_TUUD);
$isKakesdam    = in_array($tipe_akses, $ROLE_KAKESDAM);
$isWakakesdam  = in_array($tipe_akses, $ROLE_WAKAKESDAM);
$isSpri        = in_array($tipe_akses, $ROLE_SPRI);

/* ==========================================================
   ROLE AKTIF
========================================================== */

$current_role = 'guest';

if($isAdmin){
    $current_role = 'admin';
}
elseif($isSetum){
    $current_role = 'setum';
}
elseif($isKasiTuud){
    $current_role = 'kasi_tuud';
}
elseif($isKakesdam){
    $current_role = 'kakesdam_jaya';
}
elseif($isWakakesdam){
    $current_role = 'wakakesdam_jaya';
}
elseif($isSpri){
    $current_role = 'spri_pimpinan';
}

/* ==========================================================
   NAMA ROLE UNTUK TAMPILAN
========================================================== */

$role_label = match($current_role){

    'admin'            => 'Administrator',
    'setum'            => 'Setum',
    'kasi_tuud'        => 'Kasi TUUD',
    'kakesdam_jaya'    => 'Kakesdam Jaya',
    'wakakesdam_jaya'  => 'Wakakesdam Jaya',
    'spri_pimpinan'    => 'Spri Pimpinan',

    default            => 'User'
};

/* ==========================================================
   DASHBOARD TUJUAN
========================================================== */

$dashboard_url = '../dashboard/dashboard_admin.php';

switch($current_role){

    case 'kakesdam_jaya':
        $dashboard_url = '../dashboard_KakesdamJaya/dashboard_KakesdamJaya.php';
        break;

    case 'wakakesdam_jaya':
        $dashboard_url = '../dashboard_WakaKesdamJaya/dashboard_WakaKesdamJaya.php';
        break;

    case 'kasi_tuud':
        $dashboard_url = '../dashboard_KasiTuud/dashboard_KasiTuud.php';
        break;

    case 'spri_pimpinan':
        $dashboard_url = '../dashboard_Spri_Pimpinan/dashboard_Spri_Pimpinan.php';
        break;

    case 'setum':
    case 'admin':
    default:
        $dashboard_url = '../dashboard/dashboard_admin.php';
        break;
}

/* ==========================================================
   FILTER GLOBAL
========================================================== */

$filter = $_GET['filter'] ?? 'semua';

$search = trim($_GET['search'] ?? '');

$tanggal_awal  = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

$status_filter = $_GET['status'] ?? '';

/* ==========================================================
   ESCAPE SEARCH
========================================================== */

$search_sql = mysqli_real_escape_string(
    $conn,
    $search
);

/* ==========================================================
   INFO HARI INI
========================================================== */

$today = date('Y-m-d');

$today_text = date('d F Y');

/* ==========================================================
   NOTIFIKASI SESSION
========================================================== */

$notif = $_SESSION['notif'] ?? null;

/* ==========================================================
   INCLUDE HEADER TEMPLATE
========================================================== */

include __DIR__ . '/../../layout/header.php';
?>
