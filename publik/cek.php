<?php
require_once '../sistem/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

$judulHalaman = 'Diagnostik Ketidakseimbangan Neraca';

// 1. Cek Jurnal Tidak Seimbang (Unbalanced Journal Entries)
$jurnal_unbalance = query("
    SELECT nomor_bukti, tanggal, MAX(keterangan) as ket, SUM(debit) as total_debit, SUM(kredit) as total_kredit, ABS(SUM(debit) - SUM(kredit)) as selisih
    FROM jurnal_umum 
    GROUP BY nomor_bukti, tanggal 
    HAVING ROUND(SUM(debit), 2) != ROUND(SUM(kredit), 2)
");

// 2. Cek Akun Yatim (Akun ada di jurnal tapi terhapus di Master Akun)
$akun_yatim = query("
    SELECT ju.kode_akun, SUM(ju.debit) as td, SUM(ju.kredit) as tk 
    FROM jurnal_umum ju 
    LEFT JOIN daftar_akun da ON ju.kode_akun = da.kode_akun 
    WHERE da.kode_akun IS NULL 
    GROUP BY ju.kode_akun
");

// 3. Cek Anomali Saldo Normal (Salah setting saat membuat akun)
// Aset & Beban normalnya Debit (kecuali akumulasi/penyisihan). Liabilitas, Ekuitas, Pendapatan normalnya Kredit.
$anomali_saldo = query("
    SELECT kode_akun, nama_akun, kelompok_akun, saldo_normal 
    FROM daftar_akun 
    WHERE 
      (kelompok_akun IN ('Aset', 'Beban', 'Beban Lain-lain') AND saldo_normal = 'Kredit' AND nama_akun NOT LIKE '%Penyusutan%' AND nama_akun NOT LIKE '%Akumulasi%' AND nama_akun NOT LIKE '%Penyisihan%' AND nama_akun NOT LIKE '%Amortisasi%')
      OR 
      (kelompok_akun IN ('Liabilitas', 'Ekuitas', 'Pendapatan', 'Pendapatan Lain-lain') AND saldo_normal = 'Debit' AND nama_akun NOT LIKE '%Diskon%' AND nama_akun NOT LIKE '%Potongan%' AND nama_akun NOT LIKE '%Retur%')
");

// 4. Neraca Lajur (Trial Balance) untuk melihat pergerakan angka
$neraca_lajur = query("
    SELECT 
        da.kode_akun, da.nama_akun, da.kelompok_akun, da.saldo_normal,
        COALESCE(SUM(ju.debit), 0) as total_debit, 
        COALESCE(SUM(ju.kredit), 0) as total_kredit
    FROM daftar_akun da
    LEFT JOIN jurnal_umum ju ON da.kode_akun = ju.kode_akun
    GROUP BY da.kode_akun
    ORDER BY da.kode_akun ASC
");

$grand_debit = 0;
$grand_kredit = 0;
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judulHalaman ?></title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-base-200 p-8 min-h-screen">
    
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold"><i class="fas fa-stethoscope text-primary mr-2"></i> Diagnostik Sistem & Neraca</h1>
            <a href="index.php?halaman=dasbor" class="btn btn-outline">Kembali ke Dasbor</a>
        </div>

        <div class="alert alert-info shadow-lg mb-8">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h3 class="font-bold">Alat Tracing Ketidakseimbangan (Unbalance)</h3>
                <div class="text-sm">Gunakan halaman ini untuk mendeteksi akar masalah mengapa laporan neraca Anda selisih Rp 17.925.000 atau nominal lainnya. Perhatikan blok berwarna merah di bawah ini.</div>
            </div>
        </div>

        <!-- DIAGNOSTIK 1: AKUN YATIM -->
        <div class="card bg-base-100 shadow-xl mb-6 <?= !empty($akun_yatim) ? 'border-t-4 border-error' : 'border-t-4 border-success' ?>">
            <div class="card-body">
                <h2 class="card-title text-xl">1. Cek Akun Yatim (Orphan Accounts)</h2>
                <p class="text-sm text-base-content/70">Akun yatim adalah kode akun yang memiliki nilai transaksi di dalam <b>Jurnal Umum</b>, tetapi Anda (atau sistem) telah <b>menghapusnya dari Master Daftar Akun</b>. Ini adalah penyebab utama neraca tidak seimbang karena sistem gagal mengelompokkannya ke Aset/Utang/Modal.</p>
                
                <?php if (empty($akun_yatim)): ?>
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle text-xl"></i> Tidak ditemukan akun yatim. Aman.
                    </div>
                <?php else: ?>
                    <div class="alert alert-error mt-4 text-white">
                        <i class="fas fa-times-circle text-xl"></i> Ditemukan <?= count($akun_yatim) ?> akun yang hilang dari Master Data!
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="table w-full border border-base-300">
                            <thead class="bg-base-200">
                                <tr><th>Kode Akun yg Hilang</th><th class="text-right">Total Debit Jurnal</th><th class="text-right">Total Kredit Jurnal</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($akun_yatim as $y): ?>
                                <tr>
                                    <td class="font-bold text-error font-mono text-lg"><?= htmlspecialchars($y['kode_akun']) ?></td>
                                    <td class="text-right font-mono">Rp <?= number_format($y['td'], 2, ',', '.') ?></td>
                                    <td class="text-right font-mono">Rp <?= number_format($y['tk'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-warning/20 p-4 rounded mt-4 font-bold">
                        <i class="fas fa-wrench text-warning mr-2"></i> SOLUSI: Buka menu <b>Administrasi -> Master Bagan Akun</b>, lalu Tambahkan Akun Baru dengan memasukkan persis KODE AKUN berwarna merah di atas agar sistem bisa membaca nilainya kembali.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DIAGNOSTIK 2: JURNAL TIDAK SEIMBANG -->
        <div class="card bg-base-100 shadow-xl mb-6 <?= !empty($jurnal_unbalance) ? 'border-t-4 border-error' : 'border-t-4 border-success' ?>">
            <div class="card-body">
                <h2 class="card-title text-xl">2. Cek Transaksi Jurnal Tidak Seimbang (Unbalanced)</h2>
                <p class="text-sm text-base-content/70">Mendeteksi transaksi yang memiliki nomor bukti sama (1 transaksi) tetapi nilai total Debit dan total Kreditnya berbeda (selisih).</p>
                
                <?php if (empty($jurnal_unbalance)): ?>
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle text-xl"></i> Seluruh jurnal sudah seimbang antara Debit dan Kredit. Aman.
                    </div>
                <?php else: ?>
                    <div class="alert alert-error mt-4 text-white">
                        <i class="fas fa-times-circle text-xl"></i> Ditemukan <?= count($jurnal_unbalance) ?> transaksi yang tidak *balance*!
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="table w-full border border-base-300">
                            <thead class="bg-base-200">
                                <tr><th>Tanggal</th><th>Nomor Bukti</th><th>Keterangan</th><th class="text-right">Total Debit</th><th class="text-right">Total Kredit</th><th class="text-right">Selisih</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($jurnal_unbalance as $j): ?>
                                <tr>
                                    <td><?= $j['tanggal'] ?></td>
                                    <td class="font-bold text-error"><?= htmlspecialchars($j['nomor_bukti']) ?></td>
                                    <td><?= htmlspecialchars($j['ket']) ?></td>
                                    <td class="text-right font-mono">Rp <?= number_format($j['total_debit'], 2, ',', '.') ?></td>
                                    <td class="text-right font-mono">Rp <?= number_format($j['total_kredit'], 2, ',', '.') ?></td>
                                    <td class="text-right font-mono font-bold text-error">Rp <?= number_format($j['selisih'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-warning/20 p-4 rounded mt-4 font-bold">
                        <i class="fas fa-wrench text-warning mr-2"></i> SOLUSI: Buka menu <b>Laporan -> Buku Jurnal Umum</b>, cari Nomor Bukti di atas. Hapus/Batalkan transaksi tersebut melalui menu asalnya (atau menu Jurnal Penyesuaian jika manual), lalu input ulang dengan benar.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DIAGNOSTIK 3: ANOMALI SALDO NORMAL -->
        <div class="card bg-base-100 shadow-xl mb-6 <?= !empty($anomali_saldo) ? 'border-t-4 border-warning' : 'border-t-4 border-success' ?>">
            <div class="card-body">
                <h2 class="card-title text-xl">3. Cek Anomali Sifat Akun (Saldo Normal)</h2>
                <p class="text-sm text-base-content/70">Mendeteksi kesalahan pengaturan saat Anda menambahkan akun baru. Contoh: Kas/Aset seharusnya bersaldo Debit, jika diset Kredit maka akan merusak perhitungan Neraca. Begitu juga Utang/Modal yang seharusnya Kredit.</p>
                
                <?php if (empty($anomali_saldo)): ?>
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle text-xl"></i> Tidak ditemukan anomali konfigurasi saldo normal akun. Aman.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle text-xl"></i> Ditemukan <?= count($anomali_saldo) ?> akun yang memiliki konfigurasi tidak wajar (meski mungkin benar jika itu akun penyeimbang/kontra).
                    </div>
                    <div class="overflow-x-auto mt-4">
                        <table class="table w-full border border-base-300">
                            <thead class="bg-base-200">
                                <tr><th>Kode Akun</th><th>Nama Akun</th><th>Kelompok</th><th>Saldo Normal Terdaftar</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($anomali_saldo as $a): ?>
                                <tr>
                                    <td class="font-bold font-mono"><?= htmlspecialchars($a['kode_akun']) ?></td>
                                    <td><?= htmlspecialchars($a['nama_akun']) ?></td>
                                    <td><?= htmlspecialchars($a['kelompok_akun']) ?></td>
                                    <td class="font-bold text-error"><?= htmlspecialchars($a['saldo_normal']) ?> (Anomali)</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-warning/20 p-4 rounded mt-4 text-sm">
                        <i class="fas fa-info-circle text-info mr-2"></i> <b>Catatan:</b> Jika akun di atas adalah Akun Kontra (seperti Akumulasi Penyusutan, Penyisihan Piutang, atau Diskon Penjualan), maka konfigurasi tersebut <b>SUDAH BENAR</b> dan abaikan peringatan ini. Jika bukan, segera Edit akun tersebut di Master Akun.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- DIAGNOSTIK 4: NERACA LAJUR (TRIAL BALANCE) -->
        <div class="card bg-base-100 shadow-xl mb-12 border border-base-200">
            <div class="card-body">
                <h2 class="card-title text-xl">4. Neraca Lajur Mentah (Trial Balance Semua Periode)</h2>
                <p class="text-sm text-base-content/70">Ini adalah akumulasi total Debit dan total Kredit dari jurnal untuk setiap akun sejak hari pertama koperasi berdiri. Total Debit Bawah dan Total Kredit Bawah <b>HARUS SAMA PERSIS</b>. Jika berbeda, berarti ada *bug* saat menjurnal atau ada akun yang terhapus.</p>
                
                <div class="overflow-x-auto mt-4">
                    <table class="table table-compact w-full border border-base-300">
                        <thead class="bg-neutral text-neutral-content">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Akun</th>
                                <th>Kelompok</th>
                                <th class="text-right">Total Debit Jurnal (Rp)</th>
                                <th class="text-right">Total Kredit Jurnal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($neraca_lajur as $n): 
                                $grand_debit += $n['total_debit'];
                                $grand_kredit += $n['total_kredit'];
                                if ($n['total_debit'] == 0 && $n['total_kredit'] == 0) continue; // Hide zero accounts for clarity
                            ?>
                            <tr class="hover">
                                <td class="font-mono"><?= $n['kode_akun'] ?></td>
                                <td><?= htmlspecialchars($n['nama_akun']) ?></td>
                                <td class="text-xs"><?= $n['kelompok_akun'] ?></td>
                                <td class="text-right font-mono text-info"><?= number_format($n['total_debit'], 2, ',', '.') ?></td>
                                <td class="text-right font-mono text-error"><?= number_format($n['total_kredit'], 2, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-base-300 text-lg font-bold">
                                <td colspan="3" class="text-right">GRAND TOTAL NERACA LAJUR</td>
                                <td class="text-right text-info font-mono">Rp <?= number_format($grand_debit, 2, ',', '.') ?></td>
                                <td class="text-right text-error font-mono">Rp <?= number_format($grand_kredit, 2, ',', '.') ?></td>
                            </tr>
                            <tr class="bg-base-200 text-xl font-black">
                                <td colspan="3" class="text-right">SELISIH TOTAL (BALANCE KONTROL)</td>
                                <td colspan="2" class="text-center font-mono <?= round($grand_debit, 2) === round($grand_kredit, 2) ? 'text-success' : 'text-error bg-error/20' ?>">
                                    Rp <?= number_format(abs($grand_debit - $grand_kredit), 2, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</body>
</html>