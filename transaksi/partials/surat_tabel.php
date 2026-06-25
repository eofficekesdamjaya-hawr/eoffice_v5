<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_tabel.php
 * ==========================================================
 */

if (!isset($conn)) {
    die("Database belum terkoneksi.");
}

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

$where = " WHERE 1=1 ";

if (!empty($_GET['cari'])) {

    $cari = mysqli_real_escape_string(
        $conn,
        $_GET['cari']
    );

    $where .= "
        AND (
            no_surat LIKE '%$cari%'
            OR asal_surat LIKE '%$cari%'
            OR perihal LIKE '%$cari%'
        )
    ";
}

if (!empty($_GET['sumber'])) {

    $sumber = mysqli_real_escape_string(
        $conn,
        $_GET['sumber']
    );

    $where .= "
        AND role_pengirim='$sumber'
    ";
}

if (!empty($_GET['tanggal'])) {

    $tanggal = mysqli_real_escape_string(
        $conn,
        $_GET['tanggal']
    );

    $where .= "
        AND DATE(tanggal_diterima)='$tanggal'
    ";
}

/*
|--------------------------------------------------------------------------
| QUERY DATA
|--------------------------------------------------------------------------
*/

$sql = "
SELECT *
FROM surat_masuk
$where
ORDER BY id_surat DESC
";

$query = mysqli_query($conn, $sql);
?>

<div class="card shadow-sm border-0">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">
            <i class="bi bi-envelope-paper"></i>
            Data Surat Masuk
        </h5>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark text-center">

                    <tr>
                        <th width="60">No</th>
                        <th>Sumber</th>
                        <th>No Surat</th>
                        <th>Asal Surat</th>
                        <th>Perihal</th>
                        <th>Status</th>
                        <th>Role Saat Ini</th>
                        <th>Tanggal</th>
                        <th>TTD</th>
                        <th width="220">Aksi</th>
                    </tr>

                </thead>

                <tbody>

                <?php if(mysqli_num_rows($query) > 0): ?>

                    <?php
                    $no = 1;

                    while($row = mysqli_fetch_assoc($query)):

                        include __DIR__ . '/surat_row.php';

                    endwhile;
                    ?>

                <?php else: ?>

                    <tr>
                        <td colspan="10" class="text-center p-4">
                            Tidak ada data surat.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
