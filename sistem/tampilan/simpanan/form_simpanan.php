<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Penerimaan Modal BUMDes' : 'Penerimaan Simpanan Pokok & Wajib';
$judulHalaman = $lbl_judul;
$aksiForm = '../sistem/proses/simpanan/tambah_simpanan.php';
$anggota = query("SELECT id, nama_lengkap FROM anggota ORDER BY nama_lengkap ASC");

$daftar_akun_aset = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kelompok_akun = 'Aset' ORDER BY kode_akun ASC");

$lbl_dropdown = MODE_ENTITAS === 'BUMDes' ? 'Pilih Pihak Penyerta Modal' : 'Pilih Anggota Penyetor';
$lbl_info = MODE_ENTITAS === 'BUMDes' ? 'Pilih Institusi / Masyarakat' : 'Pilih Anggota Terdaftar';
$lbl_alert = MODE_ENTITAS === 'BUMDes' ? 'Jika Pemerintah Desa, pastikan sudah dimasukkan di menu Daftar Penyerta Modal.' : 'Pastikan identitas keanggotaan sudah terdaftar.';

$opt_pokok = MODE_ENTITAS === 'BUMDes' ? 'Penyertaan Modal Desa (Pemdes)' : 'Simpanan Pokok';
$opt_wajib = MODE_ENTITAS === 'BUMDes' ? 'Penyertaan Modal Masyarakat / Swasta' : 'Simpanan Wajib';
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-4 text-base-content/80">Catat penerimaan setoran yang akan diakui sebagai Ekuitas/Modal secara permanen di formulir ini.</p>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/simpanan/tambah_simpanan.php" method="POST">
            <?php csrfInput(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text"><?= $lbl_dropdown ?></span></label>
                    <select name="anggota_id" class="select-bordered" required>
                        <option disabled selected value="">-- <?= $lbl_info ?> --</option>
                        <?php foreach ($anggota as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="label"><p class="label-text-alt text-info"><?= $lbl_alert ?></p></label>
                </div>
                
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Jenis / Klasifikasi Modal</span></label>
                    <select name="jenis_simpanan" class="select select-bordered" required>
                        <option value="Pokok"><?= $opt_pokok ?></option>
                        <option value="Wajib"><?= $opt_wajib ?></option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-base-200 pt-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Bentuk Setoran (Metode)</span></label>
                    <select name="bentuk_setoran" id="bentuk_setoran" class="select select-bordered" required>
                        <option value="Tunai">Uang Tunai (Kas Utama)</option>
                        <option value="Bank">Transfer Bank (Rekening)</option>
                        <option value="Aset">Aset Non-Kas (Tanah, Bangunan, dsb)</option>
                    </select>
                </div>
                
                <div class="form-control mb-4 hidden" id="div_akun_aset">
                    <label class="label"><span class="label-text text-primary font-bold">Pilih Akun Aset Penerima</span></label>
                    <select name="kode_akun_aset" class="select select-bordered w-full">
                        <option value="">-- Pilih Akun Aset --</option>
                        <?php foreach ($daftar_akun_aset as $akun): ?>
                            <option value="<?= $akun['kode_akun'] ?>"><?= $akun['kode_akun'] ?> - <?= htmlspecialchars($akun['nama_akun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nilai / Jumlah Setoran (Rp)</span></label>
                    <input type="text" name="jumlah" placeholder="0" class="input input-bordered format-rupiah" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Diterima</span></label>
                    <input type="date" name="tanggal_setor" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Keterangan (Opsional)</span></label>
                <input type="text" name="keterangan" class="input input-bordered" placeholder="Contoh: Tanah kas desa untuk lokasi BUMDes">
            </div>
            
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=simpanan/lihat_simpanan" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>