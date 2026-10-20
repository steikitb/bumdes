<?php
cekAkses('kelola_produk_stok');

$dataProduk = [
    'id' => '', 'kode_produk' => '', 'barcode' => '', 'nama_produk' => '', 'satuan' => '', 
    'harga_beli' => 0, 'harga_jual' => 0, 'stok' => 0, 'stok_minimum' => 10,
    'id_produk_induk' => null, 'kuantitas_konversi' => null, 'jenis_barang' => 'Barang Dagangan'
];

$isEdit = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idProduk = (int)$_GET['id'];
    $data = query("SELECT * FROM produk WHERE id = ?", [$idProduk], 'i');
    if (!empty($data)) {
        $dataProduk = $data[0];
        $isEdit = true;
    }
} else if (isset($_GET['jenis'])) {
    $dataProduk['jenis_barang'] = $_GET['jenis'];
}

$judulForm = $isEdit ? 'Edit Data Barang' : 'Tambah ' . ($dataProduk['jenis_barang'] === 'Perlengkapan' ? 'Perlengkapan' : 'Persediaan');
$aksiForm = $isEdit ? 'sistem/proses/produk_stok/edit_produk.php' : 'sistem/proses/produk_stok/tambah_produk.php';
$judulHalaman = $judulForm;
$jenisUrl = urlencode($dataProduk['jenis_barang']);

$produk_induk_list = query("SELECT id, nama_produk, kode_produk, satuan FROM produk WHERE id != ? AND id_produk_induk IS NULL AND jenis_barang = ? ORDER BY nama_produk", [$dataProduk['id'] ?? 0, $dataProduk['jenis_barang']], 'is');
$isTurunan = !empty($dataProduk['id_produk_induk']);
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>

<div class="card bg-base-100 shadow-xl border-t-4 border-primary">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= $dataProduk['id'] ?>">
            <input type="hidden" name="jenis_barang" value="<?= htmlspecialchars($dataProduk['jenis_barang']) ?>">

            <div class="form-control mb-4 md:col-span-full">
                <label class="label"><span class="label-text font-bold text-primary">Klasifikasi Sistem</span></label>
                <div class="flex gap-4 p-3 bg-base-200 rounded-lg items-center">
                    <span class="badge badge-primary font-bold text-sm px-3 py-3 rounded uppercase">
                        <?= ($dataProduk['jenis_barang'] === 'Perlengkapan') ? 'Perlengkapan / ATK (Habis Pakai)' : 'Persediaan Barang Dagang' ?>
                    </span>
                    <span class="text-xs opacity-70">
                        <?= ($dataProduk['jenis_barang'] === 'Perlengkapan') ? 'Dicatat sebagai Aset Perlengkapan.' : 'Dicatat sebagai Aset Persediaan untuk dijual.' ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Kode Barang</span></label>
                    <div class="join w-full">
                        <input type="text" id="kode_produk" name="kode_produk" placeholder="Kosongkan untuk otomatis" class="input input-bordered join-item w-full" value="<?= htmlspecialchars($dataProduk['kode_produk']) ?>">
                        <button type="button" id="btn-auto-kode" class="btn btn-secondary join-item">Buat Otomatis</button>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Barcode (Scan)</span></label>
                    <div class="join w-full">
                        <input type="text" id="barcode" name="barcode" placeholder="Scan barcode di sini, atau kosongkan" class="input input-bordered join-item w-full font-mono" value="<?= htmlspecialchars($dataProduk['barcode'] ?? '') ?>" autocomplete="off">
                        <button type="button" id="btn-fokus-barcode" class="btn btn-secondary join-item" title="Fokus ke kolom barcode">Scan</button>
                    </div>
                    <label class="label"><span class="label-text-alt">Opsional. Barcode EAN/UPC dari kemasan; kode barang tetap bisa dipakai untuk scan.</span></label>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Nama Barang</span></label>
                    <input type="text" id="nama_produk" name="nama_produk" placeholder="Masukkan nama barang" class="input input-bordered" required value="<?= htmlspecialchars($dataProduk['nama_produk']) ?>">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Harga Beli / Modal Dasar</span></label>
                    <input type="text" id="harga_beli" name="harga_beli" placeholder="0" class="input input-bordered format-rupiah" required value="<?= htmlspecialchars($dataProduk['harga_beli']) ?>" <?= ($isTurunan) ? 'readonly' : '' ?>>
                    <?php if ($isTurunan): ?>
                        <label class="label"><p class="label-text-alt text-info">Harga beli produk turunan dihitung otomatis dari produk induknya.</p></label>
                    <?php endif; ?>
                </div>
                
                <div class="form-control <?= ($dataProduk['jenis_barang'] === 'Perlengkapan') ? 'hidden' : '' ?>">
                    <label class="label"><span class="label-text">Harga Jual (Pengecer)</span></label>
                    <input type="text" name="harga_jual" placeholder="0" class="input input-bordered format-rupiah" value="<?= htmlspecialchars($dataProduk['harga_jual']) ?>">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Stok Fisik Saat Ini</span></label>
                    <input type="number" name="stok" placeholder="0" class="input input-bordered" required value="<?= htmlspecialchars($dataProduk['stok']) ?>" min="0" <?= $isEdit ? 'readonly' : '' ?>>
                    <?php if ($isEdit): ?>
                        <label class="label"><span class="label-text-alt text-warning">Stok terkunci. Gunakan menu <a href="index.php?halaman=produk_stok/lihat_stok&jenis=<?= $jenisUrl ?>" class="link text-primary font-bold">Hitung Ulang Stok</a> di halaman sebelumnya untuk mengubah saldo secara akuntansi (Stok Opname).</span></label>
                    <?php else: ?>
                        <label class="label"><span class="label-text-alt text-info font-bold">PENTING: Saldo Awal (Tanpa Jurnal). Pengisian stok di sini HANYA untuk Setup/Migrasi Pertama Kali. Jika Anda mengisi angka > 0, pastikan Anda juga menyeimbangkannya di form Migrasi Saldo Awal Neraca.</span></label>
                    <?php endif; ?>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Stok Minimum (Peringatan)</span></label>
                    <input type="number" name="stok_minimum" placeholder="10" class="input input-bordered" required value="<?= htmlspecialchars($dataProduk['stok_minimum']) ?>" min="0">
                </div>
                 
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Satuan</span></label>
                    <input type="text" id="satuan" name="satuan" placeholder="Contoh: Pcs, Kg, Box" class="input input-bordered" required value="<?= htmlspecialchars($dataProduk['satuan']) ?>">
                </div>

                <?php if ($dataProduk['jenis_barang'] === 'Barang Dagangan'): ?>
                <div class="divider md:col-span-2 mt-2">Relasi Konversi Kemasan Grosir-Eceran (Opsional)</div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Barang Induk (Kemasan Besar)</span></label>
                    <select name="id_produk_induk" id="id_produk_induk" class="select select-bordered">
                        <option value="">-- Tidak ada (Ini produk mandiri) --</option>
                        <?php foreach($produk_induk_list as $p): ?>
                            <option 
                                value="<?= $p['id'] ?>" 
                                <?= ($dataProduk['id_produk_induk'] == $p['id']) ? 'selected' : '' ?>
                            ><?= htmlspecialchars($p['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="label"><p class="label-text-alt">Pilih jika barang ini adalah hasil pecahan (eceran) dari barang lain di sistem.</p></label>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Rasio Pecahan per Kemasan Besar</span></label>
                    <input type="number" name="kuantitas_konversi" id="kuantitas_konversi" placeholder="Contoh: 12" class="input input-bordered" value="<?= htmlspecialchars($dataProduk['kuantitas_konversi'] ?? '') ?>" min="1">
                    <label class="label"><p class="label-text-alt">Isi berapa jumlah barang eceran ini di dalam satu kemasan besarnya utuh.</p></label>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end gap-2 mt-8 border-t border-base-300 pt-6">
                <a href="index.php?halaman=produk_stok/lihat_stok&jenis=<?= $jenisUrl ?>" class="btn">Kembali ke Daftar</a>
                <button type="submit" class="btn btn-primary shadow-md">Simpan Data Barang</button>
            </div>
        </form>
    </div>
</div>