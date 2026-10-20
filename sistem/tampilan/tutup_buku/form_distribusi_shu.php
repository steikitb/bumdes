<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Proses Pembagian Bagi Hasil / Dividen' : 'Proses Pembagian Keuntungan (SHU)';
$judulHalaman = $lbl_judul;
cekAkses('admin');
$tahun_proses = $_GET['tahun'] ?? date('Y') - 1;
$histori_shu = query("SELECT * FROM histori_shu WHERE tahun = ?", [$tahun_proses], 'i');
$data_shu = !empty($histori_shu) ? $histori_shu[0] : null;
$sudah_diproses = false;
$rincian_kalkulasi = null;

if ($data_shu) {
    if (MODE_ENTITAS === 'BUMDes') {
        // Untuk BUMDes, kita tidak perlu hitung poin, cukup cek apakah dana sudah disetor
        $cek_pembayaran_pades = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ['BYR-PADES-'.$tahun_proses], 's');
        $sudah_diproses = !empty($cek_pembayaran_pades);
    } else {
        // Mode Koperasi: Hitung Poin Distribusi Per Anggota
        $cek_distribusi = query("SELECT id FROM histori_shu_anggota WHERE histori_shu_id = ? LIMIT 1", [$data_shu['id']], 'i');
        $sudah_diproses = !empty($cek_distribusi);

        if (!$sudah_diproses) {
            $tanggal_akhir_tahun = $tahun_proses . '-12-31';
            $persen_jasa_modal = (float)($_PENGATURAN['shu_persen_jasa_modal'] ?? 30);
            $persen_jasa_usaha = (float)($_PENGATURAN['shu_persen_jasa_usaha'] ?? 70);
            $poin_per_rupiah_modal = (int)($_PENGATURAN['shu_poin_per_rupiah_modal'] ?? 1000);
            $min_keanggotaan_bulan = (int)($_PENGATURAN['shu_min_keanggotaan_bulan'] ?? 3);
            $tgl_batas_keanggotaan = date('Y-m-d', strtotime("$tanggal_akhir_tahun -$min_keanggotaan_bulan months"));

            $dana_jasa_modal = $data_shu['alokasi_jasa_anggota'] * ($persen_jasa_modal / 100);
            $dana_jasa_usaha = $data_shu['alokasi_jasa_anggota'] * ($persen_jasa_usaha / 100);

            $total_poin_usaha_q = query("SELECT SUM(jumlah_poin) as grand_total FROM histori_poin_ppa hppa JOIN anggota a ON hppa.anggota_id = a.id WHERE jenis_poin = 'usaha' AND YEAR(tanggal_perolehan) = ? AND a.tanggal_bergabung <= ?", [$tahun_proses, $tgl_batas_keanggotaan], 'is');
            $grand_total_poin_usaha = $total_poin_usaha_q[0]['grand_total'] ?? 0;

            $simpanan_anggota_q = query("SELECT anggota_id, SUM(jumlah) as total_simpanan FROM simpanan s JOIN anggota a ON s.anggota_id = a.id WHERE s.jenis_simpanan IN ('Pokok', 'Wajib') AND s.tanggal_setor <= ? AND a.tanggal_bergabung <= ? GROUP BY s.anggota_id", [$tanggal_akhir_tahun, $tgl_batas_keanggotaan], 'ss');
            $grand_total_poin_modal = 0;
            foreach($simpanan_anggota_q as $simpanan) {
                $grand_total_poin_modal += ($poin_per_rupiah_modal > 0) ? floor($simpanan['total_simpanan'] / $poin_per_rupiah_modal) : 0;
            }

            $rincian_kalkulasi = [
                'dana_jasa_modal' => $dana_jasa_modal,
                'dana_jasa_usaha' => $dana_jasa_usaha,
                'total_poin_modal' => $grand_total_poin_modal,
                'total_poin_usaha' => $grand_total_poin_usaha,
                'nilai_per_poin_modal' => ($grand_total_poin_modal > 0) ? $dana_jasa_modal / $grand_total_poin_modal : 0,
                'nilai_per_poin_usaha' => ($grand_total_poin_usaha > 0) ? $dana_jasa_usaha / $grand_total_poin_usaha : 0,
            ];
        }
    }
}
$lbl_jasa = MODE_ENTITAS === 'BUMDes' ? 'Pembagian Laba / Bagi Hasil PADes' : 'Pembagian SHU Jasa Anggota';
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title">Pilih Tahun Buku</h2>
        <form method="GET" action=""><input type="hidden" name="halaman" value="tutup_buku/form_distribusi_shu"><div class="flex items-end gap-4"><div class="form-control"><label class="label"><span class="label-text">Tahun Tutup Buku</span></label><select name="tahun" class="select select-bordered"><?php for ($i = date('Y') - 1; $i >= date('Y') - 5; $i--): ?><option value="<?= $i ?>" <?= ($i == $tahun_proses) ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></div><button type="submit" class="btn btn-secondary">Pilih Tahun</button></div></form>
    </div>
</div>

<?php if ($data_shu): ?>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        
        <?php if (MODE_ENTITAS === 'BUMDes'): ?>
            <h2 class="card-title text-2xl">Penyerahan Hak Pemilik (PADes)</h2>
            <div class="text-xl my-4">Total Hak PADes: <strong>Rp <?= number_format($data_shu['alokasi_jasa_anggota'], 2, ',', '.') ?></strong></div>
            <p class="mb-6">Sesuai Kepmendesa 136/2022, alokasi Laba Bersih BUMDes (PADes, Cadangan, Direksi, dll) sudah diatur persentasenya saat proses <strong>Tutup Buku Tahunan</strong> dan dijurnal otomatis ke masing-masing Akun Kewajiban (Utang).<br><br>Langkah selanjutnya adalah <strong>menyetorkan (mentransfer riil)</strong> dana PADes tersebut ke Rekening Kas Desa.</p>
            <div class="flex justify-start">
                 <a href="index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun=<?= $tahun_proses ?>" class="btn btn-primary shadow-lg">Buka Menu Penyetoran Kas PADes</a>
            </div>
        <?php else: ?>
            <h2 class="card-title"><?= $lbl_jasa ?> Tahun <?= $tahun_proses ?> Berdasarkan Poin Evaluasi</h2>
            <div class="text-2xl font-bold my-4">Total Dana Untuk Anggota: Rp <?= number_format($data_shu['alokasi_jasa_anggota'], 2, ',', '.') ?></div>
            
            <?php if ($sudah_diproses): ?>
                <div role="alert" class="alert alert-success"><svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span>Pembagian SHU untuk tahun <?= $tahun_proses ?> sudah selesai diproses.</span><a href="index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun=<?= $tahun_proses ?>" class="btn btn-sm">Lihat Hasil</a></div>
            <?php elseif ($rincian_kalkulasi): ?>
                <div class="stats shadow w-full mb-6">
                  <div class="stat"><div class="stat-title">Dana Jasa Modal</div><div class="stat-value text-primary">Rp <?= number_format($rincian_kalkulasi['dana_jasa_modal'], 0, ',', '.') ?></div><div class="stat-desc">Total Poin: <?= number_format($rincian_kalkulasi['total_poin_modal']) ?></div></div>
                  <div class="stat"><div class="stat-title">Dana Jasa Usaha</div><div class="stat-value text-secondary">Rp <?= number_format($rincian_kalkulasi['dana_jasa_usaha'], 0, ',', '.') ?></div><div class="stat-desc">Total Poin: <?= number_format($rincian_kalkulasi['total_poin_usaha']) ?></div></div>
                  <div class="stat"><div class="stat-title">Nilai per Poin Modal</div><div class="stat-value">Rp <?= number_format($rincian_kalkulasi['nilai_per_poin_modal'], 2, ',', '.') ?></div></div>
                  <div class="stat"><div class="stat-title">Nilai per Poin Usaha</div><div class="stat-value">Rp <?= number_format($rincian_kalkulasi['nilai_per_poin_usaha'], 2, ',', '.') ?></div></div>
                </div>
                <p class="mb-4">Sistem akan menghitung SHU untuk setiap anggota secara otomatis berdasarkan total Poin Partisipasi Anggota (PPA) yang terkumpul selama tahun <?= $tahun_proses ?>. Pastikan semua transaksi yang menghasilkan poin sudah tercatat.</p>
                <form action="<?= BASE_URL ?>sistem/proses/tutup_buku/proses_distribusi_shu.php" method="POST">
                    <?php csrfInput(); ?>
                    <input type="hidden" name="tahun" value="<?= $tahun_proses ?>">
                    <input type="hidden" name="histori_shu_id" value="<?= $data_shu['id'] ?>">
                    <div class="flex justify-end mt-8">
                         <button type="submit" class="btn btn-primary" onclick="return confirm('Anda yakin ingin memproses pembagian SHU untuk tahun <?= $tahun_proses ?>? Proses ini tidak dapat dibatalkan dan akan menghitung SHU berdasarkan poin anggota.');">Proses Pembagian SHU</button>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php elseif (isset($_GET['tahun'])): ?>
<div role="alert" class="alert alert-warning"><svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg><span>Proses tutup buku untuk tahun <?= $tahun_proses ?> belum dilakukan. Silakan lakukan tutup buku terlebih dahulu.</span></div>
<?php endif; ?>
<?php  ?>