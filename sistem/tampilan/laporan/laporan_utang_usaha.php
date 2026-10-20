<?php
$judulHalaman = 'Laporan Saldo Utang Usaha';
cekAkses('lihat_laporan');

$tanggal_laporan = $_REQUEST['tanggal_laporan'] ?? date('Y-m-d');
$pemasok_filter_id = $_REQUEST['pemasok_id'] ?? '';

$pemasok_list = query("SELECT id, nama_pemasok FROM pemasok ORDER BY nama_pemasok ASC");

$where_clause = "";
$params = [];
if (!empty($pemasok_filter_id)) {
    $where_clause = "WHERE s.id = ?";
    $params[] = $pemasok_filter_id;
}

// 1. Perbaikan Query Laporan Utang yang Akurat & Kompatibel SQLite
$sql = "
    SELECT 
        s.id, 
        s.nama_pemasok,
        (
            (SELECT COALESCE(SUM(p.total), 0) FROM pembelian p WHERE p.pemasok_id = s.id AND p.metode_pembayaran = 'Kredit' AND p.tanggal_pembelian <= ?) 
            -
            (SELECT COALESCE(SUM(ju.debit), 0) FROM jurnal_umum ju WHERE ju.kode_akun = ? AND ju.nomor_bukti LIKE 'BYR-UTANG-' || s.id || '-%' AND ju.tanggal <= ?)
            -
            (SELECT COALESCE(SUM(ju.debit), 0) FROM jurnal_umum ju JOIN pembelian p ON ju.keterangan LIKE '%Retur Pembelian: ' || p.nomor_faktur_pemasok WHERE p.pemasok_id = s.id AND ju.kode_akun = ? AND ju.tanggal <= ?)
        ) AS saldo_akhir
    FROM pemasok s
    $where_clause
    ORDER BY s.nama_pemasok ASC
";

$akun_utang = $_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01';
$laporan_params = array_merge([$tanggal_laporan, $akun_utang, $tanggal_laporan, $akun_utang, $tanggal_laporan], $params);
$raw_laporan = query($sql, $laporan_params);

// 2. Filter hanya saldo yang tidak nol
$laporan = [];
$total_utang = 0;
foreach($raw_laporan as $row) {
    if ($row['saldo_akhir'] > 0) {
        $laporan[] = $row;
        $total_utang += $row['saldo_akhir'];
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Utang_Usaha_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN SALDO UTANG USAHA']);
    fputcsv($output, ['Per Tanggal', $tanggal_laporan]);
    fputcsv($output, []);
    
    fputcsv($output, ['No', 'Nama Pemasok', 'Sisa Utang (Rp)']);
    
    if (!empty($laporan)) {
        $no = 1;
        foreach ($laporan as $item) {
            fputcsv($output, [$no++, $item['nama_pemasok'], $item['saldo_akhir']]);
        }
        fputcsv($output, ['', 'Total Utang Usaha', $total_utang]);
    }
    
    fclose($output);
    exit;
}
?>
<h1 class="text-3xl font-bold mb-6">Laporan Rincian Saldo Utang Usaha per Pemasok</h1>
<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Filter Laporan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Per Tanggal</span></label><input type="date" name="tanggal_laporan" class="input input-bordered" value="<?= htmlspecialchars($tanggal_laporan) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Pemasok (Opsional)</span></label>
                    <select name="pemasok_id" class="select-pemasok">
                        <option value="">-- Semua Pemasok --</option>
                        <?php foreach($pemasok_list as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($pemasok_filter_id == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_pemasok']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Tampilkan</button></div>
            </div>
        </form>
    </div>
</div>
<div class="mb-6 flex gap-2 no-print">
    <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
    <form method="GET" action="" style="display: inline;">
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_utang_usaha' ?>">
        <input type="hidden" name="tanggal_laporan" value="<?= htmlspecialchars($tanggal_laporan) ?>">
        <input type="hidden" name="pemasok_id" value="<?= htmlspecialchars($pemasok_filter_id) ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4"><h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2><h3 class="text-xl">Laporan Saldo Utang Usaha</h3><p>Per Tanggal: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_laporan))) ?></p></div>
        <div class="overflow-x-auto"><table class="table w-full border border-base-300">
            <thead>
                <tr class="bg-base-200"><th>No</th><th>Nama Pemasok</th><th class="text-right">Sisa Utang (Rp)</th><th class="no-print text-center w-48">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($laporan)): ?>
                    <tr><td colspan="4" class="text-center py-6">Tidak ada saldo utang kredit yang tercatat pada tanggal ini.</td></tr>
                <?php else: $no = 1; foreach ($laporan as $item): ?>
                    <tr class="hover">
                        <th><?= $no++ ?></th>
                        <td class="font-bold"><?= htmlspecialchars($item['nama_pemasok']) ?></td>
                        <td class="text-right font-bold text-error font-mono">Rp <?= number_format($item['saldo_akhir'], 0, ',', '.') ?></td>
                        <td class="no-print text-center">
                            <button type="button" class="btn btn-sm btn-success shadow" onclick="window.bukaModalBayarUtang(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['nama_pemasok'])) ?>', <?= $item['saldo_akhir'] ?>)">Bayar / Lunasi Utang</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr class="font-bold text-lg bg-base-200">
                    <td colspan="2" class="text-right border-t-2 border-base-content">Total Utang Usaha BUMDes</td>
                    <td class="text-right border-t-2 border-base-content text-error font-mono">Rp <?= number_format($total_utang, 0, ',', '.') ?></td>
                    <td class="no-print border-t-2 border-base-content"></td>
                </tr>
            </tfoot>
        </table></div>
    </div>
</div>

<dialog id="modal_bayar_utang" class="modal no-print">
  <div class="modal-box border-t-4 border-success">
    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button></form>
    <h3 class="font-bold text-xl mb-4">Pelunasan Utang Pemasok</h3>
    <div class="bg-base-200 p-4 rounded-lg mb-4">
        <div class="text-sm opacity-70">Nama Pemasok:</div>
        <div class="font-bold text-lg" id="nama_pemasok_modal"></div>
    </div>
    <form action="<?= BASE_URL ?>sistem/proses/transaksi/proses_bayar_utang.php" method="POST">
        <?php csrfInput(); ?>
        <input type="hidden" name="pemasok_id" id="modal_pemasok_id">
        <input type="hidden" name="nama_pemasok" id="modal_nama_pemasok">
        
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Tanggal Pembayaran Transfer/Tunai</span></label>
            <input type="date" name="tanggal_bayar" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control mb-4">
                <label class="label"><span class="label-text opacity-70">Sisa Utang Terkalkulasi (Sistem)</span></label>
                <input type="text" id="modal_sisa_utang" class="input input-bordered font-bold text-error" readonly>
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold text-success">Jumlah yang Dibayar Sekarang (Rp)</span></label>
                <input type="text" name="jumlah_bayar" id="modal_jumlah_bayar" class="input input-bordered border-success format-rupiah text-lg font-black" required>
            </div>
        </div>

        <div class="form-control mb-6">
            <label class="label"><span class="label-text">Referensi / Keterangan</span></label>
            <input type="text" name="keterangan" class="input input-bordered" placeholder="Nomor kuitansi pelunasan / Bukti transfer bank" required>
        </div>
        <div class="modal-action">
            <button type="submit" class="btn btn-success text-white w-full shadow-lg text-lg"><i class="fas fa-check-circle mr-2"></i> Konfirmasi Pelunasan</button>
        </div>
    </form>
  </div>
</dialog>