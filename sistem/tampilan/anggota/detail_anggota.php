<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota');
    exit;
}
$id = (int)$_GET['id'];
$anggota_data = query("SELECT * FROM anggota WHERE id=?", [$id], 'i');
if (empty($anggota_data)) { header('Location: ' . BASE_URL . 'index.php?halaman=404'); exit; }
$anggota = $anggota_data[0];
$simpanan_pokok_wajib = query("SELECT * FROM simpanan WHERE anggota_id=? ORDER BY tanggal_setor DESC", [$id], 'i');
$simpanan_sukarela = query("SELECT * FROM simpanan_sukarela_transaksi WHERE anggota_id=? ORDER BY tanggal_transaksi DESC", [$id], 'i');
$pinjaman = query("SELECT * FROM pinjaman_anggota WHERE anggota_id=? ORDER BY tanggal_pinjaman DESC", [$id], 'i');
$penjualan = query("SELECT * FROM penjualan WHERE anggota_id=? ORDER BY tanggal_penjualan DESC", [$id], 'i');
$total_pokok_q = query("SELECT SUM(jumlah) as total FROM simpanan WHERE anggota_id=? AND jenis_simpanan='Pokok'", [$id], 'i');
$total_wajib_q = query("SELECT SUM(jumlah) as total FROM simpanan WHERE anggota_id=? AND jenis_simpanan='Wajib'", [$id], 'i');
$saldo_sukarela_q = query("SELECT (SUM(CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = 'Tarik' THEN jumlah ELSE 0 END)) as saldo FROM simpanan_sukarela_transaksi WHERE anggota_id = ?", [$id], 'i');
$total_pokok = $total_pokok_q[0]['total'] ?? 0;
$total_wajib = $total_wajib_q[0]['total'] ?? 0;
$saldo_sukarela = $saldo_sukarela_q[0]['saldo'] ?? 0;
$judulHalaman = 'Buku Induk: ' . $anggota['nama_lengkap'];

$lbl_pokok = MODE_ENTITAS === 'BUMDes' ? 'Penyertaan Desa (Pokok)' : 'Simpanan Pokok';
$lbl_wajib = MODE_ENTITAS === 'BUMDes' ? 'Modal Masyarakat (Wajib)' : 'Simpanan Wajib';
?>
<?php  ?>
<style> @media print { .no-print { display: none !important; } body, .printable-area { margin: 0; padding: 0; background: white; color: black; } } </style>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6 no-print">
    <h1 class="text-3xl font-bold">Detail Buku Induk Pihak Terkait</h1>
    <div>
        <a href="index.php?halaman=anggota/lihat_anggota" class="btn btn-ghost">← Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak Laporan Lengkap</button>
    </div>
</div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Buku Induk Riwayat Transaksi</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-base-200 p-4 rounded-xl border border-base-300">
            <div>
                <div class="text-sm opacity-70">Nama / Institusi:</div>
                <div class="font-bold text-lg"><?= htmlspecialchars($anggota['nama_lengkap']) ?>
                    <?php if ($anggota['jenis_entitas'] == 'Institusi'): ?>
                        <span class="badge badge-info badge-sm ml-2">Institusi / Desa</span>
                    <?php endif; ?>
                </div>
            </div>
            <div><div class="text-sm opacity-70">Alamat:</div><div class="font-semibold"><?= htmlspecialchars($anggota['alamat']) ?></div></div>
            <div><div class="text-sm opacity-70">Telepon:</div><div class="font-semibold"><?= htmlspecialchars($anggota['telepon']) ?></div></div>
            <div><div class="text-sm opacity-70">Terdaftar Sejak:</div><div class="font-semibold"><?= date('d M Y', strtotime($anggota['tanggal_bergabung'])) ?></div></div>
        </div>
        
        <div class="divider"></div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center stats shadow border border-base-200">
            <div class="stat"><div class="stat-title"><?= $lbl_pokok ?></div><div class="stat-value text-2xl text-primary">Rp <?= number_format($total_pokok, 0, ',', '.') ?></div></div>
            <div class="stat"><div class="stat-title"><?= $lbl_wajib ?></div><div class="stat-value text-2xl text-secondary">Rp <?= number_format($total_wajib, 0, ',', '.') ?></div></div>
            <div class="stat"><div class="stat-title">Simpanan Sukarela</div><div class="stat-value text-2xl text-accent">Rp <?= number_format($saldo_sukarela, 0, ',', '.') ?></div></div>
        </div>

        <!-- Tabel Riwayat (Sama seperti sebelumnya) -->
        <div class="mt-8">
            <h4 class="text-xl font-semibold mb-2 border-b pb-2">Riwayat Modal & Simpanan Permanen</h4>
            <div class="overflow-x-auto"><table class="table table-compact w-full border"><thead><tr class="bg-base-200"><th>Tanggal</th><th>Jenis Klasifikasi</th><th class="text-right">Jumlah</th></tr></thead><tbody>
            <?php if(empty($simpanan_pokok_wajib)): ?> <tr><td colspan="3" class="text-center py-4">Belum ada penyertaan modal yang tercatat.</td></tr> <?php else: foreach($simpanan_pokok_wajib as $s): ?><tr><td><?= date('d M Y', strtotime($s['tanggal_setor'])) ?></td><td><span class="badge badge-ghost"><?= $s['jenis_simpanan'] ?></span></td><td class="text-right font-mono">Rp <?= number_format($s['jumlah'], 0, ',', '.') ?></td></tr><?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
        <div class="mt-8">
            <h4 class="text-xl font-semibold mb-2 border-b pb-2">Riwayat Simpanan Sukarela / Tabungan Lancar</h4>
            <div class="overflow-x-auto"><table class="table table-compact w-full border"><thead><tr class="bg-base-200"><th>Tanggal</th><th>Jenis Mutasi</th><th class="text-right">Jumlah</th></tr></thead><tbody>
            <?php if(empty($simpanan_sukarela)): ?> <tr><td colspan="3" class="text-center py-4">Tidak ada data tabungan berjalan.</td></tr> <?php else: foreach($simpanan_sukarela as $s): ?><tr><td><?= date('d M Y', strtotime($s['tanggal_transaksi'])) ?></td><td><span class="badge <?= $s['jenis_transaksi'] == 'Setor' ? 'badge-success' : 'badge-error' ?> badge-sm"><?= $s['jenis_transaksi'] ?></span></td><td class="text-right font-mono">Rp <?= number_format($s['jumlah'], 0, ',', '.') ?></td></tr><?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
        <div class="mt-8 page-break-before">
            <h4 class="text-xl font-semibold mb-2 border-b pb-2">Riwayat Pinjaman / Piutang</h4>
            <div class="overflow-x-auto"><table class="table table-compact w-full border"><thead><tr class="bg-base-200"><th>Tanggal Cair</th><th class="text-right">Plafon Pinjaman</th><th>Jangka Waktu</th><th>Status</th><th class="no-print text-center">Aksi</th></tr></thead><tbody>
            <?php if(empty($pinjaman)): ?> <tr><td colspan="5" class="text-center py-4">Tidak memiliki riwayat pinjaman.</td></tr> <?php else: foreach($pinjaman as $p): 
                $total_dibayar_q = query("SELECT SUM(pokok_dibayar) as total FROM pembayaran_angsuran WHERE pinjaman_anggota_id=?", [$p['id']], 'i');
                $total_dibayar = $total_dibayar_q[0]['total'] ?? 0;
                $sisa_pokok = $p['jumlah_pokok'] - $total_dibayar;
            ?>
            <tr><td><?= date('d M Y', strtotime($p['tanggal_pinjaman'])) ?></td><td class="text-right font-mono">Rp <?= number_format($p['jumlah_pokok'], 0, ',', '.') ?></td><td><?= $p['jangka_waktu_bulan'] ?> Bulan</td><td><?= ($sisa_pokok <= 0) ? '<span class="badge badge-success badge-sm">Lunas</span>' : '<span class="badge badge-warning badge-sm">Aktif</span>' ?></td><td class="no-print text-center"><a href="index.php?halaman=pinjaman_anggota/detail_pinjaman&id=<?= $p['id'] ?>" class="btn btn-xs btn-info">Buku Pembantu</a></td></tr>
            <?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
        <div class="mt-8">
            <h4 class="text-xl font-semibold mb-2 border-b pb-2">Riwayat Transaksi Belanja (Unit Usaha)</h4>
            <div class="overflow-x-auto"><table class="table table-compact w-full border"><thead><tr class="bg-base-200"><th>Tanggal</th><th>No. Faktur</th><th class="text-right">Total Belanja (Rp)</th><th class="no-print text-center">Aksi</th></tr></thead><tbody>
             <?php if(empty($penjualan)): ?> <tr><td colspan="4" class="text-center py-4">Belum ada riwayat belanja tercatat.</td></tr> <?php else: foreach($penjualan as $p): ?><tr><td><?= date('d M Y', strtotime($p['tanggal_penjualan'])) ?></td><td class="font-mono"><?= $p['nomor_faktur'] ?></td><td class="text-right font-mono">Rp <?= number_format($p['total'] - $p['diskon'], 0, ',', '.') ?></td><td class="no-print text-center"><a href="index.php?halaman=penjualan/detail_penjualan&id=<?= $p['id'] ?>" class="btn btn-xs btn-info">Detail Struk</a></td></tr><?php endforeach; endif; ?>
            </tbody></table></div>
        </div>
    </div>
</div>