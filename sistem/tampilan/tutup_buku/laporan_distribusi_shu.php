<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Simulasi Distribusi Laba / Bagi Hasil' : 'Simulasi & Laporan Rinci Distribusi SHU';
$judulHalaman = $lbl_judul;
cekAkses(['lihat_laporan', 'proses_tutup_buku']);

$tanggal_mulai_filter = $_POST['tanggal_mulai'] ?? date('Y-01-01');
$tanggal_selesai_filter = $_POST['tanggal_selesai'] ?? date('Y-m-d');
$persen_cadangan_filter = (float)($_POST['persen_cadangan'] ?? 25);
$persen_jasa_anggota_filter = (float)($_POST['persen_jasa_anggota'] ?? 40);
$persen_dana_pengurus_filter = (float)($_POST['persen_dana_pengurus'] ?? 10);
$persen_dana_sosial_filter = (float)($_POST['persen_dana_sosial'] ?? 25);

$ringkasan_alokasi = [];
$distribusi_anggota = [];
$nilai_per_poin_modal = 0;
$nilai_per_poin_usaha = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shu_periode = hitungSHU($tanggal_mulai_filter, $tanggal_selesai_filter);

    if ($shu_periode > 0) {
        $ringkasan_alokasi = [
            'total_shu' => $shu_periode,
            'alokasi_cadangan' => $shu_periode * ($persen_cadangan_filter / 100),
            'alokasi_jasa_anggota' => $shu_periode * ($persen_jasa_anggota_filter / 100),
            'alokasi_dana_pengurus' => $shu_periode * ($persen_dana_pengurus_filter / 100),
            'alokasi_dana_sosial' => $shu_periode * ($persen_dana_sosial_filter / 100),
        ];

        $persen_jasa_modal = (float)($_PENGATURAN['shu_persen_jasa_modal'] ?? 30);
        $persen_jasa_usaha = (float)($_PENGATURAN['shu_persen_jasa_usaha'] ?? 70);
        $poin_per_rupiah_modal = (int)($_PENGATURAN['shu_poin_per_rupiah_modal'] ?? 1000);
        $min_keanggotaan_bulan = (int)($_PENGATURAN['shu_min_keanggotaan_bulan'] ?? 3);
        $tgl_batas_keanggotaan = date('Y-m-d', strtotime("$tanggal_selesai_filter -$min_keanggotaan_bulan months"));

        $dana_jasa_modal = $ringkasan_alokasi['alokasi_jasa_anggota'] * ($persen_jasa_modal / 100);
        $dana_jasa_usaha = $ringkasan_alokasi['alokasi_jasa_anggota'] * ($persen_jasa_usaha / 100);

        $total_poin_usaha_q = query("SELECT SUM(jumlah_poin) as grand_total FROM histori_poin_ppa hppa JOIN anggota a ON hppa.anggota_id = a.id WHERE jenis_poin = 'usaha' AND hppa.tanggal_perolehan BETWEEN ? AND ? AND a.tanggal_bergabung <= ?", [$tanggal_mulai_filter, $tanggal_selesai_filter, $tgl_batas_keanggotaan], 'sss');
        $grand_total_poin_usaha = $total_poin_usaha_q[0]['grand_total'] ?? 0;

        $simpanan_anggota_q = query("SELECT anggota_id, SUM(jumlah) as total_simpanan FROM simpanan s JOIN anggota a ON s.anggota_id = a.id WHERE s.jenis_simpanan IN ('Pokok', 'Wajib') AND s.tanggal_setor <= ? AND a.tanggal_bergabung <= ? GROUP BY s.anggota_id", [$tanggal_selesai_filter, $tgl_batas_keanggotaan], 'ss');
        $grand_total_poin_modal = 0;
        foreach($simpanan_anggota_q as $simpanan) {
            $grand_total_poin_modal += ($poin_per_rupiah_modal > 0) ? floor($simpanan['total_simpanan'] / $poin_per_rupiah_modal) : 0;
        }
        $nilai_per_poin_modal = ($grand_total_poin_modal > 0) ? $dana_jasa_modal / $grand_total_poin_modal : 0;
        $nilai_per_poin_usaha = ($grand_total_poin_usaha > 0) ? $dana_jasa_usaha / $grand_total_poin_usaha : 0;
        
        $anggota_valid_q = query("SELECT id, nama_lengkap FROM anggota WHERE tanggal_bergabung <= ?", [$tgl_batas_keanggotaan], 's');
        
        foreach($anggota_valid_q as $anggota) {
            $anggota_id = $anggota['id'];

            $poin_modal_q = query("SELECT SUM(jumlah) as total_simpanan FROM simpanan WHERE anggota_id = ? AND jenis_simpanan IN ('Pokok', 'Wajib') AND tanggal_setor <= ?", [$anggota_id, $tanggal_selesai_filter], 'is');
            $poin_modal = ($poin_per_rupiah_modal > 0) ? floor(($poin_modal_q[0]['total_simpanan'] ?? 0) / $poin_per_rupiah_modal) : 0;

            $poin_usaha_q = query("SELECT SUM(jumlah_poin) as total_poin FROM histori_poin_ppa WHERE anggota_id = ? AND jenis_poin = 'usaha' AND tanggal_perolehan BETWEEN ? AND ?", [$anggota_id, $tanggal_mulai_filter, $tanggal_selesai_filter], 'iss');
            $poin_usaha = $poin_usaha_q[0]['total_poin'] ?? 0;
            
            $shu_modal = $poin_modal * $nilai_per_poin_modal;
            $shu_usaha = $poin_usaha * $nilai_per_poin_usaha;
            
            if(($shu_modal + $shu_usaha) > 0) {
                $distribusi_anggota[] = [
                    'nama_lengkap' => $anggota['nama_lengkap'],
                    'total_poin_modal' => $poin_modal,
                    'total_poin_usaha' => $poin_usaha,
                    'jasa_modal_diterima' => $shu_modal,
                    'jasa_usaha_diterima' => $shu_usaha,
                    'total_diterima' => $shu_modal + $shu_usaha,
                ];
            }
        }
        
        usort($distribusi_anggota, fn($a, $b) => $b['total_diterima'] <=> $a['total_diterima']);
    }
}
$lbl_jasa = MODE_ENTITAS === 'BUMDes' ? 'Bagi Hasil' : 'Jasa Anggota';
?>
<style> @media print { .no-print { display: none !important; } .printable-area { box-shadow: none !important; border: none !important; } } </style>

<div class="flex justify-between items-center mb-6 no-print">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <button onclick="window.print()" class="btn btn-secondary">Cetak Simulasi</button>
</div>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Parameter Simulasi</h2>
        <form id="form-simulasi" method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Mulai</span></label><input type="date" name="tanggal_mulai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_mulai_filter) ?>" required></div>
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Selesai</span></label><input type="date" name="tanggal_selesai" class="input input-bordered" value="<?= htmlspecialchars($tanggal_selesai_filter) ?>" required></div>
            </div>
            <div class="divider">Persentase Alokasi Laba/SHU</div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                 <div class="form-control"><label class="label"><span class="label-text">Cadangan (%)</span></label><input type="number" step="0.1" name="persen_cadangan" class="input input-bordered alokasi-persen" value="<?= htmlspecialchars($persen_cadangan_filter) ?>" required></div>
                 <div class="form-control"><label class="label"><span class="label-text"><?= $lbl_jasa ?> (%)</span></label><input type="number" step="0.1" name="persen_jasa_anggota" class="input input-bordered alokasi-persen" value="<?= htmlspecialchars($persen_jasa_anggota_filter) ?>" required></div>
                 <div class="form-control"><label class="label"><span class="label-text">Dana Pengurus (%)</span></label><input type="number" step="0.1" name="persen_dana_pengurus" class="input input-bordered alokasi-persen" value="<?= htmlspecialchars($persen_dana_pengurus_filter) ?>" required></div>
                 <div class="form-control"><label class="label"><span class="label-text">Dana Sosial (%)</span></label><input type="number" step="0.1" name="persen_dana_sosial" class="input input-bordered alokasi-persen" value="<?= htmlspecialchars($persen_dana_sosial_filter) ?>" required></div>
            </div>
            <p id="total-persen-warning" class="text-error text-sm mt-2 hidden">Total persentase harus 100%.</p>
            <div class="form-control mt-4"><button type="submit" class="btn btn-primary w-full">Jalankan Simulasi</button></div>
        </form>
    </div>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
<div class="printable-area">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="text-center mb-6 border-b pb-4">
                <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
                <h3 class="text-xl">Simulasi Distribusi Laba Bersih / SHU</h3>
                <p>Periode: <?= htmlspecialchars(date('d M Y', strtotime($tanggal_mulai_filter))) ?> s/d <?= htmlspecialchars(date('d M Y', strtotime($tanggal_selesai_filter))) ?></p>
            </div>

            <?php if (!empty($ringkasan_alokasi)): ?>
                <h4 class="text-lg font-bold mb-2">1. Ringkasan Alokasi Berdasarkan Persentase</h4>
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full border border-base-300">
                        <tbody>
                            <tr class="bg-base-200"><td>Total Perkiraan Laba Bersih Periode Ini</td><td class="text-right font-bold text-xl text-success">Rp <?= number_format($ringkasan_alokasi['total_shu'], 2, ',', '.') ?></td></tr>
                            <tr><td class="pl-8">Alokasi untuk Cadangan Entitas (<?= $persen_cadangan_filter ?>%)</td><td class="text-right">Rp <?= number_format($ringkasan_alokasi['alokasi_cadangan'], 2, ',', '.') ?></td></tr>
                            <tr><td class="pl-8">Alokasi untuk Hak <?= $lbl_jasa ?> (<?= $persen_jasa_anggota_filter ?>%)</td><td class="text-right font-bold">Rp <?= number_format($ringkasan_alokasi['alokasi_jasa_anggota'], 2, ',', '.') ?></td></tr>
                            <tr><td class="pl-8">Alokasi untuk Dana Pengurus (<?= $persen_dana_pengurus_filter ?>%)</td><td class="text-right">Rp <?= number_format($ringkasan_alokasi['alokasi_dana_pengurus'], 2, ',', '.') ?></td></tr>
                            <tr><td class="pl-8">Alokasi untuk Dana Sosial (<?= $persen_dana_sosial_filter ?>%)</td><td class="text-right">Rp <?= number_format($ringkasan_alokasi['alokasi_dana_sosial'], 2, ',', '.') ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="divider my-6"></div>
                <h4 class="text-lg font-bold mb-2">2. Kalkulasi Nilai Poin Konversi</h4>
                <div class="stats shadow w-full">
                  <div class="stat"><div class="stat-title">Total Dana Dibagikan</div><div class="stat-value">Rp <?= number_format($ringkasan_alokasi['alokasi_jasa_anggota'], 0, ',', '.') ?></div></div>
                  <div class="stat"><div class="stat-title">Nilai per Poin Modal</div><div class="stat-value text-primary">Rp <?= number_format($nilai_per_poin_modal, 4, ',', '.') ?></div></div>
                  <div class="stat"><div class="stat-title">Nilai per Poin Usaha</div><div class="stat-value text-secondary">Rp <?= number_format($nilai_per_poin_usaha, 4, ',', '.') ?></div></div>
                </div>
                
                <div class="divider my-6"></div>
                <h4 class="text-lg font-bold mb-2">3. Detail Perkiraan Penerimaan per Pihak</h4>
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full border border-base-300">
                        <thead><tr class="bg-base-200"><th>No</th><th>Nama Pihak/Penerima</th><th class="text-right">Poin Modal</th><th class="text-right">Poin Usaha</th><th class="text-right">Bagian Modal (Rp)</th><th class="text-right">Bagian Usaha (Rp)</th><th class="text-right text-primary">Total Diterima (Rp)</th></tr></thead>
                        <tbody>
                            <?php if (empty($distribusi_anggota)): ?>
                                <tr><td colspan="7" class="text-center py-4">Tidak ada pihak yang memenuhi syarat atau berpartisipasi pada periode ini.</td></tr>
                            <?php else: $no = 1; foreach ($distribusi_anggota as $item): ?>
                                <tr>
                                    <th><?= $no++; ?></th>
                                    <td class="font-semibold"><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                                    <td class="text-right"><?= number_format($item['total_poin_modal']); ?></td>
                                    <td class="text-right"><?= number_format($item['total_poin_usaha']); ?></td>
                                    <td class="text-right"><?= number_format($item['jasa_modal_diterima'], 2, ',', '.'); ?></td>
                                    <td class="text-right"><?= number_format($item['jasa_usaha_diterima'], 2, ',', '.'); ?></td>
                                    <td class="text-right font-bold text-primary bg-base-50"><?= number_format($item['total_diterima'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div role="alert" class="alert alert-info"><span>Tidak ada Laba Bersih yang dapat dibagikan (bernilai nol atau rugi) pada periode yang dipilih.</span></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>