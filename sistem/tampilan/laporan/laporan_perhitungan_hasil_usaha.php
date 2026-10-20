<?php
$show_unit_filter = $show_unit_filter ?? true;
$unit_usaha = $unit_usaha ?? ($_REQUEST['unit_usaha'] ?? 'Konsolidasi');
$judulHalaman = $judulHalaman ?? 'Laporan Laba Rugi (Hasil Usaha)';
$tanggal_mulai = $_REQUEST['tanggal_mulai'] ?? date('Y-01-01');
$tanggal_selesai = $_REQUEST['tanggal_selesai'] ?? date('Y-m-t');

$where_conditions = [];
$params_unit = [];

if ($unit_usaha !== 'Konsolidasi') {
    $where_conditions[] = "(unit_usaha = ? OR unit_usaha = 'Konsolidasi')";
    $params_unit[] = $unit_usaha;
}

$where_sql = empty($where_conditions) ? "" : "WHERE " . implode(' AND ', $where_conditions);
$semua_akun = query("SELECT kode_akun, nama_akun, kelompok_akun, saldo_normal FROM daftar_akun $where_sql ORDER BY kode_akun", $params_unit);

$pendapatan_usaha = []; $hpp = []; $beban_usaha = []; $pendapatan_lain = []; $beban_lain = [];
$total_pendapatan_usaha = 0; $total_hpp = 0; $total_beban_usaha = 0; $total_pendapatan_lain = 0; $total_beban_lain = 0; $beban_pajak = 0;

foreach ($semua_akun as $akun) {
    $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai, $unit_usaha);
    if ($saldo != 0) {
        $kode_prefix = substr($akun['kode_akun'], 0, 1);
        $item = ['nama' => $akun['nama_akun'], 'jumlah' => abs($saldo), 'is_minus' => false];
        
        if ($kode_prefix === '4') {
            if ($akun['saldo_normal'] === 'Debit') { 
                $item['is_minus'] = true; 
                $total_pendapatan_usaha -= abs($saldo);
            } else {
                $total_pendapatan_usaha += abs($saldo);
            }
            $pendapatan_usaha[] = $item;
        } elseif ($kode_prefix === '5') {
            $total_hpp += abs($saldo);
            $hpp[] = $item;
        } elseif ($kode_prefix === '6') {
            $total_beban_usaha += abs($saldo);
            $beban_usaha[] = $item;
        } elseif ($kode_prefix === '7') {
            if ($akun['kelompok_akun'] === 'Pendapatan Lain-lain' || $akun['saldo_normal'] === 'Kredit') {
                $total_pendapatan_lain += abs($saldo);
                $pendapatan_lain[] = $item;
            } else {
                if (strpos($akun['kode_akun'], '7.3') === 0 || strpos(strtolower($akun['nama_akun']), 'pajak') !== false) {
                    $beban_pajak += abs($saldo);
                } else {
                    $total_beban_lain += abs($saldo);
                    $beban_lain[] = $item;
                }
            }
        }
    }
}

$laba_kotor = $total_pendapatan_usaha - $total_hpp;
$laba_operasi = $laba_kotor - $total_beban_usaha;
$laba_sebelum_pajak = $laba_operasi + $total_pendapatan_lain - $total_beban_lain;
$laba_bersih = $laba_sebelum_pajak - $beban_pajak;

$lbl_laba_bersih = MODE_ENTITAS === 'BUMDes' ? 'LABA BERSIH (SETELAH PAJAK)' : 'SISA HASIL USAHA BERSIH (SETELAH PAJAK)';

// D1. Export to CSV Logic
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laba_Rugi_' . $unit_usaha . '_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN LABA RUGI (HASIL USAHA) - ' . strtoupper($unit_usaha)]);
    fputcsv($output, ['Periode', $tanggal_mulai . ' s/d ' . $tanggal_selesai]);
    fputcsv($output, []);
    
    fputcsv($output, ['Keterangan', 'Nominal (Rp)']);
    fputcsv($output, ['PENDAPATAN USAHA', '']);
    foreach($pendapatan_usaha as $i) { fputcsv($output, [$i['nama'], ($i['is_minus'] ? '-' : '') . $i['jumlah']]); }
    fputcsv($output, ['Total Pendapatan Usaha', $total_pendapatan_usaha]);
    fputcsv($output, []);
    
    fputcsv($output, ['HARGA POKOK PENJUALAN', '']);
    foreach($hpp as $i) { fputcsv($output, [$i['nama'], '-' . $i['jumlah']]); }
    fputcsv($output, ['Laba Kotor', $laba_kotor]);
    fputcsv($output, []);
    
    fputcsv($output, ['BEBAN USAHA', '']);
    foreach($beban_usaha as $i) { fputcsv($output, [$i['nama'], '-' . $i['jumlah']]); }
    fputcsv($output, ['Laba Operasi', $laba_operasi]);
    fputcsv($output, []);
    
    fputcsv($output, ['PENDAPATAN / BEBAN LAIN', '']);
    foreach($pendapatan_lain as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
    foreach($beban_lain as $i) { fputcsv($output, [$i['nama'], '-' . $i['jumlah']]); }
    fputcsv($output, ['Laba Sebelum Pajak', $laba_sebelum_pajak]);
    fputcsv($output, []);
    
    fputcsv($output, ['Beban Pajak', '-' . $beban_pajak]);
    fputcsv($output, [$lbl_laba_bersih, $laba_bersih]);
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { background-color: white; color: black; padding: 20px; }
    .table td, .table th { border: none !important; padding: 4px 8px !important; }
    .row-border-bottom td { border-bottom: 1px solid #000 !important; }
    .row-border-top td { border-top: 1px solid #000 !important; }
}
</style>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>

<?php if ($show_unit_filter): ?>
<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Pilih Periode Laporan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Mulai</span></label><input type="date" name="tanggal_mulai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_mulai) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Selesai</span></label><input type="date" name="tanggal_selesai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_selesai) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Unit Usaha</span></label>
                    <select name="unit_usaha" class="select select-bordered">
                        <option value="Konsolidasi" <?= $unit_usaha == 'Konsolidasi' ? 'selected' : '' ?>>Konsolidasi (Gabungan)</option>
                        <option value="Toko" <?= $unit_usaha == 'Toko' ? 'selected' : '' ?>>Unit Toko</option>
                        <option value="USP" <?= $unit_usaha == 'USP' ? 'selected' : '' ?>>Unit Simpan Pinjam</option>
                    </select>
                </div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Tampilkan Laporan</button></div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="mb-6 flex gap-2 no-print">
    <button onclick="window.print()" class="btn btn-secondary">Cetak PDF / Printer</button>
    <form method="GET" action="" style="display: inline;">
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_perhitungan_hasil_usaha' ?>">
        <input type="hidden" name="tanggal_mulai" value="<?= $tanggal_mulai ?>">
        <input type="hidden" name="tanggal_selesai" value="<?= $tanggal_selesai ?>">
        <input type="hidden" name="unit_usaha" value="<?= $unit_usaha ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl font-bold uppercase">LAPORAN LABA RUGI (HASIL USAHA) <?= $unit_usaha !== 'Konsolidasi' ? '- UNIT '.$unit_usaha : '' ?></h3>
            <p>Untuk Periode yang Berakhir pada Tanggal <?= htmlspecialchars(date('d F Y', strtotime($tanggal_selesai))) ?></p>
        </div>
        
        <table class="table w-full text-base">
            <tbody>
                <tr class="font-bold text-primary"><td colspan="2">PENDAPATAN USAHA</td></tr>
                <?php foreach ($pendapatan_usaha as $item): ?>
                <tr>
                    <td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td>
                    <td class="text-right"><?= $item['is_minus'] ? '(Rp ' : 'Rp ' ?><?= number_format($item['jumlah'], 2, ',', '.') ?><?= $item['is_minus'] ? ')' : '' ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="font-bold row-border-bottom"><td class="pl-4">Total Pendapatan Usaha</td><td class="text-right">Rp <?= number_format($total_pendapatan_usaha, 2, ',', '.') ?></td></tr>
                
                <tr><td colspan="2">&nbsp;</td></tr>
                
                <tr class="font-bold text-error"><td colspan="2">HARGA POKOK PENJUALAN</td></tr>
                <?php foreach ($hpp as $item): ?>
                <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                <?php endforeach; ?>
                <tr class="font-bold row-border-bottom"><td class="pl-4">Total Harga Pokok Penjualan</td><td class="text-right">Rp <?= number_format($total_hpp, 2, ',', '.') ?></td></tr>
                
                <tr class="font-bold text-lg bg-base-200 row-border-bottom"><td class="pl-2">LABA (RUGI) KOTOR</td><td class="text-right">Rp <?= number_format($laba_kotor, 2, ',', '.') ?></td></tr>
                
                <tr><td colspan="2">&nbsp;</td></tr>
                
                <tr class="font-bold text-warning"><td colspan="2">BEBAN-BEBAN USAHA</td></tr>
                <?php foreach ($beban_usaha as $item): ?>
                <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                <?php endforeach; ?>
                <tr class="font-bold row-border-bottom"><td class="pl-4">Total Beban Usaha</td><td class="text-right">Rp <?= number_format($total_beban_usaha, 2, ',', '.') ?></td></tr>
                
                <tr class="font-bold text-lg bg-base-200 row-border-bottom"><td class="pl-2">LABA (RUGI) OPERASI</td><td class="text-right">Rp <?= number_format($laba_operasi, 2, ',', '.') ?></td></tr>

                <tr><td colspan="2">&nbsp;</td></tr>
                
                <tr class="font-bold text-info"><td colspan="2">PENDAPATAN DAN BEBAN LAIN-LAIN</td></tr>
                <?php foreach ($pendapatan_lain as $item): ?>
                <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                <?php endforeach; ?>
                <?php foreach ($beban_lain as $item): ?>
                <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">(Rp <?= number_format($item['jumlah'], 2, ',', '.') ?>)</td></tr>
                <?php endforeach; ?>
                <tr class="font-bold row-border-bottom"><td class="pl-4">Total Pendapatan (Beban) Lain-lain</td><td class="text-right">Rp <?= number_format($total_pendapatan_lain - $total_beban_lain, 2, ',', '.') ?></td></tr>
                
                <tr><td colspan="2">&nbsp;</td></tr>
                
                <tr class="font-bold text-xl bg-base-300"><td class="pl-2">LABA (RUGI) SEBELUM PAJAK</td><td class="text-right">Rp <?= number_format($laba_sebelum_pajak, 2, ',', '.') ?></td></tr>
                <tr><td class="pl-4">Beban Pajak</td><td class="text-right">(Rp <?= number_format($beban_pajak, 2, ',', '.') ?>)</td></tr>
                <tr class="font-bold text-2xl bg-success text-success-content row-border-bottom"><td class="pl-2"><?= $lbl_laba_bersih ?></td><td class="text-right">Rp <?= number_format($laba_bersih, 2, ',', '.') ?></td></tr>
            </tbody>
        </table>

        <div class="mt-16 flex justify-end text-center">
            <div>
                <p>Disahkan Oleh,<br><strong>Direktur / Pimpinan BUMDes</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_pimpinan'] ?? '...........................') ?></p>
            </div>
        </div>
    </div>
</div>