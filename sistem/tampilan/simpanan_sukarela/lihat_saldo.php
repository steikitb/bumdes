<?php
$judulHalaman = 'Saldo Simpanan Sukarela';
$search = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE a.nama_lengkap LIKE ?";
    $params[] = $searchTerm;
}
$sql_select_from = "SELECT a.id, a.nama_lengkap, a.telepon, COALESCE(SUM(CASE WHEN s.jenis_transaksi = 'Setor' THEN s.jumlah ELSE -s.jumlah END), 0) as saldo_akhir FROM anggota a LEFT JOIN simpanan_sukarela_transaksi s ON a.id = s.anggota_id";
$paginasi = query_paginasi($sql_select_from, $whereClause . " GROUP BY a.id, a.nama_lengkap, a.telepon ORDER BY a.nama_lengkap ASC", $params, 20);
$saldo = $paginasi['data'];
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Saldo Simpanan Sukarela Anggota</h1>
</div>
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="simpanan_sukarela/lihat_saldo">
            <div class="join w-full">
                <input type="text" name="search" placeholder="Cari nama anggota..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary join-item">Cari</button>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th><th>Nama Anggota</th><th>No. Telepon</th><th class="text-right">Total Saldo</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($saldo)): ?>
                <tr><td colspan="5" class="text-center">Belum ada data anggota.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * 20) + 1; ?>
                <?php foreach ($saldo as $item) : ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars($item['telepon']); ?></td>
                    <td class="text-right font-bold">Rp <?= number_format($item['saldo_akhir'], 0, ',', '.'); ?></td>
                    <td><a href="index.php?halaman=simpanan_sukarela/detail_anggota&id=<?= $item['id']; ?>" class="btn btn-sm btn-info">Detail & Transaksi</a></td>
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