<?php
$judulHalaman = 'Buku Kas Umum (BKU)';
cekAkses('lihat_laporan');

$bulan_filter = $_REQUEST['bulan'] ?? date('Y-m');
$tgl_mulai = $bulan_filter . '-01';
$tgl_selesai = date('Y-m-t', strtotime($tgl_mulai));
$tgl_awal_minus_1 = date('Y-m-d', strtotime($tgl_mulai . ' -1 day'));

// Ambil SEMUA akun kas & bank (1.1.01*) dari Bagan Akun
$akun_kas_q = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kelompok_akun = 'Aset'");
$kas_codes_all = array_column($akun_kas_q, 'kode_akun');
if (empty($kas_codes_all)) {
    $kas_codes_all = [($_PENGATURAN['akun_map_kas'] ?? '1.1.01.01'), ($_PENGATURAN['akun_map_bank'] ?? '1.1.01.02')];
}
$kas_codes = array_values(array_unique($kas_codes_all));
$ph_kas = implode(',', array_fill(0, count($kas_codes), '?'));

$saldo_awal = 0;
foreach ($kas_codes as $kc) {
    $saldo_awal += getSaldoAkun($kc, $tgl_awal_minus_1);
}

$mutasi = query("
    SELECT ju.*, da.nama_akun 
    FROM jurnal_umum ju
    JOIN daftar_akun da ON ju.kode_akun = da.kode_akun
    WHERE ju.kode_akun IN ($ph_kas) AND ju.tanggal BETWEEN ? AND ?
    ORDER BY ju.tanggal ASC, ju.id ASC
", array_merge($kas_codes, [$tgl_mulai, $tgl_selesai]));

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=BKU_' . $bulan_filter . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['BUKU KAS UMUM (BKU)']);
    fputcsv($output, ['Bulan', $bulan_filter]);
    fputcsv($output, []);
    
    fputcsv($output, ['Tanggal', 'No. Bukti', 'Uraian Transaksi', 'Via Akun', 'Penerimaan (Rp)', 'Pengeluaran (Rp)', 'Saldo Kas (Rp)']);
    
    $saldo_running = $saldo_awal;
    fputcsv($output, [$tgl_mulai, '-', 'Saldo Awal Bulan', '-', 0, 0, $saldo_running]);
    
    if (!empty($mutasi)) {
        foreach ($mutasi as $item) {
            $penerimaan = $item['debit'];
            $pengeluaran = $item['kredit'];
            $saldo_running = $saldo_running + $penerimaan - $pengeluaran;
            fputcsv($output, [$item['tanggal'], $item['nomor_bukti'], $item['keterangan'], $item['nama_akun'], $penerimaan, $pengeluaran, $saldo_running]);
        }
    }
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { box-shadow: none; border: none; }
    .hidden-print-header { display: block !important; }
    .table td, .table th { border: 1px solid black !important; padding: 4px !important; }
}
.hidden-print-header { display: none; }
</style>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak BKU</button>
        <form method="GET" action="" style="display: inline;">
            <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_buku_kas_umum' ?>">
            <input type="hidden" name="bulan" value="<?= htmlspecialchars($bulan_filter) ?>">
            <input type="hidden" name="export" value="csv">
            <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
        </form>
    </div>
</div>
<p class="mb-4 text-base-content/80 no-print">Laporan Buku Kas Umum (BKU) menyajikan rekapan seluruh penerimaan dan pengeluaran Kas dan Bank secara kronologis sesuai dengan standar format pembukuan entitas publik/desa.</p>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Pilih Bulan Pembukuan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Periode Bulan</span></label>
                    <input type="month" name="bulan" class="input input-bordered" value="<?= htmlspecialchars($bulan_filter) ?>" required>
                </div>
                <div class="form-control">
                    <button type="submit" class="btn btn-primary w-full">Tampilkan BKU</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-6 hidden-print-header">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl font-bold uppercase">BUKU KAS UMUM (BKU)</h3>
            <p>Bulan: <?= htmlspecialchars(date('F Y', strtotime($tgl_mulai))) ?></p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr class="bg-base-200">
                        <th class="border text-center">TANGGAL</th>
                        <th class="border text-center">NO. BUKTI</th>
                        <th class="border text-center">URAIAN TRANSAKSI</th>
                        <th class="border text-center">PENERIMAAN (Rp)</th>
                        <th class="border text-center">PENGELUARAN (Rp)</th>
                        <th class="border text-center">SALDO KAS (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-base-50 font-semibold">
                        <td class="border text-center"><?= date('d/m/Y', strtotime($tgl_mulai)) ?></td>
                        <td class="border text-center">-</td>
                        <td class="border">Saldo Awal Bulan</td>
                        <td class="border text-right">-</td>
                        <td class="border text-right">-</td>
                        <td class="border text-right font-mono"><?= number_format($saldo_awal, 2, ',', '.') ?></td>
                    </tr>
                    
                    <?php 
                    $saldo_running = $saldo_awal;
                    $total_terima = 0;
                    $total_keluar = 0;
                    
                    if (empty($mutasi)): ?>
                        <tr><td colspan="6" class="text-center py-4 border">Tidak ada transaksi kas/bank pada bulan ini.</td></tr>
                    <?php else: foreach ($mutasi as $item): 
                        $penerimaan = $item['debit'];
                        $pengeluaran = $item['kredit'];
                        
                        $saldo_running = $saldo_running + $penerimaan - $pengeluaran;
                        $total_terima += $penerimaan;
                        $total_keluar += $pengeluaran;
                    ?>
                        <tr class="hover">
                            <td class="border text-center"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                            <td class="border font-mono text-center"><?= htmlspecialchars($item['nomor_bukti']) ?></td>
                            <td class="border">
                                <?= htmlspecialchars($item['keterangan']) ?>
                                <div class="text-xs opacity-60">Via: <?= htmlspecialchars($item['nama_akun']) ?></div>
                            </td>
                            <td class="border text-right text-success font-mono"><?= $penerimaan > 0 ? number_format($penerimaan, 2, ',', '.') : '-' ?></td>
                            <td class="border text-right text-error font-mono"><?= $pengeluaran > 0 ? number_format($pengeluaran, 2, ',', '.') : '-' ?></td>
                            <td class="border text-right font-bold font-mono"><?= number_format($saldo_running, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold text-lg bg-base-200">
                        <td colspan="3" class="border text-right">TOTAL MUTASI BULAN INI</td>
                        <td class="border text-right text-success font-mono">Rp <?= number_format($total_terima, 2, ',', '.') ?></td>
                        <td class="border text-right text-error font-mono">Rp <?= number_format($total_keluar, 2, ',', '.') ?></td>
                        <td class="border text-right font-mono text-primary">Rp <?= number_format($saldo_running, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-16 flex justify-between px-8 text-center hidden-print-header">
            <div>
                <p>Mengetahui,<br><strong>Kepala Desa / Penasihat</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_kepala_desa'] ?? '...........................') ?></p>
            </div>
            <div>
                <p>Disahkan Oleh,<br><strong>Direktur BUMDes</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_pimpinan'] ?? '...........................') ?></p>
            </div>
            <div>
                <p>Dibuat Oleh,<br><strong>Bendahara / Akuntan</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_akuntan'] ?? '...........................') ?></p>
            </div>
        </div>
    </div>
</div>
<?php  ?>