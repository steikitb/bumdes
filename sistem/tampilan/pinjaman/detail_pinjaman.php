<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman/lihat_pinjaman');
    exit;
}
$id = (int)$_GET['id'];
$pinjaman = query("SELECT * FROM pinjaman_diterima WHERE id=$id")[0];
$pembayaran = query("SELECT * FROM pembayaran_pinjaman WHERE pinjaman_id=$id ORDER BY tanggal_bayar DESC");

$total_dibayar = query("SELECT SUM(pokok_dibayar) as total FROM pembayaran_pinjaman WHERE pinjaman_id=$id")[0]['total'] ?? 0;
$sisa_pokok = $pinjaman['jumlah_pokok'] - $total_dibayar;
$judulHalaman = 'Detail Pinjaman';
?>
<?php  ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Detail Pinjaman dari <?= htmlspecialchars($pinjaman['nama_kreditur']) ?></h1>
    <a href="index.php?halaman=pinjaman/lihat_pinjaman" class="btn btn-ghost">← Kembali ke Daftar</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Riwayat Pembayaran</h2>
                 <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Tanggal</th><th>Pokok Dibayar</th><th>Bunga Dibayar</th><th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pembayaran)): ?>
                                <tr><td colspan="4" class="text-center">Belum ada pembayaran.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pembayaran as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_bayar']))) ?></td>
                                    <td>Rp <?= number_format($item['pokok_dibayar'], 0, ',', '.') ?></td>
                                    <td>Rp <?= number_format($item['bunga_dibayar'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($item['keterangan']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">Informasi Pinjaman</h2>
                <p><strong>Pokok Awal:</strong><br>Rp <?= number_format($pinjaman['jumlah_pokok'], 0, ',', '.') ?></p>
                <p><strong>Sisa Pokok:</strong><br><span class="text-2xl font-bold">Rp <?= number_format($sisa_pokok, 0, ',', '.') ?></span></p>
                <p><strong>Keterangan:</strong><br><?= htmlspecialchars($pinjaman['keterangan']) ?></p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Catat Pembayaran Baru</h2>
                <form action="<?= BASE_URL ?>sistem/proses/pinjaman/proses_pembayaran.php" method="POST">
    <?php csrfInput(); ?>
                     <input type="hidden" name="pinjaman_id" value="<?= $id ?>">
                     <div class="form-control mb-2"><label class="label"><span class="label-text">Tanggal Bayar</span></label><input type="date" name="tanggal_bayar" class="input input-sm input-bordered" required value="<?= date('Y-m-d') ?>"></div>
                     <div class="form-control mb-2"><label class="label"><span class="label-text">Pokok Dibayar (Rp)</span></label><input type="text" name="pokok_dibayar" placeholder="0" class="input input-sm input-bordered format-rupiah" required></div>
                     <div class="form-control mb-2"><label class="label"><span class="label-text">Bunga Dibayar (Rp)</span></label><input type="text" name="bunga_dibayar" placeholder="0" class="input input-sm input-bordered format-rupiah" required></div>
                     <div class="form-control mb-2"><label class="label"><span class="label-text">Keterangan</span></label><input type="text" name="keterangan" placeholder="Angsuran ke-X" class="input input-sm input-bordered"></div>
                     <button type="submit" class="btn btn-primary btn-block mt-4" <?= ($sisa_pokok <= 0) ? 'disabled' : '' ?>>Simpan Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php  ?>