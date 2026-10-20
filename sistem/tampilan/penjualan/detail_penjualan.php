<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=penjualan/lihat_penjualan');
    exit;
}
$idPenjualan = (int)$_GET['id'];
$penjualan_data = query("SELECT p.*, a.nama_lengkap, a.alamat, a.telepon FROM penjualan p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE p.id = ?", [$idPenjualan]);
if (empty($penjualan_data)) {
    header('Location: ' . BASE_URL . 'index.php?halaman=404');
    exit;
}
$penjualan = $penjualan_data[0];
$detail = query("SELECT dp.kuantitas, dp.harga_saat_transaksi, dp.subtotal, pr.nama_produk, pr.satuan FROM detail_penjualan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = ?", [$idPenjualan]);
$judulHalaman = 'Detail Penjualan: ' . $penjualan['nomor_faktur'];

$nama_pelanggan = $penjualan['nama_lengkap'] ?? ($penjualan['nama_pelanggan_umum'] ? $penjualan['nama_pelanggan_umum'] . ' (Umum)' : 'Umum (Non-Anggota)');
?>
<?php  ?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Detail Transaksi</h1>
    <div class="flex gap-2">
        <a href="<?= BASE_URL ?>index.php?halaman=penjualan/cetak_struk&id=<?= $idPenjualan ?>" target="_blank" class="btn btn-secondary shadow-md"><i class="fas fa-print mr-2"></i>Cetak Struk Kasir</a>
        <?php if ($penjualan['status_batal'] == 0): ?>
            <button onclick="konfirmasiHapus('sistem/proses/transaksi/proses_retur_penjualan.php', '<?= $idPenjualan ?>', 'Yakin ingin meretur/membatalkan seluruh transaksi penjualan ini? Stok akan dikembalikan dan jurnal penjualan akan dibalik (di-reverse).')" class="btn btn-error"><i class="fas fa-undo mr-2"></i>Batalkan & Retur</button>
        <?php endif; ?>
        <a href="index.php?halaman=penjualan/lihat_penjualan" class="btn btn-ghost">← Kembali</a>
    </div>
</div>

<?php if ($penjualan['status_batal'] == 1): ?>
<div class="alert alert-error mb-6 shadow-lg">
    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span>Transaksi ini telah <b>DIBATALKAN / DIRETUR</b>. Efek jurnal akuntansi dan pengurangan stok telah dibalikkan.</span>
</div>
<?php endif; ?>

<div class="card bg-base-100 shadow-xl <?= $penjualan['status_batal'] == 1 ? 'opacity-70' : '' ?>">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4 font-mono text-primary">Faktur: <?= htmlspecialchars($penjualan['nomor_faktur']) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-base-200 p-4 rounded-lg border border-base-300">
            <div>
                <p class="mb-1"><strong>Tanggal Transaksi:</strong> <?= htmlspecialchars(date('d F Y', strtotime($penjualan['tanggal_penjualan']))) ?></p>
                <p class="mb-1"><strong>Metode Pembayaran:</strong> <span class="badge badge-neutral"><?= htmlspecialchars($penjualan['metode_pembayaran']) ?></span></p>
                <?php if ($penjualan['metode_pembayaran'] === 'Kredit'): ?>
                    <p class="text-error font-bold mt-2">Jatuh Tempo: <?= date('d F Y', strtotime($penjualan['tanggal_jatuh_tempo'])) ?></p>
                <?php elseif ($penjualan['metode_pembayaran'] === 'Transfer'): ?>
                    <p class="text-info font-bold mt-2">Ditransfer ke Akun: <?= htmlspecialchars($penjualan['kode_akun_bank']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <h3 class="font-bold mb-1 opacity-70">Identitas Pelanggan:</h3>
                <p class="font-bold text-lg"><?= htmlspecialchars($nama_pelanggan) ?></p>
                <?php if (!empty($penjualan['nama_lengkap'])): ?>
                <p class="text-sm opacity-80 mt-1"><?= htmlspecialchars($penjualan['alamat']) ?></p>
                <p class="text-sm font-mono mt-1"><i class="fas fa-phone text-xs mr-1"></i> <?= htmlspecialchars($penjualan['telepon']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="divider my-6">Rincian Barang Dibeli</div>
        <div class="overflow-x-auto border border-base-200 rounded-lg">
            <table class="table w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Produk</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-center">Kuantitas</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($detail as $item): ?>
                    <tr class="hover">
                        <th class="text-center"><?= $no++ ?></th>
                        <td class="font-bold"><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td class="text-right font-mono">Rp <?= number_format($item['harga_saat_transaksi'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <span class="font-bold text-lg"><?= $item['kuantitas'] ?></span> 
                            <span class="text-xs opacity-70"><?= htmlspecialchars($item['satuan']) ?></span>
                        </td>
                        <td class="text-right font-mono font-bold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-t-2 border-base-300">
                    <tr>
                        <td colspan="4" class="text-right opacity-70">Total Belanja (Kotor)</td>
                        <td class="text-right font-mono">Rp <?= number_format($penjualan['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php if ($penjualan['diskon'] > 0): ?>
                    <tr>
                        <td colspan="4" class="text-right font-bold text-error">Potongan Harga / Diskon</td>
                        <td class="text-right text-error font-mono font-bold">- Rp <?= number_format($penjualan['diskon'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="font-bold text-xl bg-base-200">
                        <td colspan="4" class="text-right uppercase">Total Bersih (Yang Harus Dibayar)</td>
                        <td class="text-right text-success font-mono font-black">Rp <?= number_format($penjualan['total'] - $penjualan['diskon'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php  ?>