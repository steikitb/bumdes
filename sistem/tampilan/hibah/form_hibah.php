<?php
$judulHalaman = 'Formulir Penerimaan Hibah';
cekAkses('kelola_hibah');

$daftar_akun_aset = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kelompok_akun = 'Aset' ORDER BY kode_akun ASC");
?>
<h1 class="text-3xl font-bold mb-6">Catat Penerimaan Hibah Baru</h1>
<p class="mb-6 text-base-content/80">Gunakan formulir ini untuk mencatat dana atau aset hibah yang diterima dari pemerintah maupun lembaga lain. Sistem akan otomatis membuat jurnal yang sesuai berdasarkan jenis dan bentuk hibahnya.</p>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/hibah/tambah_hibah.php" method="POST">
            <?php csrfInput(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Tanggal Diterima</span></label>
                    <input type="date" name="tanggal_terima" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Pemberi Hibah / Instansi</span></label>
                    <input type="text" name="pemberi_hibah" placeholder="Contoh: Pemprov Jawa Timur" class="input input-bordered" required>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-base-200 mt-4 pt-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Bentuk Hibah Diterima</span></label>
                    <select name="bentuk_hibah" id="bentuk_hibah" class="select select-bordered" required>
                        <option value="Tunai">Uang Tunai (Kas Utama)</option>
                        <option value="Bank">Transfer Bank (Rekening)</option>
                        <option value="Aset">Aset Non-Kas (Tanah, Bangunan, Kendaraan)</option>
                    </select>
                </div>
                
                <div class="form-control hidden" id="div_akun_aset">
                    <label class="label"><span class="label-text text-primary font-bold">Pilih Akun Aset Bertambah</span></label>
                    <select name="kode_akun_aset" class="select select-bordered w-full">
                        <option value="">-- Pilih Akun Aset --</option>
                        <?php foreach ($daftar_akun_aset as $akun): ?>
                            <option value="<?= $akun['kode_akun'] ?>"><?= $akun['kode_akun'] ?> - <?= htmlspecialchars($akun['nama_akun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-control mt-4">
                <label class="label"><span class="label-text font-bold">Nilai Hibah (Rp)</span></label>
                <input type="text" name="jumlah" placeholder="0" class="input input-bordered format-rupiah text-lg font-bold" required>
            </div>

            <div class="form-control mt-4">
                <label class="label"><span class="label-text">Keterangan / Peruntukan Hibah</span></label>
                <input type="text" name="keterangan" placeholder="Contoh: Hibah pembangunan gudang baru" class="input input-bordered" required>
            </div>

            <div class="form-control mt-4 p-4 border border-info bg-info/10 rounded-lg">
                <label class="label"><span class="label-text font-bold text-info">Jenis Hibah (Sesuai SAK EP)</span></label>
                <select name="jenis_hibah" class="select select-bordered" required>
                    <option value="Tanpa Syarat">Tanpa Syarat (Langsung diakui sebagai pendapatan)</option>
                    <option value="Bersyarat">Bersyarat (Dicatat sebagai utang/liabilitas ditangguhkan)</option>
                </select>
                 <label class="label"><p class="label-text-alt">Pilih "Bersyarat" jika ada kewajiban kinerja atau target yang harus dicapai koperasi/BUMDes sebelum hibah ini boleh diakui sebagai pendapatan utuh.</p></label>
            </div>
            
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=hibah/lihat_hibah" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Penerimaan Hibah</button>
            </div>
        </form>
    </div>
</div>