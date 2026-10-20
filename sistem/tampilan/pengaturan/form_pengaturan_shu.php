<?php
$judulHalaman = 'Pengaturan Kebijakan SHU';
cekAkses('kelola_pengaturan');
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Atur parameter dan aturan main untuk proses pembagian Sisa Hasil Usaha (SHU) kepada anggota. Pengaturan ini akan digunakan oleh sistem saat melakukan kalkulasi distribusi SHU di akhir tahun buku.</p>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/pengaturan/simpan_pengaturan.php" method="POST">
            <?php csrfInput(); ?>
            <h3 class="text-lg font-bold mb-2">Proporsi Pembagian Jasa Anggota</h3>
            <p class="text-xs text-base-content/70 mb-4">Total persentase dari Jasa Modal dan Jasa Usaha harus 100%.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Persentase Jasa Modal (%)</span></label>
                    <input type="number" step="1" name="shu_persen_jasa_modal" class="input input-bordered" required value="<?= htmlspecialchars($_PENGATURAN['shu_persen_jasa_modal'] ?? '30') ?>">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Persentase Jasa Usaha (%)</span></label>
                    <input type="number" step="1" name="shu_persen_jasa_usaha" class="input input-bordered" required value="<?= htmlspecialchars($_PENGATURAN['shu_persen_jasa_usaha'] ?? '70') ?>">
                </div>
            </div>

            <div class="divider my-6"></div>

            <h3 class="text-lg font-bold mb-2">Konversi Poin Partisipasi Anggota (PPA)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin Jasa Modal (1 Poin per)</span></label>
                    <div class="join"><span class="join-item inline-flex items-center justify-center bg-base-200 px-3 font-bold text-base-content">Rp</span><input type="number" step="1" name="shu_poin_per_rupiah_modal" class="input input-bordered join-item w-full" required value="<?= htmlspecialchars($_PENGATURAN['shu_poin_per_rupiah_modal'] ?? '1000') ?>"></div>
                    <label class="label"><span class="label-text-alt">Jumlah simpanan (pokok+wajib) untuk mendapatkan 1 poin.</span></label>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Poin Jasa Usaha (1 Poin per)</span></label>
                    <div class="join"><span class="join-item inline-flex items-center justify-center bg-base-200 px-3 font-bold text-base-content">Rp</span><input type="number" step="1" name="shu_poin_per_rupiah_usaha" class="input input-bordered join-item w-full" required value="<?= htmlspecialchars($_PENGATURAN['shu_poin_per_rupiah_usaha'] ?? '10000') ?>"></div>
                    <label class="label"><span class="label-text-alt">Jumlah belanja/bayar angsuran untuk mendapatkan 1 poin.</span></label>
                </div>
            </div>

            <div class="divider my-6"></div>

            <h3 class="text-lg font-bold mb-2">Aturan Kelayakan</h3>
             <div class="form-control max-w-xs">
                <label class="label"><span class="label-text">Minimum Keanggotaan (Bulan)</span></label>
                <div class="join"><input type="number" step="1" name="shu_min_keanggotaan_bulan" class="input input-bordered join-item w-full" required value="<?= htmlspecialchars($_PENGATURAN['shu_min_keanggotaan_bulan'] ?? '3') ?>"><span class="join-item inline-flex items-center justify-center bg-base-200 px-3 font-bold text-base-content">Bulan</span></div>
                <label class="label"><span class="label-text-alt">Minimal lama menjadi anggota untuk berhak menerima SHU.</span></label>
            </div>
            
            <div class="flex justify-end gap-2 mt-8">
                <button type="submit" class="btn btn-primary">Simpan Pengaturan SHU</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>