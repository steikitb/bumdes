<?php
$judulHalaman = 'Data Pengeluaran Beban';
$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(bo.keterangan LIKE ? OR kb.nama_kategori LIKE ?)";
    array_push($params, $searchTerm, $searchTerm);
}
if (!empty($tgl_mulai)) {
    $whereClauses[] = "bo.tanggal_beban >= ?";
    $params[] = $tgl_mulai;
}
if (!empty($tgl_selesai)) {
    $whereClauses[] = "bo.tanggal_beban <= ?";
    $params[] = $tgl_selesai;
}
$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$sql_select_from = "SELECT bo.*, kb.nama_kategori FROM beban_operasional bo JOIN kategori_beban kb ON bo.kategori_beban_id = kb.id";

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Riwayat_Pengeluaran_Beban_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tanggal', 'Keterangan', 'Kategori Beban', 'Jumlah/Beban (Rp)', 'Status']);
    
    $semua_data = query($sql_select_from . " " . $whereSql . " ORDER BY bo.tanggal_beban ASC, bo.id ASC", $params);
    foreach ($semua_data as $item) {
        $status = $item['status_batal'] == 1 ? 'Dibatalkan' : 'Aktif';
        fputcsv($output, [$item['tanggal_beban'], $item['keterangan'], $item['nama_kategori'], $item['jumlah'], $status]);
    }
    fclose($output);
    exit;
}

$paginasi = query_paginasi($sql_select_from, $whereSql . " ORDER BY bo.tanggal_beban DESC, bo.id DESC", $params, 20);
$beban = $paginasi['data'];
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
    <h1 class="text-3xl font-bold">Data Pengeluaran Beban</h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
        <a href="index.php?halaman=beban/form_beban" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Catat Pengeluaran
        </a>
    </div>
</div>

<div class="card bg-base-100 shadow mb-6 no-print">
    <div class="card-body p-4">
        <form method="GET" action="">
            <input type="hidden" name="halaman" value="beban/lihat_beban">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" placeholder="Cari keterangan atau kategori..." class="input input-bordered md:col-span-2" value="<?= htmlspecialchars($search) ?>">
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
        <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
        <h3 class="text-xl font-bold">LAPORAN RINCIAN BIAYA OPERASIONAL</h3>
        <p>Periode: <?= date('d M Y', strtotime($tgl_mulai)) ?> s/d <?= date('d M Y', strtotime($tgl_selesai)) ?></p>
    </div>

    <!-- Tabel Rincian -->
    <div class="overflow-x-auto">
        <table class="table w-full border border-base-300">
            <thead>
                <tr class="bg-base-200">
                    <th class="border text-center w-12">No</th>
                    <th class="border text-center">Tanggal</th>
                    <th class="border">Keterangan Pengeluaran</th>
                    <th class="border">Kategori Beban</th>
                    <th class="border text-right">Jumlah / Beban (Rp)</th>
                    <th class="border no-print text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($beban)): ?>
                    <tr><td colspan="6" class="text-center border py-6 text-base-content/60">Data pengeluaran operasional tidak ditemukan.</td></tr>
                <?php else: ?>
                    <?php $no = (($paginasi['halaman_sekarang'] - 1) * 20) + 1; $total_seluruh = 0; ?>
                    <?php foreach ($beban as $item) : 
                        if ($item['status_batal'] == 0) { $total_seluruh += $item['jumlah']; }
                    ?>
                    <tr class="hover <?= $item['status_batal'] == 1 ? 'opacity-50 line-through text-base-content/50' : '' ?>">
                        <td class="border text-center"><?= $no++; ?></td>
                        <td class="border text-center"><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_beban']))); ?></td>
                        <td class="border">
                            <?= htmlspecialchars($item['keterangan']); ?>
                            <?php if ($item['status_batal'] == 1): ?>
                                <br><span class="badge badge-error badge-xs mt-1">Dibatalkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="border">
                            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($item['nama_kategori']); ?></span>
                        </td>
                        <td class="border text-right font-mono font-bold">Rp <?= number_format($item['jumlah'], 0, ',', '.'); ?></td>
                        <td class="border flex gap-2 justify-center no-print">
                            <?php if ($item['status_batal'] == 1): ?>
                                -
                            <?php else: ?>
                                <a href="index.php?halaman=beban/form_beban&id=<?= $item['id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                                <button onclick="konfirmasiHapus('sistem/proses/beban/hapus_beban.php', '<?= $item['id']; ?>', 'Yakin ingin menghapus transaksi beban ini? Jurnal akan dibalik secara otomatis.')" class="btn btn-xs btn-error">Batalkan</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($beban)): ?>
            <tfoot class="hidden-print-header">
                <tr class="bg-base-200 font-bold text-lg">
                    <td colspan="4" class="border text-right">TOTAL PENGELUARAN AKTIF HALAMAN INI</td>
                    <td class="border text-right text-primary font-mono">Rp <?= number_format($total_seluruh, 0, ',', '.'); ?></td>
                    <td class="border"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<div class="flex justify-center no-print mt-6">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>