<?php
$judulHalaman = 'Laporan Jurnal Umum';
cekAkses('lihat_laporan');

$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');
$kode_akun_filter = $_GET['kode_akun'] ?? '';

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(ju.keterangan LIKE ? OR ju.nomor_bukti LIKE ?)";
    array_push($params, $searchTerm, $searchTerm);
}
if (!empty($tgl_mulai)) {
    $whereClauses[] = "ju.tanggal >= ?";
    $params[] = $tgl_mulai;
}
if (!empty($tgl_selesai)) {
    $whereClauses[] = "ju.tanggal <= ?";
    $params[] = $tgl_selesai;
}
if (!empty($kode_akun_filter)) {
    // Memastikan seluruh grup transaksi tampil jika memuat akun yang dicari
    $whereClauses[] = "ju.nomor_bukti IN (SELECT nomor_bukti FROM jurnal_umum WHERE kode_akun = ?)";
    $params[] = $kode_akun_filter;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Ekspor CSV tetap menggunakan format flat/rinci
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Jurnal_Umum_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tanggal', 'No. Bukti', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Debit', 'Kredit']);
    
    $sql_export = "SELECT ju.id, ju.tanggal, ju.nomor_bukti, ju.kode_akun, da.nama_akun, ju.keterangan, ju.debit, ju.kredit 
                   FROM jurnal_umum ju 
                   JOIN daftar_akun da ON ju.kode_akun = da.kode_akun 
                   $whereSql 
                   ORDER BY ju.tanggal ASC, ju.id ASC";
    $semua_jurnal = query($sql_export, $params);
    foreach ($semua_jurnal as $j) {
        fputcsv($output, [$j['tanggal'], $j['nomor_bukti'], $j['kode_akun'], $j['nama_akun'], $j['keterangan'], $j['debit'], $j['kredit']]);
    }
    fclose($output);
    exit;
}

// Kueri untuk mendapatkan Header Transaksi (Dikelompokkan)
$sql_select_from = "
    SELECT 
        ju.tanggal, 
        ju.nomor_bukti, 
        MAX(ju.keterangan) as keterangan, 
        SUM(ju.debit) as total_debit 
    FROM jurnal_umum ju
";

$sql_where = $whereSql . " GROUP BY ju.nomor_bukti, ju.tanggal ORDER BY ju.tanggal DESC, MAX(ju.id) DESC";

$paginasi = query_paginasi($sql_select_from, $sql_where, $params, 20);
$jurnal_grouped = $paginasi['data'];

// Kueri untuk mendapatkan Detail setiap transaksi di halaman ini
$detail_jurnal = [];
if (!empty($jurnal_grouped)) {
    $nomor_buktis = array_column($jurnal_grouped, 'nomor_bukti');
    $placeholders = implode(',', array_fill(0, count($nomor_buktis), '?'));
    
    // Ambil rincian
    $details = query("
        SELECT ju.nomor_bukti, ju.kode_akun, da.nama_akun, ju.debit, ju.kredit 
        FROM jurnal_umum ju 
        JOIN daftar_akun da ON ju.kode_akun = da.kode_akun 
        WHERE ju.nomor_bukti IN ($placeholders)
        ORDER BY ju.id ASC
    ", $nomor_buktis);
    
    foreach ($details as $d) {
        $detail_jurnal[$d['nomor_bukti']][] = $d;
    }
}

$daftar_akun = query("SELECT kode_akun, nama_akun FROM daftar_akun ORDER BY kode_akun ASC");
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6">Jurnal Umum</h1>

<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="jurnal/lihat_jurnal">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" placeholder="Cari keterangan/no bukti..." class="input input-bordered" value="<?= htmlspecialchars($search) ?>">
                <select name="kode_akun" class="select-akun">
                    <option value="">-- Semua Akun --</option>
                    <?php foreach ($daftar_akun as $akun): ?>
                        <option value="<?= $akun['kode_akun'] ?>" <?= ($kode_akun_filter == $akun['kode_akun']) ? 'selected' : '' ?>><?= $akun['kode_akun'] ?> - <?= htmlspecialchars($akun['nama_akun']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="tgl_mulai" class="input input-bordered" value="<?= htmlspecialchars($tgl_mulai) ?>">
                <input type="date" name="tgl_selesai" class="input input-bordered" value="<?= htmlspecialchars($tgl_selesai) ?>">
                
                <div class="md:col-span-4 flex justify-between gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow">Filter / Cari</button>
                    <button type="submit" name="export" value="csv" class="btn btn-success text-white">
                        <i class="fas fa-file-excel mr-2"></i> Ekspor CSV
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="overflow-x-auto pb-8">
    <table class="table w-full border border-base-300">
        <thead>
            <tr class="bg-base-200">
                <th>Tanggal</th>
                <th>No. Bukti</th>
                <th>Keterangan (Transaksi)</th>
                <th class="text-right">Total Transaksi</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php if (empty($jurnal_grouped)): ?>
                <tr><td colspan="5" class="text-center py-8 text-base-content/60">Tidak ada transaksi yang ditemukan.</td></tr>
            <?php else: foreach ($jurnal_grouped as $item) : 
                $no_bukti = $item['nomor_bukti'];
                $safe_id = preg_replace('/[^a-zA-Z0-9]/', '_', $no_bukti);
                $details = $detail_jurnal[$no_bukti] ?? [];
            ?>
                <tr class="hover cursor-pointer border-b border-base-300 transition-colors" onclick="document.getElementById('detail_<?= $safe_id ?>').classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180')">
                    <td class="whitespace-nowrap font-medium"><?= htmlspecialchars(date('d-m-Y', strtotime($item['tanggal']))) ?></td>
                    <td class="font-mono text-primary font-bold whitespace-nowrap"><?= htmlspecialchars($item['nomor_bukti']) ?></td>
                    <td class="max-w-xs xl:max-w-xl"><?= htmlspecialchars($item['keterangan']) ?></td>
                    <td class="text-right font-bold whitespace-nowrap">Rp <?= number_format($item['total_debit'], 2, ',', '.') ?></td>
                    <td class="text-center text-base-content/50 w-10"><i class="fas fa-chevron-down transition-transform duration-300"></i></td>
                </tr>
                <tr id="detail_<?= $safe_id ?>" class="hidden">
                    <td colspan="5" class="p-0 border-b border-base-300">
                        <div class="p-4 bg-base-200/40 border-l-4 border-primary">
                            <table class="table table-sm w-full bg-base-100 shadow-sm rounded-lg border border-base-200">
                                <thead>
                                    <tr class="bg-base-200 text-base-content/70 uppercase text-xs">
                                        <th class="w-1/4">Kode Akun</th>
                                        <th class="w-1/3">Nama Akun</th>
                                        <th class="text-right w-1/5">Debit</th>
                                        <th class="text-right w-1/5">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $d): ?>
                                    <tr class="border-b border-base-100 last:border-0">
                                        <td class="font-mono font-bold opacity-80"><?= htmlspecialchars($d['kode_akun']) ?></td>
                                        <td class="font-medium opacity-90"><?= htmlspecialchars($d['nama_akun']) ?></td>
                                        <td class="text-right text-info font-medium"><?= $d['debit'] > 0 ? number_format($d['debit'], 2, ',', '.') : '-' ?></td>
                                        <td class="text-right text-error font-medium"><?= $d['kredit'] > 0 ? number_format($d['kredit'], 2, ',', '.') : '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="flex justify-center mt-4">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>