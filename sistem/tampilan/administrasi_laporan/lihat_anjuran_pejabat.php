<?php
$judulHalaman = 'Buku Anjuran/Saran Pejabat/Pembina';
cekAkses('kelola_buku_administrasi');
$data_list = query("
    SELECT aap.*, p.nama_lengkap as nama_penanggap 
    FROM adm_anjuran_pejabat aap
    LEFT JOIN pengguna p ON aap.ditanggapi_oleh = p.id
    ORDER BY aap.tanggal DESC
");
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 10px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
        <a href="index.php?halaman=administrasi_laporan/form_anjuran_pejabat" class="btn btn-primary">Tambah Data</a>
    </div>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-xl font-bold uppercase">ANJURAN PEJABAT</h2></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">No</th>
                        <th class="border">Tanggal</th>
                        <th class="border">Nama Pejabat</th>
                        <th class="border">Jabatan & Kantor</th>
                        <th class="border">Isi Anjuran</th>
                        <th class="border">Tanggapan Pengurus</th>
                        <th class="border no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="7" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($data_list as $item): ?>
                    <tr>
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border"><?= date('d-m-Y', strtotime($item['tanggal'])); ?></td>
                        <td class="border"><?= htmlspecialchars($item['nama_pejabat']); ?></td>
                        <td class="border"><?= htmlspecialchars($item['jabatan_dan_kantor']); ?></td>
                        <td class="border"><?= nl2br(htmlspecialchars($item['isi_anjuran'])); ?></td>
                        <td class="border">
                            <?php if(!empty($item['tanggapan_pengurus'])): ?>
                                <?= nl2br(htmlspecialchars($item['tanggapan_pengurus'])); ?><br>
                                <small class="text-gray-500">Oleh: <?= htmlspecialchars($item['nama_penanggap']) ?> | Tgl: <?= date('d-m-Y', strtotime($item['tanggal_tanggapan'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="border no-print">
                            <a href="index.php?halaman=administrasi_laporan/form_anjuran_pejabat&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit/Tanggapi</a>
                            <button onclick="konfirmasiHapus('sistem/proses/administrasi_laporan/hapus_anjuran_pejabat', '<?= $item['id']; ?>')" class="btn btn-xs btn-error">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>