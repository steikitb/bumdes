<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman_anggota/lihat_pinjaman');
    exit;
}
$id = (int)$_GET['id'];
// FIX ERROR PAGINASI: Menghilangkan argument tipe data 'i' pada semua query() yang menyebabkan fatal error
$pinjaman_data = query("SELECT p.*, a.nama_lengkap, a.alamat FROM pinjaman_anggota p JOIN anggota a ON p.anggota_id = a.id WHERE p.id=?", [$id]);
if (empty($pinjaman_data)) {
    header('Location: ' . BASE_URL . 'index.php?halaman=404');
    exit;
}
$pinjaman = $pinjaman_data[0];

$sql_select_pembayaran = "SELECT * FROM pembayaran_angsuran";
$where_pembayaran = "WHERE pinjaman_anggota_id = ? ORDER BY tanggal_bayar ASC";
// Get ALL data for printing (tanpa tipe 'i')
$pembayaran_all = query($sql_select_pembayaran . " " . $where_pembayaran, [$id]);

// For regular UI pagination (Perbaikan: Menghapus argumen 'i' dari query_paginasi)
$paginasi_pembayaran = query_paginasi($sql_select_pembayaran, "WHERE pinjaman_anggota_id = ? ORDER BY tanggal_bayar DESC", [$id], 10);
$pembayaran_ui = $paginasi_pembayaran['data'];

$total_dibayar_q = query("SELECT SUM(pokok_dibayar) as total FROM pembayaran_angsuran WHERE pinjaman_anggota_id=?", [$id]);
$total_dibayar = $total_dibayar_q[0]['total'] ?? 0;

$sisa_pokok = max(0, $pinjaman['jumlah_pokok'] - $total_dibayar);
// Guard divide-by-zero untuk data lama dgn jangka_waktu_bulan=0/null
$jangka_waktu = (int)($pinjaman['jangka_waktu_bulan'] ?? 0);
$angsuran_pokok_per_bulan = ($jangka_waktu > 0) ? $pinjaman['jumlah_pokok'] / $jangka_waktu : 0;
$angsuran_bunga_per_bulan = ($pinjaman['jumlah_pokok'] * ($pinjaman['bunga_persen_tahun'] / 100)) / 12;
$judulHalaman = 'Buku Pembantu Piutang: ' . $pinjaman['nama_lengkap'];
?>

<style>
@media print {
    .no-print { display: none !important; }
    body, .printable-area { margin: 0; padding: 0; background: white; color: black; }
    .printable-area { display: block !important; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid black; padding: 5px; font-size: 12px; }
}
.printable-area { display: none; }
</style>

<div class="flex justify-between items-center mb-6 no-print">
    <h1 class="text-3xl font-bold">Detail Piutang: <?= htmlspecialchars($pinjaman['nama_lengkap']) ?></h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Buku Pembantu</button>
        <a href="index.php?halaman=pinjaman_anggota/lihat_pinjaman" class="btn btn-ghost">← Kembali</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 no-print">
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title border-b pb-2">Riwayat Pembayaran Angsuran</h2>
                 <div class="overflow-x-auto"><table class="table w-full"><thead><tr><th>Tanggal</th><th>Pokok Dibayar</th><th>Bunga Dibayar</th><th>Keterangan</th></tr></thead><tbody>
                    <?php if (empty($pembayaran_ui)): ?>
                        <tr><td colspan="4" class="text-center">Belum ada pembayaran.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pembayaran_ui as $item): ?>
                        <tr class="hover">
                            <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_bayar']))) ?></td>
                            <td class="font-mono">Rp <?= number_format($item['pokok_dibayar'], 0, ',', '.') ?></td>
                            <td class="font-mono">Rp <?= number_format($item['bunga_dibayar'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($item['keterangan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody></table></div>
                <div class="flex justify-center mt-4">
                    <?php render_paginasi($paginasi_pembayaran['total_halaman'], $paginasi_pembayaran['halaman_sekarang']); ?>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="card bg-base-100 shadow-xl mb-6 border-t-4 border-info">
            <div class="card-body bg-base-200 p-6 rounded-b-xl">
                <h2 class="card-title text-primary"><i class="fas fa-info-circle mr-2"></i> Informasi Fasilitas Kredit</h2>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Jenis Pinjaman:</span>
                        <span class="font-bold text-info"><?= htmlspecialchars($pinjaman['jenis_pinjaman'] ?? 'Umum') ?></span>
                    </div>
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Plafon Pinjaman:</span>
                        <span class="font-bold font-mono">Rp <?= number_format($pinjaman['jumlah_pokok'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Biaya Administrasi:</span>
                        <span class="font-bold font-mono text-warning">Rp <?= number_format($pinjaman['biaya_admin'] ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Netto Diterima Nasabah:</span>
                        <span class="font-bold font-mono text-success">Rp <?= number_format($pinjaman['jumlah_pokok'] - ($pinjaman['biaya_admin'] ?? 0), 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Metode Pencairan:</span>
                        <span class="font-bold"><?= htmlspecialchars($pinjaman['metode_pencairan'] ?? 'Tunai') ?></span>
                    </div>
                    <div class="flex justify-between border-b border-base-300 pb-1">
                        <span class="opacity-70">Tenor & Bunga:</span>
                        <span class="font-bold"><?= $pinjaman['jangka_waktu_bulan'] ?> Bln / <?= $pinjaman['bunga_persen_tahun'] ?>% p.a</span>
                    </div>
                </div>
                
                <div class="mt-6 bg-error/10 p-4 rounded-lg border border-error text-center">
                    <div class="text-sm font-bold text-error uppercase">Sisa Pokok Tagihan Berjalan</div>
                    <div class="text-3xl font-black text-error font-mono mt-1">Rp <?= number_format($sisa_pokok, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl"><div class="card-body"><h2 class="card-title border-b pb-2"><i class="fas fa-hand-holding-usd mr-2 text-success"></i>Catat Angsuran Baru</h2>
            <form action="<?= BASE_URL ?>sistem/proses/pinjaman_anggota/proses_pembayaran.php" method="POST">
                 <?php csrfInput(); ?>
                 <input type="hidden" name="pinjaman_id" value="<?= $id ?>">
                 <div class="form-control mb-2"><label class="label"><span class="label-text">Tanggal Bayar</span></label><input type="date" name="tanggal_bayar" class="input input-sm input-bordered" required value="<?= date('Y-m-d') ?>"></div>
                 <div class="form-control mb-2"><label class="label"><span class="label-text">Pokok Dibayar (Rp)</span></label><input type="text" name="pokok_dibayar" value="<?= round($angsuran_pokok_per_bulan, 0) ?>" class="input input-sm input-bordered format-rupiah" required></div>
                 <div class="form-control mb-2"><label class="label"><span class="label-text">Bunga Dibayar (Rp)</span></label><input type="text" name="bunga_dibayar" value="<?= round($angsuran_bunga_per_bulan, 0) ?>" class="input input-sm input-bordered format-rupiah" required></div>
                 <div class="form-control mb-2"><label class="label"><span class="label-text">Keterangan Referensi</span></label><input type="text" name="keterangan" placeholder="Kuitansi / Bukti / Bulan ke-" class="input input-sm input-bordered"></div>
                 <button type="submit" class="btn btn-primary w-full mt-4 shadow-lg text-white" <?= ($sisa_pokok <= 0) ? 'disabled' : '' ?>><i class="fas fa-save mr-2"></i>Simpan Pembayaran Masuk</button>
            </form>
        </div></div>
    </div>
</div>

<!-- Tampilan Khusus Cetak Buku Pembantu Piutang -->
<div class="printable-area">
    <h2 style="text-align: center; margin-bottom: 20px;">BUKU PEMBANTU PIUTANG (PINJAMAN)</h2>
    <div style="margin-bottom: 20px; font-size: 14px;">
        <table style="width: 100%; border: none;">
            <tr style="border: none;"><td style="border: none; width: 120px;"><strong>Nama Debitur</strong></td><td style="border: none;">: <?= htmlspecialchars($pinjaman['nama_lengkap']) ?></td></tr>
            <tr style="border: none;"><td style="border: none;"><strong>Alamat</strong></td><td style="border: none;">: <?= htmlspecialchars($pinjaman['alamat']) ?></td></tr>
            <tr style="border: none;"><td style="border: none;"><strong>Tgl Realisasi</strong></td><td style="border: none;">: <?= date('d M Y', strtotime($pinjaman['tanggal_pinjaman'])) ?></td></tr>
            <tr style="border: none;"><td style="border: none;"><strong>Plafon Pinjaman</strong></td><td style="border: none;">: Rp <?= number_format($pinjaman['jumlah_pokok'], 0, ',', '.') ?> (<?= htmlspecialchars($pinjaman['jenis_pinjaman'] ?? 'Umum') ?>)</td></tr>
            <tr style="border: none;"><td style="border: none;"><strong>Jangka Waktu</strong></td><td style="border: none;">: <?= $pinjaman['jangka_waktu_bulan'] ?> Bulan</td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Keterangan</th>
                <th colspan="2">Pembayaran</th>
                <th rowspan="2">Sisa Saldo Pokok (Rp)</th>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <th>Pokok (Rp)</th>
                <th>Bunga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">-</td>
                <td><?= date('d M Y', strtotime($pinjaman['tanggal_pinjaman'])) ?></td>
                <td>Pencairan Dana (Awal)</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;"><strong><?= number_format($pinjaman['jumlah_pokok'], 0, ',', '.') ?></strong></td>
            </tr>
            <?php $no = 1; $saldo_running = $pinjaman['jumlah_pokok']; ?>
            <?php foreach ($pembayaran_all as $item): 
                $saldo_running -= $item['pokok_dibayar'];
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td><?= date('d M Y', strtotime($item['tanggal_bayar'])) ?></td>
                <td><?= htmlspecialchars($item['keterangan']) ?></td>
                <td style="text-align: right;"><?= number_format($item['pokok_dibayar'], 0, ',', '.') ?></td>
                <td style="text-align: right;"><?= number_format($item['bunga_dibayar'], 0, ',', '.') ?></td>
                <td style="text-align: right;"><strong><?= number_format($saldo_running, 0, ',', '.') ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="margin-top: 30px; text-align: right; font-size: 14px;">
        <p>Dicetak tanggal: <?= date('d M Y H:i:s') ?></p>
        <p>Penanggung Jawab / Bendahara</p>
        <br><br><br>
        <p>( <?= htmlspecialchars($_PENGATURAN['nama_akuntan'] ?? '...........................') ?> )</p>
    </div>
</div>