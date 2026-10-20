<?php
$judulHalaman = 'Data Pinjaman Diterima';
$search = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE p.nama_kreditur LIKE ?";
    $params[] = $searchTerm;
}
$sql_select_from = "SELECT p.id, p.nama_kreditur, p.tanggal_pinjaman, p.jumlah_pokok, COALESCE(SUM(pb.pokok_dibayar), 0) as total_dibayar FROM pinjaman_diterima p LEFT JOIN pembayaran_pinjaman pb ON p.id = pb.pinjaman_id";
$paginasi = query_paginasi($sql_select_from, $whereClause . " GROUP BY p.id ORDER BY p.tanggal_pinjaman DESC", $params, 15);
$pinjaman = $paginasi['data'];
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Data Pinjaman Diterima</h1>
    <a href="index.php?halaman=pinjaman/form_pinjaman" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Pinjaman
    </a>
</div>
<p class="mb-4 text-base-content/80">Halaman ini berisi daftar utang atau pinjaman yang diterima oleh koperasi dari pihak luar (seperti bank atau lembaga lain).</p>
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="pinjaman/lihat_pinjaman">
            <div class="join w-full">
                <input type="text" name="search" placeholder="Cari nama kreditur..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary join-item">Cari</button>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Kreditur</th>
                <th>Tanggal Pinjam</th>
                <th class="text-right">Jumlah Pokok</th>
                <th class="text-right">Sisa Pokok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pinjaman)): ?>
                <tr><td colspan="7" class="text-center">Belum ada data pinjaman.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * 15) + 1; ?>
                <?php foreach ($pinjaman as $item) : 
                    $sisa_pokok = $item['jumlah_pokok'] - $item['total_dibayar'];
                ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td><?= htmlspecialchars($item['nama_kreditur']); ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_pinjaman']))); ?></td>
                    <td class="text-right">Rp <?= number_format($item['jumlah_pokok'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold">Rp <?= number_format($sisa_pokok, 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($sisa_pokok <= 0): ?>
                            <span class="badge badge-success">Lunas</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Belum Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?halaman=pinjaman/detail_pinjaman&id=<?= $item['id']; ?>" class="btn btn-sm btn-info">Detail & Bayar</a>
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
<?php  ?>