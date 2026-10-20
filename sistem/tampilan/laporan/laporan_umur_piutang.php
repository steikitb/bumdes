<?php
$judulHalaman = 'Laporan Analisis Umur Piutang';
cekAkses('lihat_laporan');

$tanggal_laporan = $_REQUEST['tanggal_laporan'] ?? date('Y-m-d');

$bucket1_hari = (int)($_PENGATURAN['piutang_aging_bucket_1_hari'] ?? 30);
$bucket2_hari = (int)($_PENGATURAN['piutang_aging_bucket_2_hari'] ?? 90);
$bucket3_hari = (int)($_PENGATURAN['piutang_aging_bucket_3_hari'] ?? 180);

$sql = "
    SELECT 
        a.nama_lengkap,
        SUM(p.jumlah_pokok - COALESCE(bayar.total_bayar, 0)) AS total_sisa,
        SUM(CASE WHEN CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) <= ? THEN p.jumlah_pokok - COALESCE(bayar.total_bayar, 0) ELSE 0 END) AS lancar,
        SUM(CASE WHEN CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) > ? AND CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) <= ? THEN p.jumlah_pokok - COALESCE(bayar.total_bayar, 0) ELSE 0 END) AS bucket2,
        SUM(CASE WHEN CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) > ? AND CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) <= ? THEN p.jumlah_pokok - COALESCE(bayar.total_bayar, 0) ELSE 0 END) AS bucket3,
        SUM(CASE WHEN CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) > ? THEN p.jumlah_pokok - COALESCE(bayar.total_bayar, 0) ELSE 0 END) AS bucket4
    FROM anggota a
    JOIN pinjaman_anggota p ON a.id = p.anggota_id
    LEFT JOIN (
        SELECT pinjaman_anggota_id, SUM(pokok_dibayar) as total_bayar 
        FROM pembayaran_angsuran 
        WHERE tanggal_bayar <= ?
        GROUP BY pinjaman_anggota_id
    ) bayar ON p.id = bayar.pinjaman_anggota_id
    GROUP BY a.id, a.nama_lengkap
    HAVING total_sisa > 0
    ORDER BY a.nama_lengkap ASC
";

$params = [
    $tanggal_laporan, $bucket1_hari,
    $tanggal_laporan, $bucket1_hari, $tanggal_laporan, $bucket2_hari,
    $tanggal_laporan, $bucket2_hari, $tanggal_laporan, $bucket3_hari,
    $tanggal_laporan, $bucket3_hari,
    $tanggal_laporan
];

$laporan = query($sql, $params);

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Umur_Piutang_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['LAPORAN ANALISIS UMUR PIUTANG']);
    fputcsv($output, ['Per Tanggal', $tanggal_laporan]);
    fputcsv($output, []);
    
    fputcsv($output, ['Nama Anggota', 'Total Piutang', 'Lancar (0-'.$bucket1_hari.')', ($bucket1_hari+1).'-'.$bucket2_hari, ($bucket2_hari+1).'-'.$bucket3_hari, '> '.$bucket3_hari]);
    
    if (!empty($laporan)) {
        $total_semua = 0; $total_lancar = 0; $total_b2 = 0; $total_b3 = 0; $total_b4 = 0;
        foreach ($laporan as $item) {
            $total_semua += $item['total_sisa']; $total_lancar += $item['lancar'];
            $total_b2 += $item['bucket2']; $total_b3 += $item['bucket3']; $total_b4 += $item['bucket4'];
            fputcsv($output, [$item['nama_lengkap'], $item['total_sisa'], $item['lancar'], $item['bucket2'], $item['bucket3'], $item['bucket4']]);
        }
        fputcsv($output, ['TOTAL', $total_semua, $total_lancar, $total_b2, $total_b3, $total_b4]);
    }
    
    fclose($output);
    exit;
}
?>
<?php  ?>
<style> @media print { .no-print { display: none !important; } .printable-area { box-shadow: none; border: none; } .hidden-print-header { display: block !important; } } .hidden-print-header { display: none; } </style>
<h1 class="text-3xl font-bold mb-6 no-print">Laporan Analisis Umur Piutang</h1>
<p class="mb-4 text-base-content/80 no-print">Laporan ini mengelompokkan sisa piutang anggota berdasarkan umur pinjaman untuk analisis risiko kredit.</p>
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
<div class="mb-6 flex gap-2 no-print">
    <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
    <form method="GET" action="" style="display: inline;">
        <input type="hidden" name="halaman" value="<?= $_GET['halaman'] ?? 'laporan/laporan_umur_piutang' ?>">
        <input type="hidden" name="tanggal_laporan" value="<?= htmlspecialchars($tanggal_laporan) ?>">
        <input type="hidden" name="export" value="csv">
        <button type="submit" class="btn btn-success text-white"><i class="fas fa-file-excel mr-2"></i> Ekspor CSV</button>
    </form>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4 hidden-print-header"><h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2><h3 class="text-xl">Laporan Umur Piutang</h3><p>Per Tanggal: <?= htmlspecialchars(date('d F Y', strtotime($tanggal_laporan))) ?></p></div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full">
                <thead>
                    <tr>
                        <th rowspan="2">Nama Anggota</th>
                        <th rowspan="2" class="text-right">Total Piutang</th>
                        <th colspan="4" class="text-center">Umur Piutang (Hari)</th>
                    </tr>
                    <tr>
                        <th class="text-right">Lancar (0-<?= $bucket1_hari ?>)</th>
                        <th class="text-right"><?= $bucket1_hari + 1 ?>-<?= $bucket2_hari ?></th>
                        <th class="text-right"><?= $bucket2_hari + 1 ?>-<?= $bucket3_hari ?></th>
                        <th class="text-right">> <?= $bucket3_hari ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)): ?>
                        <tr><td colspan="6" class="text-center">Tidak ada data piutang aktif.</td></tr>
                    <?php else: 
                        // Inisialisasi variabel total sebelum loop dimulai
                        $total_semua = 0; $total_lancar = 0; $total_b2 = 0; $total_b3 = 0; $total_b4 = 0;
                        foreach ($laporan as $item): 
                            $total_semua += $item['total_sisa']; $total_lancar += $item['lancar'];
                            $total_b2 += $item['bucket2']; $total_b3 += $item['bucket3']; $total_b4 += $item['bucket4'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_lengkap']) ?></td>
                            <td class="text-right font-bold"><?= number_format($item['total_sisa'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($item['lancar'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($item['bucket2'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($item['bucket3'], 0, ',', '.') ?></td>
                            <td class="text-right"><?= number_format($item['bucket4'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold text-lg bg-base-200">
                        <td>TOTAL</td>
                        <td class="text-right">Rp <?= number_format($total_semua, 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($total_lancar, 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($total_b2, 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($total_b3, 0, ',', '.') ?></td>
                        <td class="text-right">Rp <?= number_format($total_b4, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?php  ?>