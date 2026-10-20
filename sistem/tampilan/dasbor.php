<?php
$judulHalaman = 'Dasbor Utama';

$kas_terkini = 0;
$akun_kas_q = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kelompok_akun = 'Aset'");
foreach ($akun_kas_q as $ak_kas) {
    $kas_terkini += getSaldoAkun($ak_kas['kode_akun'], date('Y-m-d'));
}
$tgl_awal_bulan = date('Y-m-01');
$tgl_akhir_bulan = date('Y-m-t');

// Optimasi Query Grafik & SHU Bulan ini (Single Query grouping by month)
$grafik_q = query("
    SELECT strftime('%Y-%m', ju.tanggal) as bulan,
           SUM(CASE WHEN da.kelompok_akun LIKE 'Pendapatan%' THEN ju.kredit - ju.debit ELSE 0 END) as total_pendapatan,
           SUM(CASE WHEN da.kelompok_akun LIKE 'Beban%' THEN ju.debit - ju.kredit ELSE 0 END) as total_beban
    FROM jurnal_umum ju
    JOIN daftar_akun da ON ju.kode_akun = da.kode_akun
    WHERE ju.tanggal >= date('now', 'start of month', '-5 months')
    GROUP BY strftime('%Y-%m', ju.tanggal)
    ORDER BY bulan ASC
");

$grafik_data = [];
foreach($grafik_q as $g) {
    $grafik_data[$g['bulan']] = $g;
}

$labels = []; $data_pendapatan = []; $data_beban = [];
$shu_bulan_ini = 0;

for ($i = 5; $i >= 0; $i--) {
    $bulan_key = date('Y-m', strtotime("-$i month"));
    $labels[] = date('M Y', strtotime("-$i month"));
    
    $pendapatan = $grafik_data[$bulan_key]['total_pendapatan'] ?? 0;
    $beban = $grafik_data[$bulan_key]['total_beban'] ?? 0;
    
    $data_pendapatan[] = $pendapatan;
    $data_beban[] = $beban;
    
    if ($i === 0) { // Bulan ini
        $shu_bulan_ini = $pendapatan - $beban;
    }
}
$data_grafik_json = json_encode(['labels' => $labels, 'pendapatan' => $data_pendapatan, 'beban' => $data_beban]);

$penjualan_hari_ini_q = query("SELECT COALESCE(SUM(total), 0) as total, COUNT(id) as jumlah_transaksi FROM penjualan WHERE date(tanggal_penjualan) = date('now', 'localtime')");
$penjualan_hari_ini = $penjualan_hari_ini_q[0];

$aktivitas_terakhir = query("SELECT * FROM (
    SELECT penjualan.tanggal_penjualan as tanggal, 'Penjualan' as jenis, penjualan.total as jumlah, a.nama_lengkap as keterangan, penjualan.id as urutan_id FROM penjualan LEFT JOIN anggota a ON penjualan.anggota_id = a.id
    UNION ALL SELECT p.tanggal_pembelian as tanggal, 'Pembelian' as jenis, p.total as jumlah, s.nama_pemasok as keterangan, p.id as urutan_id FROM pembelian p LEFT JOIN pemasok s ON p.pemasok_id = s.id WHERE (p.status_batal IS NULL OR p.status_batal = 0)
    UNION ALL SELECT beban_operasional.tanggal_beban as tanggal, 'Biaya' as jenis, beban_operasional.jumlah, beban_operasional.keterangan, beban_operasional.id as urutan_id FROM beban_operasional
    UNION ALL SELECT simpanan.tanggal_setor as tanggal, 'Simpanan / Modal' as jenis, simpanan.jumlah, a.nama_lengkap as keterangan, simpanan.id as urutan_id FROM simpanan JOIN anggota a ON simpanan.anggota_id = a.id
) AS aktivitas ORDER BY tanggal DESC, urutan_id DESC LIMIT 5");

$total_anggota_q = query("SELECT COUNT(id) as total FROM anggota");
$total_anggota = $total_anggota_q[0]['total'] ?? 0;

$stok_menipis = query("SELECT * FROM produk WHERE stok <= stok_minimum AND status_aktif = 1 ORDER BY stok ASC");
$produk_terlaris = query("SELECT p.nama_produk, SUM(dp.kuantitas) as total_terjual FROM detail_penjualan dp JOIN produk p ON dp.produk_id = p.id JOIN penjualan pj ON dp.penjualan_id = pj.id WHERE pj.tanggal_penjualan BETWEEN ? AND ? GROUP BY p.id ORDER BY total_terjual DESC LIMIT 5", [$tgl_awal_bulan, $tgl_akhir_bulan]);
$anggota_teraktif = query("SELECT a.nama_lengkap, SUM(p.total) as total_belanja FROM penjualan p JOIN anggota a ON p.anggota_id = a.id WHERE p.tanggal_penjualan BETWEEN ? AND ? GROUP BY a.id ORDER BY total_belanja DESC LIMIT 5", [$tgl_awal_bulan, $tgl_akhir_bulan]);

// Peringatan Keamanan
$aktivitas_mencurigakan = query("SELECT * FROM log_aktivitas WHERE (CAST(strftime('%H', timestamp) AS INTEGER) < 8 OR CAST(strftime('%H', timestamp) AS INTEGER) >= 18) AND timestamp >= datetime('now', '-7 days') ORDER BY timestamp DESC LIMIT 5");
$penghapusan_terakhir = query("SELECT COUNT(id) as total FROM log_aktivitas WHERE aksi LIKE 'Hapus%' AND timestamp >= datetime('now', '-1 day')");
$total_penghapusan = $penghapusan_terakhir[0]['total'] ?? 0;

// Pengingat Jatuh Tempo Piutang Toko
$piutang_toko_jatuh_tempo = query("
    SELECT p.id, p.nomor_faktur, p.tanggal_jatuh_tempo, (p.total - p.diskon) as nilai, COALESCE(a.nama_lengkap, p.nama_pelanggan_umum, 'Umum') as nama 
    FROM penjualan p 
    LEFT JOIN anggota a ON p.anggota_id = a.id 
    WHERE p.metode_pembayaran = 'Kredit' 
      AND p.status_batal = 0 
      AND p.tanggal_jatuh_tempo IS NOT NULL 
      AND p.tanggal_jatuh_tempo <= date('now', '+7 days') 
    ORDER BY p.tanggal_jatuh_tempo ASC LIMIT 5
");

// Pengingat Jatuh Tempo Utang Usaha
$utang_pemasok_jatuh_tempo = query("
    SELECT p.id, p.nomor_faktur_pemasok, p.tanggal_jatuh_tempo, p.total as nilai, s.nama_pemasok as nama 
    FROM pembelian p 
    LEFT JOIN pemasok s ON p.pemasok_id = s.id 
    WHERE p.metode_pembayaran = 'Kredit' 
      AND p.tanggal_jatuh_tempo IS NOT NULL 
      AND p.tanggal_jatuh_tempo <= date('now', '+7 days') 
    ORDER BY p.tanggal_jatuh_tempo ASC LIMIT 5
");

// Pengingat Laporan
$now = new DateTime();
$current_quarter = ceil($now->format('n') / 3);
$end_of_quarter = new DateTime(date('Y-m-t', mktime(0, 0, 0, $current_quarter * 3, 1, $now->format('Y'))));
$deadline_quarterly = (clone $end_of_quarter)->modify('first day of next month')->modify('+19 days');
$deadline_annual = new DateTime(($now->format('Y') + 1) . '-04-30');
$days_to_quarterly = $now->diff($deadline_quarterly)->format('%R%a');
$days_to_annual = $now->diff($deadline_annual)->format('%R%a');

$surat_masuk_baru = query("SELECT COUNT(id) as total FROM adm_surat_masuk WHERE tanggal_masuk >= date('now', '-7 days')")[0]['total'] ?? 0;
$saran_anggota_baru = query("SELECT COUNT(id) as total FROM adm_saran_anggota WHERE tanggal >= date('now', '-7 days')")[0]['total'] ?? 0;
$tamu_hari_ini = query("SELECT COUNT(id) as total FROM adm_buku_tamu WHERE date(tanggal) = date('now', 'localtime')")[0]['total'] ?? 0;
?>

<h1 class="text-3xl font-bold mb-6">Dasbor Sistem Koperasi</h1>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const serverTime = <?= time() * 1000 ?>;
    const clientTime = Date.now();
    if (Math.abs(clientTime - serverTime) > 86400000) {
        showToast('Peringatan: Tanggal atau Jam komputer Anda tidak sinkron dengan sistem. Periksa pengaturan waktu OS Anda.', 'error');
    }
});
</script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <a href="index.php?halaman=penjualan/form_penjualan" class="card bg-primary text-primary-content hover:bg-primary-focus transition-all p-4 min-h-[100px] flex justify-center items-center shadow-lg"><h2 class="card-title text-xl text-center"><i class="fas fa-cash-register mr-2"></i> Buat Penjualan Toko</h2></a>
    <a href="index.php?halaman=simpanan/form_simpanan" class="card bg-info text-info-content hover:bg-info-focus transition-all p-4 min-h-[100px] flex justify-center items-center shadow-lg"><h2 class="card-title text-xl text-center"><i class="fas fa-piggy-bank mr-2"></i> Penerimaan Modal</h2></a>
    <a href="index.php?halaman=beban/form_beban" class="card bg-secondary text-secondary-content hover:bg-secondary-focus transition-all p-4 min-h-[100px] flex justify-center items-center shadow-lg"><h2 class="card-title text-xl text-center"><i class="fas fa-receipt mr-2"></i> Catat Pengeluaran Kas</h2></a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <?php if (!empty($piutang_toko_jatuh_tempo)): ?>
    <div class="alert shadow-lg bg-error/10 border-l-4 border-error text-base-content items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-error shrink-0 h-6 w-6 mt-1" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div class="w-full">
            <strong class="font-bold text-error block">Piutang Toko Jatuh Tempo!</strong>
            <div class="text-sm mt-1">
                Pelanggan dengan kasbon yang melewati batas waktu atau jatuh tempo dalam 7 hari ini:
                <ul class="list-disc list-inside mt-1 font-mono text-xs">
                    <?php foreach($piutang_toko_jatuh_tempo as $p): ?>
                        <li><?= htmlspecialchars($p['nomor_faktur']) ?> (<?= htmlspecialchars($p['nama']) ?>) - Rp <?= number_format($p['nilai'], 0, ',', '.') ?> - Due: <b class="text-error"><?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?></b></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-2 text-xs italic opacity-70">*Abaikan jika sudah Anda lunasi di Jurnal.</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($utang_pemasok_jatuh_tempo)): ?>
    <div class="alert shadow-lg bg-warning/20 border-l-4 border-warning text-base-content items-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-warning shrink-0 h-6 w-6 mt-1" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div class="w-full">
            <strong class="font-bold text-warning-content block">Utang Pemasok Jatuh Tempo!</strong>
            <div class="text-sm mt-1">
                Terdapat tagihan dari pemasok/supplier yang perlu segera Anda bayar:
                <ul class="list-disc list-inside mt-1 font-mono text-xs">
                    <?php foreach($utang_pemasok_jatuh_tempo as $p): ?>
                        <li><?= htmlspecialchars($p['nomor_faktur_pemasok']) ?> (<?= htmlspecialchars($p['nama']) ?>) - Rp <?= number_format($p['nilai'], 0, ',', '.') ?> - Due: <b class="text-error"><?= date('d/m/Y', strtotime($p['tanggal_jatuh_tempo'])) ?></b></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-2 text-xs italic opacity-70"><a href="index.php?halaman=laporan/laporan_utang_usaha" class="link font-bold text-primary">Buka Buku Utang</a> untuk melunasi.</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($aktivitas_mencurigakan) || $total_penghapusan > 5): ?>
<div class="card bg-error text-error-content shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title">Peringatan Keamanan Sistem</h2>
        <?php if (!empty($aktivitas_mencurigakan)): ?>
        <p class="font-bold">Terdeteksi aktivitas di luar jam kerja normal (08:00 - 18:00):</p>
        <ul class="list-disc list-inside text-sm">
            <?php foreach($aktivitas_mencurigakan as $log): ?>
            <li><strong><?= htmlspecialchars($log['nama_pengguna']) ?></strong> melakukan aksi "<?= htmlspecialchars($log['aksi']) ?>" pada <?= date('d/m/Y H:i', strtotime($log['timestamp'])) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if ($total_penghapusan > 5): ?>
        <p class="font-bold mt-2">Terdeteksi <?= $total_penghapusan ?> aksi penghapusan masif dalam 24 jam terakhir.</p>
        <?php endif; ?>
        <div class="card-actions justify-end">
            <a href="index.php?halaman=administrasi/lihat_log_aktivitas" class="btn btn-sm">Lihat Log Lengkap</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(!empty($stok_menipis)): ?>
<div class="alert shadow-lg mb-6 bg-info/20 border-l-4 border-info text-base-content">
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-info shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <strong class="font-bold">Informasi: Stok Barang Menipis</strong>
            <div class="text-sm mt-1">Beberapa barang persediaan/perlengkapan perlu segera dipesan ulang: 
                <span class="font-mono text-xs block mt-1">
                <?php $list_produk = []; foreach($stok_menipis as $p) { $list_produk[] = htmlspecialchars($p['nama_produk']) . ' (sisa ' . $p['stok'] . ')'; } echo implode(', ', $list_produk); ?>. 
                </span>
                <a href="index.php?halaman=laporan/laporan_kebutuhan_stok" class="link text-primary font-bold mt-1 inline-block">Buka Daftar Belanja Lengkap</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats shadow w-full mb-6 stats-vertical lg:stats-horizontal border border-base-200">
    <div class="stat"><div class="stat-title font-bold text-base-content/70">Kas & Bank Saat Ini</div><div class="stat-value text-primary text-3xl font-mono">Rp <?= number_format($kas_terkini, 0, ',', '.') ?></div></div>
    <div class="stat"><div class="stat-title font-bold text-base-content/70">Untung/Rugi Bulan Ini</div><div class="stat-value <?= $shu_bulan_ini < 0 ? 'text-error' : 'text-success' ?> text-3xl font-mono">Rp <?= number_format($shu_bulan_ini, 0, ',', '.') ?></div></div>
    <div class="stat"><div class="stat-title font-bold text-base-content/70">Total Penjualan Hari Ini</div><div class="stat-value text-secondary text-3xl font-mono">Rp <?= number_format($penjualan_hari_ini['total'], 0, ',', '.') ?></div><div class="stat-desc font-bold mt-1"><?= $penjualan_hari_ini['jumlah_transaksi'] ?> Struk Transaksi</div></div>
    <div class="stat"><div class="stat-title font-bold text-base-content/70">Total Entitas Terdaftar</div><div class="stat-value text-3xl"><?= $total_anggota ?></div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card bg-base-100 shadow-xl border border-base-200 lg:col-span-2">
        <div class="card-body">
            <h2 class="card-title text-lg border-b pb-2"><i class="fas fa-chart-bar mr-2"></i>Grafik Pendapatan vs Beban 6 Bulan Terakhir</h2>
            <div class="relative h-72 w-full mt-2"><canvas id="grafikKinerja" data-grafik='<?= $data_grafik_json ?>'></canvas></div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-5">
            <h3 class="font-bold border-b pb-2 text-lg text-primary"><i class="fas fa-bell mr-2"></i>Pengingat Laporan</h3>
            <div class="flex flex-col gap-3 text-sm mt-4">
                <div class="flex justify-between items-center bg-base-200 p-2 rounded">
                    <span class="font-semibold">Laporan Triwulan <?= $current_quarter ?></span> 
                    <span class="badge <?= (int)$days_to_quarterly < 0 ? 'badge-error' : 'badge-info' ?> font-bold"><?= (int)$days_to_quarterly < 0 ? 'Terlambat ' . abs((int)$days_to_quarterly) : $days_to_quarterly ?> hari</span>
                </div>
                <div class="flex justify-between items-center bg-base-200 p-2 rounded">
                    <span class="font-semibold">Laporan Tahunan <?= $now->format('Y') ?></span> 
                    <span class="badge <?= (int)$days_to_annual < 0 ? 'badge-error' : 'badge-warning' ?> font-bold"><?= (int)$days_to_annual < 0 ? 'Terlambat ' . abs((int)$days_to_annual) : $days_to_annual ?> hari</span>
                </div>
            </div>
            
            <h3 class="font-bold border-b pb-2 mt-6 text-lg text-secondary"><i class="fas fa-book mr-2"></i>Buku Administrasi</h3>
            <div class="flex flex-col gap-3 text-sm mt-4">
                <div class="flex justify-between border-b border-base-200 pb-1"><span>Surat Masuk (7 hr terakhir)</span> <strong><?= $surat_masuk_baru ?></strong></div>
                <div class="flex justify-between border-b border-base-200 pb-1"><span>Saran Masuk (7 hr terakhir)</span> <strong><?= $saran_anggota_baru ?></strong></div>
                <div class="flex justify-between"><span>Tamu Berkunjung Hari Ini</span> <strong><?= $tamu_hari_ini ?></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-5">
            <h2 class="card-title text-lg border-b pb-2"><i class="fas fa-history mr-2"></i>Aktivitas Terakhir</h2>
            <ul class="space-y-3 mt-3 text-sm">
                <?php if(empty($aktivitas_terakhir)): ?>
                    <li class="text-center opacity-60 italic py-4">Belum ada transaksi.</li>
                <?php else: foreach ($aktivitas_terakhir as $aktivitas): ?>
                <li class="flex items-start">
                    <div class="flex-shrink-0 mr-3 mt-1"><div class="w-3 h-3 rounded-full bg-primary shadow"></div></div>
                    <div class="w-full">
                        <div class="flex justify-between items-center">
                            <strong class="text-primary"><?= htmlspecialchars($aktivitas['jenis']) ?></strong>
                            <span class="font-mono font-bold text-xs">Rp <?= number_format($aktivitas['jumlah'], 0, ',', '.') ?></span>
                        </div>
                        <div class="text-xs opacity-70 mt-1 truncate w-48" title="<?= htmlspecialchars($aktivitas['keterangan'] ?? 'Umum') ?>"><?= htmlspecialchars($aktivitas['keterangan'] ?? 'Umum') ?></div>
                        <div class="text-[10px] opacity-50 mt-0.5"><?= date('d/m/Y H:i', strtotime($aktivitas['tanggal'])) ?></div>
                    </div>
                </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-5">
            <h2 class="card-title text-lg border-b pb-2"><i class="fas fa-fire mr-2 text-error"></i>Barang Terlaris Bulan Ini</h2>
            <div class="overflow-x-auto mt-2">
                <table class="table table-compact w-full text-sm">
                    <tbody>
                        <?php if(empty($produk_terlaris)): ?>
                            <tr><td class="text-center opacity-60 italic py-4">Belum ada penjualan.</td></tr>
                        <?php else: foreach($produk_terlaris as $p): ?>
                            <tr class="border-b border-base-200 hover">
                                <td class="font-semibold truncate max-w-[150px]"><?= htmlspecialchars($p['nama_produk']) ?></td>
                                <td class="text-right font-mono font-bold text-success"><?= number_format($p['total_terjual']) ?> Unit</td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-5">
            <h2 class="card-title text-lg border-b pb-2"><i class="fas fa-crown mr-2 text-warning"></i>Pelanggan Teraktif (Bulan Ini)</h2>
            <div class="overflow-x-auto mt-2">
                <table class="table table-compact w-full text-sm">
                    <tbody>
                        <?php if(empty($anggota_teraktif)): ?>
                            <tr><td class="text-center opacity-60 italic py-4">Belum ada penjualan ke pelanggan.</td></tr>
                        <?php else: foreach($anggota_teraktif as $a): ?>
                            <tr class="border-b border-base-200 hover">
                                <td class="font-semibold truncate max-w-[150px]"><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                                <td class="text-right font-mono font-bold text-info">Rp <?= number_format($a['total_belanja'], 0, '', '.') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>