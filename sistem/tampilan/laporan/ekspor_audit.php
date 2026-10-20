<?php
$judulHalaman = 'Ekspor Laporan Audit Lengkap';
cekAkses('lihat_laporan');

$tanggal_mulai = $_POST['tanggal_mulai'] ?? date('Y-01-01');
$tanggal_selesai = $_POST['tanggal_selesai'] ?? date('Y-12-31');
$is_cetak = $_SERVER['REQUEST_METHOD'] == 'POST';

if ($is_cetak) {
    $tgl_awal_minus_1 = date('Y-m-d', strtotime($tanggal_mulai . ' -1 day'));
    
    // 1. DATA JURNAL UMUM
    $jurnal_umum = query("SELECT ju.*, da.nama_akun FROM jurnal_umum ju JOIN daftar_akun da ON ju.kode_akun = da.kode_akun WHERE ju.tanggal BETWEEN ? AND ? ORDER BY ju.tanggal ASC, ju.id ASC", [$tanggal_mulai, $tanggal_selesai]);

    // 2. DATA LABA RUGI (Memperhitungkan Akun Kontra)
    $pendapatan_list = query("SELECT kode_akun, nama_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Pendapatan%' ORDER BY kode_akun");
    $beban_list = query("SELECT kode_akun, nama_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Beban%' ORDER BY kode_akun");
    
    $total_pendapatan = 0; $data_pendapatan = [];
    foreach ($pendapatan_list as $akun) {
        $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
        if ($saldo != 0) { 
            $is_contra = ($akun['saldo_normal'] == 'Debit');
            $data_pendapatan[] = ['nama' => $akun['nama_akun'], 'jumlah' => $saldo, 'is_contra' => $is_contra]; 
            $total_pendapatan += $is_contra ? -$saldo : $saldo; 
        }
    }

    $total_beban = 0; $data_beban = [];
    $beban_pajak = 0;
    foreach ($beban_list as $akun) {
        // Beban Pajak dipisah dari Laba Operasi (Beban Usaha Utama)
        if (strpos($akun['kode_akun'], '7.3') === 0 || strpos(strtolower($akun['nama_akun']), 'pajak') !== false) {
            $beban_pajak += getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
            continue;
        }
        $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
        if ($saldo != 0) { 
            $is_contra = ($akun['saldo_normal'] == 'Kredit');
            $data_beban[] = ['nama' => $akun['nama_akun'], 'jumlah' => $saldo, 'is_contra' => $is_contra]; 
            $total_beban += $is_contra ? -$saldo : $saldo; 
        }
    }
    
    $shu_bruto = $total_pendapatan - $total_beban;
    $shu_setelah_pajak = $shu_bruto - $beban_pajak;
    $shu_periode_berjalan = $shu_setelah_pajak; // Menggunakan SHU final di Neraca & Ekuitas

    // 3. DATA NERACA
        $akun_ikhtisar_ex = $_PENGATURAN['akun_map_ikhtisar_lr'] ?? '3.9.01.01'; // akun penampung penutup — jangan dihitung ganda dgn SHU berjalan
        $aset_list = query("SELECT kode_akun, nama_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun = 'Aset' ORDER BY urutan_laporan ASC, kode_akun ASC");
        $liab_list = query("SELECT kode_akun, nama_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun = 'Liabilitas' ORDER BY urutan_laporan ASC, kode_akun ASC");
        $ekuitas_list = query("SELECT kode_akun, nama_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun = 'Ekuitas' AND kode_akun != ? ORDER BY urutan_laporan ASC, kode_akun ASC", [$akun_ikhtisar_ex]);

    $total_aset = 0; $data_aset = [];
    foreach ($aset_list as $akun) {
        $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
        if ($saldo != 0) { $nilai = $akun['saldo_normal'] === 'Debit' ? $saldo : -$saldo; $data_aset[] = ['nama' => $akun['nama_akun'], 'jumlah' => $nilai]; $total_aset += $nilai; }
    }

    $total_liabilitas = 0; $data_liab = [];
    foreach ($liab_list as $akun) {
        $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
        if ($saldo != 0) { $data_liab[] = ['nama' => $akun['nama_akun'], 'jumlah' => $saldo]; $total_liabilitas += $saldo; }
    }

    $total_ekuitas = 0; $data_ekuitas = [];
    foreach ($ekuitas_list as $akun) {
        $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai);
        if ($saldo != 0) { $data_ekuitas[] = ['nama' => $akun['nama_akun'], 'jumlah' => $saldo]; $total_ekuitas += $saldo; }
    }
    
    $total_ekuitas += $shu_periode_berjalan; 

    // 4. DATA PERUBAHAN MODAL
    $simpanan_pokok_awal = getSaldoAkun($_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01', $tgl_awal_minus_1);
    $simpanan_wajib_awal = getSaldoAkun($_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01', $tgl_awal_minus_1);
    $cadangan_awal = getSaldoAkun($_PENGATURAN['akun_map_cadangan'] ?? '3.3.01.02', $tgl_awal_minus_1);
    $shu_awal = getSaldoAkun($_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01', $tgl_awal_minus_1);
    
    $simpanan_pokok_akhir = getSaldoAkun($_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01', $tanggal_selesai);
    $simpanan_wajib_akhir = getSaldoAkun($_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01', $tanggal_selesai);
    $cadangan_akhir = getSaldoAkun($_PENGATURAN['akun_map_cadangan'] ?? '3.3.01.02', $tanggal_selesai);
    $shu_akhir_neraca = getSaldoAkun($_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01', $tanggal_selesai);

    $akun_shu = $_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3.3.01.01';
    $alokasi_shu_q = query("SELECT SUM(debit) as total FROM jurnal_umum WHERE kode_akun = ? AND keterangan LIKE 'Jurnal Alokasi SHU%' AND tanggal BETWEEN ? AND ?", [$akun_shu, $tanggal_mulai, $tanggal_selesai]);
    $alokasi_shu_periode = $alokasi_shu_q[0]['total'] ?? 0;
}
?>
<?php  ?>

<style>
@media print {
    body * { visibility: visible !important; }
    
    .no-print, .drawer-side, .navbar, .footer, #toast-container { 
        display: none !important; 
    }

    .drawer, .drawer-content, main {
        display: block !important;
        position: static !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        background-color: white !important;
        color: black !important;
    }

    .printable-area {
        position: static !important;
        left: auto !important;
        top: auto !important;
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }

    @page { size: A4 portrait; margin: 15mm; }
    .page-break { page-break-before: always; margin-top: 30px; }
    
    .table { width: 100% !important; border-collapse: collapse !important; }
    .table th, .table td { 
        border: 1px solid #000 !important; 
        color: #000 !important; 
        padding: 6px 10px !important;
    }
    .table thead th { 
        background-color: #f3f4f6 !important; 
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact; 
        font-weight: bold !important;
    }
    .bg-gray { 
        background-color: #e5e7eb !important; 
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact; 
    }
    tr { page-break-inside: avoid; }
}
</style>

<div class="no-print">
    <h1 class="text-3xl font-bold mb-6">Ekspor Laporan Audit Lengkap</h1>
    <p class="mb-4">Gunakan fitur ini untuk mengekspor seluruh aktivitas jurnal dan laporan ke dalam satu dokumen PDF untuk keperluan audit, analisis AI, atau arsip eksternal.</p>

    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h2 class="card-title">Pilih Periode Ekspor</h2>
            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="form-control"><label class="label"><span class="label-text">Tanggal Mulai</span></label><input type="date" name="tanggal_mulai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_mulai) ?>" required></div>
                    <div class="form-control"><label class="label"><span class="label-text">Tanggal Selesai</span></label><input type="date" name="tanggal_selesai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_selesai) ?>" required></div>
                    <div class="form-control"><button type="submit" class="btn btn-primary w-full">Buat Dokumen Ekspor</button></div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($is_cetak): ?>
<div class="no-print mb-6 p-4 bg-info text-info-content rounded-lg flex justify-between items-center shadow-lg">
    <div>
        <h3 class="font-bold text-lg"><i class="fas fa-file-pdf mr-2"></i>Dokumen Siap Dicetak</h3>
        <p class="text-sm mt-1">Klik tombol di samping, lalu pada dialog Print, pastikan <strong>"Save as PDF" / "Simpan sebagai PDF"</strong> yang dipilih.</p>
    </div>
    <button onclick="window.print()" class="btn btn-neutral text-white shadow-md">Cetak / Simpan PDF</button>
</div>

<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body p-4 md:p-8">
        
        <div class="text-center mb-10 border-b-2 border-base-300 pb-6">
            <h1 class="text-3xl font-bold mb-2 uppercase"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h1>
            <h2 class="text-2xl mb-4 uppercase">DOKUMEN AUDIT LENGKAP</h2>
            <p class="text-lg">Periode: <?= date('d M Y', strtotime($tanggal_mulai)) ?> s/d <?= date('d M Y', strtotime($tanggal_selesai)) ?></p>
        </div>

        <!-- 1. JURNAL UMUM -->
        <div class="mb-10">
            <h3 class="text-xl font-bold mb-4">1. RINCIAN JURNAL UMUM</h3>
            <div class="overflow-x-auto">
                <table class="table table-compact w-full text-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Bukti</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Keterangan</th>
                            <th class="text-right">Debit (Rp)</th>
                            <th class="text-right">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_jurnal_debit = 0; $total_jurnal_kredit = 0;
                        if(empty($jurnal_umum)): ?>
                            <tr><td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td></tr>
                        <?php else: foreach($jurnal_umum as $j): 
                            $total_jurnal_debit += $j['debit'];
                            $total_jurnal_kredit += $j['kredit'];
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($j['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($j['nomor_bukti']) ?></td>
                            <td class="font-mono"><?= htmlspecialchars($j['kode_akun']) ?></td>
                            <td><?= htmlspecialchars($j['nama_akun']) ?></td>
                            <td><?= htmlspecialchars($j['keterangan']) ?></td>
                            <td class="text-right"><?= $j['debit'] > 0 ? number_format($j['debit'], 2, ',', '.') : '-' ?></td>
                            <td class="text-right"><?= $j['kredit'] > 0 ? number_format($j['kredit'], 2, ',', '.') : '-' ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-bold text-base">
                            <td colspan="5" class="text-right">TOTAL MUTASI JURNAL</td>
                            <td class="text-right"><?= number_format($total_jurnal_debit, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($total_jurnal_kredit, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. LABA RUGI -->
        <div class="page-break mb-10">
            <h3 class="text-xl font-bold mb-4">2. LAPORAN LABA RUGI (HASIL USAHA)</h3>
            <div class="overflow-x-auto">
                <table class="table w-full text-base">
                    <tbody>
                        <tr class="bg-gray font-bold"><td colspan="2">PENDAPATAN</td></tr>
                        <?php foreach($data_pendapatan as $item): ?>
                            <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right"><?= $item['is_contra'] ? '(Rp ' : '' ?><?= number_format($item['jumlah'], 2, ',', '.') ?><?= $item['is_contra'] ? ')' : '' ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="font-bold border-b-2"><td>Total Pendapatan</td><td class="text-right"><?= number_format($total_pendapatan, 2, ',', '.') ?></td></tr>
                        
                        <tr><td colspan="2" class="border-none">&nbsp;</td></tr>

                        <tr class="bg-gray font-bold"><td colspan="2">BEBAN USAHA</td></tr>
                        <?php foreach($data_beban as $item): ?>
                            <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right"><?= $item['is_contra'] ? '' : '(' ?><?= number_format($item['jumlah'], 2, ',', '.') ?><?= $item['is_contra'] ? '' : ')' ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="font-bold border-b-2"><td>Total Beban Usaha</td><td class="text-right">(<?= number_format($total_beban, 2, ',', '.') ?>)</td></tr>

                        <tr><td colspan="2" class="border-none">&nbsp;</td></tr>
                        
                        <tr class="bg-gray font-bold text-lg"><td>SISA HASIL USAHA (SEBELUM PAJAK)</td><td class="text-right"><?= number_format($shu_bruto, 2, ',', '.') ?></td></tr>
                        <tr><td>Beban Pajak Penghasilan Badan</td><td class="text-right">(<?= number_format($beban_pajak, 2, ',', '.') ?>)</td></tr>
                        <tr class="bg-gray font-bold text-xl"><td>SISA HASIL USAHA BERSIH (SETELAH PAJAK)</td><td class="text-right"><?= number_format($shu_setelah_pajak, 2, ',', '.') ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. NERACA -->
        <div class="page-break mb-10">
            <h3 class="text-xl font-bold mb-4">3. LAPORAN POSISI KEUANGAN (NERACA)</h3>
            <div class="overflow-x-auto">
                <table class="table w-full text-base">
                    <tbody>
                        <tr class="bg-gray font-bold"><td colspan="2">ASET</td></tr>
                        <?php foreach($data_aset as $item): ?>
                            <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray font-bold"><td>TOTAL ASET</td><td class="text-right"><?= number_format($total_aset, 2, ',', '.') ?></td></tr>
                        
                        <tr><td colspan="2" class="border-none">&nbsp;</td></tr>

                        <tr class="bg-gray font-bold"><td colspan="2">LIABILITAS (UTANG)</td></tr>
                        <?php foreach($data_liab as $item): ?>
                            <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="font-bold border-b-2"><td>Total Liabilitas</td><td class="text-right"><?= number_format($total_liabilitas, 2, ',', '.') ?></td></tr>

                        <tr><td colspan="2" class="border-none">&nbsp;</td></tr>

                        <tr class="bg-gray font-bold"><td colspan="2">EKUITAS (MODAL)</td></tr>
                        <?php foreach($data_ekuitas as $item): ?>
                            <tr><td class="pl-8"><?= htmlspecialchars($item['nama']) ?></td><td class="text-right"><?= number_format($item['jumlah'], 2, ',', '.') ?></td></tr>
                        <?php endforeach; ?>
                        <tr><td class="pl-8">SHU Tahun Berjalan</td><td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td></tr>
                        <tr class="font-bold border-b-2"><td>Total Ekuitas</td><td class="text-right"><?= number_format($total_ekuitas, 2, ',', '.') ?></td></tr>
                        
                        <tr><td colspan="2" class="border-none">&nbsp;</td></tr>
                        
                        <tr class="bg-gray font-bold text-xl"><td>TOTAL LIABILITAS DAN EKUITAS</td><td class="text-right"><?= number_format($total_liabilitas + $total_ekuitas, 2, ',', '.') ?></td></tr>
                    </tbody>
                </table>
                
                <?php if(round($total_aset, 2) !== round($total_liabilitas + $total_ekuitas, 2)): ?>
                <div class="mt-4 p-4 border border-error bg-error/10 text-error font-bold rounded">
                    PERINGATAN AUDIT: Neraca tidak seimbang. Terdapat selisih sebesar Rp <?= number_format(abs($total_aset - ($total_liabilitas + $total_ekuitas)), 2, ',', '.') ?>.
                </div>
                <?php else: ?>
                <div class="mt-4 p-4 border border-success bg-success/10 text-success font-bold rounded">
                    Status Neraca: SEIMBANG (BALANCE).
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 4. PERUBAHAN MODAL -->
        <div class="page-break">
            <h3 class="text-xl font-bold mb-4">4. LAPORAN PERUBAHAN EKUITAS</h3>
            <div class="overflow-x-auto">
                <table class="table w-full text-base">
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th class="text-right">Modal Desa & Masyarakat</th>
                            <th class="text-right">Cadangan</th>
                            <th class="text-right">SHU Belum Dibagi</th>
                            <th class="text-right">Total Ekuitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-bold">Saldo Awal (<?= date('d/m/Y', strtotime($tanggal_mulai)) ?>)</td>
                            <td class="text-right"><?= number_format($simpanan_pokok_awal + $simpanan_wajib_awal, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($cadangan_awal, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($shu_awal, 2, ',', '.') ?></td>
                            <td class="text-right font-bold"><?= number_format($simpanan_pokok_awal + $simpanan_wajib_awal + $cadangan_awal + $shu_awal, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>Setoran Penambahan Modal Periode Ini</td>
                            <td class="text-right"><?= number_format(($simpanan_pokok_akhir - $simpanan_pokok_awal) + ($simpanan_wajib_akhir - $simpanan_wajib_awal), 2, ',', '.') ?></td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><?= number_format(($simpanan_pokok_akhir - $simpanan_pokok_awal) + ($simpanan_wajib_akhir - $simpanan_wajib_awal), 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td>SHU Tahun Berjalan</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($shu_periode_berjalan, 2, ',', '.') ?></td>
                        </tr>
                         <tr>
                            <td>Alokasi SHU Tahun Lalu (Ditahan)</td>
                            <td class="text-right">-</td>
                            <td class="text-right"><?= number_format($cadangan_akhir - $cadangan_awal, 2, ',', '.') ?></td>
                            <td class="text-right">(<?= number_format($alokasi_shu_periode, 2, ',', '.') ?>)</td>
                            <td class="text-right">-</td>
                        </tr>
                    </tbody>
                    <tfoot>
                         <tr class="bg-gray font-bold text-lg">
                            <td>Saldo Akhir (<?= date('d/m/Y', strtotime($tanggal_selesai)) ?>)</td>
                            <td class="text-right"><?= number_format($simpanan_pokok_akhir + $simpanan_wajib_akhir, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($cadangan_akhir, 2, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($shu_akhir_neraca, 2, ',', '.') ?></td>
                            <td class="text-right text-primary"><?= number_format($simpanan_pokok_akhir + $simpanan_wajib_akhir + $cadangan_akhir + $shu_akhir_neraca, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-16 text-center text-sm opacity-70">
                <p>-- Akhir Laporan Audit --</p>
                <p>Dicetak dari Sistem Akuntansi <?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?> pada <?= date('d/m/Y H:i:s') ?></p>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>