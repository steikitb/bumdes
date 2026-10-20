<?php
cekAkses('transaksi_pembelian');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=pembelian/lihat_pembelian');
    exit;
}
$idPembelian = (int)$_GET['id'];
$pembelian_data = query("SELECT p.id, p.nomor_faktur_pemasok, p.tanggal_pembelian, p.metode_pembayaran, p.tanggal_jatuh_tempo, p.kode_akun_bank, s.nama_pemasok, p.total, p.status_batal FROM pembelian p LEFT JOIN pemasok s ON p.pemasok_id = s.id WHERE p.id = ?", [$idPembelian], 'i');

if (empty($pembelian_data)) {
    header('Location: ' . BASE_URL . 'index.php?halaman=404');
    exit;
}
$pembelian = $pembelian_data[0];
$detail = query("SELECT dp.kuantitas, dp.harga_saat_transaksi, dp.subtotal, pr.nama_produk, pr.satuan, pr.jenis_barang FROM detail_pembelian dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.pembelian_id = ?", [$idPembelian], 'i');
$judulHalaman = 'Detail Pembelian: ' . $pembelian['nomor_faktur_pemasok'];
?>
<?php  ?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Detail Transaksi Pembelian</h1>
    <div class="flex gap-2">
        <button onclick="konfirmasiHapus('sistem/proses/transaksi/proses_retur_pembelian.php', '<?= $idPembelian ?>', 'Yakin ingin meretur/membatalkan seluruh transaksi pembelian ini? Stok akan dikurangi dan jurnal pembelian akan dibalik (di-reverse).')" class="btn btn-error shadow-md"><i class="fas fa-undo mr-2"></i>Batalkan & Retur Pembelian</button>
        <a href="index.php?halaman=pembelian/lihat_pembelian" class="btn btn-ghost">← Kembali</a>
    </div>
</div>
<div class="card bg-base-100 shadow-xl border-t-4 border-warning">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4 font-mono text-primary">Faktur Pemasok: <?= htmlspecialchars($pembelian['nomor_faktur_pemasok']) ?></h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-base-200 p-4 rounded-lg border border-base-300">
            <div>
                <p class="mb-1"><strong>Tanggal Pembelian:</strong> <?= htmlspecialchars(date('d F Y', strtotime($pembelian['tanggal_pembelian']))) ?></p>
                <p class="mb-1"><strong>Metode Pembayaran:</strong> <span class="badge badge-neutral"><?= htmlspecialchars($pembelian['metode_pembayaran'] ?? 'Kredit') ?></span></p>
                <?php if ($pembelian['metode_pembayaran'] === 'Kredit'): ?>
                    <p class="text-error font-bold mt-2">Jatuh Tempo: <?= date('d F Y', strtotime($pembelian['tanggal_jatuh_tempo'])) ?></p>
                <?php elseif ($pembelian['metode_pembayaran'] === 'Transfer'): ?>
                    <p class="text-info font-bold mt-2">Sumber Dana Bank: <?= htmlspecialchars($pembelian['kode_akun_bank']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <p class="opacity-70 text-sm font-bold mb-1">Nama Pemasok / Supplier:</p>
                <p class="text-xl font-bold"><?= htmlspecialchars($pembelian['nama_pemasok']) ?></p>
            </div>
        </div>
        <div class="divider my-6">Rincian Barang Masuk</div>
        <div class="overflow-x-auto border border-base-200 rounded-lg shadow-sm">
            <table class="table w-full">
                <thead class="bg-base-200">
                    <tr>
                        <th class="w-12 text-center">No</th>
                        <th>Nama Produk / Jenis</th>
                        <th class="text-right">Harga Satuan Beli</th>
                        <th class="text-center">Kuantitas</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($detail as $item): ?>
                    <tr class="hover">
                        <th class="text-center"><?= $no++ ?></th>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($item['nama_produk']) ?></div>
                            <div class="text-xs opacity-70 badge badge-ghost badge-sm mt-1"><?= htmlspecialchars($item['jenis_barang']) ?></div>
                        </td>
                        <td class="text-right font-mono">Rp <?= number_format($item['harga_saat_transaksi'], 0, ',', '.') ?></td>
                        <td class="text-center font-bold text-lg"><?= $item['kuantitas'] ?> <span class="text-xs font-normal"><?= htmlspecialchars($item['satuan']) ?></span></td>
                        <td class="text-right font-mono font-bold text-primary">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="border-t-2 border-base-300 bg-base-100">
                    <tr class="font-bold text-xl">
                        <td colspan="4" class="text-right uppercase">Total Nilai Pembelian</td>
                        <td class="text-right text-warning font-black font-mono">Rp <?= number_format($pembelian['total'], 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php  ?>