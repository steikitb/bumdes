<?php
$judulHalaman = 'Form Pembelian Persediaan & Perlengkapan';
$produk = query("SELECT id, kode_produk, barcode, nama_produk, harga_beli, jenis_barang FROM produk ORDER BY nama_produk");
$pemasok_list = query("SELECT id, nama_pemasok, keterangan FROM pemasok WHERE status_aktif = 1 ORDER BY nama_pemasok ASC");

// Ambil daftar rekening bank dari daftar akun untuk QRIS/Transfer
$rekening_bank = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kode_akun != ?", [$_PENGATURAN['akun_map_kas'] ?? '1.1.01.01']);
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6">Catat Pembelian Persediaan (Stok) Baru</h1>

<form action="<?= BASE_URL ?>sistem/proses/transaksi/proses_pembelian.php" method="POST" id="form-pembelian">
    <?php csrfInput(); ?>
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Tanggal Pembelian</span></label>
                    <input type="date" name="tanggal_pembelian" class="input input-bordered" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Pilih Pemasok (Supplier)</span></label>
                    <div class="join w-full">
                        <div class="w-full">
                            <select name="pemasok_id" id="pilihan-pemasok" required>
                                <option value="">-- Pilih Pemasok --</option>
                                <?php foreach($pemasok_list as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['nama_pemasok']) ?> 
                                    <?= !empty($p['keterangan']) ? '('.htmlspecialchars($p['keterangan']).')' : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" onclick="modal_tambah_pemasok.showModal()" class="btn btn-secondary join-item" title="Tambah Pemasok Baru">+</button>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Nomor Faktur dari Pemasok</span></label>
                    <input type="text" name="nomor_faktur_pemasok" placeholder="Nomor faktur dari pemasok" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold text-primary">Metode Pembayaran</span></label>
                    <select name="metode_pembayaran" id="metode-pembayaran-beli" class="select select-bordered font-bold" required>
                        <option value="Kredit">Kredit / Utang Usaha (Tempo)</option>
                        <option value="Tunai">Tunai (Kas Utama)</option>
                        <option value="Transfer">Transfer Bank / QRIS</option>
                    </select>
                </div>

                <!-- Div Pilihan Rekening (Muncul jika metode Transfer/QRIS) -->
                <div class="form-control hidden bg-info/10 p-3 rounded border border-info/30" id="div-rekening-bank-beli">
                    <label class="label pt-0"><span class="label-text text-xs font-bold text-info">Pilih Rekening Sumber Dana</span></label>
                    <select name="kode_akun_bank" id="kode_akun_bank" class="select select-sm select-bordered w-full">
                        <?php foreach($rekening_bank as $rb): ?>
                            <option value="<?= $rb['kode_akun'] ?>"><?= htmlspecialchars($rb['nama_akun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Div Jatuh Tempo (Muncul jika metode Kredit) -->
                <div class="form-control bg-error/10 p-3 rounded border border-error/30" id="div-jatuh-tempo-beli">
                    <label class="label pt-0"><span class="label-text text-xs font-bold text-error">Tanggal Jatuh Tempo Tagihan</span></label>
                    <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="input input-sm input-bordered w-full" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>

            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <h2 class="card-title">Barang yang Dibeli</h2>
            <div class="form-control mb-4 bg-primary/10 p-4 rounded-lg border-2 border-primary/40">
                <label class="label py-1"><span class="label-text font-bold text-primary"><i class="fas fa-barcode mr-1"></i>Scan Barcode (Terima Barang)</span><span class="label-text-alt">Fokus otomatis &middot; F3 untuk kembali ke sini</span></label>
                <div class="join w-full">
                    <input type="text" id="scan-barcode-beli" class="input input-bordered input-lg join-item w-full font-mono text-xl" placeholder="Scan barcode / ketik kode barang lalu Enter" autocomplete="off">
                    <button type="button" id="btn-scan-fokus-beli" class="btn btn-primary join-item"><i class="fas fa-crosshairs"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end bg-base-200 p-4 rounded-lg border border-base-300">
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text font-bold">Pilih Barang dari Master</span></label>
                    <select id="pilihan-produk">
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($produk as $item): ?>
                            <option value="<?= $item['id'] ?>" data-harga="<?= $item['harga_beli'] ?>" data-jenis="<?= htmlspecialchars($item['jenis_barang']) ?>" data-kode="<?= htmlspecialchars($item['kode_produk']) ?>" data-barcode="<?= htmlspecialchars($item['barcode'] ?? '') ?>"><?= htmlspecialchars($item['nama_produk']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Harga Beli Satuan (Rp)</span></label>
                    <input type="text" id="harga-produk" placeholder="Harga" class="input input-bordered format-rupiah">
                    <label class="label"><span class="label-text-alt text-warning">Dapat diubah sesuai nota asli.</span></label>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Jumlah Qty</span></label>
                    <input type="number" id="kuantitas-produk" placeholder="Jumlah" class="input input-bordered" min="1">
                </div>
                <div class="form-control">
                    <button type="button" id="btn-tambah-item" class="btn btn-secondary w-full shadow-md"><i class="fas fa-plus mr-2"></i>Tambahkan ke Daftar Pembelian</button>
                </div>
            </div>
            <div class="overflow-x-auto mt-4 rounded-lg border border-base-200">
                <table class="table w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th>Barang</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="keranjang-pembelian">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card bg-neutral text-neutral-content shadow-2xl">
        <div class="card-body items-end p-6">
            <div class="text-xl font-bold">TOTAL PEMBELIAN:</div>
            <div class="text-4xl font-black text-warning my-2">
                <span>Rp </span><span id="total-pembelian-display">0</span>
            </div>
            <input type="hidden" name="total" id="total-pembelian-input" value="0">
            <button type="submit" class="btn btn-primary btn-lg mt-4 shadow-lg text-white w-full md:w-auto px-12">Simpan Transaksi Pembelian</button>
        </div>
    </div>
</form>

<dialog id="modal_tambah_pemasok" class="modal">
  <div class="modal-box border-t-4 border-secondary">
    <h3 class="font-bold text-lg">Tambah Pemasok Baru Cepat</h3>
    <form id="form-tambah-pemasok" class="py-4">
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Nama Pemasok</span></label>
            <input type="text" name="nama_pemasok" class="input input-bordered" required>
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Keterangan Pemasok</span></label>
            <input type="text" name="keterangan" placeholder="Contoh: Distributor Beras, Grosir ATK, dll." class="input input-bordered">
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Alamat (Opsional)</span></label>
            <textarea name="alamat" class="textarea textarea-bordered"></textarea>
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Telepon (Opsional)</span></label>
            <input type="text" name="telepon" class="input input-bordered">
        </div>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">Email (Opsional)</span></label>
            <input type="email" name="email" class="input input-bordered">
        </div>
        <div class="modal-action">
            <button type="button" class="btn" onclick="modal_tambah_pemasok.close()">Batal</button>
            <button type="submit" class="btn btn-secondary text-white">Simpan Pemasok</button>
        </div>
    </form>
  </div>
</dialog>
<?php  ?>