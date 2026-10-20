<?php
$judulHalaman = 'Laporan Kebutuhan Stok';
cekAkses('lihat_laporan');
$produk_menipis = query("SELECT kode_produk, nama_produk, satuan, stok, stok_minimum FROM produk WHERE stok <= stok_minimum AND status_aktif = 1 ORDER BY nama_produk ASC");

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Kebutuhan_Stok_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN KEBUTUHAN STOK']);
    fputcsv($output, ['Per Tanggal', date('d F Y')]);
    fputcsv($output, []);
    
    fputcsv($output, ['No', 'Kode Barang', 'Nama Barang', 'Stok Saat Ini', 'Stok Minimum', 'Butuh Beli', 'Satuan']);
    
    if (!empty($produk_menipis)) {
        $no = 1;
        foreach ($produk_menipis as $item) {
            fputcsv($output, [$no++, $item['kode_produk'], $item['nama_produk'], $item['stok'], $item['stok_minimum'], ($item['stok_minimum'] - $item['stok']), $item['satuan']]);
        }
    }
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold">Laporan Kebutuhan Stok</h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            Cetak Daftar Belanja
        </button>
        <form method="GET" action="" style="display: inline;">
            <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_kebutuhan_stok' ?>">
            <input type="hidden" name="export" value="csv">
            <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
        </form>
    </div>
</div>
<p class="mb-6 text-base-content/80 no-print">Daftar ini berisi semua barang yang stoknya sudah mencapai atau di bawah batas minimum yang telah ditentukan. Gunakan daftar ini sebagai panduan untuk melakukan pembelian ulang.</p>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-6 hidden-print-header">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Daftar Kebutuhan Stok</h3>
            <p>Per Tanggal: <?= htmlspecialchars(date('d F Y')) ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Minimum</th>
                        <th class="text-center">Butuh Beli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($produk_menipis)): ?>
                        <tr><td colspan="6" class="text-center py-4">
                            <div class="text-success font-bold">Hebat! Semua stok masih di atas batas minimum.</div>
                        </td></tr>
                    <?php else: ?>
                        <?php $no = 1; ?>
                        <?php foreach ($produk_menipis as $item): ?>
                        <tr>
                            <th><?= $no++ ?></th>
                            <td><?= htmlspecialchars($item['kode_produk']) ?></td>
                            <td><?= htmlspecialchars($item['nama_produk']) ?></td>
                            <td class="text-center font-bold text-error"><?= htmlspecialchars($item['stok']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($item['stok_minimum']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                            <td class="text-center font-bold"><?= ($item['stok_minimum'] - $item['stok']) ?> <?= htmlspecialchars($item['satuan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { box-shadow: none; border: none; }
    .hidden-print-header { display: block !important; }
}
.hidden-print-header { display: none; }
</style>
<?php  ?>