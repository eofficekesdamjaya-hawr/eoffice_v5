<?php
require_once "../config/session.php";
include '../layout/header.php';
?>

<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4"><i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Export Data Terpadu</h4>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Pilih Kriteria Export</h6>
                    <form action="proses_export.php" method="GET">
                        <div class="mb-3">
                            <label class="form-label">Jenis Data</label>
                            <select name="jenis" class="form-select" required>
                                <option value="surat_masuk">Surat Masuk</option>
                                <option value="surat_keluar">Surat Keluar</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label">Dari</label>
                                <input type="date" name="tgl_awal" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Sampai</label>
                                <input type="date" name="tgl_akhir" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download me-1"></i> Download Excel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>