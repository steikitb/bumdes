<?php
$judulHalaman = 'Edit Teks Naratif CaLK';
$ph_kebijakan = MODE_ENTITAS == 'BUMDes' ? 'Pernyataan Laporan Keuangan Disusun Sesuai Keputusan Menteri Desa PDTT Nomor 136 Tahun 2022 beserta Standar Akuntansi Keuangan Entitas Privat (SAK EP)...' : 'Jelaskan bahwa laporan disusun sesuai SAK EP, mata uang, dll...';
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6">Edit Teks Naratif CaLK</h1>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/pengaturan/simpan_calk.php" method="POST">
            <?php csrfInput(); ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">1. Informasi Umum</span></label>
                <textarea name="calk_info_umum" class="textarea textarea-bordered h-32" placeholder="Jelaskan tentang pendirian, alamat, dan bidang usaha koperasi/BUMDes..."><?= htmlspecialchars($_PENGATURAN['calk_info_umum'] ?? '') ?></textarea>
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">2. Dasar Penyusunan Laporan Keuangan</span></label>
                <textarea name="calk_kebijakan_akuntansi" class="textarea textarea-bordered h-32" placeholder="<?= $ph_kebijakan ?>"><?= htmlspecialchars($_PENGATURAN['calk_kebijakan_akuntansi'] ?? '') ?></textarea>
            </div>
             <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">3. Ikhtisar Kebijakan Akuntansi Signifikan</span></label>
                <div class="pl-4 space-y-2">
                    <label class="label"><span class="label-text">a. Piutang Usaha</span></label>
                    <textarea name="calk_kebijakan_piutang" class="textarea textarea-bordered h-24"><?= htmlspecialchars($_PENGATURAN['calk_kebijakan_piutang'] ?? '') ?></textarea>
                    <label class="label"><span class="label-text">b. Persediaan</span></label>
                    <textarea name="calk_kebijakan_persediaan" class="textarea textarea-bordered h-24"><?= htmlspecialchars($_PENGATURAN['calk_kebijakan_persediaan'] ?? '') ?></textarea>
                    <label class="label"><span class="label-text">c. Aset Tetap</span></label>
                    <textarea name="calk_kebijakan_aset_tetap" class="textarea textarea-bordered h-24"><?= htmlspecialchars($_PENGATURAN['calk_kebijakan_aset_tetap'] ?? '') ?></textarea>
                    <label class="label"><span class="label-text">d. Pengakuan Pendapatan</span></label>
                    <textarea name="calk_kebijakan_pendapatan" class="textarea textarea-bordered h-24"><?= htmlspecialchars($_PENGATURAN['calk_kebijakan_pendapatan'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-8">
                 <a href="index.php?halaman=calk/lihat_calk" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Teks Naratif</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>