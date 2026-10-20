<?php
$judulHalaman = 'Pecah Barang (Konversi Stok)';
cekAkses('kelola_produk_stok');

$produk_induk_list = query("SELECT id, kode_produk, nama_produk, stok, satuan FROM produk WHERE id IN (SELECT DISTINCT id_produk_induk FROM produk WHERE id_produk_induk IS NOT NULL)");
$produk_turunan_list = query("SELECT id, id_produk_induk, nama_produk, satuan, kuantitas_konversi FROM produk WHERE id_produk_induk IS NOT NULL");
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Gunakan fitur ini untuk membuka kemasan besar (Induk) menjadi satuan yang lebih kecil (Turunan). Sistem akan secara otomatis mengurangi stok induk, menambah stok turunan, menghitung ulang HPP, dan membuat jurnal persediaan.</p>

<?php if (isset($_GET['status']) && $_GET['status'] === 'gagal'): ?>
<div class="alert alert-error shadow-lg mb-6">
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <div>
            <h3 class="font-bold">Pecah barang gagal!</h3>
            <div class="text-sm mt-1"><?= htmlspecialchars($_GET['error'] ?? 'Terjadi kesalahan sistem.') ?></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($produk_induk_list)): ?>
<div class="alert alert-warning shadow-lg mb-6 border-l-4 border-warning">
    <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <h3 class="font-bold">Tidak ada Produk Induk yang tersedia!</h3>
            <div class="text-sm mt-1">Anda belum mengatur Relasi Induk-Turunan. Silakan ke menu <a href="index.php?halaman=produk_stok/lihat_stok" class="link text-primary font-bold">Data Barang & Stok</a>, Edit/Tambah barang eceran Anda, lalu isi bagian <b>Relasi Konversi Kemasan</b> untuk menghubungkannya ke barang grosir.</div>
        </div>
    </div>
</div>
<?php endif; ?>

<div id="data-turunan-container" data-turunan='<?= htmlspecialchars(json_encode($produk_turunan_list), ENT_QUOTES, 'UTF-8') ?>' class="hidden"></div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/produk_stok/proses_pecah_barang.php" method="POST">
            <?php csrfInput(); ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">1. Pilih Produk Induk (yang akan dibongkar)</span></label>
                <select name="id_induk" id="pilihan-induk" required <?= empty($produk_induk_list) ? 'disabled' : '' ?>>
                    <option value="">-- Pilih Induk --</option>
                    <?php foreach($produk_induk_list as $induk): ?>
                    <option value="<?= $induk['id'] ?>" data-stok="<?= $induk['stok'] ?>" data-satuan="<?= htmlspecialchars($induk['satuan']) ?>"><?= htmlspecialchars($induk['nama_produk']) ?> (Stok: <?= $induk['stok'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">2. Pilih Produk Turunan (hasil pecahan)</span></label>
                    <select name="id_turunan" id="pilihan-turunan" class="select select-bordered" required <?= empty($produk_induk_list) ? 'disabled' : '' ?>>
                        <option value="">-- Pilih produk di atas dahulu --</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">3. Jumlah Induk yang Akan Dipecah</span></label>
                    <div class="join w-full">
                        <input type="number" name="jumlah_dipecah" id="jumlah-dipecah" class="input input-bordered join-item w-full" required min="1" placeholder="Berapa karung/dus yang dibuka?" <?= empty($produk_induk_list) ? 'disabled' : '' ?>>
                        <span class="join-item btn pointer-events-none" id="label-satuan-induk">...</span>
                    </div>
                </div>
            </div>

            <div class="divider">Hasil Konversi</div>

            <div class="stats shadow w-full">
                <div class="stat">
                    <div class="stat-title">Induk Berkurang</div>
                    <div class="stat-value text-error" id="induk-kurang-display">0</div>
                </div>
                <div class="stat">
                    <div class="stat-title">Turunan Bertambah</div>
                    <div class="stat-value text-success" id="turunan-tambah-display">0</div>
                    <div class="stat-desc" id="konversi-desc-display">Rasio: -</div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=produk_stok/lihat_stok" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn-proses" disabled onclick="return confirm('Yakin ingin memecah barang ini? Aksi ini akan mengubah stok kedua barang secara permanen.')">Proses Pecah Barang</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>