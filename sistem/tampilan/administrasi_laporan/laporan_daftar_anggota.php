<?php
$judulHalaman = 'Buku Daftar Anggota';
cekAkses('kelola_buku_administrasi');

$anggota = query("SELECT *, CAST((julianday('now') - julianday(tanggal_lahir))/365.25 AS INTEGER) AS umur FROM anggota ORDER BY nama_lengkap ASC");
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 10px; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-xl font-bold uppercase">DAFTAR ANGGOTA</h2></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr>
                        <th class="border">No Urut</th>
                        <th class="border">Nama Lengkap</th>
                        <th class="border">Umur</th>
                        <th class="border">Jenis Kelamin</th>
                        <th class="border">Pekerjaan</th>
                        <th class="border">Alamat</th>
                        <th class="border">Tgl Masuk</th>
                        <th class="border" style="width:100px;">Cap Ibu Jari</th>
                        <th class="border" style="width:100px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($anggota)): ?>
                        <tr><td colspan="9" class="text-center border">Data tidak ditemukan.</td></tr>
                    <?php else: $no = 1; foreach ($anggota as $item): ?>
                    <tr>
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border"><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                        <td class="border text-center"><?= htmlspecialchars($item['umur'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['jenis_kelamin'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['pekerjaan'] ?? ''); ?></td>
                        <td class="border"><?= htmlspecialchars($item['alamat']); ?></td>
                        <td class="border"><?= date('d-m-Y', strtotime($item['tanggal_bergabung'])); ?></td>
                        <td class="border h-16"></td>
                        <td class="border h-16"></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>