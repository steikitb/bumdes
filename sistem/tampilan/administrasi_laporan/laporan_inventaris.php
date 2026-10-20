<?php
$judulHalaman = 'Buku Daftar Inventaris';
cekAkses('kelola_buku_administrasi');
$aset = query("SELECT * FROM aset_tetap ORDER BY tanggal_perolehan ASC");
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 9px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold uppercase">BUKU DAFTAR INVENTARIS BARANG</h2>
            <p class="text-xs no-print text-base-content/60 mt-1">*Kolom Umur Teknis dan Ekonomis menggunakan nilai Masa Manfaat dari master Aset Tetap.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr class="bg-base-200">
                        <th class="border text-center" rowspan="2">No</th>
                        <th class="border text-center" rowspan="2">Nama / Jenis Barang</th>
                        <th class="border text-center" colspan="4">KETERANGAN PEMBELIAN</th>
                        <th class="border text-center" colspan="2">TAKSIRAN UMUR</th>
                        <th class="border text-center" rowspan="2">Kategori Aset</th>
                    </tr>
                    <tr class="bg-base-200">
                        <th class="border text-center">Tanggal</th>
                        <th class="border text-center">Jumlah</th>
                        <th class="border text-center">Harga Satuan (Rp)</th>
                        <th class="border text-center">Total (Rp)</th>
                        <th class="border text-center">Teknis</th>
                        <th class="border text-center">Ekonomis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aset)): ?>
                        <tr><td colspan="9" class="text-center border py-4">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($aset as $item): ?>
                    <tr class="hover">
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border font-semibold"><?= htmlspecialchars($item['nama_aset']); ?></td>
                        <td class="border text-center"><?= date('d-m-Y', strtotime($item['tanggal_perolehan'])); ?></td>
                        <td class="border text-center">1 Unit</td>
                        <td class="border text-right font-mono"><?= number_format($item['harga_perolehan'], 0, ',', '.'); ?></td>
                        <td class="border text-right font-mono"><?= number_format($item['harga_perolehan'], 0, ',', '.'); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['masa_manfaat_tahun']); ?> Thn</td>
                        <td class="border text-center"><?= htmlspecialchars($item['masa_manfaat_tahun']); ?> Thn</td>
                        <td class="border text-center"><?= htmlspecialchars($item['kategori_aset']); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>