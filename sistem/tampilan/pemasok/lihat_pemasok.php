<?php
cekAkses('kelola_pemasok');
$judulHalaman = 'Data Pemasok';
$search = $_GET['search'] ?? '';
$params = [];
$whereClauses = ["status_aktif = 1"]; 

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(nama_pemasok LIKE ? OR alamat LIKE ? OR telepon LIKE ? OR keterangan LIKE ?)";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}
$whereClause = "WHERE " . implode(" AND ", $whereClauses);
$paginasi = query_paginasi("SELECT * FROM pemasok", $whereClause . " ORDER BY nama_pemasok ASC", $params, 15);
$pemasok_list = $paginasi['data'];
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Data Pemasok (Supplier)</h1>
    <a href="index.php?halaman=pemasok/form_pemasok" class="btn btn-primary shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Pemasok
    </a>
</div>
<div class="card bg-base-100 shadow mb-6 border border-base-200">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="pemasok/lihat_pemasok">
            <div class="join w-full">
                <input type="text" name="search" placeholder="Cari nama, keterangan jenis, alamat, atau telepon..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary join-item">Cari Pemasok</button>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto bg-base-100 rounded-lg shadow">
    <table class="table w-full">
        <thead class="bg-base-200">
            <tr><th>No</th><th>Nama Pemasok</th><th>Keterangan</th><th>Alamat</th><th>Kontak</th><th class="text-center w-32">Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (empty($pemasok_list)): ?>
                <tr><td colspan="6" class="text-center py-6 text-base-content/60">Data tidak ditemukan.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * 15) + 1; ?>
                <?php foreach ($pemasok_list as $item) : ?>
                <tr class="hover border-b border-base-200">
                    <th><?= $no++; ?></th>
                    <td class="font-bold"><?= htmlspecialchars($item['nama_pemasok']); ?></td>
                    <td>
                        <?php if (!empty($item['keterangan'])): ?>
                            <span class="badge badge-info badge-sm"><?= htmlspecialchars($item['keterangan']); ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['alamat']); ?></td>
                    <td>
                        <div class="font-medium"><?= htmlspecialchars($item['telepon']); ?></div>
                        <div class="text-xs opacity-70"><?= htmlspecialchars($item['email']); ?></div>
                    </td>
                    <td class="flex flex-wrap justify-center gap-2">
                        <a href="index.php?halaman=pemasok/form_pemasok&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                        <button onclick="konfirmasiHapus('sistem/proses/pemasok/hapus_pemasok.php', '<?= $item['id']; ?>', 'Hapus pemasok ini? (Jika sudah ada transaksi, pemasok hanya akan disembunyikan)')" class="btn btn-xs btn-error">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="flex justify-center mt-6">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>
<?php  ?>