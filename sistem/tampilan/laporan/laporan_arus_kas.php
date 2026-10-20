<?php
$judulHalaman = 'Laporan Arus Kas';
cekAkses('lihat_laporan');

$tanggal_mulai = $_REQUEST['tanggal_mulai'] ?? date('Y-01-01');
$tanggal_selesai = $_REQUEST['tanggal_selesai'] ?? date('Y-m-d');
$tanggal_awal_minus_1 = date('Y-m-d', strtotime($tanggal_mulai . ' -1 day'));

// Semua akun kas & bank (1.1.01*) — bukan hanya 2 akun
$akun_kas_q = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kelompok_akun = 'Aset'");
$kas_codes_all = array_column($akun_kas_q, 'kode_akun');
if (empty($kas_codes_all)) {
    $kas_codes_all = [($_PENGATURAN['akun_map_kas'] ?? '1.1.01.01'), ($_PENGATURAN['akun_map_bank'] ?? '1.1.01.02')];
}
$kas_codes = array_values(array_unique($kas_codes_all));
$ph_kas = implode(',', array_fill(0, count($kas_codes), '?'));

// --- METODE LANGSUNG (DIRECT METHOD) ---
// Hanya ambil jurnal bukti yang menyentuh kas; semua baris bukti tsb (untuk netting transfer internal & klasifikasi lawan)
$cf_q = query("
    SELECT ju.nomor_bukti, ju.tanggal, ju.kode_akun, ju.debit, ju.kredit, da.kelompok_akun, da.kategori_arus_kas
    FROM jurnal_umum ju
    JOIN daftar_akun da ON ju.kode_akun = da.kode_akun
    WHERE ju.nomor_bukti IN (
        SELECT DISTINCT nomor_bukti FROM jurnal_umum WHERE kode_akun IN ($ph_kas) AND tanggal BETWEEN ? AND ?
    ) AND ju.tanggal BETWEEN ? AND ?
    ORDER BY ju.nomor_bukti, ju.id
", array_merge($kas_codes, [$tanggal_mulai, $tanggal_selesai], [$tanggal_mulai, $tanggal_selesai]));

$rows_by_bukti = [];
foreach ($cf_q as $r) { $rows_by_bukti[$r['nomor_bukti']][] = $r; }

$op_terima_pelanggan = 0; $op_bayar_pemasok = 0; $op_bayar_karyawan = 0; $op_bayar_pajak = 0; $op_lain_masuk = 0; $op_lain_keluar = 0;
$inv_masuk = 0; $inv_keluar = 0;
$pend_masuk_modal = 0; $pend_masuk_utang = 0; $pend_keluar = 0;

foreach ($rows_by_bukti as $bukti => $rows) {
    $kas_masuk_b = 0; $kas_keluar_b = 0; $lawan = [];
    foreach ($rows as $r) {
        if (in_array($r['kode_akun'], $kas_codes, true)) {
            $kas_masuk_b += $r['debit'];
            $kas_keluar_b += $r['kredit'];
        } else {
            $lawan[] = $r;
        }
    }
    // Tidak ada lawan = transfer internal kas↔kas; eliminasi (net nol terhadap pihak luar)
    if (empty($lawan)) continue;
    if ($kas_masuk_b == 0 && $kas_keluar_b == 0) continue;

    $kas_bersih = $kas_masuk_b - $kas_keluar_b;
    $kas_masuk = ($kas_bersih > 0) ? $kas_bersih : 0;
    $kas_keluar = ($kas_bersih < 0) ? -$kas_bersih : 0;

    // Ambil 1 lawan paling signifikan utk klasifikasi aktivitas
    $best = null; $best_amt = -1;
    foreach ($lawan as $o) { $amt = $o['debit'] + $o['kredit']; if ($amt > $best_amt) { $best_amt = $amt; $best = $o; } }
    if (!$best) continue;
    $kategori = $best['kategori_arus_kas'];
    if ($kategori === null || $kategori === '') {
        // Akun ekuitas (modal/simpanan) = aktivitas Pendanaan; selain itu default Operasi
        $kategori = (strpos($best['kelompok_akun'], 'Ekuitas') === 0) ? 'Pendanaan' : 'Operasi';
    }
    $kelompok = $best['kelompok_akun'];
    $kode_lawan = $best['kode_akun'];

    if ($kategori === 'Operasi') {
        if ($kas_masuk > 0) {
            if ($kelompok === 'Pendapatan' || strpos($kode_lawan, '1.1.03') === 0) {
                $op_terima_pelanggan += $kas_masuk;
            } else {
                $op_lain_masuk += $kas_masuk;
            }
        }
        if ($kas_keluar > 0) {
            if (strpos($kode_lawan, '1.1.05') === 0 || strpos($kode_lawan, '2.1.01') === 0 || strpos($kode_lawan, '5.1') === 0) {
                $op_bayar_pemasok += $kas_keluar;
            } elseif (strpos($kode_lawan, '6.1.01') === 0 || strpos($kode_lawan, '2.1.03') === 0) {
                $op_bayar_karyawan += $kas_keluar;
            } elseif (strpos($kode_lawan, '2.1.02') === 0 || strpos($kode_lawan, '7.3') === 0) {
                $op_bayar_pajak += $kas_keluar;
            } else {
                $op_lain_keluar += $kas_keluar;
            }
        }
    } elseif ($kategori === 'Investasi') {
        if ($kas_masuk > 0) $inv_masuk += $kas_masuk;
        if ($kas_keluar > 0) $inv_keluar += $kas_keluar;
    } elseif ($kategori === 'Pendanaan') {
        if ($kas_masuk > 0) {
            if ($kelompok === 'Ekuitas' || strpos($kode_lawan, '3.') === 0) $pend_masuk_modal += $kas_masuk;
            else $pend_masuk_utang += $kas_masuk;
        }
        if ($kas_keluar > 0) $pend_keluar += $kas_keluar;
    }
}

$kas_dari_operasi = ($op_terima_pelanggan + $op_lain_masuk) - ($op_bayar_pemasok + $op_bayar_karyawan + $op_bayar_pajak + $op_lain_keluar);
$kas_dari_investasi = $inv_masuk - $inv_keluar;
$kas_dari_pendanaan = ($pend_masuk_modal + $pend_masuk_utang) - $pend_keluar;

$kenaikan_penurunan_kas = $kas_dari_operasi + $kas_dari_investasi + $kas_dari_pendanaan;

// Saldo kas awal & akhir = jumlah SEMUA akun 1.1.01*
$kas_awal_periode = 0; $kas_akhir_periode = 0;
foreach ($kas_codes as $kc) {
    $kas_awal_periode += getSaldoAkun($kc, $tanggal_awal_minus_1);
}
$kas_akhir_periode = $kas_awal_periode + $kenaikan_penurunan_kas;

// --- METODE TIDAK LANGSUNG (INDIRECT METHOD) BUMDES ---
$laba_bersih = hitungSHU($tanggal_mulai, $tanggal_selesai);

$piutang_awal = getSaldoAkun($_PENGATURAN['akun_map_piutang_usaha_anggota'] ?? '1.1.03.01', $tanggal_awal_minus_1) + getSaldoAkun($_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04', $tanggal_awal_minus_1);
$piutang_akhir = getSaldoAkun($_PENGATURAN['akun_map_piutang_usaha_anggota'] ?? '1.1.03.01', $tanggal_selesai) + getSaldoAkun($_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04', $tanggal_selesai);
$kenaikan_piutang = $piutang_akhir - $piutang_awal;

$persediaan_awal = getSaldoAkun($_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01', $tanggal_awal_minus_1);
$persediaan_akhir = getSaldoAkun($_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01', $tanggal_selesai);
$kenaikan_persediaan = $persediaan_akhir - $persediaan_awal;

$utang_usaha_awal = getSaldoAkun($_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01', $tanggal_awal_minus_1);
$utang_usaha_akhir = getSaldoAkun($_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01', $tanggal_selesai);
$kenaikan_utang = $utang_usaha_akhir - $utang_usaha_awal;

// Jumlahkan SEMUA beban penyusutan & amortisasi (6.1.07.02/03/05/06) — bukan akun default yang tidak ada di CoA
$beban_penyusutan = 0;
$akun_dep_q = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun IN ('6.1.07.02','6.1.07.03','6.1.07.05','6.1.07.06')");
foreach ($akun_dep_q as $ad) { $beban_penyusutan += getSaldoAkun($ad['kode_akun'], $tanggal_selesai, $tanggal_mulai); }
$beban_penyisihan = getSaldoAkun($_PENGATURAN['akun_map_beban_kerugian_piutang'] ?? '6.1.07.01', $tanggal_selesai, $tanggal_mulai);

$kas_operasi_tidak_langsung = $laba_bersih + $beban_penyusutan + $beban_penyisihan - $kenaikan_piutang - $kenaikan_persediaan + $kenaikan_utang;

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Arus_Kas_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN ARUS KAS (METODE LANGSUNG)']);
    fputcsv($output, ['Periode', $tanggal_mulai . ' s/d ' . $tanggal_selesai]);
    fputcsv($output, []);
    
    fputcsv($output, ['Keterangan', 'Nominal (Rp)']);
    fputcsv($output, ['ARUS KAS DARI AKTIVITAS OPERASI', '']);
    fputcsv($output, ['Penerimaan Kas dari Pelanggan & Operasi', $op_terima_pelanggan]);
    fputcsv($output, ['Penerimaan Lainnya dari Aktivitas Operasi', $op_lain_masuk]);
    fputcsv($output, ['Pengeluaran Kas ke Pemasok', -$op_bayar_pemasok]);
    fputcsv($output, ['Pembayaran Kas kepada Karyawan', -$op_bayar_karyawan]);
    fputcsv($output, ['Pembayaran Pajak Penghasilan', -$op_bayar_pajak]);
    fputcsv($output, ['Pengeluaran Kas Operasi Lainnya', -$op_lain_keluar]);
    fputcsv($output, ['Arus Kas Bersih dari Aktivitas Operasi', $kas_dari_operasi]);
    fputcsv($output, []);
    
    fputcsv($output, ['ARUS KAS DARI AKTIVITAS INVESTASI', '']);
    fputcsv($output, ['Penerimaan Penjualan Aset Tetap / Tak Berwujud', $inv_masuk]);
    fputcsv($output, ['Pembayaran untuk Perolehan Aset Tetap / KDP', -$inv_keluar]);
    fputcsv($output, ['Arus Kas Bersih dari Aktivitas Investasi', $kas_dari_investasi]);
    fputcsv($output, []);
    
    fputcsv($output, ['ARUS KAS DARI AKTIVITAS PENDANAAN', '']);
    fputcsv($output, ['Penerimaan Penyertaan Modal Desa / Masyarakat', $pend_masuk_modal]);
    fputcsv($output, ['Penerimaan Utang Bank / Pihak Ketiga', $pend_masuk_utang]);
    fputcsv($output, ['Pengeluaran untuk Pembayaran Utang & Bagi Hasil', -$pend_keluar]);
    fputcsv($output, ['Arus Kas Bersih dari Aktivitas Pendanaan', $kas_dari_pendanaan]);
    fputcsv($output, []);
    
    fputcsv($output, ['Kenaikan (Penurunan) Bersih Kas', $kenaikan_penurunan_kas]);
    fputcsv($output, ['Kas dan Setara Kas Awal Periode', $kas_awal_periode]);
    fputcsv($output, ['KAS DAN SETARA KAS AKHIR PERIODE', $kas_akhir_periode]);
    
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
}
.hidden-print-header { display: none; }
</style>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-4 text-base-content/80">Laporan ini merangkum semua aliran uang masuk dan uang keluar entitas. Kepmendesa 136/2022 mengizinkan penggunaan Metode Langsung (Direct) atau Metode Tidak Langsung (Indirect). Keduanya disediakan di bawah ini.</p>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body"><h2 class="card-title">Pilih Periode Laporan</h2>
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
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_arus_kas' ?>">
        <input type="hidden" name="tanggal_mulai" value="<?= $tanggal_mulai ?>">
        <input type="hidden" name="tanggal_selesai" value="<?= $tanggal_selesai ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>

<!-- METODE LANGSUNG -->
<div class="card bg-base-100 shadow-xl printable-area mb-8 page-break-after">
    <div class="card-body p-4 md:p-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl font-bold">LAPORAN ARUS KAS (METODE LANGSUNG)</h3>
            <p>Untuk Periode: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_mulai))) ?> s/d <?= htmlspecialchars(date('d F Y', strtotime($tanggal_selesai))) ?></p>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-base border-collapse">
                <tbody>
                    <tr class="font-bold bg-base-200 border-b border-t border-base-300"><td colspan="2">ARUS KAS DARI AKTIVITAS OPERASI</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Penerimaan Kas dari Pelanggan & Operasi</td><td class="text-right border-b border-base-200">Rp <?= number_format($op_terima_pelanggan, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Penerimaan Lainnya dari Aktivitas Operasi</td><td class="text-right border-b border-base-200">Rp <?= number_format($op_lain_masuk, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pengeluaran Kas ke Pemasok</td><td class="text-right border-b border-base-200">(Rp <?= number_format($op_bayar_pemasok, 2, ',', '.') ?>)</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pembayaran Kas kepada Karyawan</td><td class="text-right border-b border-base-200">(Rp <?= number_format($op_bayar_karyawan, 2, ',', '.') ?>)</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pembayaran Pajak Penghasilan</td><td class="text-right border-b border-base-200">(Rp <?= number_format($op_bayar_pajak, 2, ',', '.') ?>)</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pengeluaran Kas Operasi Lainnya</td><td class="text-right border-b border-base-200">(Rp <?= number_format($op_lain_keluar, 2, ',', '.') ?>)</td></tr>
                    <tr class="font-bold border-b-2 border-black"><td class="pl-4">Arus Kas Bersih dari Aktivitas Operasi</td><td class="text-right">Rp <?= number_format($kas_dari_operasi, 2, ',', '.') ?></td></tr>
                    
                    <tr><td colspan="2" class="border-none">&nbsp;</td></tr>
                    
                    <tr class="font-bold bg-base-200 border-b border-t border-base-300"><td colspan="2">ARUS KAS DARI AKTIVITAS INVESTASI</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Penerimaan Penjualan Aset Tetap / Tak Berwujud</td><td class="text-right border-b border-base-200">Rp <?= number_format($inv_masuk, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pembayaran untuk Perolehan Aset Tetap / KDP</td><td class="text-right border-b border-base-200">(Rp <?= number_format($inv_keluar, 2, ',', '.') ?>)</td></tr>
                    <tr class="font-bold border-b-2 border-black"><td class="pl-4">Arus Kas Bersih dari Aktivitas Investasi</td><td class="text-right">Rp <?= number_format($kas_dari_investasi, 2, ',', '.') ?></td></tr>
                    
                    <tr><td colspan="2" class="border-none">&nbsp;</td></tr>
                    
                    <tr class="font-bold bg-base-200 border-b border-t border-base-300"><td colspan="2">ARUS KAS DARI AKTIVITAS PENDANAAN</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Penerimaan Penyertaan Modal Desa / Masyarakat</td><td class="text-right border-b border-base-200">Rp <?= number_format($pend_masuk_modal, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Penerimaan Utang Bank / Pihak Ketiga</td><td class="text-right border-b border-base-200">Rp <?= number_format($pend_masuk_utang, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Pengeluaran untuk Pembayaran Utang & Bagi Hasil</td><td class="text-right border-b border-base-200">(Rp <?= number_format($pend_keluar, 2, ',', '.') ?>)</td></tr>
                    <tr class="font-bold border-b-2 border-black"><td class="pl-4">Arus Kas Bersih dari Aktivitas Pendanaan</td><td class="text-right">Rp <?= number_format($kas_dari_pendanaan, 2, ',', '.') ?></td></tr>
                    
                    <tr><td colspan="2" class="border-none">&nbsp;</td></tr>
                    
                    <tr class="font-bold text-lg bg-base-200 border-b border-base-300"><td>Kenaikan (Penurunan) Bersih Kas</td><td class="text-right">Rp <?= number_format($kenaikan_penurunan_kas, 2, ',', '.') ?></td></tr>
                    <tr class="border-b border-base-300"><td>Kas dan Setara Kas Awal Periode</td><td class="text-right">Rp <?= number_format($kas_awal_periode, 2, ',', '.') ?></td></tr>
                    <tr class="font-bold text-xl bg-base-300 border-b border-black text-primary"><td>KAS DAN SETARA KAS AKHIR PERIODE</td><td class="text-right">Rp <?= number_format($kas_akhir_periode, 2, ',', '.') ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- METODE TIDAK LANGSUNG -->
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body p-4 md:p-8">
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold uppercase">LAPORAN ARUS KAS (METODE TIDAK LANGSUNG)</h3>
            <p>Alternatif Penyajian Aktivitas Operasi BUMDes Sesuai SAK EP</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-base border-collapse">
                <tbody>
                    <tr class="font-bold bg-base-200 border-b border-t border-base-300"><td colspan="2">ARUS KAS DARI AKTIVITAS OPERASI</td></tr>
                    <tr><td class="pl-8 border-b border-base-200">Laba Bersih Tahun Berjalan</td><td class="text-right border-b border-base-200">Rp <?= number_format($laba_bersih, 2, ',', '.') ?></td></tr>
                    
                    <tr class="font-bold text-sm bg-base-50 border-b border-base-200"><td colspan="2" class="pl-8 text-base-content/70">Penyesuaian untuk merekonsiliasi laba menjadi kas bersih:</td></tr>
                    
                    <tr><td class="pl-12 border-b border-base-200">Beban Penyusutan & Amortisasi</td><td class="text-right border-b border-base-200">Rp <?= number_format($beban_penyusutan, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-12 border-b border-base-200">Beban Penyisihan Kerugian Piutang</td><td class="text-right border-b border-base-200">Rp <?= number_format($beban_penyisihan, 2, ',', '.') ?></td></tr>
                    <tr><td class="pl-12 border-b border-base-200">(Kenaikan) / Penurunan Piutang Usaha & Anggota</td><td class="text-right border-b border-base-200"><?= $kenaikan_piutang > 0 ? '(Rp ' : 'Rp ' ?><?= number_format(abs($kenaikan_piutang), 2, ',', '.') ?><?= $kenaikan_piutang > 0 ? ')' : '' ?></td></tr>
                    <tr><td class="pl-12 border-b border-base-200">(Kenaikan) / Penurunan Persediaan Barang</td><td class="text-right border-b border-base-200"><?= $kenaikan_persediaan > 0 ? '(Rp ' : 'Rp ' ?><?= number_format(abs($kenaikan_persediaan), 2, ',', '.') ?><?= $kenaikan_persediaan > 0 ? ')' : '' ?></td></tr>
                    <tr><td class="pl-12 border-b border-base-200">Kenaikan / (Penurunan) Utang Usaha</td><td class="text-right border-b border-base-200"><?= $kenaikan_utang < 0 ? '(Rp ' : 'Rp ' ?><?= number_format(abs($kenaikan_utang), 2, ',', '.') ?><?= $kenaikan_utang < 0 ? ')' : '' ?></td></tr>
                    
                    <tr class="font-bold border-b-2 border-t-2 border-black"><td class="pl-4">Arus Kas Bersih dari Aktivitas Operasi</td><td class="text-right text-primary">Rp <?= number_format($kas_operasi_tidak_langsung, 2, ',', '.') ?></td></tr>
                    
                    <tr><td colspan="2" class="text-center text-sm opacity-70 p-4 border-none">*(Arus Kas dari Aktivitas Investasi dan Pendanaan memiliki nilai yang sama persis dengan Metode Langsung di atas)</td></tr>
                </tbody>
            </table>
        </div>

        <div class="mt-16 flex justify-end text-center no-print">
            <div>
                <p>Disahkan Oleh,<br><strong>Direktur / Pimpinan BUMDes</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_pimpinan'] ?? '...........................') ?></p>
            </div>
        </div>
    </div>
</div>
<?php  ?>