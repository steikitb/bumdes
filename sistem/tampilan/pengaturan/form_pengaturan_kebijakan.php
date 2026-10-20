<?php
$judulHalaman = 'Pengaturan Kebijakan & Pemetaan Akun';
cekAkses('kelola_pengaturan');

$daftar_akun = query("SELECT kode_akun, nama_akun FROM daftar_akun ORDER BY kode_akun ASC");

function renderAkunSelect($name, $label, $default, $daftar_akun) {
    global $_PENGATURAN;
    $val = $_PENGATURAN[$name] ?? $default;
    $html = '<div class="form-control"><label class="label"><span class="label-text">'.$label.'</span></label>';
    $html .= '<select name="'.$name.'" class="select select-bordered select-akun" required>';
    $html .= '<option value="">-- Pilih Akun --</option>';
    foreach($daftar_akun as $akun) {
        $sel = ($akun['kode_akun'] == $val) ? 'selected' : '';
        $html .= '<option value="'.$akun['kode_akun'].'" '.$sel.'>'.$akun['kode_akun'].' - '.htmlspecialchars($akun['nama_akun']).'</option>';
    }
    $html .= '</select></div>';
    return $html;
}
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Halaman ini sangat penting. Digunakan untuk memetakan (menghubungkan) kode akun yang akan digunakan secara otomatis oleh sistem saat menjurnal transaksi operasional entitas. Anda dapat menyesuaikannya dengan Daftar Akun Anda.</p>

<?php if (MODE_ENTITAS == 'BUMDes'): ?>
    <div class="alert alert-info shadow-lg mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>Dalam mode <b>BUMDes</b>, persentase penyisihan piutang bersifat baku sesuai Kepmendesa 136/2022: Lancar (0.5%), Kurang Lancar (10%), Diragukan (50%), Macet (100%). Input persentase di bawah akan diabaikan oleh sistem.</span>
    </div>
<?php endif; ?>

<form action="<?= BASE_URL ?>sistem/proses/pengaturan/simpan_pengaturan.php" method="POST">
    <?php csrfInput(); ?>
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            
            <h3 class="text-xl font-bold mb-2">Kebijakan Penyisihan Piutang (Analisis Umur)</h3>
            <p class="text-sm text-base-content/70 mb-4">Atur persentase penyisihan kerugian piutang berdasarkan umur pinjaman.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="form-control"><label class="label"><span class="label-text">Lancar (0 - X hari)</span></label><input type="number" name="piutang_aging_bucket_1_hari" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_1_hari'] ?? '30') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Penyisihan Lancar (%)</span></label><input type="number" step="0.1" name="piutang_aging_bucket_1_persen" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_1_persen'] ?? '0') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Kurang Lancar (X - Y hari)</span></label><input type="number" name="piutang_aging_bucket_2_hari" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_2_hari'] ?? '90') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Penyisihan Kurang Lancar (%)</span></label><input type="number" step="0.1" name="piutang_aging_bucket_2_persen" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_2_persen'] ?? '5') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Diragukan (Y - Z hari)</span></label><input type="number" name="piutang_aging_bucket_3_hari" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_3_hari'] ?? '180') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Penyisihan Diragukan (%)</span></label><input type="number" step="0.1" name="piutang_aging_bucket_3_persen" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_3_persen'] ?? '25') ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Macet (&gt; Z hari)</span></label></div>
                <div class="form-control"><label class="label"><span class="label-text">Penyisihan Macet (%)</span></label><input type="number" step="0.1" name="piutang_aging_bucket_4_persen" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['piutang_aging_bucket_4_persen'] ?? '50') ?>"></div>
            </div>
        </div>
    </div>

    <?php if (MODE_ENTITAS == 'Koperasi'): ?>
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h3 class="text-xl font-bold mb-2">Kebijakan Skor Kredit Anggota</h3>
            <p class="text-sm text-base-content/70 mb-4">
                Aturan ini dipakai sistem untuk menilai kelayakan kredit anggota di menu <b>Piutang Simpan Pinjam</b>.
                Skor dihitung dari empat sumber: riwayat pembayaran angsuran, lama keanggotaan, aktivitas belanja di toko,
                dan perbandingan total simpanan dengan sisa pinjamannya. Skor dipakai sebagai rekomendasi --
                sistem hanya <b>menolak</b> pengajuan bila skor anggota di bawah batas minimum yang Anda tetapkan.
            </p>

            <div class="divider my-1 text-sm font-bold text-primary">Sumber Poin</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin per Pembayaran Angsuran</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_per_pembayaran" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_per_pembayaran'] ?? '10') ?>">
                    <span class="label-text-alt opacity-70">Diberikan 1x setiap kali angsuran tercatat.</span>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin per Tahun Keanggotaan</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_per_tahun_anggota" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_per_tahun_anggota'] ?? '5') ?>">
                    <span class="label-text-alt opacity-70">Diberikan 1x untuk setiap 1 tahun menjadi anggota.</span>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin jika Aktif Belanja</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_aktivitas_belanja" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_aktivitas_belanja'] ?? '50') ?>">
                    <span class="label-text-alt opacity-70">Diberikan 1x bila ada belanja dalam 3 bulan terakhir.</span>
                </div>
            </div>

            <div class="divider my-1 text-sm font-bold text-primary">Rasio Simpanan &divide; Sisa Pinjaman</div>
            <p class="text-sm text-base-content/70 mb-4">Rasio = total simpanan anggota (pokok, wajib, dan tabungan) dibagi sisa pinjamannya.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Batas Rasio Atas</span></label>
                    <input type="number" step="0.1" min="0" name="skor_kredit_rasio_batas_atas" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_rasio_batas_atas'] ?? '1') ?>">
                    <span class="label-text-alt opacity-70">Rasio di atas nilai ini = paling sehat.</span>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin untuk Rasio di Atas Batas Atas</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_rasio_atas" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_rasio_atas'] ?? '100') ?>">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Batas Rasio Bawah</span></label>
                    <input type="number" step="0.1" min="0" name="skor_kredit_rasio_batas_bawah" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_rasio_batas_bawah'] ?? '0.5') ?>">
                    <span class="label-text-alt opacity-70">Rasio di atas nilai ini = cukup sehat.</span>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin untuk Rasio di Atas Batas Bawah</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_rasio_bawah" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_rasio_bawah'] ?? '50') ?>">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin untuk Rasio di Bawah Batas Bawah</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_poin_rasio_dasar" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_poin_rasio_dasar'] ?? '20') ?>">
                    <span class="label-text-alt opacity-70">Poin minimum, misalnya saat anggota belum punya simpanan.</span>
                </div>
            </div>

            <div class="divider my-1 text-sm font-bold text-primary">Ambang Batas Rekomendasi</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <div class="form-control">
                    <label class="label"><span class="label-text">Ambang Batas "Baik"</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_batas_baik" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_batas_baik'] ?? '70') ?>">
                    <span class="label-text-alt opacity-70">Di atas nilai ini: rekomendasi "Baik", verifikasi oleh staf.</span>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Ambang Batas "Sangat Baik"</span></label>
                    <input type="number" step="1" min="0" name="skor_kredit_batas_sangat_baik" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['skor_kredit_batas_sangat_baik'] ?? '85') ?>">
                    <span class="label-text-alt opacity-70">Di atas nilai ini: pengajuan otomatis disetujui.</span>
                </div>
            </div>

            <div role="alert" class="alert alert-info shadow mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-info shrink-0 w-6 h-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm">Atur <b>Ambang Batas "Baik"</b> sesuai aturan koperasi Anda. Bila diisi <b>0</b>, tidak ada anggota yang tersaring otomatis dan setiap pengajuan perlu diperiksa manual oleh staf.</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h3 class="text-xl font-bold mb-2">Pemetaan Akun (Mapping) Jurnal Otomatis</h3>
            <p class="text-sm text-base-content/70 mb-4">Sistem akan menggunakan akun-akun ini saat membuat jurnal secara otomatis di latar belakang.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                
                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">A. Aset & Kas Bank</div>
                <?= renderAkunSelect('akun_map_kas', 'Kas Tunai Utama', '1.1.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_bank', 'Rekening Bank (Transfer)', '1.1.01.02', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_piutang_usaha_anggota', 'Piutang Usaha (Toko)', '1.1.03.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_piutang_pinjaman_anggota', 'Piutang Anggota (SPP)', '1.1.03.04', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_penyisihan_piutang', 'Akumulasi Penyisihan Piutang', '1.1.04.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_persediaan', 'Persediaan Barang Dagang', '1.1.05.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_perlengkapan', 'Perlengkapan / ATK', '1.1.06.01', $daftar_akun) ?>
                
                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">B. Aset Tetap & Penyusutan</div>
                <?= renderAkunSelect('akun_map_aset_bangunan', 'Aset Bangunan', '1.3.05.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_akumulasi_bangunan', 'Akumulasi Penyusutan Bangunan', '1.3.07.04', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_aset_peralatan', 'Aset Peralatan & Mesin', '1.3.03.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_akumulasi_peralatan', 'Akumulasi Penyusutan Peralatan', '1.3.07.02', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_akumulasi_amortisasi', 'Akumulasi Amortisasi (Tak Berwujud)', '1.4.02.01', $daftar_akun) ?>
                
                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">C. Liabilitas (Utang)</div>
                <?= renderAkunSelect('akun_map_utang_usaha', 'Utang Usaha (Pemasok)', '2.1.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_simpanan_sukarela', 'Tabungan / Simpanan Sukarela', '2.1.05.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_utang_jasa_anggota', 'Utang Bagi Hasil (Jasa/PADes)', '2.1.05.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_utang_dana_pengurus', 'Utang Dana Pengurus', '2.1.03.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_utang_dana_sosial', 'Utang Dana Sosial', '2.1.09.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_utang_pajak', 'Utang Pajak (PPh/PPN)', '2.1.02.04', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_utang_bank', 'Utang Ke Bank / Lembaga', '2.2.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_hibah_diterima_dimuka', 'Utang Hibah Bersyarat (Ditangguhkan)', '2.2.99.99', $daftar_akun) ?>

                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">D. Ekuitas (Modal)</div>
                <?= renderAkunSelect('akun_map_simpanan_pokok', 'Modal Awal / Simpanan Pokok', '3.1.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_simpanan_wajib', 'Modal Tambahan / Simpanan Wajib', '3.1.02.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_cadangan', 'Cadangan Laba Ditahan', '3.3.01.02', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_shu_belum_dibagi', 'SHU / Laba Belum Dibagi', '3.3.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_ikhtisar_lr', 'Ikhtisar Laba Rugi (Jurnal Penutup)', '3.9.01.01', $daftar_akun) ?>
                
                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">E. Pendapatan</div>
                <?= renderAkunSelect('akun_map_pendapatan_penjualan_anggota', 'Pendapatan Penjualan Barang', '4.2.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_potongan_penjualan', 'Potongan / Retur Penjualan', '4.2.01.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_pendapatan_bunga_pinjaman', 'Pendapatan Bunga Pinjaman (SPP)', '4.1.08.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_pendapatan_hibah', 'Pendapatan Hibah (Tanpa Syarat)', '7.1.99.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_pendapatan_stok_lebih', 'Pendapatan Penyesuaian Stok (Lebih)', '7.1.99.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_laba_pelepasan_aset', 'Laba Pelepasan Aset Tetap', '7.1.05.01', $daftar_akun) ?>

                <div class="font-bold text-lg text-primary md:col-span-2 lg:col-span-3 mt-4 border-b pb-2">F. Beban & Harga Pokok</div>
                <?= renderAkunSelect('akun_map_hpp', 'Harga Pokok Penjualan (HPP)', '5.1.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_gaji', 'Beban Gaji & THR Karyawan', '6.1.01.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_penyusutan', 'Beban Penyusutan Aset', '6.1.07.03', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_amortisasi', 'Beban Amortisasi', '6.1.07.06', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_kerugian_piutang', 'Beban Kerugian Piutang', '6.1.07.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_pajak_kini', 'Beban Pajak Penghasilan (PPh Badan)', '7.3.01.06', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_stok_kurang', 'Beban Penyesuaian Stok (Kurang)', '7.2.99.99', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_rugi_pelepasan_aset', 'Rugi Pelepasan Aset Tetap', '7.2.04.01', $daftar_akun) ?>
                <?= renderAkunSelect('akun_map_beban_bunga_simpanan', 'Beban Bunga Simpanan/Tabungan', '7.2.02.01', $daftar_akun) ?>

            </div>
            
            <div class="flex justify-end gap-2 mt-8">
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Semua Pengaturan</button>
            </div>
        </div>
    </div>
</form>

<?php  ?>