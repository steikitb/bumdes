<?php
$judulHalaman = 'Buku Agenda Surat Masuk';
cekAkses('kelola_buku_administrasi');
$paginasi = query_paginasi("SELECT * FROM adm_surat_masuk", "ORDER BY tanggal_masuk DESC, id DESC", [], '', 25);
$data_list = $paginasi['data'];
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 10px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
        <a href="index.php?halaman=administrasi_laporan/form_surat_masuk" class="btn btn-primary">Tambah Data</a>
    </div>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-xl font-bold uppercase"><?= $judulHalaman ?></h2></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">No Urut</th>
                        <th class="border">Tgl Masuk Surat</th>
                        <th class="border">Nomor, Tanggal, dan Perihal Surat</th>
                        <th class="border">Asal Surat</th>
                        <th class="border no-print" style="width:100px;">Tanda Tangan</th>
                        <th class="border no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="6" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($data_list as $item): ?>
                    <tr>
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border"><?= date('d-m-Y', strtotime($item['tanggal_masuk'])); ?></td>
                        <td class="border">
                            <b><?= htmlspecialchars($item['nomor_surat']); ?></b><br>
                            Tgl: <?= date('d-m-Y', strtotime($item['tanggal_surat'])); ?><br>
                            Perihal: <?= htmlspecialchars($item['perihal']); ?>
                        </td>
                        <td class="border"><?= htmlspecialchars($item['asal_surat']); ?></td>
                        <td class="border h-16 no-print"></td>
                        <td class="border no-print">
                            <a href="index.php?halaman=administrasi_laporan/form_surat_masuk&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                            <button onclick="konfirmasiHapus('sistem/proses/administrasi_laporan/hapus_surat_masuk', '<?= $item['id']; ?>')" class="btn btn-xs btn-error">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="flex justify-center no-print">
            <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
        </div>
    </div>
</div>
<?php  ?>