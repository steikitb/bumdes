<?php
$jenis_filter = $_GET['jenis'] ?? 'Barang Dagangan';
$judulHalaman = $jenis_filter === 'Perlengkapan' ? 'Data Perlengkapan (Habis Pakai)' : 'Data Persediaan (Barang Dagang)';

$search = $_GET['search'] ?? '';
$limit = $_GET['limit'] ?? 20;

$whereClauses = ["status_aktif = 1", "jenis_barang = ?"];
$params = [$jenis_filter];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(nama_produk LIKE ? OR kode_produk LIKE ?)";
    array_push($params, $searchTerm, $searchTerm);
}

$whereClause = "WHERE " . implode(' AND ', $whereClauses);
$paginasi = query_paginasi("SELECT * FROM produk", $whereClause . " ORDER BY nama_produk ASC", $params, $limit);
$produk = $paginasi['data'];
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div class="flex flex-wrap gap-2">
        <?php if ($jenis_filter === 'Barang Dagangan'): ?>
            <a href="index.php?halaman=produk_stok/form_penurunan_nilai" class="btn btn-warning">
                Penyesuaian Nilai Barang
            </a>
            <a href="index.php?halaman=produk_stok/form_pecah_barang" class="btn btn-secondary">
                Pecah Karung
            </a>
        <?php endif; ?>
        <a href="index.php?halaman=produk_stok/form_produk&jenis=<?= urlencode($jenis_filter) ?>" class="btn btn-primary shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Tambah Baru
        </a>
    </div>
</div>
<div class="card bg-base-100 shadow mb-6 border border-base-200">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="produk_stok/lihat_stok">
            <input type="hidden" name="jenis" value="<?= htmlspecialchars($jenis_filter) ?>">
            <div class="flex gap-2">
                <select name="limit" class="select select-bordered" onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 Baris</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 Baris</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 Baris</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 Baris</option>
                </select>
                <div class="join w-full">
                    <input type="text" name="search" placeholder="Cari nama atau kode barang..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-secondary join-item">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto bg-base-100 rounded-lg shadow">
    <table class="table w-full">
        <thead class="bg-base-200 text-base-content/80">
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th class="text-right">HPP Rata-rata</th>
                <?php if ($jenis_filter === 'Barang Dagangan'): ?>
                    <th class="text-right">Harga Jual</th>
                <?php endif; ?>
                <th class="text-center">Stok</th>
                <th class="text-center w-56">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($produk)): ?>
                <tr><td colspan="<?= $jenis_filter === 'Barang Dagangan' ? '7' : '6' ?>" class="text-center py-8 text-base-content/60">Tidak ada barang yang terdaftar.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * $limit) + 1; ?>
                <?php foreach ($produk as $item) : ?>
                <tr class="hover <?= ($item['stok'] <= $item['stok_minimum']) ? 'bg-error/10' : '' ?>">
                    <th><?= $no++; ?></th>
                    <td class="font-mono text-xs"><?= htmlspecialchars($item['kode_produk']); ?></td>
                    <td class="font-bold"><?= htmlspecialchars($item['nama_produk']); ?></td>
                    <td class="text-right font-mono">Rp <?= number_format($item['harga_rata_rata'], 0, ',', '.'); ?></td>
                    
                    <?php if ($jenis_filter === 'Barang Dagangan'): ?>
                        <td class="text-right font-mono text-primary">Rp <?= number_format($item['harga_jual'], 0, ',', '.'); ?></td>
                    <?php endif; ?>

                    <td class="text-center font-bold text-lg">
                        <?= htmlspecialchars($item['stok']) ?> <span class="text-xs font-normal"><?= htmlspecialchars($item['satuan']); ?></span>
                        <?php if ($item['stok'] <= $item['stok_minimum']): ?>
                            <br><span class="badge badge-error badge-xs text-white">Stok Kritis</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="dropdown dropdown-end dropdown-left">
                            <label tabindex="0" class="btn btn-sm btn-ghost btn-circle m-1"><i class="fas fa-ellipsis-v"></i></label>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 border border-base-200">
                                <li><a href="index.php?halaman=produk_stok/kartu_stok&id=<?= $item['id'] ?>"><i class="fas fa-list-alt mr-2 text-info"></i> Kartu Stok (Mutasi)</a></li>
                                <li><a class="btn-buka-modal-penyesuaian" data-id="<?= $item['id'] ?>" data-nama="<?= htmlspecialchars($item['nama_produk']) ?>" data-stok="<?= $item['stok'] ?>"><i class="fas fa-boxes mr-2 text-success"></i> Hitung Ulang Stok (Opname)</a></li>
                                <div class="divider my-1"></div>
                                <li><a href="index.php?halaman=produk_stok/form_produk&id=<?= $item['id']; ?>"><i class="fas fa-edit mr-2 text-warning"></i> Edit Master Data</a></li>
                                <li><a onclick="konfirmasiHapus('sistem/proses/produk_stok/hapus_produk.php', '<?= $item['id']; ?>', 'Yakin menghapus barang ini? Jika pernah terjadi transaksi, barang hanya akan disembunyikan.')" class="text-error"><i class="fas fa-trash mr-2"></i> Hapus Barang</a></li>
                            </ul>
                        </div>
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

<dialog id="modal_penyesuaian_stok" class="modal">
  <div class="modal-box border-t-4 border-success">
    <form method="dialog">
      <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
    </form>
    <h3 class="font-bold text-xl mb-4" id="modal_judul_produk">Hitung Ulang Stok (Stok Opname)</h3>
    <form action="<?= BASE_URL ?>sistem/proses/produk_stok/proses_penyesuaian_stok.php" method="POST">
        <?php csrfInput(); ?>
        <input type="hidden" name="produk_id" id="modal_produk_id">
        <input type="hidden" name="redirect_jenis" value="<?= htmlspecialchars($jenis_filter) ?>">
        <div class="form-control mb-4">
            <label class="label"><span class="label-text opacity-70">Stok Terdaftar di Sistem (Buku)</span></label>
            <input type="number" id="modal_stok_sistem" class="input input-bordered font-bold text-lg" readonly>
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text font-bold text-primary">Stok Riil (Hasil Hitung Fisik di Gudang)</span></label>
            <input type="number" name="stok_sebenarnya" class="input input-bordered border-primary text-xl font-black" required placeholder="0" autofocus>
            <label class="label"><span class="label-text-alt">Sistem akan otomatis membuat <b>Jurnal Penyesuaian (Selisih Lebih/Kurang)</b> jika terdapat perbedaan jumlah.</span></label>
        </div>
        <div class="modal-action">
            <button type="submit" class="btn btn-success text-white w-full shadow-lg">Eksekusi Penyesuaian</button>
        </div>
    </form>
  </div>
</dialog>