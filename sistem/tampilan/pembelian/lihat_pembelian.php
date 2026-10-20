<?php
$judulHalaman = 'Riwayat Pembelian';
$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(p.nomor_faktur_pemasok LIKE ? OR s.nama_pemasok LIKE ?)";
    array_push($params, $searchTerm, $searchTerm);
}
if (!empty($tgl_mulai)) {
    $whereClauses[] = "p.tanggal_pembelian >= ?";
    $params[] = $tgl_mulai;
}
if (!empty($tgl_selesai)) {
    $whereClauses[] = "p.tanggal_pembelian <= ?";
    $params[] = $tgl_selesai;
}
$whereClauses[] = '(p.status_batal IS NULL OR p.status_batal = 0)';
$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$sql_select_from = "SELECT p.id, p.nomor_faktur_pemasok, p.tanggal_pembelian, p.metode_pembayaran, p.tanggal_jatuh_tempo, p.kode_akun_bank, s.nama_pemasok, p.total FROM pembelian p LEFT JOIN pemasok s ON p.pemasok_id = s.id";

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Riwayat_Pembelian_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No. Faktur Pemasok', 'Tanggal', 'Pemasok', 'Metode Bayar', 'Jatuh Tempo / Bank', 'Total (Rp)']);
    
    $semua_data = query($sql_select_from . " " . $whereSql . " ORDER BY p.tanggal_pembelian ASC, p.id ASC", $params);
    foreach ($semua_data as $item) {
        $info_tambahan = '-';
        if ($item['metode_pembayaran'] === 'Kredit') $info_tambahan = $item['tanggal_jatuh_tempo'];
        if ($item['metode_pembayaran'] === 'Transfer') $info_tambahan = $item['kode_akun_bank'];
        
        fputcsv($output, [$item['nomor_faktur_pemasok'], $item['tanggal_pembelian'], $item['nama_pemasok'], $item['metode_pembayaran'], $info_tambahan, $item['total']]);
    }
    fclose($output);
    exit;
}

$paginasi = query_paginasi($sql_select_from, $whereSql . " ORDER BY p.tanggal_pembelian DESC, p.id DESC", $params, 20);
$pembelian = $paginasi['data'];
?>
<?php  ?>
<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { box-shadow: none; border: none; }
    .hidden-print-header { display: block !important; }
}
.hidden-print-header { display: none; }
</style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold">Riwayat Pembelian Persediaan (Stok)</h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
        <a href="index.php?halaman=pembelian/form_pembelian" class="btn btn-primary shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Catat Pembelian Baru
        </a>
    </div>
</div>
<div class="card bg-base-100 shadow mb-6 border border-base-200 no-print">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="pembelian/lihat_pembelian">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" placeholder="Cari no. faktur atau pemasok..." class="input input-bordered md:col-span-2" value="<?= htmlspecialchars($search) ?>">
                <input type="date" name="tgl_mulai" class="input input-bordered" value="<?= htmlspecialchars($tgl_mulai) ?>">
                <input type="date" name="tgl_selesai" class="input input-bordered" value="<?= htmlspecialchars($tgl_selesai) ?>">
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow">Filter / Cari</button>
                    <button type="submit" name="export" value="csv" class="btn btn-success text-white w-48"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="printable-area bg-base-100 rounded-lg shadow">
    <div class="text-center mb-6 hidden-print-header">
        <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
        <h3 class="text-xl">Laporan Riwayat Pembelian</h3>
        <p>
            Filter: <?= !empty($search) ? htmlspecialchars($search) : 'Semua' ?> |
            Periode: <?= !empty($tgl_mulai) ? date('d/m/Y', strtotime($tgl_mulai)) : 'Awal' ?> s/d <?= !empty($tgl_selesai) ? date('d/m/Y', strtotime($tgl_selesai)) : 'Sekarang' ?>
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead class="bg-base-200">
                <tr>
                    <th>No</th>
                    <th>No. Faktur Pemasok</th>
                    <th>Tanggal</th>
                    <th>Nama Pemasok</th>
                    <th>Metode Pembayaran</th>
                    <th class="text-right">Total Transaksi</th>
                    <th class="no-print text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pembelian)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-6 text-base-content/60">Belum ada data transaksi pembelian.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = (($paginasi['halaman_sekarang'] - 1) * 20) + 1; $total_seluruh = 0; ?>
                    <?php foreach ($pembelian as $item) : $total_seluruh += $item['total']; ?>
                    <tr class="hover border-b border-base-200">
                        <th><?= $no++; ?></th>
                        <td class="font-mono font-bold text-sm text-primary"><?= htmlspecialchars($item['nomor_faktur_pemasok']); ?></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_pembelian']))); ?></td>
                        <td class="font-bold"><?= htmlspecialchars($item['nama_pemasok']); ?></td>
                        <td>
                            <div class="font-medium"><?= htmlspecialchars($item['metode_pembayaran']); ?></div>
                            <?php if ($item['metode_pembayaran'] === 'Kredit'): ?>
                                <div class="text-xs text-error font-semibold mt-1">Tempo: <?= date('d/m/y', strtotime($item['tanggal_jatuh_tempo'])) ?></div>
                            <?php elseif ($item['metode_pembayaran'] === 'Transfer'): ?>
                                <div class="text-xs text-info font-mono mt-1"><?= $item['kode_akun_bank'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-right font-bold text-warning font-mono">Rp <?= number_format($item['total'], 0, ',', '.'); ?></td>
                        <td class="no-print text-center">
                            <a href="index.php?halaman=pembelian/detail_pembelian&id=<?= $item['id']; ?>" class="btn btn-sm btn-info text-white">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($pembelian)): ?>
            <tfoot class="hidden-print-header">
                <tr class="bg-base-200 font-bold text-lg">
                    <td colspan="5" class="text-right">TOTAL AKTIF HALAMAN INI</td>
                    <td class="text-right text-warning font-mono">Rp <?= number_format($total_seluruh, 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<div class="flex justify-center mt-6 no-print">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>
<?php  ?>