<?php
$judulHalaman = 'Laporan Barang Terlaris';
cekAkses('lihat_laporan');

$tanggal_mulai = $_REQUEST['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_REQUEST['tanggal_selesai'] ?? date('Y-m-t');

$laporan = query("
    SELECT 
        p.nama_produk, 
        p.satuan, 
        SUM(dp.kuantitas) as total_terjual
    FROM detail_penjualan dp
    JOIN produk p ON dp.produk_id = p.id
    JOIN penjualan pj ON dp.penjualan_id = pj.id
    WHERE pj.tanggal_penjualan BETWEEN ? AND ?
    AND pj.status_batal = 0
    GROUP BY p.id, p.nama_produk, p.satuan
    ORDER BY total_terjual DESC
", [$tanggal_mulai, $tanggal_selesai], 'ss');

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Barang_Terlaris_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN BARANG TERLARIS']);
    fputcsv($output, ['Periode', $tanggal_mulai . ' s/d ' . $tanggal_selesai]);
    fputcsv($output, []);
    
    fputcsv($output, ['Peringkat', 'Nama Barang', 'Total Terjual', 'Satuan']);
    
    if (!empty($laporan)) {
        $no = 1;
        foreach ($laporan as $item) {
            fputcsv($output, [$no++, $item['nama_produk'], $item['total_terjual'], $item['satuan']]);
        }
    }
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6">Laporan Barang Terlaris</h1>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Pilih Periode Laporan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Mulai</span></label><input type="date" name="tanggal_mulai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_mulai) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Selesai</span></label><input type="date" name="tanggal_selesai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_selesai) ?>" required></div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Tampilkan Laporan</button></div>
            </div>
        </form>
    </div>
</div>

<div class="mb-6 flex gap-2 no-print">
    <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
    <form method="GET" action="" style="display: inline;">
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_barang_terlaris' ?>">
        <input type="hidden" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>">
        <input type="hidden" name="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai) ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Laporan Barang Terlaris</h3>
            <p>Periode: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_mulai))) ?> s/d <?= htmlspecialchars(date('d F Y', strtotime($tanggal_selesai))) ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Nama Barang</th>
                        <th class="text-right">Total Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)): ?>
                        <tr><td colspan="3" class="text-center">Tidak ada data penjualan pada periode ini.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($laporan as $item): ?>
                        <tr>
                            <th><?= $no++ ?></th>
                            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                            <td class="text-right"><?= number_format($item['total_terjual'], 0, ',', '.') ?> <?= htmlspecialchars($item['satuan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php  ?>