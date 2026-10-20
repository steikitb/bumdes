<?php
$judulHalaman = 'Buku Daftar Pengurus';
cekAkses('kelola_buku_administrasi');
$pengurus = query("
    SELECT p.*, a.pekerjaan, a.id as anggota_id_num, CAST((julianday('now') - julianday(p.tanggal_lahir))/365.25 AS INTEGER) AS umur 
    FROM pengguna p 
    LEFT JOIN anggota a ON p.anggota_id = a.id
    WHERE p.role IN ('pengurus', 'admin') 
    ORDER BY p.nama_lengkap ASC
");
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="print-header-container">
            <div class="print-logo">
                <img src="<?= BASE_URL ?>logo.png" alt="Logo Koperasi">
            </div>
            <div class="print-title">
                <h2 class="text-xl font-bold uppercase">SUSUNAN PENGURUS</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">No</th>
                        <th class="border">Nama Lengkap</th>
                        <th class="border">Umur</th>
                        <th class="border">Jenis Kelamin</th>
                        <th class="border">Pekerjaan</th>
                        <th class="border">Alamat</th>
                        <th class="border">No. Anggota</th>
                        <th class="border">Jabatan</th>
                        <th class="border">Tgl Diangkat</th>
                        <th class="border" style="width:100px;">Cap Ibu Jari</th>
                        <th class="border" style="width:100px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengurus)): ?>
                        <tr><td colspan="11" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($pengurus as $item): ?>
                    <tr>
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border"><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['umur'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['jenis_kelamin'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['pekerjaan'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['alamat'] ?? ''); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['anggota_id_num'] ?? ''); ?></td>
                        <td class="border">Pengurus</td>
                        <td class="border"><?= !empty($item['tanggal_masuk']) ? date('d-m-Y', strtotime($item['tanggal_masuk'])) : '' ?></td>
                        <td class="border h-16"></td>
                        <td class="border h-16"></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>