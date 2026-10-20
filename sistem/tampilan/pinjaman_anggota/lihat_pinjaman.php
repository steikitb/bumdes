<?php
$judulHalaman = 'Data Pinjaman Anggota';
$search = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE a.nama_lengkap LIKE ?";
    $params[] = $searchTerm;
}
$sql_select_from = "SELECT p.id, p.tanggal_pinjaman, p.jumlah_pokok, p.jenis_pinjaman, a.nama_lengkap, COALESCE(SUM(pa.pokok_dibayar), 0) as total_dibayar FROM pinjaman_anggota p JOIN anggota a ON p.anggota_id = a.id LEFT JOIN pembayaran_angsuran pa ON p.id = pa.pinjaman_anggota_id";
$paginasi = query_paginasi($sql_select_from, $whereClause . " GROUP BY p.id ORDER BY p.tanggal_pinjaman DESC", $params, 15);
$pinjaman = $paginasi['data'];
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Data Piutang Simpan Pinjam</h1>
    <a href="index.php?halaman=pinjaman_anggota/form_pinjaman" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Beri Pinjaman Baru
    </a>
</div>
<p class="mb-4 text-base-content/80">Halaman ini berisi daftar pinjaman (kredit) yang diberikan oleh koperasi/BUMDes kepada pihak lain. Secara akuntansi, ini adalah piutang (aset) bagi entitas.</p>
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="pinjaman_anggota/lihat_pinjaman">
            <div class="join w-full">
                <input type="text" name="search" placeholder="Cari nama debitur/anggota..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary join-item">Cari</button>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th><th>Nama Debitur</th><th>Jenis</th><th>Tanggal Pinjam</th><th class="text-right">Plafon Pokok</th><th class="text-right">Sisa Tagihan</th><th>Status</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pinjaman)): ?>
                <tr><td colspan="8" class="text-center">Belum ada data piutang.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * 15) + 1; ?>
                <?php foreach ($pinjaman as $item) : 
                    $sisa_pokok = $item['jumlah_pokok'] - $item['total_dibayar'];
                ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td class="font-bold"><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                    <td><span class="badge badge-info badge-sm"><?= htmlspecialchars($item['jenis_pinjaman'] ?? 'Umum'); ?></span></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_pinjaman']))); ?></td>
                    <td class="text-right font-mono">Rp <?= number_format($item['jumlah_pokok'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold text-error font-mono">Rp <?= number_format($sisa_pokok, 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($sisa_pokok <= 0): ?>
                            <span class="badge badge-success">Lunas</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?halaman=pinjaman_anggota/detail_pinjaman&id=<?= $item['id']; ?>" class="btn btn-sm btn-info text-white">Detail & Bayar</a>
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