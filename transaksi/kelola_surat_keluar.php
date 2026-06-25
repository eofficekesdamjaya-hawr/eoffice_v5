<?php

require_once __DIR__.'/../config/session.php';

require_once "../config/koneksi.php";

require_once "../config/auth_config.php";

require_once "../config/hak_akses.php";  



// SINKRONISASI DENGAN STRUKTUR SESSION ANDA

$id_user     = $_SESSION['id_user'] ?? '';

$nama_user   = $_SESSION['nama_user'] ?? 'Personel Ruangan';

$tipe_akses  = $_SESSION['tipe_akses'] ?? '';



// Menentukan user_role untuk kebutuhan filter (diambil dari tipe_akses session)

$user_role   = $tipe_akses;

$user_email  = $_SESSION['email'] ?? ''; // Jika ada email di session



$ruanganMap = [

    1 => 'Seksi Tuud', 2 => 'Seksi Was', 3 => 'Seksi Dukkes',

    4 => 'Seksi Kesprev', 5 => 'Seksi Renproggar', 6 => 'Seksi Minlogkes',

    7 => 'Seksi Matkes', 8 => 'Seksi Yankes', 9 => 'Gudang Kesrah', 10 => 'SMK Kesdam Jaya'

];



$filter = $_GET['filter'] ?? '';

$additional_query = "";



// Kondisi Logika Penyaringan Surat Komando

if ($filter === 'disposisi') {

    $additional_query = " AND (status_proses = 'Proses Disposisi' OR status_proses = 'Pending')";

} elseif ($filter === 'belum_ttd') {

    $additional_query = " AND status_proses = 'Di terima'";

} elseif ($filter === 'sudah_ttd') {

    $additional_query = " AND status_proses = 'Selesai'";

}



// MENYESUAIKAN ROLE: Jika pimpinan/ruangan membuka halaman utama tanpa filter khusus

if ($user_role === 'pimpinan' && empty($filter)) {

    $additional_query .= " AND (status_proses = 'Proses Disposisi' OR status_proses = 'Pending')";

}



// PROTEKSI DATA: Jika tipe aksesnya ruangan, batasi hanya melihat surat miliknya sendiri

if ($user_role === 'ruangan') {

    $safe_creator = mysqli_real_escape_string($conn, $id_user);

    $additional_query .= " AND created_by = '$safe_creator'";

}



// QUERY DATA SINKRON

$sqlHariIni = "SELECT * FROM surat_keluar WHERE DATE(created_at) = CURDATE() $additional_query ORDER BY id_surat DESC";

$resHariIni = mysqli_query($conn, $sqlHariIni);



$sqlSebelumnya = "SELECT * FROM surat_keluar WHERE DATE(created_at) < CURDATE() $additional_query ORDER BY id_surat DESC";

$resSebelumnya = mysqli_query($conn, $sqlSebelumnya);



function renderBadgeAlur($status) {

    $status_clean = trim(strtolower($status ?? ''));

    switch ($status_clean) {

        case 'pending': return "<span class='badge bg-warning text-dark'><i class='bi bi-clock'></i> Pending</span>";

        case 'di terima':

        case 'diterima': return "<span class='badge bg-info text-white'><i class='bi bi-check-circle'></i> Di terima</span>";

        case 'ditolak': return "<span class='badge bg-danger text-white'><i class='bi bi-x-circle'></i> Ditolak</span>";

        case 'proses disposisi': return "<span class='badge bg-primary text-white'><i class='bi bi-shuffle'></i> Proses Disposisi</span>";

        case 'selesai': return "<span class='badge bg-success text-white'><i class='bi bi-check-all'></i> Selesai</span>";

        default: return "<span class='badge bg-secondary text-white'>".htmlspecialchars($status)."</span>";

    }

}

?>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">



<div class="d-flex bg-light text-dark" style="min-height: 100vh; width: 100%;">



<?php

if ($user_role === 'ruangan' || $user_role === 'stranger') {

    require_once '../ruangan/sidebar.php';

} else {

    require_once '../dashboard/sidebar_admin.php';

}

?>



    <div class="p-4 flex-grow-1" style="overflow-x: hidden;">

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



        <style>

        .table-responsive {
    overflow: visible !important;
}

        .table th { vertical-align: middle; text-align: center; font-size: 0.78rem; text-transform: uppercase; background-color: #1e293b !important; color: #fff; border: 1px solid #334155; }

        .table td { font-size: 0.85rem; vertical-align: top; color: #334155; }

        .section-divider { border-left: 4px solid #0284c7; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }

        .section-divider-old { border-left: 4px solid #64748b; padding-left: 10px; margin-bottom: 15px; font-weight: bold; }

        .dropdown-menu { z-index: 1050 !important; }

        </style>



        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-envelope-arrow-up-fill text-info me-2"></i>Otoritas Pengendali Surat Keluar Kesdam Jaya</h4>

    <small class="text-muted">Log Aktivitas Sebagai: <span class="badge bg-danger text-uppercase"><?= htmlspecialchars($nama_user) ?></span></small>

    <?php if(!empty($filter)): ?>

        <span class="badge bg-primary ms-2"><i class="bi bi-funnel-fill"></i> MODE FILTER: <?= strtoupper(str_replace('_', ' ', $filter)) ?></span>

    <?php endif; ?>

</div>

            </div>



            <div class="d-flex justify-content-start mb-3">

                <a href="?filter=<?= urlencode($filter) ?>" class="btn btn-sm btn-info text-white fw-bold shadow-sm rounded px-3" style="border: 1px solid #0284c7;">

                    <i class="bi bi-arrow-clockwise me-1"></i> SEGARKAN DATA (REFRESH)

                </a>

            </div>



            <div class="section-divider text-info fs-5 mt-2">

                <i class="bi bi-calendar-check-fill me-2"></i>Draf Surat Keluar Masuk Hari Ini

            </div>

           

            <?php displayTableSurat($resHariIni, $user_role, $ruanganMap, $user_email); ?>



            <div class="section-divider-old text-secondary fs-5 mt-4">

                <i class="bi bi-clock-history me-2"></i>Draf Masuk Sebelum Hari Ini (Kemarin / Lampau)

            </div>

           

            <?php displayTableSurat($resSebelumnya, $user_role, $ruanganMap, $user_email); ?>

        </div>

    </div>

</div>



<?php

// DEKLARASI FUNGSI KONTEN UTAMA PRO VERSION

function displayTableSurat($result, $user_role, $ruanganMap, $user_email) {

?>

<div class="card shadow-sm border-0 rounded-3 mb-4 mx-1">

    <div class="card-body p-0 table-responsive">

        <table class="table table-bordered table-hover table-striped align-top mb-0">

            <thead>

                <tr>

                    <th style="width: 1%;">No</th>

                    <th style="width: 12%;">No Agenda<br>Asal Satuan</th>

                    <th style="width: 10%;">No Surat</th>

                    <th style="width: 8%;">Tgl Surat</th>

                    <th style="width: 8%;">Tgl Kirim</th>

                    <th style="width: 8%;">Bentuk / Jenis</th>

                    <th style="width: 8%;">Klasifikasi / Derajat</th>

                    <th style="width: 14%;">Tujuan Disposisi &<br>Tujuan Utama</th>

                    <th style="width: 18%;">Perihal &<br>Tembusan</th>

                    <th style="width: 3%;">File</th>

                    <th style="width: 5%;">Status</th>

                    <th style="width: 10%;">Aksi Komando</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $no = 1;

            if (mysqli_num_rows($result) > 0):

                while ($row = mysqli_fetch_assoc($result)):

                    $asalMap = [

                        'kakesdam_jaya'      => 'Kakesdam Jaya',

                        'wakakesdam_jaya'    => 'Wakakesdam Jaya',

                        'dandenkeslap'       => 'Dandenkeslap',

                        'setum'              => 'Setum',

                        'kasi_tuud'          => 'Kasi Tuud',

                        'kasi_was'           => 'Kasi Was',

                        'kasi_dukkes'        => 'Kasi Dukkes',

                        'kasi_kesprev'       => 'Kasi Kesprev',

                        'kasi_renproggar'    => 'Kasi Renproggar',

                        'kasi_minlogkes'     => 'Kasi Minlogkes',

                        'kasi_matkes'        => 'Kasi Matkes',

                        'kasi_yankes'        => 'Kasi Yankes',

                        'ka_primkop'         => 'Ka Primkop',

                        'kagud_kesrah'       => 'Kagud Kesrah',

                        'pers_tuud'          => 'Pers Tuud',

                        'kaur_infokes'       => 'Kaur Infokes',

                        'kaur_pers'          => 'Kaur Pers',

                        'kaur_log'           => 'Kaur Log',

                        'kaur_dal'           => 'Kaurdal',

                        'kaur_pam'           => 'Kaurpam',

                        'juyar'              => 'Juyar',

                        'paku_kesdam'        => 'Paku Kesdam',

                        'ka_smk_kesdam'      => 'Ka SMK Kesdam Jaya',

                        'persit_kck_ranting5'=> 'Persit',

                        'korpri_kesdam'      => 'Korpri',

                        'spri_pimpinan'      => 'Spri Pimpinan'

                    ];



                    $kode_asal = trim($row['created_by'] ?? '');

                    $asal_ruangan = isset($asalMap[$kode_asal]) ? $asalMap[$kode_asal] : ($row['asal_satuan'] ?? 'Ruangan');

            ?>

                <tr>

                    <td class="text-center fw-bold"><?= $no++ ?></td>

                    <td>

                        <span class="fw-bold text-primary"><?= htmlspecialchars($row['no_agenda'] ?? '-') ?></span><br>

                        <small class="text-muted fw-bold"><?= htmlspecialchars($asal_ruangan) ?></small>

                    </td>

                    <td class="font-monospace text-xs fw-bold text-secondary"><?= htmlspecialchars($row['no_surat'] ?? 'Belum Diisi Setum') ?></td>

                    <td class="text-center"><?= !empty($row['tanggal_surat']) ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>

                    <td class="text-center"><?= !empty($row['tanggal_kirim']) ? date('d-m-Y', strtotime($row['tanggal_kirim'])) : '-' ?></td>

                    <td>

                        <span class="badge bg-secondary text-xs"><?= htmlspecialchars($row['shapes_surat'] ?? $row['bentuk_surat'] ?? 'Fisik') ?></span><br>

                        <small class="text-xs text-secondary"><?= htmlspecialchars($row['jenis_surat'] ?? '-') ?></small>

                    </td>

                    <td>

                        <span class="text-danger fw-bold text-xs d-block"><?= htmlspecialchars($row['klasifikasi_surat'] ?? 'Biasa') ?></span>

                        <span class="text-warning fw-bold text-xs d-block"><?= htmlspecialchars($row['derajat_surat'] ?? 'Biasa') ?></span>

                    </td>

                    <td>

                        <div class="mb-1 text-xs">Disp: <span class="fw-bold text-primary"><?= htmlspecialchars($row['tujuan_disposisi'] ?? '-') ?></span></div>

                        <div class="text-xs">Utama: <span class="fw-bold text-success"><?= htmlspecialchars($row['tujuan_utama'] ?? '-') ?></span></div>

                    </td>

                    <td>

                        <p class="mb-1 fw-bold text-dark text-wrap"><?= htmlspecialchars($row['perihal'] ?? '-') ?></p>

                        <small class="text-muted d-block border-top pt-1 text-xs">Tembusan: <?= htmlspecialchars($row['tembusan'] ?? '-') ?></small>

                    </td>

                    <td class="text-center">

                        <?php if (!empty($row['file_surat'])) : ?>

                            <a href="../uploads/surat_keluar/<?= urlencode($row['file_surat']) ?>" target="_blank" class="btn btn-sm btn-outline-danger p-1"><i class="bi bi-file-pdf fs-5"></i></a>

                        <?php else: ?>

                            <span class="text-muted">-</span>

                        <?php endif; ?>

                    </td>

<td class="text-center"><?= renderBadgeAlur($row['status_proses'] ?? 'Pending') ?></td>
<td>
    <div class="d-flex flex-column gap-1">
        
        <?php 
        // GRUP 1: Otoritas Penuh & Pengendali Utama Komando (Superadmin, Kakesdam, Wakakesdam, Kasi TUUD)
        if (in_array($user_role, ['superadmin', 'kakesdam_jaya', 'wakakesdam_jaya', 'kasi_tuud'])): 
        ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
            <a href="../tte/ttd_surat.php?id=<?= $row['id_surat'] ?>&action=tte" class="btn btn-success btn-sm text-xs fw-bold"><i class="bi bi-pen-fill"></i> TTD Surat</a>
            <a href="../surat_keluar/hapus_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Permanen Berkas?')"><i class="bi bi-trash"></i> Hapus</a>
            
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-boundary="viewport" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/detail_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/riwayat_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                    <li><button type="button" class="dropdown-item text-xs" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_surat'] ?>"><i class="bi bi-pencil"></i> Edit No/Tgl/Surat</button></li>
                </ul>
            </div>

        <?php // GRUP 2: Staf Sekretariat / Pengelola Berkas (Setum & Admin)
        elseif (in_array($user_role, ['setum', 'admin'])): ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
            
            <?php if ($user_email === 'admin@gmail.com'): ?>
                <a href="../surat_keluar/hapus_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm text-xs" onclick="return confirm('Hapus Berkas?')"><i class="bi bi-trash"></i> Hapus</a>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/detail_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/riwayat_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                    <li><button type="button" class="dropdown-item text-xs" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_surat'] ?>"><i class="bi bi-pencil-square"></i> Edit No/Tgl Surat</button></li>
                </ul>
            </div>

        <?php // GRUP 3: Spri Pimpinan (Akses Terbatas, Tidak Ada Tombol TTD & Hapus)
        elseif ($user_role === 'spri_pimpinan'): ?>
            <button type="button" class="btn btn-warning btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#verifModal<?= $row['id_surat'] ?>"><i class="bi bi-shield-check"></i> Verifikasi</button>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-shuffle"></i> Disposisi</a>
            
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100 text-xs" type="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">Menu Lainnya</button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/detail_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-eye"></i> Detail Surat</a></li>
                    <li><a class="dropdown-item text-xs" href="../surat_keluar/riwayat_surat_keluar.php?id=<?= $row['id_surat'] ?>"><i class="bi bi-clock-history"></i> Riwayat Surat</a></li>
                </ul>
            </div>

        <?php // GRUP DEFAULT: Ruangan / User Lain (Hanya bisa revisi/jawab)
        else: ?>
            <a href="../disposisi/disposisi_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-reply-all-fill"></i> Jawab / Revisi</a>
        <?php endif; ?>

    </div>
</td>

                </tr>

            <?php

                endwhile;

            else:

            ?>

                <tr>

                    <td colspan="12" class="text-center py-4 text-muted bg-white">Belum ada rekaman log draf berkas surat keluar.</td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php

}

?>