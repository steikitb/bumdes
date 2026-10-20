<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=simpanan_sukarela/lihat_saldo');
    exit;
}
$id = (int)$_GET['id'];
$anggota = query("SELECT nama_lengkap FROM anggota WHERE id=$id")[0];
$transaksi = query("SELECT * FROM simpanan_sukarela_transaksi WHERE anggota_id=$id ORDER BY tanggal_transaksi DESC, id DESC");
$saldo_q = query("SELECT (SUM(CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = 'Tarik' THEN jumlah ELSE 0 END)) as saldo FROM simpanan_sukarela_transaksi WHERE anggota_id = $id");
$saldo_saat_ini = $saldo_q[0]['saldo'] ?? 0;
$judulHalaman = 'Detail Simpanan Sukarela';
?>
<?php  ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Detail Simpanan: <?= htmlspecialchars($anggota['nama_lengkap']) ?></h1>
    <a href="index.php?halaman=simpanan_sukarela/lihat_saldo" class="btn btn-ghost">← Kembali ke Daftar Saldo</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl"><div class="card-body"><h2 class="card-title">Riwayat Transaksi</h2>
            <div class="overflow-x-auto"><table class="table w-full"><thead><tr><th>Tanggal</th><th>Jenis</th><th class="text-right">Jumlah</th><th>Keterangan</th></tr></thead><tbody>
                <?php if (empty($transaksi)): ?>
                    <tr><td colspan="4" class="text-center">Belum ada transaksi.</td></tr>
                <?php else: ?>
                    <?php foreach ($transaksi as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_transaksi']))) ?></td>
                        <td><span class="badge <?= $item['jenis_transaksi'] == 'Setor' ? 'badge-success' : 'badge-error' ?>"><?= $item['jenis_transaksi'] ?></span></td>
                        <td class="text-right">Rp <?= number_format($item['jumlah'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($item['keterangan']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody></table></div>
        </div></div>
    </div>
    <div>
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">Informasi Saldo</h2>
                <p><strong>Total Saldo Saat Ini:</strong><br><span class="text-2xl font-bold">Rp <?= number_format($saldo_saat_ini, 0, ',', '.') ?></span></p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl"><div class="card-body"><h2 class="card-title">Buat Transaksi Baru</h2>
            <form action="<?= BASE_URL ?>sistem/proses/simpanan_sukarela/tambah_transaksi.php" method="POST">
     <?php csrfInput(); ?>
     <input type="hidden" name="anggota_id" value="<?= $id ?>">
     <div class="form-control mb-2"><label class="label"><span class="label-text">Tanggal Transaksi</span></label><input type="date" name="tanggal_transaksi" class="input input-sm input-bordered" required value="<?= date('Y-m-d') ?>"></div>
     <div class="form-control mb-2"><label class="label"><span class="label-text">Jenis Transaksi</span></label><select name="jenis_transaksi" class="select select-sm select-bordered" required><option value="Setor">Setor</option><option value="Tarik">Tarik</option></select></div>
     <div class="form-control mb-2"><label class="label"><span class="label-text">Jumlah (Rp)</span></label><input type="text" name="jumlah" placeholder="0" class="input input-sm input-bordered format-rupiah" required></div>
     <div class="form-control mb-2"><label class="label"><span class="label-text">Keterangan</span></label><input type="text" name="keterangan" placeholder="Keterangan singkat" class="input input-sm input-bordered"></div>
     <button type="submit" class="btn btn-primary btn-block mt-4">Simpan Transaksi</button>
</form>
        </div></div>
    </div>
</div>

<?php  ?>