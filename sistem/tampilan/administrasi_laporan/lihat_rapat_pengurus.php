<?php
$judulHalaman = 'Buku Notulen Rapat Pengurus';
cekAkses('kelola_buku_administrasi');
$paginasi = query_paginasi("SELECT * FROM adm_rapat_pengurus", "ORDER BY tanggal_rapat DESC", [], '', 25);
$data_list = $paginasi['data'];
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 8px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
        <a href="index.php?halaman=administrasi_laporan/form_rapat_pengurus" class="btn btn-primary">Tambah Data</a>
    </div>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-xl font-bold uppercase">NOTULEN RAPAT PENGURUS DAN KEPUTUSANNYA</h2></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">Tanggal</th>
                        <th class="border">Tempat</th>
                        <th class="border">Jenis</th>
                        <th class="border">Jml Pengurus</th>
                        <th class="border">Jml Hadir</th>
                        <th class="border">Pimpinan</th>
                        <th class="border">Acara</th>
                        <th class="border">Keputusan</th>
                        <th class="border no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="9" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: foreach ($data_list as $item): ?>
                    <tr>
                        <td class="border"><?= date('d-m-Y', strtotime($item['tanggal_rapat'])); ?></td>
                        <td class="border"><?= htmlspecialchars($item['tempat_rapat']); ?></td>
                        <td class="border"><?= htmlspecialchars($item['jenis_rapat']); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['jumlah_pengurus_total']); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['jumlah_hadir']); ?></td>
                        <td class="border"><?= htmlspecialchars($item['pimpinan_rapat']); ?></td>
                        <td class="border"><?= nl2br(htmlspecialchars($item['acara_rapat'])); ?></td>
                        <td class="border"><?= nl2br(htmlspecialchars($item['keputusan_rapat'])); ?></td>
                        <td class="border no-print">
                            <a href="index.php?halaman=administrasi_laporan/form_rapat_pengurus&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                            <button onclick="konfirmasiHapus('sistem/proses/administrasi_laporan/hapus_rapat_pengurus', '<?= $item['id']; ?>')" class="btn btn-xs btn-error">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>