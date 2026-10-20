<?php
$judulHalaman = 'Buku Catatan Pengawas';
cekAkses('kelola_buku_administrasi');
$data_list = query("
    SELECT acp.*, p.nama_lengkap as nama_pengawas, pg.nama_lengkap as nama_penanggap 
    FROM adm_catatan_pengawas acp
    JOIN pengguna p ON acp.pengawas_id = p.id
    LEFT JOIN pengguna pg ON acp.ditanggapi_oleh = pg.id
    ORDER BY acp.tanggal DESC
");
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 10px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
        <a href="index.php?halaman=administrasi_laporan/form_catatan_pengawas" class="btn btn-primary">Tambah Catatan</a>
    </div>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-xl font-bold uppercase">CATATAN/ SARAN/ PERTANYAAAN BADAN PENGAWAS</h2></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">No</th>
                        <th class="border">Tanggal</th>
                        <th class="border">Nama Badan Pengawas</th>
                        <th class="border">Isi Catatan/Saran/Pertanyaan</th>
                        <th class="border">Tanggapan Pengurus</th>
                        <th class="border no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="6" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($data_list as $item): ?>
                    <tr>
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border"><?= date('d-m-Y', strtotime($item['tanggal'])); ?></td>
                        <td class="border"><?= htmlspecialchars($item['nama_pengawas']); ?></td>
                        <td class="border"><?= nl2br(htmlspecialchars($item['isi_catatan'])); ?></td>
                        <td class="border">
                            <?php if(!empty($item['tanggapan_pengurus'])): ?>
                                <?= nl2br(htmlspecialchars($item['tanggapan_pengurus'])); ?><br>
                                <small class="text-gray-500">Oleh: <?= htmlspecialchars($item['nama_penanggap']) ?> | Tgl: <?= date('d-m-Y', strtotime($item['tanggal_tanggapan'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="border no-print">
                            <a href="index.php?halaman=administrasi_laporan/form_catatan_pengawas&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit/Tanggapi</a>
                            <button onclick="konfirmasiHapus('sistem/proses/administrasi_laporan/hapus_catatan_pengawas', '<?= $item['id']; ?>')" class="btn btn-xs btn-error">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>