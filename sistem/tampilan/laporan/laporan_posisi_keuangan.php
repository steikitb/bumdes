<?php
$show_unit_filter = $show_unit_filter ?? true;
$unit_usaha = $unit_usaha ?? ($_REQUEST['unit_usaha'] ?? 'Konsolidasi');
$judulHalaman = $judulHalaman ?? 'Laporan Posisi Keuangan (Neraca)';
$tanggal_laporan = $_REQUEST['tanggal_laporan'] ?? date('Y-m-d');
$where_unit = '';
$params_unit = [];

if ($unit_usaha !== 'Konsolidasi') {
    $where_unit = "WHERE unit_usaha = ? OR unit_usaha = 'Konsolidasi'";
    $params_unit[] = $unit_usaha;
}

$semua_akun = query("SELECT kode_akun, nama_akun, kelompok_akun, saldo_normal FROM daftar_akun $where_unit ORDER BY urutan_laporan ASC, kode_akun ASC", $params_unit);
$aset_lancar = []; $aset_tetap = [];
$liab_pendek = []; $liab_panjang = [];
$ekuitas = [];

$total_aset = 0; $total_aset_lancar = 0; $total_aset_tetap = 0;
$total_liabilitas = 0; $total_liab_pendek = 0; $total_liab_panjang = 0; 
$total_ekuitas = 0;

$tahun_laporan = date('Y', strtotime($tanggal_laporan));
$tanggal_awal_tahun = $tahun_laporan . '-01-01';

$shu_periode_berjalan = 0;

$akun_ikhtisar = $_PENGATURAN['akun_map_ikhtisar_lr'] ?? '3.9.01.01'; // Default kepmendesa

foreach ($semua_akun as $akun) {
    if ($unit_usaha === 'Konsolidasi' && MODE_ENTITAS === 'BUMDes') {
        // Eliminasi akun Pusat/Cabang jika konsolidasi
        if (strpos($akun['kode_akun'], '1.1.99') === 0 || strpos($akun['kode_akun'], '3.8.00') === 0) {
            continue;
        }
    }

    $kode = $akun['kode_akun'];
    $saldo = getSaldoAkun($kode, $tanggal_laporan, null, $unit_usaha);
    
    // Perhitungan Laba Rugi Otomatis Berjalan (Hanya transaksi di tahun berjalan)
    if (strpos($akun['kelompok_akun'], 'Pendapatan') === 0 || strpos($akun['kelompok_akun'], 'Beban') === 0) {
        $saldo_lr_berjalan = getSaldoAkun($kode, $tanggal_laporan, $tanggal_awal_tahun, $unit_usaha);
                // SHU/Laba berjalan = Σ pendapatan (saldo normal Kredit) − Σ beban & kontra-pendapatan (saldo normal Debit)
                if ($akun['saldo_normal'] === 'Kredit') {
                    $shu_periode_berjalan += $saldo_lr_berjalan;
                } else {
                    $shu_periode_berjalan -= $saldo_lr_berjalan;
                }
        continue; // Akun Pendapatan/Beban tidak tampil di Neraca
    }

    if ($saldo != 0 && $kode !== $akun_ikhtisar) {
        $item = ['kode' => $kode, 'nama' => $akun['nama_akun'], 'jumlah' => $saldo];

        if ($akun['kelompok_akun'] === 'Aset') {
            $nilai = $akun['saldo_normal'] === 'Debit' ? $saldo : -$saldo;
            if (strpos($kode, '1.1') === 0) {
                $aset_lancar[] = $item;
                $total_aset_lancar += $nilai;
            } else {
                $aset_tetap[] = $item;
                $total_aset_tetap += $nilai;
            }
            $total_aset += $nilai;
        } elseif ($akun['kelompok_akun'] === 'Liabilitas') {
            $nilai = $akun['saldo_normal'] === 'Kredit' ? $saldo : -$saldo;
            if (strpos($kode, '2.1') === 0) {
                $liab_pendek[] = $item;
                $total_liab_pendek += $nilai;
            } else {
                $liab_panjang[] = $item;
                $total_liab_panjang += $nilai;
            }
            $total_liabilitas += $nilai;
        } elseif ($akun['kelompok_akun'] === 'Ekuitas') {
            $nilai = $akun['saldo_normal'] === 'Kredit' ? $saldo : -$saldo;
            $ekuitas[] = $item;
            $total_ekuitas += $nilai;
        }
    }
}

// Laba/SHU tahun berjalan menambah Ekuitas pada SEMUA agregasi (Konsolidasi maupun per-unit)
// agar persamaan Neraca A = L + E senantiasa seimbang.
$total_ekuitas += $shu_periode_berjalan;

$lbl_shu_berjalan = MODE_ENTITAS === 'BUMDes' ? 'Laba Bersih Tahun Berjalan' : 'SHU Tahun Berjalan';
$selisih_neraca = round($total_aset - ($total_liabilitas + $total_ekuitas), 2);

// D1. Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Neraca_' . $unit_usaha . '_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN POSISI KEUANGAN (NERACA) - ' . strtoupper($unit_usaha)]);
    fputcsv($output, ['Per Tanggal', $tanggal_laporan]);
    fputcsv($output, []);
    
    fputcsv($output, ['ASET LANCAR', '']);
    foreach($aset_lancar as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
    fputcsv($output, ['Total Aset Lancar', $total_aset_lancar]);
    fputcsv($output, []);

    fputcsv($output, ['ASET TIDAK LANCAR', '']);
    foreach($aset_tetap as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
    fputcsv($output, ['Total Aset Tidak Lancar', $total_aset_tetap]);
    fputcsv($output, []);

    fputcsv($output, ['TOTAL ASET', $total_aset]);
    fputcsv($output, []);
    
    fputcsv($output, ['LIABILITAS JANGKA PENDEK', '']);
    foreach($liab_pendek as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
    fputcsv($output, ['Total Liabilitas Pendek', $total_liab_pendek]);
    fputcsv($output, []);

    fputcsv($output, ['LIABILITAS JANGKA PANJANG', '']);
    foreach($liab_panjang as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
    fputcsv($output, ['Total Liabilitas Panjang', $total_liab_panjang]);
    fputcsv($output, []);
    
    fputcsv($output, ['TOTAL LIABILITAS', $total_liabilitas]);
    fputcsv($output, []);

    fputcsv($output, ['EKUITAS (MODAL)', '']);
    foreach($ekuitas as $i) { fputcsv($output, [$i['nama'], $i['jumlah']]); }
        fputcsv($output, [$lbl_shu_berjalan, $shu_periode_berjalan]);
        fputcsv($output, ['Total Ekuitas', $total_ekuitas]);
    fputcsv($output, []);
    
    fputcsv($output, ['TOTAL LIABILITAS & EKUITAS', $total_liabilitas + $total_ekuitas]);
    
    fclose($output);
    exit;
}
?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-4 text-base-content/80">Laporan ini menunjukkan kondisi kekayaan (Aset), utang (Liabilitas), dan modal (Ekuitas) pada tanggal tertentu. Nilai selalu seimbang matematis asalkan Jurnal Awal dan Transaksi selaras.</p>

<?php if ($show_unit_filter): ?>
<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Pilih Tanggal Laporan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Per Tanggal</span></label><input type="date" name="tanggal_laporan" class="input input-bordered" value="<?= htmlspecialchars($tanggal_laporan) ?>" required></div>
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
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_posisi_keuangan' ?>">
        <input type="hidden" name="tanggal_laporan" value="<?= $tanggal_laporan ?>">
        <input type="hidden" name="unit_usaha" value="<?= $unit_usaha ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Laporan Posisi Keuangan - Unit: <?= $unit_usaha ?></h3>
            <p>Per Tanggal: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_laporan))) ?></p>
            <?php if ($unit_usaha === 'Konsolidasi' && MODE_ENTITAS === 'BUMDes'): ?>
                <p class="text-xs text-base-content/70 mt-1">*Disajikan sebagai Laporan Konsolidasian. Akun Resiprokal / Rekening Koran (RK) antar unit telah dieliminasi otomatis.</p>
            <?php endif; ?>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead><tr class="bg-base-200"><th class="text-lg" colspan="2">ASET</th></tr></thead>
                <tbody>
                    <tr><td colspan="2" class="font-bold pl-4">Aset Lancar</td></tr>
                    <?php foreach ($aset_lancar as $item): ?>
                        <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="font-bold bg-base-100"><td class="pl-4">Total Aset Lancar</td><td class="text-right border-t border-base-300">Rp <?= number_format($total_aset_lancar, 2, ',', '.') ?></td></tr>
                    
                    <tr><td colspan="2" class="font-bold pl-4 mt-2">Aset Tidak Lancar</td></tr>
                    <?php foreach ($aset_tetap as $item): ?>
                        <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="font-bold bg-base-100"><td class="pl-4">Total Aset Tidak Lancar</td><td class="text-right border-t border-base-300">Rp <?= number_format($total_aset_tetap, 2, ',', '.') ?></td></tr>

                    <tr class="font-bold text-xl bg-base-200"><td>TOTAL ASET</td><td class="text-right">Rp <?= number_format($total_aset, 2, ',', '.') ?></td></tr>
                </tbody>
                 <thead><tr class="bg-base-200"><th class="text-lg" colspan="2">LIABILITAS DAN EKUITAS</th></tr></thead>
                 <tbody>
                    <tr><td class="font-bold pl-4 text-lg">Liabilitas</td><td></td></tr>
                    
                    <tr><td colspan="2" class="font-bold pl-4">Liabilitas Jangka Pendek</td></tr>
                    <?php foreach ($liab_pendek as $item): ?>
                        <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="font-bold bg-base-100"><td class="pl-4">Total Liabilitas Jangka Pendek</td><td class="text-right border-t border-base-300">Rp <?= number_format($total_liab_pendek, 2, ',', '.') ?></td></tr>

                    <tr><td colspan="2" class="font-bold pl-4 mt-2">Liabilitas Jangka Panjang</td></tr>
                    <?php foreach ($liab_panjang as $item): ?>
                        <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="font-bold bg-base-100"><td class="pl-4">Total Liabilitas Jangka Panjang</td><td class="text-right border-t border-base-300">Rp <?= number_format($total_liab_panjang, 2, ',', '.') ?></td></tr>

                    <tr class="font-bold bg-base-100 border-b-2"><td class="pl-4">Total Liabilitas</td><td class="text-right">Rp <?= number_format($total_liabilitas, 2, ',', '.') ?></td></tr>

                    <tr><td colspan="2">&nbsp;</td></tr>

                    <tr><td class="font-bold pl-4 text-lg">Ekuitas</td><td></td></tr>
                    <?php foreach ($ekuitas as $item): ?>
                        <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right">Rp <?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                                        <tr><td class="pl-8"><?= $lbl_shu_berjalan ?></td><td class="text-right">Rp <?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td></tr>
                                        <tr class="font-bold"><td class="pl-4 border-t border-base-300">Total Ekuitas</td><td class="text-right border-t border-base-300">Rp <?= number_format($total_ekuitas, 2, ',', '.') ?></td></tr>

                    <tr class="font-bold text-xl bg-base-200"><td>TOTAL LIABILITAS DAN EKUITAS</td><td class="text-right">Rp <?= number_format($total_liabilitas + $total_ekuitas, 2, ',', '.') ?></td></tr>
                </tbody>
            </table>
            
            <?php if(abs($selisih_neraca) > 1): ?>
            <div class="mt-4 p-4 border border-error bg-error/10 text-error font-bold rounded">
                PERINGATAN KETIDAKSEIMBANGAN: Terdapat selisih matematis sebesar Rp <?= number_format(abs($selisih_neraca), 2, ',', '.') ?>. Hal ini dapat terjadi karena terdapat Kode Akun di Jurnal Umum yang tidak terdaftar di Master Bagan Akun, atau ada kesalahan pemetaan Kelompok Akun. Silakan cek Rincian Jurnal Umum Anda.
            </div>
            <?php else: ?>
            <div class="mt-4 p-4 border border-success bg-success/10 text-success font-bold rounded">
                Status Neraca: SEIMBANG (BALANCE).
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>