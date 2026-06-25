<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";

// 1. Ambil Parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<script>alert('ID Surat Keluar Tidak Valid!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
    exit;
}

// 2. Query Data (Harus di atas agar $data tersedia)
$query = "SELECT * FROM surat_keluar WHERE id_surat = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data Surat Keluar Tidak Ditemukan!'); window.location='../transaksi/kelola_surat_keluar.php';</script>";
    exit;
}

// 3. Sekarang baru include auth_config (Variabel $dari akan terisi oleh sesi user)
require_once "../config/auth_config.php";

// 4. Tambahkan logika untuk Asal Satuan Surat (Bukan Jabatan User)
// Kita menggunakan $jabatanMap yang sudah ada di dalam auth_config.php
$raw_asal = $data['asal_satuan'] ?? 'Ruangan';
$asal_ruangan = isset($jabatanMap[strtolower($raw_asal)]) ? $jabatanMap[strtolower($raw_asal)] : ucwords(str_replace('_', ' ', $raw_asal));

// 5. Integrasi Path File
$base_upload_url = "../uploads/surat_keluar/"; 
$file_path = !empty($data['file_surat']) ? $base_upload_url . $data['file_surat'] : "";

include '../layout/header.php';
?>

<style>
#signature-pad {
    border: 2px dashed #ccc;
    border-radius: 8px;
    background: #f9f9f9;
    cursor: crosshair;
    max-width: 100%;
}
.preview-container {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.preview-frame {
    width: 100%;
    height: 600px;
    border: none;
}
.preview-img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 4px;
}
</style>

<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-send-check-fill text-warning me-2"></i>Form Disposisi Surat Keluar Berjenjang
            </h4>
            <p class="text-muted small">ID Surat Keluar: #<?= $id ?> | Alur Validasi Server</p>
        </div>
        <a href="../transaksi/kelola_surat_keluar.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> File Lampiran / Draft Surat
                </div>
                <div class="card-body">
                    <?php if (!empty($file_path) && file_exists($file_path)): ?>
                        <?php $ext = strtolower(pathinfo($data['file_surat'], PATHINFO_EXTENSION)); ?>
                        <div class="preview-container p-2 mb-3">
                            <?php if ($ext === 'pdf'): ?>
                                <iframe src="<?= $file_path ?>#toolbar=0" class="preview-frame"></iframe>
                            <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                <a href="<?= $file_path ?>" target="_blank">
                                    <img src="<?= $file_path ?>" class="preview-img" alt="Draft Surat Keluar">
                                </a>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-file-earmark-zip fs-1 text-warning"></i>
                                    <p class="mb-0 mt-2 font-monospace" style="font-size:12px;"><?= htmlspecialchars($data['file_surat']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?= $file_path ?>" target="_blank" class="btn btn-sm btn-dark fw-semibold w-100">
                                    <i class="bi bi-fullscreen me-1"></i> Buka Penuh Tab Baru
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary fw-semibold w-100">
                                    <i class="bi bi-download me-1"></i> Unduh Berkas
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border border-secondary-subtle text-center py-5 my-2">
                            <i class="bi bi-file-earmark-x fs-2 text-muted d-block mb-2"></i>
                            <span class="text-muted small d-block">Draft berkas digital tidak ditemukan.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">Meta Data Surat Keluar</div>
                <div class="card-body small">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block mb-1">No Agenda Surat:</label>
                            <span class="fw-bold fs-6 text-dark"><?= htmlspecialchars($data['no_agenda'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block mb-1">Kepada (Tujuan Keluar):</label>
                            <span class="fw-bold d-block text-primary fs-6"><?= htmlspecialchars($data['kepada'] ?? $data['tujuan_utama'] ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted d-block mb-1">Perihal:</label>
                        <p class="fw-bold text-dark mb-0"><?= htmlspecialchars($data['perihal'] ?? '-') ?></p>
                    </div>
                    <div class="row">

<div class="col-md-6 mb-3">
    <label class="text-muted d-block mb-1">Asal Satuan Konseptor:</label>
    <span class="fw-semibold d-block text-uppercase text-secondary"><?= htmlspecialchars($asal_ruangan) ?></span>
</div>

                        <div class="col-md-6 mb-3">
                            <label class="text-muted d-block mb-1">Klasifikasi / Derajat:</label>
                            <div>
                                <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($data['klasifikasi_surat'] ?? 'BIASA') ?></span>
                                <span class="badge bg-dark text-uppercase"><?= htmlspecialchars($data['derajat_surat'] ?? 'BIASA') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="bi bi-pencil-square me-1"></i> Aksi Lembar Disposisi Keluar
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="proses_disposisi_surat_keluar.php" id="formDisposisi">
                        <input type="hidden" name="id_surat" value="<?= $id ?>">
                        <input type="hidden" name="dari_role" value="<?= htmlspecialchars($role) ?>">
                        <input type="hidden" name="signature_data" id="signature_data">
                        <input type="hidden" name="tujuan_utama" id="tujuan_utama">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Disposisi Selaku Jabatan:</label>
                            <input type="text" class="form-control bg-light fw-bold text-uppercase text-danger" value="<?= htmlspecialchars($dari) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <?php
                            $is_final_distribusi = (strtolower($data['status_proses'] ?? '') == 'selesai' && strtolower($role) == 'setum');
                            ?>
                            <label class="form-label fw-bold small text-primary">
                                <?= $is_final_distribusi ? 'Distribusikan Surat Keluar ke:' : 'Disposisi Pimpinan ke:' ?>
                            </label>

                            <select name="disposisi_pimpinan" class="form-select border-primary-subtle" required>
    <?php if ($is_final_distribusi): ?>
        <option value="">-- Pilih Distribusi Surat Keluar --</option>
        <option value="kasi_tuud">Kasi Tuud</option>
        <option value="kasi_was">Kasi Was</option>
        <option value="kasi_dukkes">Kasi Dukkes</option>
        <option value="kasi_kesprev">Kasi Kesprev</option>
        <option value="kasi_renproggar">Kasi Renproggar</option>
        <option value="kasi_minlogkes">Kasi Minlogkes</option>
        <option value="kasi_matkes">Kasi Matkes</option>
        <option value="kasi_yankes">Kasi Yankes</option>
        <option value="ruangan_pengirim">Ruangan Pengirim</option>
        <option value="arsip">Arsip Setum</option>
    <?php elseif ($is_admin_setum): ?>
        <option value="">-- Pilih Tujuan Disposisi --</option>
        <option value="tidak_ada">Tidak Ada</option>
        <option value="kakesdam_jaya">Kakesdam Jaya</option>
        <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
        <option value="dandenkeslap">Dandenkeslap</option>
        <option value="kasi_tuud">Kasi Tuud</option>
        <option value="kasi_was">Kasi Was</option>
        <option value="kasi_dukkes">Kasi Dukkes</option>
        <option value="kasi_kesprev">Kasi Kesprev</option>
        <option value="kasi_renproggar">Kasi Renproggar</option>
        <option value="kasi_minlogkes">Kasi Minlogkes</option>
        <option value="kasi_matkes">Kasi Matkes</option>
        <option value="kasi_yankes">Kasi Yankes</option>
        <option value="ka_primkop">Ka Primkop</option>
        <option value="kagud_kesrah">Kagud Kesrah</option>
        <option value="pers_tuud">Pers Tuud</option>
        <option value="kaur_infokes">Kaur Infokes</option>
        <option value="kaur_pers">Kaur Pers</option>
        <option value="kaur_log">Kaur Log</option>
        <option value="kaur_dal">Kaurdal</option>
        <option value="kaur_pam">Kaurpam</option>
        <option value="juyar">Juyar</option>
        <option value="paku_kesdam">Paku Kesdam</option>
        <option value="ka_smk_kesdam">Ka SMK Kesdam Jaya</option>
        <option value="persit_kck_ranting5">Persit</option>
        <option value="korpri_kesdam">Korpri</option>
    <?php elseif (strtolower($role) === 'wakakesdam_jaya'): ?>
        <option value="kakesdam_jaya">Kakesdam Jaya (ACC / Teruskan Berjenjang)</option>
        <option value="spri_pimpinan">Spri Pimpinan</option>
    <?php elseif (strtolower($role) === 'kakesdam_jaya'): ?>
        <option value="spri_pimpinan">Spri Pimpinan</option>

<?php elseif (strtolower($role) === 'spri_pimpinan' || strtolower($role) === 'spri'): ?>
        <option value="setum">Setum / Admin</option>
    <?php elseif (strtolower($role) === 'kasi_tuud' || strtolower($role) === 'kasituud'): ?>
        <option value="">-- Pilih Tujuan Disposisi --</option>
        <option value="setum">Kembalikan ke Setum / Admin (Lanjut Berjenjang)</option>
    <?php elseif (strtolower($role) === 'ruangan' || strtolower($_SESSION['tipe_akses'] ?? '') === 'ruangan' || strtolower($_SESSION['id_user'] ?? '') !== ''): ?>
        <option value="">-- Pilih Tujuan Pengembalian --</option>
        <option value="setum" selected>Kirim ke Setum / Admin (Proses Validasi)</option>
    <?php endif; ?>

</select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-primary">Tembusan ke (Unit Terkait):</label>
                            <?php if ($is_admin_setum): ?>
                                <?php
                                $asal_lower = strtolower($data['asal_surat'] ?? '');
                                $is_internal = (strpos($asal_lower, 'ruangan') !== false || strpos($asal_lower, 'internal') !== false);
                                ?>
                                <?php if ($is_internal): ?>
                                    <div class="p-2 border bg-light rounded text-muted small">
                                        <i class="bi bi-info-circle-fill me-1 text-info"></i> Asal surat dari Internal/Ruangan. Tembusan otomatis kosong saat proses pimpinan.
                                    </div>
                                    <select name="disposisi_tembusan[]" class="form-select bg-light" style="display:none;" multiple>
                                        <option value="tidak_ada" selected>Tidak Ada</option>
                                    </select>
                                <?php else: ?>
                                    <select name="disposisi_tembusan[]" class="form-select border-primary-subtle" multiple style="height:130px;">
                                        <option value="tidak_ada" selected>-- Tidak Ada Tembusan (-) --</option>
                                        <option value="kakesdam_jaya">Kakesdam Jaya</option>
                                        <option value="wakakesdam_jaya">Wakakesdam Jaya</option>
                                    <option value="dandenkeslap">Dandenkeslap</option>
                                    <option value="kasi_tuud">Kasi Tuud</option>
                                    <option value="kasi_was">Kasi Was</option>
                                    <option value="kasi_dukkes">Kasi Dukkes</option>
                                    <option value="kasi_kesprev">Kasi Kesprev</option>
                                    <option value="kasi_renproggar">Kasi Renproggar</option>
                                    <option value="kasi_minlogkes">Kasi Minlogkes</option>
                                    <option value="kasi_matkes">Kasi Matkes</option>
                                    <option value="kasi_yankes">Kasi Yankes</option>
                                    <option value="ka_primkop">Ka Primkop</option>
                                    <option value="kagud_kesrah">Kagud Kesrah</option>
                                    <option value="pers_tuud">Pers Tuud</option>
                                    <option value="kaur_infokes">Kaur Infokes</option>
                                    <option value="kaur_pers">Kaur Pers</option>
                                    <option value="kaur_log">Kaur Log</option>
                                    <option value="kaur_dal">Kaurdal</option>
                                    <option value="kaur_pam">Kaurpam</option>
                                    <option value="juyar">Juyar</option>
                                    <option value="paku_kesdam">Paku Kesdam</option>
                                    <option value="ka_smk_kesdam">Ka SMK Kesdam Jaya</option>
                                    <option value="persit_kck_ranting5">Persit</option>
                                    <option value="korpri_kesdam">Korpri</option>
                                    </select>
                                <?php endif; ?>
                            <?php elseif ($role == 'spri_pimpinan' && htmlspecialchars($data['status_proses'] ?? '') == 'Selesai'): ?>
                                <select name="disposisi_tembusan[]" style="display:none;" multiple>
                                    <option value="kasi_tuud" selected>Kasi Tuud</option>
                                    <option value="ruangan_pengirim" selected>Ruangan Pengirim</option>
                                </select>
                            <?php else: ?>
                                <select name="disposisi_tembusan[]" class="form-select bg-light" multiple style="height:45px;" readonly>
                                    <option value="tidak_ada" selected>Otomatisasi Sistem Terkunci</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-primary">Catatan / Instruksi Jabatan</label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Masukkan instruksi..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark"><i class="bi bi-pencil-fill me-1"></i> Tanda Tangan Resmi Disposisi</label>
                            <div class="text-center bg-light p-2 rounded border">
                                <canvas id="signature-pad" width="320" height="150"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" id="clear-signature">
                                        <i class="bi bi-eraser-fill"></i> Hapus Tanda Tangan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="submit_disposisi" class="btn btn-warning fw-bold py-2.5 shadow-sm text-dark">
                                <i class="bi bi-send-fill me-2"></i> PROSES & TERUSKAN DISPOSISI
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
const canvas = document.getElementById('signature-pad');
const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgba(255,255,255,0)',
    penColor: 'rgb(0,0,0)'
});

document.getElementById('clear-signature').addEventListener('click', function() {
    signaturePad.clear();
});

const tujuanSelect = document.querySelector('[name="disposisi_pimpinan"]');
const tujuanUtama = document.getElementById('tujuan_utama');

// Set inisialisasi awal nilai input tersembunyi
if(tujuanSelect && tujuanUtama) {
    tujuanUtama.value = tujuanSelect.value;
    tujuanSelect.addEventListener('change', function () {
        tujuanUtama.value = this.value;
    });
}

document.getElementById('formDisposisi').addEventListener('submit', function(e) {
    if(!signaturePad.isEmpty()){
        const dataUrl = signaturePad.toDataURL('image/png');
        document.getElementById('signature_data').value = dataUrl;
    } else {
        alert('Tanda tangan wajib diisi sebagai bukti pengesahan disposisi!');
        e.preventDefault();
    }
});
</script>
<?php include '../layout/footer.php'; ?>
