<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Tutup Buku & Perhitungan Laba BUMDes' : 'Proses Tutup Buku Tahunan';
$judulHalaman = $lbl_judul;
$tahun_proses = $_POST['tahun'] ?? date('Y') - 1;
$total_shu = 0;

$belum_ada_penyusutan = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['tahun'])) {
    if (isset($_GET['tahun'])) {
        $tahun_proses = (int)$_GET['tahun'];
    }
    
    $tanggal_mulai = $tahun_proses . '-01-01';
    $tanggal_selesai = $tahun_proses . '-12-31';
    
    $total_shu = hitungSHU($tanggal_mulai, $tanggal_selesai);

    // Cek apakah ada jurnal penyusutan di tahun tersebut (PENYUSUTAN-2024 atau PENYUSUTAN-BLN-202412)
    $cek_penyusutan = query("SELECT id FROM jurnal_umum WHERE nomor_bukti LIKE 'PENYUSUTAN-%' AND tanggal BETWEEN ? AND ? LIMIT 1", [$tanggal_mulai, $tanggal_selesai]);
    $belum_ada_penyusutan = empty($cek_penyusutan);
}

$histori = query("SELECT tahun FROM histori_shu WHERE tahun = ?", [$tahun_proses], 'i');
$sudah_diproses = !empty($histori);

$lbl_shu = MODE_ENTITAS === 'BUMDes' ? 'Laba Bersih' : 'Sisa Hasil Usaha (SHU)';
$lbl_jasa = MODE_ENTITAS === 'BUMDes' ? 'Bagi Hasil (PADes & Penyerta)' : 'Jasa Anggota';
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>

<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title">Pilih Tahun yang Akan Diproses</h2>
        <form method="POST" action="index.php?halaman=tutup_buku/form_tutup_buku">
            <div class="flex items-end gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Tahun Buku</span></label>
                    <select name="tahun" class="select select-bordered">
                        <?php for ($i = date('Y') - 1; $i >= date('Y') - 5; $i--): ?>
                        <option value="<?= $i ?>" <?= ($i == $tahun_proses) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Hitung <?= $lbl_shu ?></button>
                <a href="index.php?halaman=tutup_buku/lihat_histori" class="btn btn-ghost">Lihat Histori</a>
            </div>
        </form>
    </div>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['tahun'])): ?>
    
    <?php if ($belum_ada_penyusutan && !$sudah_diproses): ?>
    <div class="alert alert-warning shadow-lg mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <h3 class="font-bold">Peringatan: Belum Ada Jurnal Penyusutan!</h3>
            <div class="text-sm">Kami mendeteksi belum ada jurnal beban penyusutan aset yang tercatat untuk tahun <?= $tahun_proses ?>. Pastikan Anda telah melakukan <a href="index.php?halaman=jurnal/form_penyesuaian" class="link text-primary font-bold">Penyesuaian Akhir Bulan/Tahun</a> sebelum mengeksekusi Tutup Buku agar nilai Laba Bersih akurat.</div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Alokasi <?= $lbl_shu ?> Tahun <?= $tahun_proses ?></h2>
            <div class="text-2xl font-bold my-4">Total <?= $lbl_shu ?>: Rp <?= number_format($total_shu, 2, ',', '.') ?></div>
            
            <?php if ($sudah_diproses): ?>
                <div role="alert" class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Perhatian: Proses tutup buku untuk tahun <?= $tahun_proses ?> sudah pernah dilakukan.</span>
                </div>
            <?php else: ?>
                <form action="<?= BASE_URL ?>sistem/proses/tutup_buku/proses.php" method="POST">
                    <?php csrfInput(); ?>
                    <input type="hidden" name="tahun" value="<?= $tahun_proses ?>">
                    <input type="hidden" name="total_shu" value="<?= $total_shu ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control"><label class="label"><span class="label-text">Persen Cadangan (%)</span></label><input type="number" step="0.01" name="persen_cadangan" class="input input-bordered" required value="25"></div>
                        <div class="form-control"><label class="label"><span class="label-text">Persen <?= $lbl_jasa ?> (%)</span></label><input type="number" step="0.01" name="persen_jasa_anggota" class="input input-bordered" required value="40"></div>
                        <div class="form-control"><label class="label"><span class="label-text">Persen Dana Pengurus (%)</span></label><input type="number" step="0.01" name="persen_dana_pengurus" class="input input-bordered" required value="10"></div>
                        <div class="form-control"><label class="label"><span class="label-text">Persen Dana Sosial (%)</span></label><input type="number" step="0.01" name="persen_dana_sosial" class="input input-bordered" required value="25"></div>
                    </div>
                    <div class="flex justify-end mt-8">
                         <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin ingin memproses tutup buku untuk tahun <?= $tahun_proses ?>? Proses ini tidak dapat dibatalkan.');">Proses Tutup Buku</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php  ?>