<?php
$judulHalaman = 'Daftar Hibah Diterima';
cekAkses('kelola_hibah');
$paginasi = query_paginasi("SELECT * FROM hibah_pemerintah", "ORDER BY tanggal_terima DESC", [], '', 15);
$hibah_list = $paginasi['data'];
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Riwayat Penerimaan Hibah</h1>
    <a href="index.php?halaman=hibah/form_hibah" class="btn btn-primary">
        Catat Hibah Baru
    </a>
</div>
<div class="overflow-x-auto bg-base-100 shadow rounded-lg border border-base-200">
    <table class="table w-full">
        <thead class="bg-base-200">
            <tr>
                <th>Tanggal Terima</th>
                <th>Pemberi Hibah</th>
                <th>Keterangan / Aset</th>
                <th class="text-right">Nilai (Rp)</th>
                <th>Jenis SAK</th>
                <th>Status Syarat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($hibah_list)): ?>
                <tr><td colspan="7" class="text-center py-6">Belum ada data hibah yang diterima.</td></tr>
            <?php else: ?>
                <?php foreach ($hibah_list as $item) : ?>
                <tr class="hover border-b border-base-200">
                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_terima']))); ?></td>
                    <td class="font-bold text-primary"><?= htmlspecialchars($item['pemberi_hibah'] ?? '-'); ?></td>
                    <td>
                        <?= htmlspecialchars($item['keterangan']); ?>
                        <div class="text-xs opacity-70 mt-1">Bentuk: <?= htmlspecialchars($item['bentuk_hibah']) ?></div>
                    </td>
                    <td class="text-right font-mono font-bold">Rp <?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                    <td><span class="badge badge-ghost"><?= htmlspecialchars($item['jenis_hibah']); ?></span></td>
                    <td>
                        <?php if ($item['status_syarat'] == 'Terpenuhi'): ?>
                            <span class="badge badge-success">Terpenuhi</span>
                        <?php elseif ($item['status_syarat'] == 'Belum Terpenuhi'): ?>
                            <span class="badge badge-warning">Belum Terpenuhi</span>
                        <?php elseif ($item['status_syarat'] == 'Dikembalikan'): ?>
                            <span class="badge badge-error">Gagal / Dikembalikan</span>
                        <?php else: ?>
                            <span class="badge badge-info">Tidak Relevan</span>
                        <?php endif; ?>
                    </td>
                    <td class="flex gap-2">
                        <?php if ($item['jenis_hibah'] == 'Bersyarat' && $item['status_syarat'] == 'Belum Terpenuhi'): ?>
                            <button onclick="konfirmasiHapus('sistem/proses/hibah/penuhi_syarat.php', '<?= $item['id']; ?>', 'Anda yakin syarat untuk hibah ini sudah dipenuhi koperasi/BUMDes? Jurnal pengakuan pendapatan (laba) akan dibuat otomatis.')" class="btn btn-sm btn-success">Sahkan Terpenuhi</button>
                            <button onclick="konfirmasiHapus('sistem/proses/hibah/proses_kembalikan_hibah.php', '<?= $item['id']; ?>', 'Anda yakin syarat GAGAL dipenuhi dan aset/dana ditarik kembali oleh pemberi hibah? Jurnal reversal akan dibuat.')" class="btn btn-sm btn-error">Gagal (Tarik Aset)</button>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="flex justify-center mt-6">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>