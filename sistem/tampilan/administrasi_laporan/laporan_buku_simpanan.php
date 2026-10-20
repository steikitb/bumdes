<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Buku Tabungan / Penyertaan Modal' : 'Buku Simpanan Anggota';
$judulHalaman = $lbl_judul;
cekAkses('kelola_buku_administrasi');
$anggota_list = query("SELECT id, nama_lengkap FROM anggota ORDER BY nama_lengkap ASC");
$anggota_id = $_POST['anggota_id'] ?? null;
$anggota_detail = null;
$transaksi_simpanan = [];

if ($anggota_id) {
    $anggota_detail = query("SELECT * FROM anggota WHERE id = ?", [$anggota_id], 'i')[0];
    $transaksi_simpanan_all = query("
        SELECT tanggal_setor as tanggal, 'Setoran' as uraian, jenis_simpanan, jumlah, 0 as tarik FROM simpanan WHERE anggota_id = ?
        UNION ALL
        SELECT tanggal_transaksi as tanggal, keterangan as uraian, 'Sukarela' as jenis_simpanan, 
               CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE 0 END as jumlah,
               CASE WHEN jenis_transaksi = 'Tarik' THEN jumlah ELSE 0 END as tarik
        FROM simpanan_sukarela_transaksi WHERE anggota_id = ?
        ORDER BY tanggal ASC
    ", [$anggota_id, $anggota_id], 'ii');
    
    $saldo = 0;
    foreach($transaksi_simpanan_all as $t) {
        $saldo = $saldo + $t['jumlah'] - $t['tarik'];
        $transaksi_simpanan[] = [
            'tanggal' => $t['tanggal'],
            'uraian' => $t['uraian'],
            'pokok' => ($t['jenis_simpanan'] == 'Pokok' ? $t['jumlah'] : 0),
            'wajib' => ($t['jenis_simpanan'] == 'Wajib' ? $t['jumlah'] : 0),
            'sukarela' => ($t['jenis_simpanan'] == 'Sukarela' ? $t['jumlah'] - $t['tarik'] : 0),
            'jumlah' => $saldo
        ];
    }
}
?>
<?php  ?>
<style> @media print { .no-print { display: none; } .printable-area { font-size: 10px; } } </style>

<div class="card bg-base-100 shadow-xl mb-6 no-print">
    <div class="card-body">
        <h2 class="card-title">Cetak Buku Mutasi per Entitas</h2>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Pilih Pihak Terkait</span></label>
                    <select name="anggota_id" id="anggota_id" class="select-bordered" required>
                        <option value="">-- Pilih Nama --</option>
                        <?php foreach($anggota_list as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($anggota_id == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control"><button type="submit" class="btn btn-primary w-full">Tampilkan Buku Cetak</button></div>
            </div>
        </form>
    </div>
</div>

<?php if ($anggota_detail): ?>
<div class="mb-4 flex gap-2 no-print justify-end"><button onclick="window.print()" class="btn btn-secondary shadow-lg">Cetak Buku</button></div>
<div class="card bg-base-100 shadow-xl printable-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold uppercase">BUKU BUKTI - MUTASI SIMPANAN / PENYERTAAN</h2>
            <div class="text-left mt-4 p-4 bg-base-50 rounded-lg border border-base-200">
                <p><strong>NAMA PEMILIK :</strong> <?= htmlspecialchars($anggota_detail['nama_lengkap']) ?></p>
                <p><strong>ALAMAT DOMISILI :</strong> <?= htmlspecialchars($anggota_detail['alamat']) ?></p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-compact w-full border border-base-300">
                <thead>
                    <tr class="bg-base-200">
                        <th class="border text-center" rowspan="2">Tanggal</th>
                        <th class="border text-center" rowspan="2">Uraian Transaksi</th>
                        <th class="border text-center" colspan="3">MUTASI (Rp)</th>
                        <th class="border text-center" rowspan="2">Saldo Akhir (Rp)</th>
                    </tr>
                     <tr class="bg-base-200">
                        <th class="border text-center">Pokok / Desa</th>
                        <th class="border text-center">Wajib / Masyarakat</th>
                        <th class="border text-center">Sukarela (Tarik/Setor)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi_simpanan)): ?>
                        <tr><td colspan="6" class="text-center border py-4 text-base-content/60">Data mutasi tidak ditemukan.</td></tr>
                    <?php else: foreach ($transaksi_simpanan as $item): ?>
                    <tr class="hover">
                        <td class="border text-center"><?= date('d-m-Y', strtotime($item['tanggal'])) ?></td>
                        <td class="border font-medium"><?= htmlspecialchars($item['uraian']) ?></td>
                        <td class="border text-right text-info font-mono"><?= $item['pokok'] != 0 ? number_format($item['pokok'], 0,',','.') : '-' ?></td>
                        <td class="border text-right text-info font-mono"><?= $item['wajib'] != 0 ? number_format($item['wajib'], 0,',','.') : '-' ?></td>
                        <td class="border text-right text-warning font-mono"><?= $item['sukarela'] != 0 ? number_format($item['sukarela'], 0,',','.') : '-' ?></td>
                        <td class="border text-right font-bold font-mono text-primary bg-base-50"><?= number_format($item['jumlah'], 0,',','.') ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
<?php  ?>