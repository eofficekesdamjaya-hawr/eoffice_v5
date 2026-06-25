<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI LOGIN JAJARAN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

// ============================
// VALIDASI METHOD POST
// ============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kirim_surat_jajaran.php");
    exit;
}

// ============================
// FUNCTION BERSIHKAN INPUT
// ============================
function clean($data)
{
    return htmlspecialchars(trim($data));
}

// ============================
// DATA SESSION
// ============================
$id_jajaran   = $_SESSION['id_user'];
$nama_jajaran = $_SESSION['nama'];

// ============================
// AMBIL DATA FORM
// ============================
$kode               = clean($_POST['kode']);
$no_agenda          = clean($_POST['no_agenda']);
$asal_satuan        = clean($_POST['asal_satuan']);
$nama_pengirim      = clean($_POST['nama_pengirim']);
$jabatan_pengirim   = clean($_POST['jabatan_pengirim']);
$no_hp_pengirim     = clean($_POST['no_hp_pengirim']);

$no_surat           = clean($_POST['no_surat']);
$tanggal_surat      = clean($_POST['tanggal_surat']);

$bentuk_surat       = clean($_POST['bentuk_surat']);
$jenis_surat        = clean($_POST['jenis_surat']);
$klasifikasi        = clean($_POST['klasifikasi_surat']);
$sifat_surat        = clean($_POST['sifat_surat']);

$tujuan_disposisi   = clean($_POST['tujuan_disposisi']);
$kepada             = clean($_POST['kepada']);

$tembusan           = clean($_POST['tembusan']);
$perihal            = clean($_POST['perihal']);
$keterangan         = clean($_POST['keterangan']);

// ============================
// STATUS DEFAULT FLOW
// ============================
$status_dokumen = "Aktif";

$role_pengirim  = "jajaran";

$jenis_pengirim = "jajaran";

$status_proses  = "Dikirim ke Setum";

$posisi_surat   = "Setum";

// ============================
// VALIDASI NOMOR SURAT DUPLIKAT
// ============================
$cek = $conn->prepare("
    SELECT id_surat 
    FROM surat_masuk
    WHERE no_surat = ?
");

$cek->bind_param("s", $no_surat);
$cek->execute();

if ($cek->get_result()->num_rows > 0) {

    $_SESSION['error'] = "Nomor surat sudah pernah digunakan!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

// ============================
// VALIDASI FILE
// ============================
if (
    !isset($_FILES['file_surat']) ||
    $_FILES['file_surat']['error'] != 0
) {

    $_SESSION['error'] = "File surat wajib diupload!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

$file = $_FILES['file_surat'];

$allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// ============================
// VALIDASI EXTENSION
// ============================
if (!in_array($ext, $allowed_ext)) {

    $_SESSION['error'] = "Format file tidak diizinkan!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

// ============================
// VALIDASI UKURAN FILE
// ============================
if ($file['size'] > 2 * 1024 * 1024) {

    $_SESSION['error'] = "Ukuran file maksimal 2MB!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

// ============================
// GENERATE NAMA FILE
// ============================
$new_name = "JAJ_" . time() . "_" . uniqid() . "." . $ext;

$upload_path = "../uploads/" . $new_name;

// ============================
// UPLOAD FILE
// ============================
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {

    $_SESSION['error'] = "Gagal upload file surat!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

// ============================
// SIMPAN DATABASE
// ============================
$sql = "INSERT INTO surat_masuk (

    kode,
    no_agenda,
    no_surat,
    tanggal_diterima,

    asal_surat,
    nama_pengirim,
    jabatan_pengirim,
    no_hp_pengirim,

    perihal,
    tujuan_disposisi,
    kepada,
    tembusan,

    status_dokumen,

    file_surat,

    id_user,

    role_pengirim,
    jenis_pengirim,

    status_proses,
    posisi_surat,
    tanggal_status,

    keterangan,

    bentuk_surat,
    jenis_surat,
    klasifikasi_surat,
    sifat_surat

) VALUES (

    ?, ?, ?, ?,
    ?, ?, ?, ?,
    ?, ?, ?, ?,
    ?,
    ?,
    ?,
    ?,
    ?,
    ?, ?, NOW(),
    ?,
    ?, ?, ?, ?

)";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    $_SESSION['error'] = "Prepare statement gagal!";

    header("Location: kirim_surat_jajaran.php");
    exit;
}

$stmt->bind_param(

    "sssssssssssssisssssssss",

    $kode,
    $no_agenda,
    $no_surat,
    $tanggal_surat,

    $asal_satuan,
    $nama_pengirim,
    $jabatan_pengirim,
    $no_hp_pengirim,

    $perihal,
    $tujuan_disposisi,
    $kepada,
    $tembusan,

    $status_dokumen,

    $new_name,

    $id_jajaran,

    $role_pengirim,
    $jenis_pengirim,

    $status_proses,
    $posisi_surat,

    $keterangan,

    $bentuk_surat,
    $jenis_surat,
    $klasifikasi,
    $sifat_surat
);

// ============================
// EKSEKUSI SIMPAN
// ============================
if ($stmt->execute()) {

    $_SESSION['success'] = "Surat berhasil dikirim ke Setum Kesdam Jaya!";

    header("Location: dashboard_jajaran.php");
    exit;

} else {

    $_SESSION['error'] = "Gagal menyimpan data : " . $stmt->error;

    header("Location: kirim_surat_jajaran.php");
    exit;
}
?>