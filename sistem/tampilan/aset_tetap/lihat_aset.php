<?php
$judulHalaman = 'Data Aset Tetap';
$aset_tetap = query("SELECT * FROM aset_tetap ORDER BY tanggal_perolehan DESC");
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Daftar Aset Tetap & Inventaris</h1>
    <a href="index.php?halaman=aset_tetap/form_aset" class="btn btn-primary shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Aset Baru / Aset Lama
    </a>
</div>
<div class="overflow-x-auto bg-base-100 rounded-lg shadow border border-base-200">
    <table class="table w-full">
        <thead class="bg-base-200">
            <tr><th>No</th><th>Nama Aset</th><th>Tgl Perolehan</th><th class="text-right">Harga Perolehan</th>
            <th class="text-right">
                <div class="tooltip" data-tip="Dihitung otomatis dengan metode garis lurus berdasarkan masa manfaat.">
                    Akumulasi Penyusutan <i class="fas fa-info-circle"></i>
                </div>
            </th>
            <th class="text-right">Nilai Buku</th><th class="text-center w-48">Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (empty($aset_tetap)): ?>
                <tr><td colspan="7" class="text-center py-8 text-base-content/60">Belum ada data aset tetap.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($aset_tetap as $aset) :
                    $penyusutan_per_tahun = 0;
                    if ($aset['masa_manfaat_tahun'] > 0) { $penyusutan_per_tahun = ($aset['harga_perolehan'] - $aset['nilai_residu']) / $aset['masa_manfaat_tahun']; }
                    $tgl_perolehan = new DateTime($aset['tanggal_perolehan']);
                    $tgl_sekarang = new DateTime();
                    $selisih = $tgl_sekarang->diff($tgl_perolehan);
                    $umur_aset_bulan = ($selisih->y * 12) + $selisih->m;
                    $umur_aset_tahun = $umur_aset_bulan / 12;
                    if ($umur_aset_tahun > $aset['masa_manfaat_tahun']) { $umur_aset_tahun = $aset['masa_manfaat_tahun']; }
                    $akumulasi_penyusutan = $penyusutan_per_tahun * $umur_aset_tahun;
                    if ($aset['masa_manfaat_tahun'] > 0 && $akumulasi_penyusutan > ($aset['harga_perolehan'] - $aset['nilai_residu'])) { $akumulasi_penyusutan = $aset['harga_perolehan'] - $aset['nilai_residu']; }
                    $nilai_buku = $aset['harga_perolehan'] - $akumulasi_penyusutan;
                ?>
                <tr class="hover border-b border-base-200">
                    <th><?= $no++; ?></th>
                    <td>
                        <div class="font-bold"><?= htmlspecialchars($aset['nama_aset']); ?></div>
                        <div class="text-xs opacity-70 badge badge-ghost badge-sm mt-1"><?= htmlspecialchars($aset['kategori_aset']); ?></div>
                    </td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($aset['tanggal_perolehan']))); ?></td>
                    <td class="text-right font-mono">Rp <?= number_format($aset['harga_perolehan'], 0, ',', '.'); ?></td>
                    <td class="text-right font-mono text-error">Rp <?= number_format($akumulasi_penyusutan, 0, ',', '.'); ?></td>
                    <td class="text-right font-bold font-mono text-primary">Rp <?= number_format($nilai_buku, 0, ',', '.'); ?></td>
                    <td class="flex gap-2 justify-center">
                        <a href="index.php?halaman=aset_tetap/form_aset&id=<?= $aset['id']; ?>" class="btn btn-sm btn-warning" title="Edit Master Data">Edit</a>
                        <button onclick="window.bukaModalPelepasan(<?= $aset['id']; ?>, '<?= htmlspecialchars(addslashes($aset['nama_aset'])) ?>', <?= $nilai_buku ?>)" class="btn btn-sm btn-error" title="Jual/Buang Aset">Lepas Aset</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<dialog id="modal_pelepasan_aset" class="modal">
  <div class="modal-box border-t-4 border-error">
    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button></form>
    <h3 class="font-bold text-xl mb-4">Pelepasan / Penjualan Aset: <span id="nama_aset_modal" class="text-primary"></span></h3>
    <form action="<?= BASE_URL ?>sistem/proses/aset_tetap/proses_pelepasan_aset.php" method="POST">
        <?php csrfInput(); ?>
        <input type="hidden" name="id" id="modal_aset_id">
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Tanggal Pelepasan (Transaksi)</span></label>
            <input type="date" name="tanggal_pelepasan" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text opacity-70">Sisa Nilai Buku (Otomatis)</span></label>
            <input type="text" id="modal_nilai_buku" class="input input-bordered font-bold text-lg" readonly>
            <label class="label"><span class="label-text-alt text-info">Diestimasi dari akumulasi penyusutan hari ini.</span></label>
        </div>
        <div class="form-control mb-6">
            <label class="label"><span class="label-text font-bold text-primary">Harga Jual Pelepasan (Uang Masuk) Rp</span></label>
            <input type="text" name="harga_jual" id="modal_harga_jual" class="input input-bordered border-primary format-rupiah text-xl font-bold" required placeholder="0">
            <label class="label"><span class="label-text-alt">Isi "0" jika aset dibuang/rusak tanpa ada penerimaan kas. Sistem otomatis menghitung Laba/Rugi.</span></label>
        </div>
        <div class="modal-action">
            <button type="submit" class="btn btn-error w-full text-white shadow-lg">Eksekusi Jurnal Pelepasan</button>
        </div>
    </form>
  </div>
</dialog>
<?php  ?>