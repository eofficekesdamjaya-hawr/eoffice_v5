<?php
require_once "../config/session.php";
require_once "../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// 1. Validasi Parameter ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo "<script>alert('ID Surat tidak valid!'); window.location.href='../transaksi/kelola_surat_keluar.php';</script>";
    exit();
}

// 2. Proteksi Hak Akses Berdasarkan Role Session
$user_role = strtolower($_SESSION['tipe_akses'] ?? ''); 
$akses_diizinkan = ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya'];

if (!in_array($user_role, $akses_diizinkan)) {
    echo "<script>alert('Anda tidak memiliki otoritas (Akses Ditolak) untuk menandatangani surat ini!'); window.location.href='../transaksi/kelola_surat_keluar.php';</script>";
    exit();
}

// 3. Ambil Data Surat Keluar dari Database
$querySurat = "SELECT file_surat, status_proses FROM surat_keluar WHERE id_surat = ?";
$stmtSurat  = $conn->prepare($querySurat);
$stmtSurat->bind_param("i", $id);
$stmtSurat->execute();
$resultSurat = $stmtSurat->get_result()->fetch_assoc();
$stmtSurat->close();

if (!$resultSurat) {
    echo "<script>alert('Data berkas draf surat keluar tidak ditemukan!'); window.location.href='../transaksi/kelola_surat_keluar.php';</script>";
    exit();
}

// 4. Sinkronisasi Status dan Lokasi File PDF
$status_proses = trim(strtolower($resultSurat['status_proses'] ?? ''));
$is_ttd        = ($status_proses === 'selesai') ? true : false;

// Ambil data email dari session
$user_email = $_SESSION['email'] ?? ''; 

// Logika penentuan label, gambar, dan text badge berdasarkan email yang login
if ($user_email === 'wakakesdamjaya2026@gmail.com') {
    $label_title = "TTD Asli Wakakesdam Jaya (Preview)";
    $label_desc  = "Preview tanda tangan resmi Wakakesdam.";
    $img_preview = "../assets/ttd_wakakesdam_asli.png"; 
    $text_badge  = "Penempatan TTD Wakakesdam";
} else {
    $label_title = "TTD Asli Kakesdam Jaya (Preview)";
    $label_desc  = "Preview tanda tangan resmi Komando.";
    $img_preview = "../assets/ttd_kakesdam_asli.png";
    $text_badge  = "Penempatan TTD Kakesdam";
}

$nama_file     = $resultSurat['file_surat'] ?? '';
$file_found    = false;
$pdf_preview   = '';

if (!empty($nama_file)) {
    $path_file = "../uploads/surat_keluar/" . $nama_file;
    if (file_exists($path_file)) {
        $file_found  = true;
        $pdf_preview = $path_file;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Tangan Digital - Kesdam Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
    body {
        background: #eef2f7;
    }
    .main-card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
    }
    #signature-pad {
        width: 100%;
        height: 220px;
        border: 2px dashed #ced4da;
        border-radius: 14px;
        background: #fff;
        touch-action: none;
    }
    .btn {
        border-radius: 10px;
    }
    .loading-spinner {
        display: none;
    }

    .document-container-wrapper {
        position: relative;
        background: #6c757d;
        padding: 20px;
        display: flex;
        justify-content: center;
        overflow-x: auto;
    }
    .pdf-render-canvas-container {
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        background: #ffffff;
        line-height: 0;
        display: inline-block;
    }
    #pdf-render-canvas {
        max-width: 100%;
        height: auto;
        display: block;
    }

    /* KOTAK DRAGGABLE TANDA TANGAN */
    .drag-component {
        position: absolute;
        cursor: move;
        z-index: 100;
        user-select: none;
        box-sizing: border-box;
    }
    #drag-ttd {
        border: 2px dashed #0d6efd;
        background: rgba(13, 110, 253, 0.15);
        width: 150px;
        height: 60px;
        display: none;
    }
    #drag-ttd::after {
        content: "<?= $text_badge ?>";
        position: absolute;
        top: -22px;
        left: 0;
        background: #0d6efd;
        color: #fff;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    #ttd-preview-canvas {
        width: 100%;
        height: 100%;
        opacity: 0.85;
        pointer-events: none;
    }

    /* KOTAK DRAGGABLE STEMPEL */
    #drag-stempel {
        border: 2px dashed #dc3545;
        background: rgba(220, 53, 69, 0.15);
        width: 140px;
        height: 70px;
        display: none;
    }
    #drag-stempel::after {
        content: "Stempel Kakesdam";
        position: absolute;
        top: -22px;
        left: 0;
        background: #dc3545;
        color: #fff;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    #stempel-preview-img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        opacity: 0.85;
        pointer-events: none;
    }

    /* KOTAK DRAGGABLE QR CODE */
    #drag-qr {
        border: 2px dashed #495057;
        background: rgba(73, 80, 87, 0.15);
        width: 75px;
        height: 75px;
        display: none;
    }
    #drag-qr::after {
        content: "QR Code";
        position: absolute;
        top: -22px;
        left: 0;
        background: #495057;
        color: #fff;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    #qr-preview-img {
        width: 100%;
        height: 100%;
        opacity: 0.85;
        pointer-events: none;
    }
    </style>
</head>
<body>

<div class="main-content p-4">
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bi bi-vector-pen text-success me-2"></i>
            Tanda Tangan Surat Digital (Otoritas Kakesdam)
        </h4>
        <small class="text-muted">Mode Surat Keluar: Geser komponen ke posisi penandatanganan dokumen resmi</small>
    </div>
    <a href="../transaksi/kelola_surat_keluar.php" class="btn btn-secondary shadow-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card main-card shadow-sm mb-3">
            <div class="card-header bg-warning text-dark py-2 px-3 fw-bold d-flex align-items-center justify-content-between">
                <span><i class="bi bi-arrows-move me-2"></i> GESER KOMPONEN DI HALAMAN TERAKHIR</span>
                <span id="page-info" class="badge bg-dark">Memuat Halaman...</span>
            </div>
            <div class="card-body p-0">
                <?php if($file_found): ?>
                    <div class="document-container-wrapper">
                        <div class="pdf-render-canvas-container" id="pdf-container">
                            <canvas id="pdf-render-canvas"></canvas>

                            <div id="drag-ttd" class="drag-component">
                                <canvas id="ttd-preview-canvas"></canvas>
                            </div>
                            <div id="drag-stempel" class="drag-component">
                                <img id="stempel-preview-img" src="../assets/stempel_kesdam1.png" alt="Stempel">
                            </div>
                            <div id="drag-qr" class="drag-component">
                                <img id="qr-preview-img" src="../assets/qr_dummy.png" alt="QR Code">
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column justify-content-center align-items-center text-center p-5" style="height:60vh;">
                        <i class="bi bi-file-earmark-x text-danger" style="font-size:90px;"></i>
                        <h4 class="fw-bold text-danger mt-4">File PDF Tidak Ditemukan</h4>
                        <small class="text-muted">Pastikan draf dokumen fisik sudah diupload di folder uploads/surat_keluar/</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card main-card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-pen-fill text-success me-2"></i> Ruang Panel Tanda Tangan
                    </h6>
                    <?= $is_ttd ? '<span class="badge bg-success signature-status"><i class="bi bi-patch-check-fill"></i> SUDAH TTD</span>' : '<span class="badge bg-warning text-dark signature-status"><i class="bi bi-clock-history"></i> BELUM TTD</span>' ?>
                </div>
            </div>
            <div class="card-body">
                <?php if($is_ttd): ?>
                    <div class="alert alert-success border-0 shadow-sm">
                        <div class="d-flex">
                            <i class="bi bi-check-circle-fill fs-3 me-2"></i>
                            <div>
                                <div class="fw-bold">Dokumen Sudah Ditandatangani</div>
                                <small>Tanda tangan resmi Kakesdam telah diterapkan di dokumen surat keluar ini.</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="<?= htmlspecialchars($pdf_preview) ?>" target="_blank" class="btn btn-success">
                            <i class="bi bi-eye-fill me-1"></i> Lihat Hasil PDF
                        </a>
                        <a href="hapus_ttd_aksi.php?id=<?= $id ?>&jenis=keluar" class="btn btn-outline-danger" onclick="return confirm('Hapus tanda tangan ini dan ulangi?')">
                            <i class="bi bi-arrow-repeat me-1"></i> Tanda Tangan Ulang
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="proses_ttd_surat_pdf.php" id="formTTD">
                        <input type="hidden" name="id_surat" value="<?= $id ?>">
                        <input type="hidden" name="jenis_tabel" value="keluar">
                        <input type="hidden" name="signature_data" id="signature_data">

                        <input type="hidden" name="pos_x_ttd" id="pos_x_ttd">
                        <input type="hidden" name="pos_y_ttd" id="pos_y_ttd">
                        <input type="hidden" name="pos_x_stempel" id="pos_x_stempel">
                        <input type="hidden" name="pos_y_stempel" id="pos_y_stempel">
                        <input type="hidden" name="pos_x_qr" id="pos_x_qr">
                        <input type="hidden" name="pos_y_qr" id="pos_y_qr">
                        <input type="hidden" name="canvas_width" id="canvas_width">
                        <input type="hidden" name="canvas_height" id="canvas_height">

                        <div class="mb-3">
                            <label class="fw-bold small text-uppercase mb-2 d-block">Goreskan Tanda Tangan Anda</label>
                            <canvas id="signature-pad"></canvas>
                        </div>

                        <div class="card border-success shadow-sm mb-3"> 
                            <div class="card-header bg-success text-white py-2"> 
                                <i class="bi bi-pen-fill me-1"></i> <?= $label_title ?> 
                            </div> 
                            <div class="card-body text-center"> 
                                <img src="<?= $img_preview ?>" alt="Preview TTD" style="max-width:100%; height:90px; object-fit:contain;"> 
                                <small class="text-muted d-block mt-2"><?= $label_desc ?></small> 
                            </div> 
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="clear-signature">
                                <i class="bi bi-trash"></i> Bersihkan Coretan
                            </button>
                            <button type="button" class="btn btn-warning btn-sm fw-bold text-dark" id="lock-pad-to-screen">
                                <i class="bi bi-patch-check"></i> 1. Kunci & Tampilkan di Dokumen
                            </button>
                        </div>

                        <div class="alert alert-info small py-2 mb-3">
                            <i class="bi bi-info-circle-fill"></i> Langkah: Coret TTD &rarr; Klik tombol kuning &rarr; Geser komponen pada PDF di kiri &rarr; Klik Simpan.
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm" id="btnSubmit" disabled>
                            <span class="normal-text"><i class="bi bi-save2-fill me-1"></i> 2. Simpan & Terapkan Dokumen</span>
                            <span class="loading-spinner"><span class="spinner-border spinner-border-sm me-2"></span>Menyimpan Data...</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

const canvasPad = document.getElementById('signature-pad');
let signaturePad;

// Objek penyimpan koordinat default asli bawaan user
let defaultPositions = { ttd: {x:0, y:0}, stempel: {x:0, y:0}, qr: {x:0, y:0} };

if (canvasPad) {
    signaturePad = new SignaturePad(canvasPad, {
        backgroundColor: 'rgba(0,0,0,0)',
        penColor: 'rgb(0,0,128)',
        velocityFilterWeight: 0.7,
        minWidth: 1.5,
        maxWidth: 3.5
    });

    function resizeSignatureCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvasPad.width = canvasPad.offsetWidth * ratio;
        canvasPad.height = canvasPad.offsetHeight * ratio;
        canvasPad.getContext("2d").scale(ratio, ratio);
        signaturePad.clear();
    }

    resizeSignatureCanvas();
    window.addEventListener("resize", resizeSignatureCanvas);

    document.getElementById('clear-signature').addEventListener('click', function() {
        signaturePad.clear();
        document.getElementById('drag-ttd').style.display = 'none';
        document.getElementById('drag-stempel').style.display = 'none';
        document.getElementById('drag-qr').style.display = 'none';
        document.getElementById('btnSubmit').disabled = true;
    });

    document.getElementById('lock-pad-to-screen').addEventListener('click', function() {
        if (signaturePad.isEmpty()) {
            alert('Silakan coret tanda tangan terlebih dahulu!');
            return false;
        }

        const previewCanvas = document.getElementById('ttd-preview-canvas');
        const pCtx = previewCanvas.getContext('2d');

        const img = new Image();
        img.src = signaturePad.toDataURL('image/png');
        img.onload = function() {
            previewCanvas.width = 150;
            previewCanvas.height = 60;
            pCtx.clearRect(0, 0, 150, 60);
            pCtx.drawImage(img, 0, 0, 150, 60);

            document.getElementById('drag-ttd').style.display = 'block';
            document.getElementById('drag-stempel').style.display = 'block';
            document.getElementById('drag-qr').style.display = 'block';

            document.getElementById('btnSubmit').disabled = false;

            // Eksekusi penempatan koordinat asli di bawah dekat TTD halaman terakhir
            initPosition('drag-ttd', defaultPositions.ttd.x, defaultPositions.ttd.y);
            initPosition('drag-stempel', defaultPositions.stempel.x, defaultPositions.stempel.y);
            initPosition('drag-qr', defaultPositions.qr.x, defaultPositions.qr.y);
        };
    });
}

/* RENDER ENGINE PDF.JS UNTUK AREA INTERAKTIF DRAG */
const pdfUrl = <?= json_encode($pdf_preview) ?>;

if (pdfUrl && pdfUrl.trim() !== '') {
    pdfjsLib.getDocument({
        url: pdfUrl
    }).promise
    .then(function(pdf) {
        const lastPage = pdf.numPages;
        document.getElementById('page-info').textContent = "Halaman Terakhir: " + lastPage + " dari " + lastPage;
        return pdf.getPage(lastPage);
    })
    .then(function(page) {
        const scale = 1.3;
        const viewport = page.getViewport({ scale });
        const renderCanvas = document.getElementById('pdf-render-canvas');
        const context = renderCanvas.getContext('2d');

        renderCanvas.width = viewport.width;
        renderCanvas.height = viewport.height;
        renderCanvas.style.width = viewport.width + 'px';
        renderCanvas.style.height = viewport.height + 'px';

        const container = document.getElementById('pdf-container');
        container.style.width = viewport.width + 'px';
        container.style.height = viewport.height + 'px';

        const canvasWidthInput = document.getElementById('canvas_width');
        const canvasHeightInput = document.getElementById('canvas_height');

        if (canvasWidthInput) canvasWidthInput.value = viewport.width;
        if (canvasHeightInput) canvasHeightInput.value = viewport.height;

        // Hitung rumus koordinat asli bawaan user tepat setelah kontainer PDF siap dideteksi tingginya
        defaultPositions.ttd = { x: container.clientWidth / 2 - 40, y: container.clientHeight - 180 };
        defaultPositions.stempel = { x: container.clientWidth / 2 - 120, y: container.clientHeight - 200 };
        defaultPositions.qr = { x: container.clientWidth / 2 - 140, y: container.clientHeight - 160 };

        return page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
    })
    .then(function() {
        console.log('PDF berhasil dirender');
    })
    .catch(function(error) {
        console.error('Gagal render PDF:', error);
        document.getElementById('page-info').textContent = 'Gagal memuat PDF';
        alert('PDF gagal dimuat: ' + error.message);
    });
}

/* CORE ENGINE: LOGIKA DRAG & DROP KOMPONEN */
function initPosition(elementId, x, y) {
    const el = document.getElementById(elementId);
    el.style.left = x + 'px';
    el.style.top = y + 'px';
    updateCoordinateInputs(elementId, x, y);
}

function makeElementDraggable(elementId) {
    const el = document.getElementById(elementId);
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    el.onmousedown = dragMouseDown;
    el.ontouchstart = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        if (e.type !== 'touchstart') e.preventDefault();

        pos3 = (e.type === 'touchstart') ? e.touches[0].clientX : e.clientX;
        pos4 = (e.type === 'touchstart') ? e.touches[0].clientY : e.clientY;

        document.onmouseup = closeDragElement;
        document.ontouchend = closeDragElement;
        document.onmousemove = elementDrag;
        document.ontouchmove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        let clientX = (e.type === 'touchmove') ? e.touches[0].clientX : e.clientX;
        let clientY = (e.type === 'touchmove') ? e.touches[0].clientY : e.clientY;

        pos1 = pos3 - clientX;
        pos2 = pos4 - clientY;
        pos3 = clientX;
        pos4 = clientY;

        let newX = el.offsetLeft - pos1;
        let newY = el.offsetTop - pos2;

        const container = document.getElementById('pdf-container');
        if (newX < 0) newX = 0;
        if (newY < 0) newY = 0;
        if (newX + el.offsetWidth > container.clientWidth) newX = container.clientWidth - el.offsetWidth;
        if (newY + el.offsetHeight > container.clientHeight) newY = container.clientHeight - el.offsetHeight;

        el.style.left = newX + "px";
        el.style.top = newY + "px";

        updateCoordinateInputs(elementId, newX, newY);
    }

    function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
        document.ontouchend = null;
        document.ontouchmove = null;
    }
}

function updateCoordinateInputs(elementId, x, y) {
    if (elementId === 'drag-ttd') {
        document.getElementById('pos_x_ttd').value = x;
        document.getElementById('pos_y_ttd').value = y;
    } else if (elementId === 'drag-stempel') {
        document.getElementById('pos_x_stempel').value = x;
        document.getElementById('pos_y_stempel').value = y;
    } else if (elementId === 'drag-qr') {
        document.getElementById('pos_x_qr').value = x;
        document.getElementById('pos_y_qr').value = y;
    }
}

makeElementDraggable('drag-ttd');
makeElementDraggable('drag-stempel');
makeElementDraggable('drag-qr');

/* SUBMIT INTERCEPTOR FORM */
document.getElementById('formTTD').addEventListener('submit', function(e) {
    document.getElementById('signature_data').value = signaturePad.toDataURL('image/png');
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.querySelector('.normal-text').style.display = 'none';
    btn.querySelector('.loading-spinner').style.display = 'inline-flex';
});
</script>
</body>
</html>