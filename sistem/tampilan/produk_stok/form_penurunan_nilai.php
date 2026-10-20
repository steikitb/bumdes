<?php
$judulHalaman = 'Penyesuaian Penurunan/Pemulihan Nilai Persediaan';
$produk_list = query("SELECT id, nama_produk, stok, harga_beli FROM produk ORDER BY nama_produk ASC");
?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6">Gunakan formulir ini untuk mencatat kerugian jika Nilai Realisasi Bersih (NRV) suatu produk lebih rendah dari biaya perolehannya, atau sebaliknya melakukan <b>pemulihan</b> (reversal) jika nilai barang naik kembali sesuai SAK EP Bab 13.</p>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/produk_stok/proses_penurunan_nilai.php" method="POST" id="form-penurunan-nilai">
            <?php csrfInput(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Pilih Produk</span></label>
                    <select name="produk_id" id="pilihan-produk" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($produk_list as $produk): ?>
                        <option value="<?= $produk['id'] ?>" data-harga-beli="<?= $produk['harga_beli'] ?>" data-stok="<?= $produk['stok'] ?>"><?= htmlspecialchars($produk['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Jenis Penyesuaian</span></label>
                    <select name="jenis_penyesuaian" id="jenis_penyesuaian" class="select select-bordered" required>
                        <option value="penurunan">Pencatatan Penurunan Nilai (Loss)</option>
                        <option value="pemulihan">Pemulihan Penurunan Nilai (Reversal)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Biaya Perolehan per Unit (Rp)</span></label>
                    <input type="text" id="harga-beli-display" class="input input-bordered" readonly>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Stok Saat Ini</span></label>
                    <input type="text" id="stok-display" class="input input-bordered" readonly>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">NRV per Unit (Rp)</span></label>
                    <input type="text" name="nrv_per_unit" id="nrv-input" class="input input-bordered format-rupiah" required>
                </div>
            </div>

            <div class="divider my-6"></div>

            <div class="stats shadow">
                <div class="stat">
                    <div class="stat-title" id="judul-total">Total Kerugian Penurunan Nilai</div>
                    <div class="stat-value" id="total-kerugian-display">Rp 0</div>
                    <div class="stat-desc" id="desc-total">Akan dijurnal sebagai beban</div>
                </div>
            </div>

            <input type="hidden" name="total_penyesuaian" id="total-kerugian-input" value="0">
            
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=produk_stok/lihat_stok" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn-simpan" disabled>Simpan Jurnal Penyesuaian</button>
            </div>
        </form>
    </div>
</div>