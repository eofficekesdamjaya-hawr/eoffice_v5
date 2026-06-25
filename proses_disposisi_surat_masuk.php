<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/koneksi.php";

/* =========================
   ANTI DOUBLE SUBMIT
========================= */
if (!isset($_SESSION['last_submit'])) {
    $_SESSION['last_submit'] = 0;
}

if (time() - $_SESSION['last_submit'] < 2) {
    die("Duplicate submit blocked");
}

$_SESSION['last_submit'] = time();

/* =========================
   NOTIF FUNCTION (CLEAN CORE)
========================= */
function insert_notifikasi($conn, $id_surat, $to, $from, $pesan, $link) {

    $query = "INSERT INTO notifikasi 
    (id_surat, untuk_role, dari_role, pesan, link, status_baca)
    VALUES (?, ?, ?, ?, ?, 'belum')";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issss", $id_surat, $to, $from, $pesan, $link);
    $stmt->execute();
    $stmt->close();
}

/* =========================
   VALIDASI POST
========================= */
if (!isset($_POST['submit_disposisi_masuk'])) {
    header("Location: ../transaksi/kelola_surat_masuk.php");
    exit;
}

/* =========================
   INPUT
========================= */
$id_surat = (int)$_POST['id_surat'];
$dari_role = strtolower(trim($_POST['dari_role']));
$tujuan_disposisi = strtolower(trim($_POST['tujuan_disposisi']));
$catatan = trim($_POST['catatan_disposisi']);
$signature = $_POST['signature_data'] ?? '';

$tembusan = $_POST['tembusan_pimpinan'] ?? ['tidak_ada'];
$tembusan_string = implode(',', $tembusan);

if ($id_surat <= 0 || !$tujuan_disposisi || !$catatan) {
    die("Data tidak lengkap");
}

/* =========================
   SIGNATURE SAVE
========================= */
$file_ttd = "no_signature.png";

if ($signature) {
    $img = explode(',', $signature);
    if (isset($img[1])) {
        $decoded = base64_decode($img[1]);

        $file_ttd = "ttd_" . $id_surat . "_" . time() . ".png";
        $dir = "../uploads/tanda_tangan/";

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dir . $file_ttd, $decoded);
    }
}

/* =========================
   INSERT DISPOSISI
========================= */
$query = "INSERT INTO disposisi_surat 
(id_surat, dari_role, tujuan_role, tembusan, catatan, tanda_tangan, tgl_disposisi)
VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    "isssss",
    $id_surat,
    $dari_role,
    $tujuan_disposisi,
    $tembusan_string,
    $catatan,
    $file_ttd
);

if (!$stmt->execute()) {
    die("Gagal simpan disposisi");
}

/* =========================
   AMBIL SURAT
========================= */
$get = $conn->prepare("SELECT no_agenda, perihal FROM surat_masuk WHERE id_surat=?");
$get->bind_param("i", $id_surat);
$get->execute();
$data = $get->get_result()->fetch_assoc();

if (!$data) {
    $no_agenda = "-";
    $perihal = "-";
} else {
    $no_agenda = $data['no_agenda'];
    $perihal = $data['perihal'];
}

/* =========================
   LINK NOTIF
========================= */
$link = "../disposisi/disposisi_surat_masuk.php?id=$id_surat";

/* =========================
   PESAN UTAMA
========================= */
$forward = "Disposisi #$id_surat dari " . strtoupper($dari_role);
$back = "Revisi/Respon disposisi #$id_surat dari " . strtoupper($dari_role);

/* =========================
   FLOW ENGINE (RULE BASED)
========================= */

/**
 * RULE FLOW INTI:
 * ruangan -> setum -> tuud -> setum -> wakakesdam -> kakesdam -> spri -> setum -> finish
 */

$role = $dari_role;
$target = $tujuan_disposisi;

/* =========================
   1. FORWARD
========================= */
if ($target !== 'tidak_ada') {
    insert_notifikasi($conn, $id_surat, $target, $role, $forward, $link);
}

/* =========================
   2. REVERSE FLOW (DUA ARAH / REVISI)
   - Setum ⇌ Ruangan
   - Spri ⇌ Wakakesdam
========================= */

$reverse_pairs = [
    'setum' => 'ruangan',
    'ruangan' => 'setum',
    'spri' => 'wakakesdam',
    'wakakesdam' => 'spri'
];

if (isset($reverse_pairs[$role])) {
    $reverse_target = $reverse_pairs[$role];

    insert_notifikasi(
        $conn,
        $id_surat,
        $reverse_target,
        $role,
        $back,
        $link
    );
}

/* =========================
   3. TEMBUSAN (AMAN FILTER)
========================= */
if (!in_array('tidak_ada', $tembusan)) {

    foreach ($tembusan as $t) {

        $t = strtolower(trim($t));

        if ($t && $t !== $role && $t !== $target) {

            insert_notifikasi(
                $conn,
                $id_surat,
                $t,
                $role,
                "[TEMBUSAN] " . $forward,
                $link
            );
        }
    }
}

/* =========================
   FINISH
========================= */
echo "<script>
alert('Disposisi berhasil diproses (Production Mode)');
window.location='../transaksi/kelola_surat_masuk.php';
</script>";

exit;
?>
