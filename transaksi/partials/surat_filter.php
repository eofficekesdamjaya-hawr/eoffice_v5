<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_filter.php
 * ==========================================================
 */

$cari           = $_GET['cari'] ?? '';
$sumber         = $_GET['sumber'] ?? '';
$jenis          = $_GET['jenis'] ?? '';
$status         = $_GET['status'] ?? '';
$tanggal_awal   = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir  = $_GET['tanggal_akhir'] ?? '';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-funnel-fill text-primary"></i>
            Filter Surat V5
        </h5>
    </div>

    <div class="card-body">

        <form method="GET">

            <div class="row g-3">

                <!-- Cari -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Cari Surat
                    </label>

                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        value="<?= htmlspecialchars($cari) ?>"
                        placeholder="Nomor surat / perihal / asal surat">
                </div>

                <!-- Jenis Surat -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        Jenis
                    </label>

                    <select name="jenis" class="form-select">

                        <option value="">
                            Semua
                        </option>

                        <option value="masuk"
                            <?= ($jenis=='masuk') ? 'selected' : '' ?>>
                            Surat Masuk
                        </option>

                        <option value="keluar"
                            <?= ($jenis=='keluar') ? 'selected' : '' ?>>
                            Surat Keluar
                        </option>

                    </select>
                </div>

                <!-- Sumber -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        Sumber
                    </label>

                    <select name="sumber" class="form-select">

                        <option value="">
                            Semua
                        </option>

                        <option value="ruangan"
                            <?= ($sumber=='ruangan') ? 'selected' : '' ?>>
                            Ruangan
                        </option>

                        <option value="jajaran"
                            <?= ($sumber=='jajaran') ? 'selected' : '' ?>>
                            Jajaran
                        </option>

                        <option value="satuan_lain"
                            <?= ($sumber=='satuan_lain') ? 'selected' : '' ?>>
                            Satuan Lain
                        </option>

                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        Status
                    </label>

                    <select name="status" class="form-select">

                        <option value="">
                            Semua
                        </option>

                        <option value="Disetujui"
                            <?= ($status=='Disetujui') ? 'selected' : '' ?>>
                            Disetujui
                        </option>

                        <option value="Diterima"
                            <?= ($status=='Diterima') ? 'selected' : '' ?>>
                            Diterima
                        </option>

                        <option value="TTD"
                            <?= ($status=='TTD') ? 'selected' : '' ?>>
                            Sudah TTD
                        </option>

                        <option value="BelumTTD"
                            <?= ($status=='BelumTTD') ? 'selected' : '' ?>>
                            Belum TTD
                        </option>

                    </select>
                </div>

            </div>

            <div class="row mt-3 g-3">

                <!-- Tanggal Awal -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Tanggal Awal
                    </label>

                    <input
                        type="date"
                        name="tanggal_awal"
                        class="form-control"
                        value="<?= $tanggal_awal ?>">
                </div>

                <!-- Tanggal Akhir -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        Tanggal Akhir
                    </label>

                    <input
                        type="date"
                        name="tanggal_akhir"
                        class="form-control"
                        value="<?= $tanggal_akhir ?>">
                </div>

                <!-- Tombol -->
                <div class="col-md-6 d-flex align-items-end gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-search"></i>
                        Filter
                    </button>

                    <a
                        href="kelola_surat.php"
                        class="btn btn-secondary">

                        <i class="bi bi-arrow-clockwise"></i>
                        Reset
                    </a>

                </div>

            </div>

        </form>

    </div>
</div>
