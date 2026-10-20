<?php
$judulHalaman = 'Penjelasan Laporan Keuangan (CaLK)';
$tanggal_laporan = $_POST['tanggal_laporan'] ?? date('Y-m-d');
$tahun_laporan = date('Y', strtotime($tanggal_laporan));
$tanggal_mulai = date('Y-01-01', strtotime($tanggal_laporan));
$tanggal_awal_minus_1 = date('Y-m-d', strtotime($tanggal_mulai . ' -1 day'));

// Rincian Piutang (kode CoA Kepmendesa 136/2022)
$ak_piutang_anggota = $_PENGATURAN['akun_map_piutang_usaha_anggota'] ?? '1.1.03.01';
$ak_piutang_pinjaman = $_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04';
$ak_piutang_non_anggota = '1.1.03.05';
$ak_penyisihan_piutang = $_PENGATURAN['akun_map_penyisihan_piutang'] ?? '1.1.04.01';
$piutang_anggota = getSaldoAkun($ak_piutang_anggota, $tanggal_laporan);
$piutang_non_anggota = getSaldoAkun($ak_piutang_non_anggota, $tanggal_laporan);
$piutang_pinjaman = getSaldoAkun($ak_piutang_pinjaman, $tanggal_laporan);
$penyisihan_piutang = getSaldoAkun($ak_penyisihan_piutang, $tanggal_laporan);
$total_piutang_bersih = $piutang_anggota + $piutang_non_anggota + $piutang_pinjaman - $penyisihan_piutang;

// Rincian Aset Tetap (Rekonsiliasi) — default CoA Kepmendesa
$akun_aset_bangunan = $_PENGATURAN['akun_map_aset_bangunan'] ?? '1.3.05.01';
$akun_akumulasi_bangunan = $_PENGATURAN['akun_map_akumulasi_bangunan'] ?? '1.3.07.04';
$akun_aset_peralatan = $_PENGATURAN['akun_map_aset_peralatan'] ?? '1.3.03.01';
$akun_akumulasi_peralatan = $_PENGATURAN['akun_map_akumulasi_peralatan'] ?? '1.3.07.02';
$akun_beban_penyusutan = $_PENGATURAN['akun_map_beban_penyusutan'] ?? '6.1.07.03';

$hp_awal = getSaldoAkun($akun_aset_bangunan, $tanggal_awal_minus_1) + getSaldoAkun($akun_aset_peralatan, $tanggal_awal_minus_1);
$akum_awal = getSaldoAkun($akun_akumulasi_bangunan, $tanggal_awal_minus_1) + getSaldoAkun($akun_akumulasi_peralatan, $tanggal_awal_minus_1);

$penambahan_aset_q = query("SELECT SUM(harga_perolehan) as total FROM aset_tetap WHERE tanggal_perolehan BETWEEN ? AND ?", [$tanggal_mulai, $tanggal_laporan], 'ss');
$penambahan_aset = $penambahan_aset_q[0]['total'] ?? 0;

$pengurangan_aset_q = query("SELECT SUM(kredit) as total FROM jurnal_umum WHERE (kode_akun = ? OR kode_akun = ?) AND tanggal BETWEEN ? AND ?", [$akun_aset_bangunan, $akun_aset_peralatan, $tanggal_mulai, $tanggal_laporan], 'ssss');
$pengurangan_aset = $pengurangan_aset_q[0]['total'] ?? 0;

$penyusutan_periode = getSaldoAkun($akun_beban_penyusutan, $tanggal_laporan, $tanggal_mulai);
$hp_akhir = getSaldoAkun($akun_aset_bangunan, $tanggal_laporan) + getSaldoAkun($akun_aset_peralatan, $tanggal_laporan);
$akum_akhir = getSaldoAkun($akun_akumulasi_bangunan, $tanggal_laporan) + getSaldoAkun($akun_akumulasi_peralatan, $tanggal_laporan);

// Rincian Liabilitas (kode CoA Kepmendesa / akun_map)
$ak_utang_usaha = $_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01';
$ak_simpanan_sukarela = $_PENGATURAN['akun_map_simpanan_sukarela'] ?? '2.1.05.01';
$ak_utang_jasa_anggota = $_PENGATURAN['akun_map_utang_jasa_anggota'] ?? '2.1.05.99';
$ak_utang_dana_pengurus = $_PENGATURAN['akun_map_utang_dana_pengurus'] ?? '2.1.03.01';
$ak_utang_dana_sosial = $_PENGATURAN['akun_map_utang_dana_sosial'] ?? '2.1.09.99';
$utang_usaha = getSaldoAkun($ak_utang_usaha, $tanggal_laporan);
$simpanan_sukarela = getSaldoAkun($ak_simpanan_sukarela, $tanggal_laporan);
$utang_pinjaman_q = query("SELECT SUM(p.jumlah_pokok - (SELECT COALESCE(SUM(pokok_dibayar),0) FROM pembayaran_pinjaman WHERE pinjaman_id = p.id AND tanggal_bayar <= ?)) as total FROM pinjaman_diterima p WHERE p.tanggal_pinjaman <= ?", [$tanggal_laporan, $tanggal_laporan], 'ss');
$utang_pinjaman = $utang_pinjaman_q[0]['total'] ?? 0;
$total_liabilitas_jangka_pendek = $utang_usaha + $simpanan_sukarela + getSaldoAkun($ak_utang_jasa_anggota, $tanggal_laporan) + getSaldoAkun($ak_utang_dana_pengurus, $tanggal_laporan) + getSaldoAkun($ak_utang_dana_sosial, $tanggal_laporan);

// Rincian Ekuitas (Rekonsiliasi) — kode CoA Kepmendesa / akun_map
$ak_simpanan_pokok = $_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01';
$ak_simpanan_wajib = $_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01';
$ak_cadangan = $_PENGATURAN['akun_map_cadangan'] ?? '3.3.01.02';
$ak_shu = $_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01';
$simpanan_pokok_awal = getSaldoAkun($ak_simpanan_pokok, $tanggal_awal_minus_1);
$simpanan_wajib_awal = getSaldoAkun($ak_simpanan_wajib, $tanggal_awal_minus_1);
$cadangan_awal = getSaldoAkun($ak_cadangan, $tanggal_awal_minus_1);
$shu_awal = getSaldoAkun($ak_shu, $tanggal_awal_minus_1);

$simpanan_pokok_akhir = getSaldoAkun($ak_simpanan_pokok, $tanggal_laporan);
$simpanan_wajib_akhir = getSaldoAkun($ak_simpanan_wajib, $tanggal_laporan);
$cadangan_akhir = getSaldoAkun($ak_cadangan, $tanggal_laporan);
$setoran_pokok_periode = $simpanan_pokok_akhir - $simpanan_pokok_awal;
$setoran_wajib_periode = $simpanan_wajib_akhir - $simpanan_wajib_awal;
$penambahan_cadangan = $cadangan_akhir - $cadangan_awal;

$total_pendapatan = 0;
$pendapatan_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Pendapatan%'");
foreach ($pendapatan_list as $akun) {
    $s = getSaldoAkun($akun['kode_akun'], $tanggal_laporan, $tanggal_mulai);
    $total_pendapatan += ($akun['saldo_normal'] === 'Kredit') ? $s : -$s;
}
$total_beban = 0;
$beban_list = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Beban%'");
foreach ($beban_list as $akun) {
    $s = getSaldoAkun($akun['kode_akun'], $tanggal_laporan, $tanggal_mulai);
    $total_beban += ($akun['saldo_normal'] === 'Debit') ? $s : -$s;
}
$shu_periode_berjalan = $total_pendapatan - $total_beban;

$alokasi_shu_q = query("SELECT SUM(debit) as total FROM jurnal_umum WHERE kode_akun = ? AND keterangan LIKE 'Jurnal Alokasi SHU%' AND tanggal BETWEEN ? AND ?", [$ak_shu, $tanggal_mulai, $tanggal_laporan], 'sss');
$alokasi_shu_periode = $alokasi_shu_q[0]['total'] ?? 0;
$shu_akhir = $shu_awal + $shu_periode_berjalan - $alokasi_shu_periode;
?>
<?php  ?>
<style> @media print { .printable-area { background-color: white !important; color: black !important; } body * { visibility: hidden; } .printable-area, .printable-area * { visibility: visible; } .printable-area { position: absolute; left: 0; top: 0; width: 100%; } .no-print { display: none; } } </style>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <div class="flex gap-2">
        <a href="index.php?halaman=calk/form_edit_calk" class="btn btn-secondary">Edit Teks Penjelasan</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak Laporan</button>
    </div>
</div>
<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Pilih Tanggal Laporan</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Per Tanggal</span></label><input type="date" name="tanggal_laporan" class="input input-bordered" value="<?= htmlspecialchars($tanggal_laporan) ?>" required></div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Tampilkan Laporan</button></div>
            </div>
        </form>
    </div>
</div>
<div class="printable-area p-4 bg-base-100 rounded-lg shadow">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
        <h3 class="text-xl font-semibold">Catatan atas Laporan Keuangan</h3>
        <p>Untuk Periode yang Berakhir pada <?= htmlspecialchars(date('d F Y', strtotime($tanggal_laporan))) ?></p>
    </div>
    <div class="space-y-6">
        <div><h4 class="text-lg font-bold border-b pb-1 mb-2">1. Informasi Umum</h4><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_info_umum'] ?? '')) ?></p></div>
        <div><h4 class="text-lg font-bold border-b pb-1 mb-2">2. Dasar Penyusunan Laporan Keuangan</h4><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_kebijakan_akuntansi'] ?? '')) ?></p></div>
        <div><h4 class="text-lg font-bold border-b pb-1 mb-2">3. Ikhtisar Kebijakan Akuntansi Signifikan</h4>
             <div class="pl-4 space-y-4">
                <div><h5 class="font-semibold">Piutang</h5><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_kebijakan_piutang'] ?? '')) ?></p></div>
                <div><h5 class="font-semibold">Persediaan</h5><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_kebijakan_persediaan'] ?? '')) ?></p></div>
                <div><h5 class="font-semibold">Aset Tetap</h5><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_kebijakan_aset_tetap'] ?? '')) ?></p></div>
                <div><h5 class="font-semibold">Pengakuan Pendapatan</h5><p class="text-sm whitespace-pre-wrap"><?= nl2br(htmlspecialchars($_PENGATURAN['calk_kebijakan_pendapatan'] ?? '')) ?></p></div>
            </div>
        </div>
        <div><h4 class="text-lg font-bold border-b pb-1 mb-2">4. Rincian Pos Laporan Keuangan</h4>
            <div class="pl-4 space-y-4">
                <div><h5 class="font-semibold">4.1 Piutang</h5><div class="overflow-x-auto"><table class="table table-compact w-full mt-2"><tbody>
                    <tr><td>Piutang Usaha - Anggota</td><td class="text-right"><?= number_format($piutang_anggota,0,',','.') ?></td></tr>
                    <tr><td>Piutang Usaha - Non Anggota</td><td class="text-right"><?= number_format($piutang_non_anggota,0,',','.') ?></td></tr>
                    <tr><td>Piutang Pinjaman Anggota</td><td class="text-right"><?= number_format($piutang_pinjaman,0,',','.') ?></td></tr>
                    <tr class="bg-base-200"><td>Penyisihan Kerugian Piutang</td><td class="text-right">(<?= number_format($penyisihan_piutang,0,',','.') ?>)</td></tr>
                    <tr class="font-bold bg-base-300"><td>Total Piutang Bersih</td><td class="text-right"><?= number_format($total_piutang_bersih,0,',','.') ?></td></tr>
                    </tbody></table></div></div>
                 <div><h5 class="font-semibold">4.2 Aset Tetap</h5><div class="overflow-x-auto"><table class="table table-compact w-full mt-2">
                    <thead><tr><th>Keterangan</th><th class="text-right">Saldo Awal</th><th class="text-right">Penambahan</th><th class="text-right">Pengurangan</th><th class="text-right">Penyusutan</th><th class="text-right">Saldo Akhir</th></tr></thead>
                    <tbody>
                        <tr><td>Harga Perolehan</td><td class="text-right"><?= number_format($hp_awal, 2, ',', '.') ?></td><td class="text-right"><?= number_format($penambahan_aset, 2, ',', '.') ?></td><td class="text-right">(<?= number_format($pengurangan_aset, 2, ',', '.') ?>)</td><td class="text-right">-</td><td class="text-right"><?= number_format($hp_akhir, 2, ',', '.') ?></td></tr>
                        <tr><td>Akumulasi Penyusutan</td><td class="text-right">(<?= number_format($akum_awal, 2, ',', '.') ?>)</td><td class="text-right">-</td><td class="text-right">-</td><td class="text-right">(<?= number_format($penyusutan_periode, 2, ',', '.') ?>)</td><td class="text-right">(<?= number_format($akum_akhir, 2, ',', '.') ?>)</td></tr>
                        <tr class="font-bold bg-base-300"><td>Nilai Buku</td><td class="text-right"><?= number_format($hp_awal - $akum_awal, 2, ',', '.') ?></td><td colspan="3"></td><td class="text-right"><?= number_format($hp_akhir - $akum_akhir, 2, ',', '.') ?></td></tr>
                    </tbody></table></div></div>
                <div><h5 class="font-semibold">4.3 Liabilitas</h5><div class="overflow-x-auto"><table class="table table-compact w-full mt-2"><tbody>
                    <tr><td>Utang Usaha</td><td class="text-right"><?= number_format($utang_usaha,0,',','.') ?></td></tr>
                    <tr><td>Simpanan Sukarela</td><td class="text-right"><?= number_format($simpanan_sukarela,0,',','.') ?></td></tr>
                    <tr class="font-bold bg-base-300"><td>Total Liabilitas Jangka Pendek</td><td class="text-right"><?= number_format($total_liabilitas_jangka_pendek,0,',','.') ?></td></tr>
                    <tr><td>Utang Pinjaman</td><td class="text-right"><?= number_format($utang_pinjaman,0,',','.') ?></td></tr>
                    </tbody></table></div></div>
                 <div><h5 class="font-semibold">4.4 Ekuitas</h5><div class="overflow-x-auto"><table class="table table-compact w-full mt-2">
                    <thead><tr><th>Keterangan</th><th class="text-right">Simpanan Pokok</th><th class="text-right">Simpanan Wajib</th><th class="text-right">Cadangan</th><th class="text-right">SHU Belum Dibagi</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                         <tr>
                            <td class="font-bold">Saldo Awal</td>
                            <td class="text-right"><?= number_format($simpanan_pokok_awal, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($simpanan_wajib_awal, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($cadangan_awal, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($shu_awal, 2, ',', '.') ?></td>
                            <td class="text-right font-bold"><?= number_format($simpanan_pokok_awal + $simpanan_wajib_awal + $cadangan_awal + $shu_awal, 2, ',', '.') ?></td>
                         </tr>
                         <tr><td>Setoran Periode Ini</td><td class="text-right"><?= number_format($setoran_pokok_periode, 2, ',', '.') ?></td><td class="text-right"><?= number_format($setoran_wajib_periode, 2, ',', '.') ?></td><td></td><td></td><td class="text-right"><?= number_format($setoran_pokok_periode+$setoran_wajib_periode, 2, ',', '.') ?></td></tr>
                         <tr><td>SHU Tahun Berjalan</td><td></td><td></td><td></td><td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td><td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td></tr>
                         <tr><td>Alokasi SHU Tahun Lalu</td><td></td><td></td><td class="text-right"><?= number_format($penambahan_cadangan, 2, ',', '.') ?></td><td class="text-right">(<?= number_format($alokasi_shu_periode, 2, ',', '.') ?>)</td><td></td></tr>
                         <tr class="font-bold bg-base-300">
                             <td>Saldo Akhir</td>
                             <td class="text-right"><?= number_format($simpanan_pokok_akhir, 2, ',', '.') ?></td>
                             <td class="text-right"><?= number_format($simpanan_wajib_akhir, 2, ',', '.') ?></td>
                             <td class="text-right"><?= number_format($cadangan_akhir, 2, ',', '.') ?></td>
                             <td class="text-right"><?= number_format($shu_akhir, 2, ',', '.') ?></td>
                             <td class="text-right"><?= number_format($simpanan_pokok_akhir + $simpanan_wajib_akhir + $cadangan_akhir + $shu_akhir, 2, ',', '.') ?></td>
                         </tr>
                    </tbody></table></div></div>
            </div>
        </div>
    </div>
</div>
<?php  ?>