<?php
$judulHalaman = 'Kartu Stok (Buku Mutasi Barang)';
cekAkses('kelola_produk_stok');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok');
    exit;
}
$idProduk = (int)$_GET['id'];

$produk_q = query("SELECT * FROM produk WHERE id = ?", [$idProduk], 'i');
if(empty($produk_q)) {
    header('Location: ' . BASE_URL . 'index.php?halaman=404'); exit;
}
$produk = $produk_q[0];

$whereClause = "";
$params = [$idProduk, $idProduk];
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01', strtotime('-1 months'));
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $whereClause = " AND DATE(tanggal) BETWEEN ? AND ?";
    array_push($params, $tgl_mulai, $tgl_selesai, $tgl_mulai, $tgl_selesai);
}

// Rekonstruksi pergerakan stok dari transaksi asli. 
// Catatan: Karena sistem ini tidak memiliki tabel kartu_stok independen dari awal,
// mutasi dihitung on-the-fly dari relasi tabel penjualan & pembelian.
$sql_mutasi = "
    SELECT * FROM (
        SELECT 'Pembelian' as jenis, p.tanggal_pembelian as tanggal, dp.kuantitas as masuk, 0 as keluar, p.nomor_faktur_pemasok as referensi
        FROM detail_pembelian dp JOIN pembelian p ON dp.pembelian_id = p.id WHERE dp.produk_id = ?
        UNION ALL
        SELECT 'Penjualan' as jenis, pj.tanggal_penjualan as tanggal, 0 as masuk, dp.kuantitas as keluar, pj.nomor_faktur as referensi
        FROM detail_penjualan dp JOIN penjualan pj ON dp.penjualan_id = pj.id WHERE dp.produk_id = ? AND pj.status_batal = 0
    ) as mutasi
    WHERE 1=1 $whereClause
    ORDER BY tanggal ASC
";

$mutasi_list = query($sql_mutasi, $params);

// Menghitung Saldo Awal (sebelum tanggal filter) - Agak kompleks tanpa tabel stok murni
// Asumsi sederhana: Saldo berjalan dihitung dari 0 atau jika butuh saldo akurat, 
// sistem ini mengandalkan stok akhir di tabel master 'produk'.
// Agar laporan ini masuk akal, kita asumsikan ini menampilkan 'semua' atau berjalan maju.
?>

<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { background-color: white; color: black; }
    .table td, .table th { border: 1px solid #000 !important; }
}
</style>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Kartu Stok</button>
        <a href="index.php?halaman=produk_stok/lihat_stok&jenis=<?= urlencode($produk['jenis_barang']) ?>" class="btn btn-ghost">Kembali ke Daftar</a>
    </div>
</div>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="produk_stok/kartu_stok">
            <input type="hidden" name="id" value="<?= $idProduk ?>">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Dari Tanggal</span></label><input type="date" name="tgl_mulai" class="input input-bordered" value="<?= htmlspecialchars($tgl_mulai) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Sampai Tanggal</span></label><input type="date" name="tgl_selesai" class="input input-bordered" value="<?= htmlspecialchars($tgl_selesai) ?>" required></div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Saring Mutasi</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body p-6">
        <div class="text-center mb-8 border-b border-base-300 pb-4">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'ENTITAS') ?></h2>
            <h3 class="text-xl">BUKU MUTASI KARTU STOK</h3>
            <p>Periode: <?= date('d M Y', strtotime($tgl_mulai)) ?> s/d <?= date('d M Y', strtotime($tgl_selesai)) ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-base-200 p-4 rounded-lg border border-base-300">
            <div>
                <p class="text-sm opacity-70">Kode Barang:</p>
                <p class="font-mono font-bold text-lg"><?= htmlspecialchars($produk['kode_produk']) ?></p>
            </div>
            <div>
                <p class="text-sm opacity-70">Nama / Rincian Barang:</p>
                <p class="font-bold text-lg"><?= htmlspecialchars($produk['nama_produk']) ?></p>
            </div>
            <div>
                <p class="text-sm opacity-70">Satuan Dasar:</p>
                <p class="font-bold"><?= htmlspecialchars($produk['satuan']) ?></p>
            </div>
            <div>
                <p class="text-sm opacity-70">Stok Akhir Sistem Saat Ini (Real-Time):</p>
                <p class="font-bold text-2xl text-primary"><?= htmlspecialchars($produk['stok']) ?> <?= htmlspecialchars($produk['satuan']) ?></p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full border border-base-300">
                <thead>
                    <tr class="bg-base-200">
                        <th class="border text-center">Tanggal</th>
                        <th class="border">No. Referensi / Bukti</th>
                        <th class="border">Uraian / Keterangan</th>
                        <th class="border text-center">Masuk (+)</th>
                        <th class="border text-center">Keluar (-)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($mutasi_list)): ?>
                        <tr><td colspan="5" class="text-center py-6 border italic opacity-60">Tidak ada riwayat pergerakan stok keluar/masuk (pembelian/penjualan) pada rentang waktu ini.</td></tr>
                    <?php else: 
                        $total_masuk = 0; $total_keluar = 0;
                        foreach($mutasi_list as $m): 
                            $total_masuk += $m['masuk'];
                            $total_keluar += $m['keluar'];
                    ?>
                    <tr class="hover">
                        <td class="border text-center"><?= date('d/m/Y', strtotime($m['tanggal'])) ?></td>
                        <td class="border font-mono text-sm"><?= htmlspecialchars($m['referensi']) ?></td>
                        <td class="border"><?= htmlspecialchars($m['jenis']) ?></td>
                        <td class="border text-center text-success font-bold"><?= $m['masuk'] > 0 ? $m['masuk'] : '-' ?></td>
                        <td class="border text-center text-error font-bold"><?= $m['keluar'] > 0 ? $m['keluar'] : '-' ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if(!empty($mutasi_list)): ?>
                <tfoot>
                    <tr class="bg-base-200 font-bold text-lg border-t-2 border-base-content">
                        <td colspan="3" class="text-right border pr-6">TOTAL MUTASI PERIODE INI</td>
                        <td class="border text-center text-success"><?= $total_masuk ?></td>
                        <td class="border text-center text-error"><?= $total_keluar ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            
            <p class="text-xs opacity-60 mt-4 no-print">*Catatan Laporan: Kartu stok mutasi ini disajikan berdasarkan rekapan otomatis dokumen transaksi pembelian dan penjualan bersih yang terdaftar di sistem. Penyesuaian stok fisik (Opname / Pecah Karung) mungkin tidak tercermin dalam daftar ini.</p>
        </div>
    </div>
</div>