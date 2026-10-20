<?php
$judulHalaman = 'Laporan Perubahan Modal (Ekuitas)';

$tanggal_mulai = $_REQUEST['tanggal_mulai'] ?? date('Y-01-01');
$tanggal_selesai = $_REQUEST['tanggal_selesai'] ?? date('Y-m-d');

$tanggal_awal_minus_1 = date('Y-m-d', strtotime($tanggal_mulai . ' -1 day'));

// Menggunakan COA mapping dari Pengaturan
$simpanan_pokok_awal = getSaldoAkun($_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01', $tanggal_awal_minus_1);
$simpanan_wajib_awal = getSaldoAkun($_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01', $tanggal_awal_minus_1);
$cadangan_awal = getSaldoAkun($_PENGATURAN['akun_map_cadangan'] ?? '3.3.01.02', $tanggal_awal_minus_1);
$shu_awal = getSaldoAkun($_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01', $tanggal_awal_minus_1);
$total_ekuitas_awal = $simpanan_pokok_awal + $simpanan_wajib_awal + $cadangan_awal + $shu_awal;

$simpanan_pokok_akhir = getSaldoAkun($_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01', $tanggal_selesai);
$simpanan_wajib_akhir = getSaldoAkun($_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01', $tanggal_selesai);

$setoran_pokok_periode = $simpanan_pokok_akhir - $simpanan_pokok_awal;
$setoran_wajib_periode = $simpanan_wajib_akhir - $simpanan_wajib_awal;

$shu_periode_berjalan = hitungSHU($tanggal_mulai, $tanggal_selesai);

$akun_shu = $_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01';
$alokasi_shu_q = query("SELECT SUM(debit) as total FROM jurnal_umum WHERE kode_akun = ? AND keterangan LIKE 'Jurnal Alokasi SHU%' AND tanggal BETWEEN ? AND ?", [$akun_shu, $tanggal_mulai, $tanggal_selesai]);
$alokasi_shu_periode = $alokasi_shu_q[0]['total'] ?? 0;

$cadangan_akhir = getSaldoAkun($_PENGATURAN['akun_map_cadangan'] ?? '3.3.01.02', $tanggal_selesai);
$shu_akhir = $shu_awal + $shu_periode_berjalan - $alokasi_shu_periode;
$total_ekuitas_akhir = $simpanan_pokok_akhir + $simpanan_wajib_akhir + $cadangan_akhir + $shu_akhir;

$lbl_modal_pokok = MODE_ENTITAS === 'BUMDes' ? 'Penyertaan Desa (Pokok)' : 'Simpanan Pokok';
$lbl_modal_wajib = MODE_ENTITAS === 'BUMDes' ? 'Modal Masyarakat (Wajib)' : 'Simpanan Wajib';
$lbl_laba_dt = MODE_ENTITAS === 'BUMDes' ? 'Laba Belum Dibagi' : 'SHU Belum Dibagi';
$lbl_laba_berjalan = MODE_ENTITAS === 'BUMDes' ? 'Laba Bersih Tahun Berjalan' : 'SHU Tahun Berjalan';
$lbl_alokasi = MODE_ENTITAS === 'BUMDes' ? 'Alokasi Laba Tahun Lalu' : 'Alokasi SHU Tahun Lalu';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Perubahan_Ekuitas_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN PERUBAHAN EKUITAS (MODAL)']);
    fputcsv($output, ['Periode', $tanggal_mulai . ' s/d ' . $tanggal_selesai]);
    fputcsv($output, []);
    
    fputcsv($output, ['Keterangan', $lbl_modal_pokok, $lbl_modal_wajib, 'Cadangan', $lbl_laba_dt, 'Total Ekuitas']);
    fputcsv($output, ['Saldo Awal', $simpanan_pokok_awal, $simpanan_wajib_awal, $cadangan_awal, $shu_awal, $total_ekuitas_awal]);
    fputcsv($output, ['Setoran Modal Periode Ini', $setoran_pokok_periode, $setoran_wajib_periode, 0, 0, $setoran_pokok_periode + $setoran_wajib_periode]);
    fputcsv($output, [$lbl_laba_berjalan, 0, 0, 0, $shu_periode_berjalan, $shu_periode_berjalan]);
    fputcsv($output, [$lbl_alokasi . ' (Ditahan)', 0, 0, $cadangan_akhir - $cadangan_awal, -$alokasi_shu_periode, 0]);
    fputcsv($output, ['Saldo Akhir', $simpanan_pokok_akhir, $simpanan_wajib_akhir, $cadangan_akhir, $shu_akhir, $total_ekuitas_akhir]);
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-4 text-base-content/80">Laporan ini menjelaskan perubahan modal (ekuitas) entitas dari awal hingga akhir periode, termasuk penambahan modal dan laba bersih (Sisa Hasil Usaha) tahun ini.</p>

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
    <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
    <form method="GET" action="" style="display: inline;">
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_perubahan_ekuitas' ?>">
        <input type="hidden" name="tanggal_mulai" value="<?= $tanggal_mulai ?>">
        <input type="hidden" name="tanggal_selesai" value="<?= $tanggal_selesai ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Laporan Perubahan Ekuitas (Modal)</h3>
            <p>Untuk Periode yang Berakhir pada <?= htmlspecialchars(date('d F Y', strtotime($tanggal_selesai))) ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th class="text-right"><?= $lbl_modal_pokok ?></th>
                        <th class="text-right"><?= $lbl_modal_wajib ?></th>
                        <th class="text-right">Cadangan</th>
                        <th class="text-right"><?= $lbl_laba_dt ?></th>
                        <th class="text-right text-primary">Total Ekuitas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">Saldo Awal (<?= date('d M Y', strtotime($tanggal_mulai)) ?>)</td>
                        <td class="text-right"><?= number_format($simpanan_pokok_awal, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($simpanan_wajib_awal, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($cadangan_awal, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($shu_awal, 2, ',', '.') ?></td>
                        <td class="text-right font-bold text-primary"><?= number_format($total_ekuitas_awal, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Setoran Modal Periode Ini</td>
                        <td class="text-right"><?= number_format($setoran_pokok_periode, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($setoran_wajib_periode, 2, ',', '.') ?></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"><?= number_format($setoran_pokok_periode + $setoran_wajib_periode, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td><?= $lbl_laba_berjalan ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td>
                    </tr>
                     <tr>
                        <td><?= $lbl_alokasi ?> (Ditahan)</td>
                        <td></td>
                        <td></td>
                        <td class="text-right"><?= number_format($cadangan_akhir - $cadangan_awal, 2, ',', '.') ?></td>
                        <td class="text-right">(<?= number_format($alokasi_shu_periode, 2, ',', '.') ?>)</td>
                        <td></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold text-lg bg-base-200">
                        <td>Saldo Akhir (<?= date('d M Y', strtotime($tanggal_selesai)) ?>)</td>
                        <td class="text-right"><?= number_format($simpanan_pokok_akhir, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($simpanan_wajib_akhir, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($cadangan_akhir, 2, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($shu_akhir, 2, ',', '.') ?></td>
                        <td class="text-right text-primary"><?= number_format($total_ekuitas_akhir, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php  ?>