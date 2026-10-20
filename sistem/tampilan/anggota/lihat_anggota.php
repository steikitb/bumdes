<?php
$judulHalaman = MODE_ENTITAS === 'BUMDes' ? 'Daftar Penyerta Modal & Pelanggan' : 'Daftar Anggota Koperasi';
$search = $_GET['search'] ?? '';
$limit = $_GET['limit'] ?? 10;
$params = [];
$whereClause = '';

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE nama_lengkap LIKE ? OR alamat LIKE ? OR telepon LIKE ?";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$sql_select_from = "SELECT * FROM anggota";
$paginasi = query_paginasi($sql_select_from, $whereClause . " ORDER BY nama_lengkap ASC", $params, $limit);
$anggota = $paginasi['data'];
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <a href="index.php?halaman=anggota/form_anggota" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Baru
    </a>
</div>
<p class="mb-4 text-base-content/80">Menu ini adalah Buku Induk yang memuat daftar semua orang maupun institusi (seperti Pemerintah Desa) yang berinteraksi dengan organisasi Anda.</p>
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="anggota/lihat_anggota">
            <div class="flex gap-2">
                <select name="limit" class="select select-bordered" onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 Baris</option>
                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 Baris</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 Baris</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 Baris</option>
                </select>
                <div class="join w-full">
                    <input type="text" name="search" placeholder="Cari nama, alamat, atau telepon..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-secondary join-item">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr><th>No</th><th>Nama Entitas</th><th>Alamat</th><th>No. Telepon</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (empty($anggota)): ?>
                <tr><td colspan="6" class="text-center">Data tidak ditemukan.</td></tr>
            <?php else: ?>
                <?php $no = (($paginasi['halaman_sekarang'] - 1) * $limit) + 1; ?>
                <?php foreach ($anggota as $item) : ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td>
                        <div class="font-bold"><?= htmlspecialchars($item['nama_lengkap']); ?></div>
                        <?php if ($item['jenis_entitas'] == 'Institusi'): ?>
                            <span class="badge badge-info badge-xs mt-1">Institusi / Desa</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['alamat']); ?></td>
                    <td><?= htmlspecialchars($item['telepon']); ?></td>
                    <td>
                        <?php if ($item['status_keanggotaan'] == 'Aktif'): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-error">Keluar (<?= date('d/m/Y', strtotime($item['tanggal_keluar'])) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="flex gap-2">
                        <a href="index.php?halaman=anggota/detail_anggota&id=<?= $item['id']; ?>" class="btn btn-sm btn-info">Buku Induk</a>
                        <a href="index.php?halaman=anggota/form_anggota&id=<?= $item['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <?php if ($item['status_keanggotaan'] == 'Aktif'): ?>
                        <button onclick="konfirmasiHapus('sistem/proses/anggota/hapus_anggota.php', '<?= $item['id']; ?>', 'Yakin ingin memproses entitas ini keluar? Pastikan semua kewajiban telah lunas.')" class="btn btn-sm btn-error">Proses Keluar</button>
                        <?php endif; ?>
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