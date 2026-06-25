<tr>
    <td class="text-center"><?= $no++ ?></td>
    <td>
        <strong class="text-primary"><?= htmlspecialchars($row['no_agenda']) ?></strong><br>
        <small class="text-muted"><?= htmlspecialchars($row['asal_satuan']) ?></small>
    </td>
    <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_input'])) ?></td>
    <td class="text-center"><?= $row['no_surat'] ? htmlspecialchars($row['no_surat']) : '<span class="badge bg-warning text-dark">Draft Setum</span>' ?></td>
    <td class="text-center"><?= $row['tanggal_surat'] ? date('d-m-Y', strtotime($row['tanggal_surat'])) : '-' ?></td>
    <td class="text-center"><?= $row['tanggal_kirim'] ? date('d-m-Y', strtotime($row['tanggal_kirim'])) : '-' ?></td>
    <td>
        <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['bentuk_surat']) ?></span><br>
        <small class="fw-bold"><?= htmlspecialchars($row['jenis_surat']) ?></small>
    </td>
    <td>
        <?php 
        $klas = $row['klasifikasi_surat'];
        $badge = ($klas == 'Rahasia' || $klas == 'Sangat Rahasia') ? 'bg-danger' : 'bg-success';
        ?>
        <span class="badge <?= $badge ?>"><?= htmlspecialchars($klas) ?></span><br>
        <small class="text-muted"><?= htmlspecialchars($row['derajat_surat'] ?? 'Biasa') ?></small>
    </td>
    <td>
        <small class="fw-bold text-success">Disp: <?= htmlspecialchars($row['tujuan_disposisi']) ?></small><br>
        <small class="text-muted">Target: <?= htmlspecialchars($row['tujuan_utama']) ?></small>
    </td>
    <td>
        <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($row['perihal']) ?>">
            <?= htmlspecialchars($row['perihal']) ?>
        </div>
        <small class="text-muted font-monospace d-block" style="font-size: 0.75rem;">Ada Tembusan</small>
    </td>
    <td class="text-center">
        <a href="../uploads/surat_keluar/<?= htmlspecialchars($row['file_surat']) ?>" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-1" title="Lihat PDF">
            <i class="fas fa-file-pdf">file</i>
        </a>
    </td>
    <td class="text-center">
        <?php if ($row['status_proses'] === 'Baru'): ?>
            <span class="badge bg-info text-dark">Menunggu Setum</span>
        <?php elseif ($row['status_proses'] === 'Ditolak'): ?>
            <span class="badge bg-danger">Ditolak Setum</span>
        <?php else: ?>
            <span class="badge bg-secondary"><?= htmlspecialchars($row['status_proses']) ?></span>
        <?php endif; ?>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm" role="group">
            <a href="detail_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-info text-white" title="Detail"><i class="fas fa-eye"></i>detail</a>
            <a href="riwayat_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-warning text-white" title="Riwayat Log"><i class="fas fa-history"></i>riwayat</a>
            
            <?php if ($row['status_proses'] === 'Baru' || $row['status_proses'] === 'Ditolak'): ?>
                <a href="edit_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-primary" title="Edit"><i class="fas fa-edit"></i>edit</a>
                <a href="hapus_surat_keluar.php?id=<?= $row['id_surat'] ?>" class="btn btn-danger" onclick="return confirm('Hapus draft surat ini?')" title="Hapus"><i class="fas fa-trash"></i>Hapus</a>
            <?php endif; ?>

            <a href="print_surat_keluar.php?id=<?= $row['id_surat'] ?>" target="_blank" class="btn btn-secondary" title="Cetak Lembar"><i class="fas fa-print"></i>print</a>
            <a href="export_surat_keluar_pdf.php?id=<?= $row['id_surat'] ?>" class="btn btn-dark" title="Export PDF"><i class="fas fa-file-export"></i>export</a>
        </div>
    </td>
</tr>
