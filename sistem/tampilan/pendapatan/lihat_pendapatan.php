<?php
$judulHalaman = 'Penerimaan Pendapatan Lain-lain';
$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';
$whereClauses = [];
$params = [];

$whereClauses[] = "ju.nomor_bukti LIKE 'INC-%'";
$whereClauses[] = "ju.kredit > 0"; // Kita hanya ambil baris pendapatan (kredit) untuk list

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(ju.keterangan LIKE ? OR da.nama_akun LIKE ?)";
    array_push($params, $searchTerm, $searchTerm);
}
if (!empty($tgl_mulai)) {
    $whereClauses[] = "ju.tanggal >= ?";
    $params[] = $tgl_mulai;
}
if (!empty($tgl_selesai)) {
    $whereClauses[] = "ju.tanggal <= ?";
    $params[] = $tgl_selesai;
}
$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$sql_select_from = "SELECT ju.id, ju.nomor_bukti, ju.tanggal, ju.keterangan, ju.kredit as nominal, da.nama_akun as jenis_pendapatan FROM jurnal_umum ju JOIN daftar_akun da ON ju.kode_akun = da.kode_akun";

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Penerimaan_Pendapatan_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tanggal', 'No. Bukti', 'Keterangan', 'Jenis Pendapatan', 'Nominal (Rp)']);
    
    $semua_data = query($sql_select_from . " " . $whereSql . " ORDER BY ju.tanggal ASC, ju.id ASC", $params);
    foreach ($semua_data as $item) {
        fputcsv($output, [$item['tanggal'], $item['nomor_bukti'], $item['keterangan'], $item['jenis_pendapatan'], $item['nominal']]);
    }
    fclose($output);
    exit;
}

$paginasi = query_paginasi($sql_select_from, $whereSql . " ORDER BY ju.tanggal DESC, ju.id DESC", $params, 20);
$pendapatan = $paginasi['data'];
?>
<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { box-shadow: none; border: none; }
    .hidden-print-header { display: block !important; }
}
.hidden-print-header { display: none; }
</style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold">Penerimaan Pendapatan Lainnya</h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
        <a href="index.php?halaman=pendapatan/form_pendapatan" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Catat Penerimaan
        </a>
    </div>
</div>
<div class="card bg-base-100 shadow mb-6 no-print">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="pendapatan/lihat_pendapatan">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" placeholder="Cari keterangan atau jenis..." class="input input-bordered md:col-span-2" value="<?= htmlspecialchars($search) ?>">
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
<div class="printable-area">
    <div class="text-center mb-6 hidden-print-header">
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
        <h3 class="text-xl">Laporan Rincian Pendapatan Lainnya</h3>
        <p>
            Filter: <?= !empty($search) ? htmlspecialchars($search) : 'Semua' ?> |
            Periode: <?= !empty($tgl_mulai) ? date('d/m/Y', strtotime($tgl_mulai)) : 'Awal' ?> s/d <?= !empty($tgl_selesai) ? date('d/m/Y', strtotime($tgl_selesai)) : 'Sekarang' ?>
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr><th>No</th><th>Tanggal</th><th>No. Bukti</th><th>Keterangan</th><th>Jenis Pendapatan</th><th class="text-right">Nominal</th><th class="no-print">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($pendapatan)): ?>
                    <tr><td colspan="7" class="text-center py-6">Belum ada data penerimaan pendapatan.</td></tr>
                <?php else: ?>
                    <?php $no = (($paginasi['halaman_sekarang'] - 1) * 20) + 1; $total_seluruh = 0; ?>
                    <?php foreach ($pendapatan as $item) : $total_seluruh += $item['nominal']; ?>
                    <tr class="hover">
                        <th><?= $no++; ?></th>
                        <td class="whitespace-nowrap"><?= htmlspecialchars(date('d F Y', strtotime($item['tanggal']))); ?></td>
                        <td class="font-mono text-sm"><?= htmlspecialchars($item['nomor_bukti']); ?></td>
                        <td><?= htmlspecialchars($item['keterangan']); ?></td>
                        <td><span class="badge badge-ghost"><?= htmlspecialchars($item['jenis_pendapatan']); ?></span></td>
                        <td class="text-right font-bold text-success">Rp <?= number_format($item['nominal'], 0, ',', '.'); ?></td>
                        <td class="flex gap-2 no-print">
                            <button onclick="konfirmasiHapus('sistem/proses/pendapatan/hapus_pendapatan.php', '<?= $item['nomor_bukti']; ?>', 'Yakin ingin menghapus dan membatalkan transaksi penerimaan ini?')" class="btn btn-sm btn-error">Batalkan</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($pendapatan)): ?>
            <tfoot class="hidden-print-header">
                <tr class="bg-base-200">
                    <td colspan="5" class="text-right font-bold text-lg">TOTAL HALAMAN INI</td>
                    <td class="text-right font-bold text-success text-lg">Rp <?= number_format($total_seluruh, 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<div class="flex justify-center no-print">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>