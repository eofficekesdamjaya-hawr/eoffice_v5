<?php
/**
 * ==========================================================
 * E-OFFICE KESDAM JAYA V5
 * PARTIAL : surat_row.php
 * ==========================================================
 */

$status = trim(
    $row['status_proses'] ?? ''
);

$role = trim(
    $row['current_role'] ?? 'setum'
);

$sudahTTD =
(
    !empty($row['file_surat_ttd'])
);

$badgeStatus = 'secondary';

switch($status){

    case 'Disetujui':
        $badgeStatus='success';
        break;

    case 'Diterima':
        $badgeStatus='primary';
        break;

    default:
        $badgeStatus='secondary';
}

?>

<tr>

    <!-- NO -->

    <td class="text-center">

        <?= $no++ ?>

    </td>

    <!-- SUMBER -->

    <td class="text-center">

        <span class="badge bg-dark">

            <?= strtoupper(
                $row['role_pengirim']
                ?? '-'
            ) ?>

        </span>

    </td>

    <!-- NOMOR SURAT -->

    <td>

        <div class="fw-bold">

            <?= htmlspecialchars(
                $row['no_surat']
                ?? '-'
            ) ?>

        </div>

        <small class="text-muted">

            Agenda :
            <?= htmlspecialchars(
                $row['no_agenda']
                ?? '-'
            ) ?>

        </small>

    </td>

    <!-- ASAL SURAT -->

    <td>

        <?= htmlspecialchars(
            $row['asal_surat']
            ?? '-'
        ) ?>

    </td>

    <!-- PERIHAL -->

    <td>

        <?= htmlspecialchars(
            $row['perihal']
            ?? '-'
        ) ?>

    </td>

    <!-- STATUS -->

    <td class="text-center">

        <span class="badge bg-<?= $badgeStatus ?>">

            <?= $status ?: '-' ?>

        </span>

    </td>

    <!-- ROLE SAAT INI -->

    <td class="text-center">

        <span class="badge bg-info">

            <?= strtoupper($role) ?>

        </span>

    </td>

    <!-- TANGGAL -->

    <td class="text-center">

        <?php if(!empty($row['tanggal_diterima'])): ?>

            <?= date(
                'd-m-Y',
                strtotime(
                    $row['tanggal_diterima']
                )
            ) ?>

        <?php else: ?>

            -

        <?php endif; ?>

    </td>

    <!-- TTD -->

    <td class="text-center">

        <?php if($sudahTTD): ?>

            <span class="badge bg-success">

                Sudah TTD

            </span>

        <?php else: ?>

            <span class="badge bg-warning text-dark">

                Belum TTD

            </span>

        <?php endif; ?>

    </td>

    <!-- AKSI -->

    <td>

        <?php

        $surat = $row;

        include __DIR__ . '/surat_actions.php';

        ?>

    </td>

</tr>
