<?php
$judulHalaman = 'Catat Penerimaan Pendapatan';
$daftar_akun_pendapatan = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kelompok_akun IN ('Pendapatan', 'Pendapatan Lain-lain') ORDER BY kode_akun ASC");
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Gunakan form ini untuk mencatat penerimaan kas di luar penjualan unit usaha, seperti: Jasa PPOB, Bunga Bank, Fee Konsinyasi, Sewa, dll.</p>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/pendapatan/tambah_pendapatan.php" method="POST">
            <?php csrfInput(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Diterima</span></label>
                    <input type="date" name="tanggal" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Pilih Jenis Pendapatan (Akun)</span></label>
                    <select name="kode_akun" class="select select-akun select-bordered w-full" required>
                        <option value="">-- Cari Akun Pendapatan --</option>
                        <?php foreach ($daftar_akun_pendapatan as $akun): ?>
                            <option value="<?= $akun['kode_akun'] ?>"><?= $akun['kode_akun'] ?> - <?= htmlspecialchars($akun['nama_akun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nomor Bukti (Opsional)</span></label>
                    <input type="text" name="nomor_bukti" placeholder="Otomatis jika kosong" class="input input-bordered">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Keterangan Penerimaan</span></label>
                    <input type="text" name="keterangan" placeholder="Contoh: Penerimaan Fee PLN Bulan Juni" class="input input-bordered" required>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Disimpan Ke</span></label>
                    <select name="metode_penerimaan" class="select select-bordered" required>
                        <option value="Tunai">Kas Tunai (Tunai)</option>
                        <option value="Bank">Rekening Bank (Transfer)</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nominal (Rp)</span></label>
                    <input type="text" name="nominal" placeholder="0" class="input input-bordered format-rupiah" required>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=pendapatan/lihat_pendapatan" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</div>