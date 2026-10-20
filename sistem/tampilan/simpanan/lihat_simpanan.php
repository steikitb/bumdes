<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Riwayat Penyertaan Modal BUMDes' : 'Riwayat Simpanan Pokok & Wajib';
$judulHalaman = $lbl_judul;
$sql_select_from = "SELECT s.id, s.tanggal_setor, s.jenis_simpanan, s.bentuk_setoran, s.keterangan, s.jumlah, a.nama_lengkap FROM simpanan s JOIN anggota a ON s.anggota_id = a.id";
$paginasi = query_paginasi($sql_select_from, "ORDER BY s.tanggal_setor DESC, s.id DESC", [], '', 25);
$simpanan = $paginasi['data'];

$lbl_desc = MODE_ENTITAS === 'BUMDes' ? 'Menu ini mencatat masuknya modal BUMDes secara permanen dari Pemerintah Desa atau Masyarakat.' : 'Menu ini digunakan untuk mengelola riwayat setoran Simpanan Pokok dan Wajib milik anggota Koperasi.';
$lbl_kolom = MODE_ENTITAS === 'BUMDes' ? 'Penyerta Modal (Pemilik)' : 'Nama Anggota';
?>
<?php  ?>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <a href="index.php?halaman=simpanan/form_simpanan" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Catat Penerimaan Baru
    </a>
</div>
<p class="mb-4 text-base-content/80"><?= $lbl_desc ?></p>

<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Setor</th>
                <th><?= $lbl_kolom ?></th>
                <th>Klasifikasi Modal</th>
                <th>Metode / Bentuk</th>
                <th class="text-right">Nilai (Rp)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($simpanan)): ?>
                <tr><td colspan="7" class="text-center">Belum ada data penerimaan yang tercatat.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * 25) + 1; ?>
                <?php foreach ($simpanan as $item) : ?>
                <tr class="hover">
                    <th><?= $no++; ?></th>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_setor']))); ?></td>
                    <td class="font-semibold">
                        <?= htmlspecialchars($item['nama_lengkap']); ?>
                        <?php if(!empty($item['keterangan'])): ?>
                            <div class="text-xs font-normal opacity-70"><?= htmlspecialchars($item['keterangan']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(MODE_ENTITAS === 'BUMDes'): ?>
                            <span class="badge <?= $item['jenis_simpanan'] == 'Pokok' ? 'badge-primary' : 'badge-ghost' ?>"><?= $item['jenis_simpanan'] == 'Pokok' ? 'Penyertaan Desa' : 'Penyertaan Masyarakat' ?></span>
                        <?php else: ?>
                            <span class="badge <?= $item['jenis_simpanan'] == 'Pokok' ? 'badge-info' : 'badge-ghost' ?>"><?= htmlspecialchars($item['jenis_simpanan']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['bentuk_setoran']) ?></td>
                    <td class="text-right font-mono font-bold">Rp <?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                    <td>
                        <button onclick="konfirmasiHapus('sistem/proses/simpanan/hapus_simpanan.php', '<?= $item['id']; ?>', 'Yakin ingin menghapus transaksi ini? Jurnal akan dibalik secara otomatis.')" class="btn btn-sm btn-error">Batalkan</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="flex justify-center">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>

<?php  ?>