<?php
require_once "../config/session.php";
require_once '../config/koneksi.php';

// ============================
// PROTEKSI LOGIN
// ============================
if (!isset($_SESSION['id_user']) || $_SESSION['tipe_akses'] !== 'jajaran') {
    header("Location: ../auth/login_jajaran.php");
    exit;
}

// ============================
// DATA LOGIN
// ============================
$id_user  = $_SESSION['id_user'];
$id_surat = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$error_msg   = "";
$success_msg = "";

// ============================
// FUNCTION
// ============================
function clean($data)
{
    return htmlspecialchars(trim($data));
}

// ============================
// AMBIL DATA SURAT
// HANYA BOLEH EDIT STATUS PENDING
// ============================
$query = "
    SELECT *
    FROM surat_masuk
    WHERE id_surat = ?
    AND id_user = ?
    AND status_proses = 'Pending'
";

$stmt = $conn->prepare($query);

if (!$stmt) {

    die("Query error : " . $conn->error);
}

$stmt->bind_param("ii", $id_surat, $id_user);
$stmt->execute();

$result = $stmt->get_result();
$data   = $result->fetch_assoc();

// ============================
// VALIDASI DATA
// ============================
if (!$data) {

    echo "
    <script>
        alert('Data tidak ditemukan atau surat sudah diproses!');
        window.location='surat_terkirim_jajaran.php';
    </script>
    ";

    exit;
}

// ============================
// PROSES UPDATE
// ============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $no_surat   = clean($_POST['no_surat']);
    $perihal    = clean($_POST['perihal']);
    $kepada     = clean($_POST['kepada']);
    $keterangan = clean($_POST['keterangan']);

    // ============================
    // VALIDASI NOMOR SURAT DUPLIKAT
    // ============================
    $cek = $conn->prepare("
        SELECT id_surat
        FROM surat_masuk
        WHERE no_surat = ?
        AND id_surat != ?
    ");

    $cek->bind_param("si", $no_surat, $id_surat);
    $cek->execute();

    if ($cek->get_result()->num_rows > 0) {

        $error_msg = "Nomor surat sudah digunakan!";

    } else {

        // ============================
        // DEFAULT FILE LAMA
        // ============================
        $new_name = $data['file_surat'];

        // ============================
        // JIKA GANTI FILE
        // ============================
        if (
            isset($_FILES['file_surat']) &&
            $_FILES['file_surat']['error'] === 0
        ) {

            $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

            $file = $_FILES['file_surat'];

            $ext = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            // Validasi format
            if (!in_array($ext, $allowed_ext)) {

                $error_msg = "Format file tidak diizinkan!";

            }
            // Validasi ukuran 100MB
            elseif ($file['size'] > 100 * 1024 * 1024) {

                $error_msg = "Ukuran file maksimal 100MB!";

            } else {

                // Hapus file lama
                if (
                    !empty($data['file_surat']) &&
                    file_exists("../uploads/" . $data['file_surat'])
                ) {

                    unlink("../uploads/" . $data['file_surat']);
                }

                // Generate nama file baru
                $new_name = "JAJ_EDIT_" . time() . "_" . uniqid() . "." . $ext;

                // Upload file baru
                move_uploaded_file(
                    $file['tmp_name'],
                    "../uploads/" . $new_name
                );
            }
        }

        // ============================
        // UPDATE DATABASE
        // ============================
        if (!isset($error_msg)) {

            $update = $conn->prepare("
                UPDATE surat_masuk
                SET
                    no_surat  = ?,
                    perihal   = ?,
                    kepada    = ?,
                    keterangan= ?,
                    file_surat= ?
                WHERE id_surat = ?
                AND id_user = ?
            ");

            if (!$update) {

                $error_msg = "Query update gagal : " . $conn->error;

            } else {

                $update->bind_param(
                    "sssssii",
                    $no_surat,
                    $perihal,
                    $kepada,
                    $keterangan,
                    $new_name,
                    $id_surat,
                    $id_user
                );

                if ($update->execute()) {

                    echo "
                    <script>
                        alert('Surat berhasil diperbarui!');
                        window.location='surat_terkirim_jajaran.php';
                    </script>
                    ";

                    exit;

                } else {

                    $error_msg = "Gagal update surat!";
                }
            }
        }
    }
}

include '../layout/header.php';
?>

<div class="container py-4">

    <div class="card shadow border-0">

        <div class="card-header bg-warning py-3">

            <h5 class="mb-0 fw-bold">
                <i class="fas fa-edit me-2"></i>
                Edit Surat Terkirim Jajaran
            </h5>

        </div>

        <div class="card-body">

            <?php if($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_msg) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="row">

                    <!-- NOMOR SURAT -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Nomor Surat
                        </label>

                        <input type="text"
                               name="no_surat"
                               class="form-control"
                               value="<?= htmlspecialchars($data['no_surat']) ?>"
                               required>

                    </div>

                    <!-- TUJUAN -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">
                            Tujuan / Kepada
                        </label>

                        <select name="kepada"
                                class="form-select"
                                required>

                            <option value="<?= htmlspecialchars($data['kepada']) ?>">
                                <?= htmlspecialchars($data['kepada']) ?>
                            </option>

                            <option disabled>──────────</option>

                            <option>Spri Pimpinan</option>
                            <option>Kakesdam Jaya</option>
                            <option>Waka Kesdam Jaya</option>
                            <option>Kasi Tuud</option>
                            <option>Dandenkeslap</option>
                            <option>Kaprimkop</option>
                            <option>Kasi Matkes</option>
                            <option>Kasi Yankes</option>
                            <option>Kasi Minlogkes</option>
                            <option>Kasi Was</option>
                            <option>Kasi Kesprev</option>
                            <option>Kasi Dukkes</option>
                            <option>Kasi Renproggar</option>
                            <option>KagudKesrah</option>
                            <option>Kaur Infokes</option>
                            <option>Paku Kesdam</option>
                            <option>Korpri</option>
                            <option>Persit</option>

                        </select>

                    </div>

                </div>

                <!-- PERIHAL -->
                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Perihal
                    </label>

                    <textarea name="perihal"
                              class="form-control"
                              rows="3"
                              required><?= htmlspecialchars($data['perihal']) ?></textarea>

                </div>

                <!-- KETERANGAN -->
                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Keterangan Tambahan
                    </label>

                    <textarea name="keterangan"
                              class="form-control"
                              rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>

                </div>

                <!-- FILE -->
                <div class="mb-4">

                    <label class="form-label fw-bold">
                        Ganti Lampiran File
                    </label>

                    <input type="file"
                           name="file_surat"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png">

                    <div class="form-text text-danger small mt-2">

                        <i class="fas fa-info-circle me-1"></i>

                        Kosongkan jika tidak ingin mengganti file.
                        Maksimal ukuran file 100MB.

                    </div>

                    <?php if(!empty($data['file_surat'])): ?>

                        <small class="text-muted d-block mt-2">

                            File saat ini :

                            <a href="../uploads/<?= $data['file_surat'] ?>"
                               target="_blank">

                                <?= htmlspecialchars($data['file_surat']) ?>

                            </a>

                        </small>

                    <?php endif; ?>

                </div>

                <!-- BUTTON -->
                <div class="d-flex gap-2">

                    <button type="submit"
                            class="btn btn-primary px-4 rounded-pill">

                        <i class="fas fa-save me-2"></i>
                        Update Surat

                    </button>

                    <a href="surat_terkirim_jajaran.php"
                       class="btn btn-outline-secondary px-4 rounded-pill">

                        <i class="fas fa-arrow-left me-2"></i>
                        Batal

                    </a>

                </div>

            </form>

        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>