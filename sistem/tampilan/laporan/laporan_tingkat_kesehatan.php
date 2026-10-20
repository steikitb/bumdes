<?php
$judulHalaman = 'Penilaian Tingkat Kesehatan BUMDes';
cekAkses('lihat_laporan');

$tahun = $_REQUEST['tahun'] ?? date('Y');
$tanggal_mulai = $tahun . '-01-01';
$tanggal_selesai = $tahun . '-12-31';

// 1. Hitung Total Aset & Komponen Aset
$aset_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun = 'Aset'");
$total_aset = 0; $aset_lancar = 0; $kas_bank = 0; $piutang = 0; $persediaan = 0;
foreach ($aset_list as $akun) {
    $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
    $nilai = $akun['saldo_normal'] === 'Debit' ? $saldo : -$saldo;
    $total_aset += $nilai;
    if (strpos($akun['kode_akun'], '1.1') === 0) $aset_lancar += $nilai;
    if (strpos($akun['kode_akun'], '1.1.01') === 0) $kas_bank += $nilai;
    if (strpos($akun['kode_akun'], '1.1.03') === 0) $piutang += $nilai;
    if (strpos($akun['kode_akun'], '1.1.05') === 0) $persediaan += $nilai;
}

// 2. Hitung Liabilitas
$liab_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun = 'Liabilitas'");
$total_liabilitas = 0; $liabilitas_pendek = 0;
foreach ($liab_list as $akun) {
    $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
    $total_liabilitas += $saldo;
    if (strpos($akun['kode_akun'], '2.1') === 0) $liabilitas_pendek += $saldo;
}

// 3. Hitung Laba Rugi Menggunakan Perhitungan SHU yang benar (mendukung akun kontra)
$total_pendapatan = 0;
$pendapatan_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Pendapatan%'");
foreach ($pendapatan_list as $akun) {
    $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
    $total_pendapatan += ($akun['saldo_normal'] == 'Kredit') ? $saldo : -$saldo;
}

$total_beban = 0; $total_hpp = 0;
$beban_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Beban%'");
foreach ($beban_list as $akun) {
    $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
    $total_beban += ($akun['saldo_normal'] == 'Debit') ? $saldo : -$saldo;
    if (strpos($akun['kode_akun'], '5.') === 0) {
        $total_hpp += ($akun['saldo_normal'] == 'Debit') ? $saldo : -$saldo;
    }
}
$laba_bersih = $total_pendapatan - $total_beban;

// 4. Hitung Ekuitas (termasuk Laba Bersih tahun berjalan)
$ekuitas_list = query("SELECT kode_akun FROM daftar_akun WHERE kelompok_akun = 'Ekuitas'");
$total_ekuitas = 0;
foreach ($ekuitas_list as $akun) {
    $total_ekuitas += getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
}
// Tambahkan SHU berjalan jika belum diposting (belum tutup buku)
$cek_tutup_buku = query("SELECT id FROM histori_shu WHERE tahun = ?", [$tahun]);
if (empty($cek_tutup_buku)) {
    $total_ekuitas += $laba_bersih;
}

// --- PERHITUNGAN SKOR KESEHATAN (Standar BUMDes) ---

// 1. ROE (Return on Equity)
$roe = $total_ekuitas != 0 ? ($laba_bersih / $total_ekuitas) * 100 : 0;
$score_roe = 0;
if ($roe >= 20) $score_roe = 29;
elseif ($roe >= 15) $score_roe = 24;
elseif ($roe >= 10) $score_roe = 18;
elseif ($roe >= 5) $score_roe = 12;
elseif ($roe >= 0) $score_roe = 6;

// 2. ROI (Return on Investment)
$roi = $total_aset != 0 ? ($laba_bersih / $total_aset) * 100 : 0;
$score_roi = 0;
if ($roi >= 15) $score_roi = 22;
elseif ($roi >= 10) $score_roi = 18;
elseif ($roi >= 5) $score_roi = 14;
elseif ($roi >= 1) $score_roi = 10;
elseif ($roi >= 0) $score_roi = 5;

// 3. Rasio Kas (Cash Ratio)
$rasio_kas = $liabilitas_pendek != 0 ? ($kas_bank / $liabilitas_pendek) * 100 : ($kas_bank > 0 ? 100 : 0);
$score_rasio_kas = 0;
if ($rasio_kas >= 35) $score_rasio_kas = 7;
elseif ($rasio_kas >= 25) $score_rasio_kas = 6;
elseif ($rasio_kas >= 15) $score_rasio_kas = 5;
elseif ($rasio_kas >= 10) $score_rasio_kas = 4;
elseif ($rasio_kas >= 5) $score_rasio_kas = 3;
else $score_rasio_kas = 2;

// 4. Rasio Lancar (Current Ratio)
$rasio_lancar = $liabilitas_pendek != 0 ? ($aset_lancar / $liabilitas_pendek) * 100 : ($aset_lancar > 0 ? 150 : 0);
$score_rasio_lancar = 0;
if ($rasio_lancar >= 150) $score_rasio_lancar = 7;
elseif ($rasio_lancar >= 125) $score_rasio_lancar = 6;
elseif ($rasio_lancar >= 100) $score_rasio_lancar = 5;
elseif ($rasio_lancar >= 75) $score_rasio_lancar = 4;
elseif ($rasio_lancar >= 50) $score_rasio_lancar = 3;
else $score_rasio_lancar = 2;

// 5. Periode Penagihan (Collection Period)
$collection_period = $total_pendapatan != 0 ? ($piutang / $total_pendapatan) * 365 : 0;
$score_cp = 0;
if ($collection_period <= 60) $score_cp = 7;
elseif ($collection_period <= 90) $score_cp = 6;
elseif ($collection_period <= 120) $score_cp = 5;
elseif ($collection_period <= 150) $score_cp = 4;
elseif ($collection_period <= 180) $score_cp = 3;
else $score_cp = 0;

// 6. Perputaran Persediaan (Inventory Turnover)
$inventory_turnover = $total_hpp != 0 ? ($persediaan / $total_hpp) * 365 : 0;
$score_it = 0;
if ($inventory_turnover <= 60) $score_it = 7;
elseif ($inventory_turnover <= 90) $score_it = 6;
elseif ($inventory_turnover <= 120) $score_it = 5;
elseif ($inventory_turnover <= 150) $score_it = 4;
elseif ($inventory_turnover <= 180) $score_it = 3;
else $score_it = 0;

// 7. Perputaran Total Aset (Total Asset Turnover)
$asset_turnover = $total_aset != 0 ? ($total_pendapatan / $total_aset) * 100 : 0;
$score_at = 0;
if ($asset_turnover >= 120) $score_at = 7;
elseif ($asset_turnover >= 100) $score_at = 6;
elseif ($asset_turnover >= 80) $score_at = 5;
elseif ($asset_turnover >= 60) $score_at = 4;
elseif ($asset_turnover >= 40) $score_at = 3;
else $score_at = 2;

// 8. Rasio Total Modal Pemilik thd Total Aset
$equity_ratio = $total_aset != 0 ? ($total_ekuitas / $total_aset) * 100 : 0;
$score_er = 0;
if ($equity_ratio >= 60) $score_er = 14;
elseif ($equity_ratio >= 50) $score_er = 12;
elseif ($equity_ratio >= 40) $score_er = 10;
elseif ($equity_ratio >= 30) $score_er = 8;
elseif ($equity_ratio >= 20) $score_er = 6;
else $score_er = 4;

$total_skor = $score_roe + $score_roi + $score_rasio_kas + $score_rasio_lancar + $score_cp + $score_it + $score_at + $score_er;

$kategori_kesehatan = '';
$class_bg = '';
if ($total_skor >= 65) {
    $kategori_kesehatan = 'SEHAT';
    $class_bg = 'bg-success text-success-content';
} elseif ($total_skor >= 30) {
    $kategori_kesehatan = 'KURANG SEHAT';
    $class_bg = 'bg-warning text-warning-content';
} else {
    $kategori_kesehatan = 'TIDAK SEHAT';
    $class_bg = 'bg-error text-error-content';
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Tingkat_Kesehatan_BUMDes_' . $tahun . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['HASIL PENILAIAN TINGKAT KESEHATAN ASPEK KEUANGAN']);
    fputcsv($output, ['Tahun Buku', $tahun]);
    fputcsv($output, ['Hasil Kategori', $kategori_kesehatan]);
    fputcsv($output, ['Skor Total', $total_skor]);
    fputcsv($output, []);
    
    fputcsv($output, ['No', 'Indikator', 'Skor Maks', 'Nilai', 'Skor']);
    fputcsv($output, [1, 'Imbalan kepada pemilik (ROE)', 29, round($roe) . '%', $score_roe]);
    fputcsv($output, [2, 'Imbalan Investasi (ROI)', 22, round($roi) . '%', $score_roi]);
    fputcsv($output, [3, 'Rasio Kas', 7, round($rasio_kas) . '%', $score_rasio_kas]);
    fputcsv($output, [4, 'Rasio Lancar', 7, round($rasio_lancar) . '%', $score_rasio_lancar]);
    fputcsv($output, [5, 'Periode Penagihan (Collection Period)', 7, round($collection_period) . ' hari', $score_cp]);
    fputcsv($output, [6, 'Perputaran Persediaan', 7, round($inventory_turnover) . ' hari', $score_it]);
    fputcsv($output, [7, 'Perputaran Total Aset', 7, round($asset_turnover) . '%', $score_at]);
    fputcsv($output, [8, 'Rasio Total Modal Pemilik thd Total Aset', 14, round($equity_ratio) . '%', $score_er]);
    
    fputcsv($output, []);
    fputcsv($output, ['', 'TOTAL SKOR', 100, '', $total_skor]);
    
    fclose($output);
    exit;
}
?>
<?php  ?>

<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { 
        background-color: white !important; 
        color: black !important; 
        padding: 20px; 
        box-shadow: none !important;
        border: none !important;
    }
    .table td, .table th { 
        border: 1px solid #000 !important; 
        padding: 6px 10px !important; 
        color: black !important; 
    }
    .bg-base-200 { 
        background-color: #f3f4f6 !important; 
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact; 
    }
    .score-box { 
        border: 2px solid #000 !important; 
        color: #000 !important; 
        background: transparent !important; 
    }
}
</style>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold">Penilaian Tingkat Kesehatan</h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak Laporan</button>
        <form method="GET" action="" style="display: inline;">
            <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_tingkat_kesehatan' ?>">
            <input type="hidden" name="tahun" value="<?= htmlspecialchars($tahun) ?>">
            <input type="hidden" name="export" value="csv">
            <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
        </form>
    </div>
</div>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body p-6">
        <h2 class="card-title mb-2">Pilih Tahun Evaluasi</h2>
        <form method="POST" action="">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="form-control w-full md:w-64">
                    <label class="label"><span class="label-text">Tahun Buku</span></label>
                    <select name="tahun" class="select select-bordered w-full">
                        <?php for($i=date('Y'); $i>=date('Y')-5; $i--): ?>
                            <option value="<?= $i ?>" <?= $i==$tahun ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full md:w-auto">Hitung & Evaluasi</button>
            </div>
        </form>
    </div>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'BUMDes') ?></h2>
            <h3 class="text-xl font-bold uppercase mt-1">HASIL PENILAIAN TINGKAT KESEHATAN ASPEK KEUANGAN</h3>
            <p class="mt-2">Tahun Buku 1 Januari - 31 Desember <?= $tahun ?></p>
        </div>

        <div class="text-center py-6 my-8 rounded-xl shadow-sm score-box <?= $class_bg ?>">
            <h3 class="text-3xl font-black">HASIL: <?= $kategori_kesehatan ?> (SKOR <?= $total_skor ?>)</h3>
        </div>

        <div class="overflow-x-auto mb-8">
            <table class="table w-full border border-base-300 text-base">
                <thead>
                    <tr class="bg-base-200">
                        <th class="text-center border border-base-300 w-16">No.</th>
                        <th class="border border-base-300">Indikator</th>
                        <th class="text-center border border-base-300 w-32">Skor Maks</th>
                        <th class="text-center border border-base-300 w-32">Nilai</th>
                        <th class="text-center border border-base-300 w-24">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center border border-base-300">1</td>
                        <td class="border border-base-300">Imbalan kepada pemilik (ROE)</td>
                        <td class="text-center border border-base-300">29</td>
                        <td class="text-center border border-base-300"><?= round($roe) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_roe ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">2</td>
                        <td class="border border-base-300">Imbalan Investasi (ROI)</td>
                        <td class="text-center border border-base-300">22</td>
                        <td class="text-center border border-base-300"><?= round($roi) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_roi ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">3</td>
                        <td class="border border-base-300">Rasio Kas</td>
                        <td class="text-center border border-base-300">7</td>
                        <td class="text-center border border-base-300"><?= round($rasio_kas) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_rasio_kas ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">4</td>
                        <td class="border border-base-300">Rasio Lancar</td>
                        <td class="text-center border border-base-300">7</td>
                        <td class="text-center border border-base-300"><?= round($rasio_lancar) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_rasio_lancar ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">5</td>
                        <td class="border border-base-300">Periode Penagihan <i>(Collection Period)</i></td>
                        <td class="text-center border border-base-300">7</td>
                        <td class="text-center border border-base-300"><?= round($collection_period) ?> hari</td>
                        <td class="text-center border border-base-300"><?= $score_cp ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">6</td>
                        <td class="border border-base-300">Perputaran Persediaan</td>
                        <td class="text-center border border-base-300">7</td>
                        <td class="text-center border border-base-300"><?= round($inventory_turnover) ?> hari</td>
                        <td class="text-center border border-base-300"><?= $score_it ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">7</td>
                        <td class="border border-base-300">Perputaran Total Aset</td>
                        <td class="text-center border border-base-300">7</td>
                        <td class="text-center border border-base-300"><?= round($asset_turnover) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_at ?></td>
                    </tr>
                    <tr>
                        <td class="text-center border border-base-300">8</td>
                        <td class="border border-base-300">Rasio Total Modal Pemilik thd Total Aset</td>
                        <td class="text-center border border-base-300">14</td>
                        <td class="text-center border border-base-300"><?= round($equity_ratio) ?>%</td>
                        <td class="text-center border border-base-300"><?= $score_er ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-base-200">
                        <td colspan="2" class="text-center border border-base-300">Total Skor</td>
                        <td class="text-center border border-base-300">100</td>
                        <td class="text-center border border-base-300"></td>
                        <td class="text-center border border-base-300 text-xl"><?= $total_skor ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-8 text-sm md:text-base border border-base-300 p-6 rounded-lg bg-base-50">
            <p class="font-bold mb-4 text-lg">Keterangan Laporan:</p>
            <ol class="list-decimal list-inside space-y-2 mb-6">
                <li>Informasi tingkat kesehatan ini dihasilkan secara otomatis berdasarkan data saldo akun dari Jurnal Umum pada tahun berjalan.</li>
                <li>Rasio-rasio ini dirancang khusus mengikuti standar evaluasi kesehatan Badan Usaha Milik Desa.</li>
            </ol>
            
            <p class="font-bold mb-2">Tabel Referensi Kategori Tingkat Kesehatan:</p>
            <div class="overflow-x-auto w-full md:w-1/2">
                <table class="table table-compact border border-base-300">
                    <thead>
                        <tr class="bg-base-200">
                            <th class="border border-base-300">Kategori</th>
                            <th class="border border-base-300">Rentang Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="border border-base-300">Sehat</td><td class="border border-base-300">≥ 65</td></tr>
                        <tr><td class="border border-base-300">Kurang Sehat</td><td class="border border-base-300">30 ≤ x < 65</td></tr>
                        <tr><td class="border border-base-300">Tidak Sehat</td><td class="border border-base-300">< 30</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-16 flex justify-end text-center">
            <div>
                <p>Disahkan Oleh,<br><strong>Direktur / Pimpinan BUMDes</strong></p>
                <br><br><br>
                <p class="font-bold underline"><?= htmlspecialchars($_PENGATURAN['nama_pimpinan'] ?? '...........................') ?></p>
            </div>
        </div>
    </div>
</div>
<?php  ?>