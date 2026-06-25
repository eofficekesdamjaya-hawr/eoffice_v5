<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_actions.php
 * ==========================================================
 */

$id = (int)($surat['id_surat'] ?? 0);

$statusProses = trim(
    $surat['status_proses'] ?? ''
);

$sudahTTD =
(
    !empty($surat['file_surat_ttd'])
);
?>

<div class="d-flex flex-wrap gap-1 justify-content-center">

    <!-- DETAIL -->

    <a
        href="detail_surat_masuk.php?id=<?= $id ?>"
        class="btn btn-sm btn-info text-white">

        <i class="bi bi-eye-fill"></i>
        Detail

    </a>

    <!-- VERIFIKASI -->

    <div class="dropdown">

        <button
            class="btn btn-sm btn-primary dropdown-toggle"
            data-bs-toggle="dropdown">

            <i class="bi bi-shield-check"></i>
            Verifikasi

        </button>

        <ul class="dropdown-menu shadow">

            <li>

                <a
                    class="dropdown-item text-success"
                    href="proses_verifikasi.php?id=<?= $id ?>&status=Disetujui">

                    <i class="bi bi-check-circle"></i>
                    Disetujui

                </a>

            </li>

            <li>

                <a
                    class="dropdown-item text-primary"
                    href="proses_verifikasi.php?id=<?= $id ?>&status=Diterima">

                    <i class="bi bi-envelope-check"></i>
                    Diterima

                </a>

            </li>

        </ul>

    </div>

    <!-- DISPOSISI -->

    <a
        href="disposisi_surat_masuk.php?id=<?= $id ?>"
        class="btn btn-sm btn-warning">

        <i class="bi bi-send-fill"></i>
        Disposisi

    </a>

    <!-- TTD -->

    <?php if(!$sudahTTD): ?>

        <a
            href="ttd_surat.php?id=<?= $id ?>"
            class="btn btn-sm btn-success">

            <i class="bi bi-pen-fill"></i>
            TTD

        </a>

    <?php else: ?>

        <a
            href="../uploads/<?= $surat['file_surat_ttd'] ?>"
            target="_blank"
            class="btn btn-sm btn-outline-success">

            <i class="bi bi-file-earmark-check"></i>
            Hasil TTD

        </a>

    <?php endif; ?>

    <!-- HAPUS -->

    <a
        href="hapus_surat_masuk.php?id=<?= $id ?>"
        class="btn btn-sm btn-danger"
        onclick="return confirm(
            'Yakin menghapus surat ini ?'
        )">

        <i class="bi bi-trash-fill"></i>
        Hapus

    </a>

</div>
