<?php
$judulHalaman = 'Form Transaksi Penjualan Toko';

// Ambil data untuk pre-load dropdown agar tampil langsung tanpa AJAX
$anggota_list = query("SELECT id, nama_lengkap, alamat, telepon FROM anggota WHERE status_keanggotaan = 'Aktif' ORDER BY nama_lengkap ASC");
$produk_list = query("SELECT p1.*, p2.stok as stok_induk FROM produk p1 LEFT JOIN produk p2 ON p1.id_produk_induk = p2.id WHERE p1.status_aktif = 1 ORDER BY p1.nama_produk ASC");

// Ambil daftar rekening bank dari daftar akun untuk QRIS/Transfer
$rekening_bank = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kode_akun != ?", [$_PENGATURAN['akun_map_kas'] ?? '1.1.01.01']);
?>
<?php  ?>
<style>
.ts-control > input::placeholder {
    color: #a6adbb;
}
</style>
<h1 class="text-3xl font-bold mb-6">Transaksi Penjualan Baru (Kasir)</h1>
<form action="<?= BASE_URL ?>sistem/proses/transaksi/proses_penjualan.php" method="POST" id="form-penjualan">
    <?php csrfInput(); ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title border-b pb-2 mb-4"><i class="fas fa-shopping-cart mr-2 text-primary"></i>Daftar Item Keranjang</h2>
                    <div class="form-control mb-4 bg-primary/10 p-4 rounded-lg border-2 border-primary/40">
                        <label class="label py-1"><span class="label-text font-bold text-primary"><i class="fas fa-barcode mr-1"></i>Scan Barcode</span><span class="label-text-alt">Fokus otomatis &middot; F3 untuk kembali ke sini</span></label>
                        <div class="join w-full">
                            <input type="text" id="scan-barcode" class="input input-bordered input-lg join-item w-full font-mono text-xl" placeholder="Scan barcode / ketik kode barang lalu Enter" autocomplete="off" autofocus>
                            <button type="button" id="btn-scan-fokus" class="btn btn-primary join-item"><i class="fas fa-crosshairs"></i></button>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-base-200 p-4 rounded-lg border border-base-300">
                        <div class="form-control md:col-span-2">
                            <label class="label"><span class="label-text font-bold">Cari Produk (Nama / Kode / Barcode)</span></label>
                            <select id="pilihan-produk" placeholder="Ketik untuk mencari produk...">
                                <option value="">-- Pilih Barang --</option>
                                <?php foreach($produk_list as $p): ?>
                                    <option value="<?= $p['id'] ?>" 
                                        data-harga="<?= $p['harga_jual'] ?>" 
                                        data-stok="<?= $p['stok'] ?>" 
                                        data-satuan="<?= htmlspecialchars($p['satuan']) ?>"
                                        data-kode="<?= htmlspecialchars($p['kode_produk']) ?>"
                                        data-barcode="<?= htmlspecialchars($p['barcode'] ?? '') ?>"
                                        data-induk="<?= $p['id_produk_induk'] ?>"
                                        data-stok-induk="<?= $p['stok_induk'] ?>"
                                        data-konversi="<?= $p['kuantitas_konversi'] ?>"
                                        <?= ($p['stok'] <= 0 && empty($p['id_produk_induk'])) ? 'disabled' : '' ?>
                                    >
                                        <?= htmlspecialchars($p['nama_produk']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Jumlah (Qty)</span></label>
                            <input type="number" id="kuantitas-produk" placeholder="Jumlah" class="input input-bordered text-center font-bold" min="1" value="1">
                        </div>
                        <div class="form-control">
                            <button type="button" id="btn-tambah-item" class="btn btn-secondary w-full shadow-md"><i class="fas fa-plus mr-2"></i>Tambah</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto mt-4 rounded-lg border border-base-200 shadow-sm">
                        <table class="table w-full">
                            <thead class="bg-base-200">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center w-24">Qty</th>
                                    <th class="text-right w-32">Harga Satuan</th>
                                    <th class="text-right w-32">Subtotal</th>
                                    <th class="text-center w-16">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="keranjang-penjualan">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card bg-base-100 shadow-xl mb-6 border-t-4 border-info">
                <div class="card-body p-4">
                    <h2 class="card-title text-xl mb-2"><i class="fas fa-file-invoice mr-2 text-info"></i>Data Transaksi</h2>
                    <div class="form-control mb-2">
                        <label class="label"><span class="label-text text-xs font-bold">Tanggal Pembelian</span></label>
                        <input type="date" name="tanggal_penjualan" class="input input-sm input-bordered" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="divider my-2">Identitas Pembeli</div>
                    
                    <div class="form-control mb-2">
                        <label class="label"><span class="label-text text-xs">Pelanggan Terdaftar (Member)</span></label>
                        <select name="anggota_id" id="pilihan-anggota" placeholder="Ketik untuk cari anggota...">
                            <option value="">-- Pelanggan Umum (Non-Anggota) --</option>
                            <?php foreach($anggota_list as $a): ?>
                                <option value="<?= $a['id'] ?>" data-detail="<?= htmlspecialchars($a['telepon'] . ' - ' . substr($a['alamat'], 0, 30)) ?>"><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-control mb-2" id="pelanggan-umum-div">
                        <label class="label"><span class="label-text text-xs">Nama Pembeli Umum (Opsional)</span></label>
                        <input type="text" name="nama_pelanggan_umum" id="nama_pelanggan_umum" placeholder="Ketik jika perlu nama..." class="input input-sm input-bordered">
                    </div>
                    
                    <div class="divider my-2">Pembayaran</div>
                    
                     <div class="form-control mb-2">
                        <label class="label"><span class="label-text text-xs font-bold text-primary">Metode Pelunasan</span></label>
                        <select name="metode_pembayaran" id="metode-pembayaran" class="select select-sm select-bordered font-bold" required>
                            <option value="Tunai">Uang Tunai (Kasir)</option>
                            <option value="Transfer">Transfer / QRIS (Bank)</option>
                            <option value="Kredit">Kredit / Kasbon (Tempo)</option>
                        </select>
                    </div>

                    <!-- Div Pilihan Rekening (Muncul jika metode Transfer/QRIS) -->
                    <div class="form-control mb-2 hidden bg-info/10 p-3 rounded border border-info/30" id="div-rekening-bank">
                        <label class="label pt-0"><span class="label-text text-xs font-bold text-info">Pilih Rekening Tujuan (Penerima)</span></label>
                        <select name="kode_akun_bank" id="kode_akun_bank" class="select select-sm select-bordered w-full">
                            <?php foreach($rekening_bank as $rb): ?>
                                <option value="<?= $rb['kode_akun'] ?>"><?= htmlspecialchars($rb['nama_akun']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Div Jatuh Tempo (Muncul jika metode Kredit) -->
                    <div class="form-control mb-2 hidden bg-error/10 p-3 rounded border border-error/30" id="div-jatuh-tempo">
                        <label class="label pt-0"><span class="label-text text-xs font-bold text-error">Tanggal Jatuh Tempo Pelunasan</span></label>
                        <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="input input-sm input-bordered w-full" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>

                </div>
            </div>

            <div class="card bg-neutral text-neutral-content shadow-2xl">
                <div class="card-body p-6 items-end">
                    <div class="w-full text-right">
                        <div class="text-sm font-bold opacity-70 mb-1">TOTAL BELANJA</div>
                        <div class="text-xl font-bold mb-2 text-warning">Rp <span id="total-belanja-display">0</span></div>
                        <input type="hidden" name="total" id="total-belanja-input" value="0">
                        
                        <div class="form-control mt-2 mb-4 text-left text-neutral-content">
                            <label class="label pb-1"><span class="label-text text-neutral-content text-xs font-bold">Potongan/Diskon Khusus (Rp)</span></label>
                            <input type="text" name="diskon" id="diskon-input" class="input input-sm input-bordered bg-neutral-focus text-white border-neutral-content/30 format-rupiah" placeholder="0">
                        </div>

                        <div class="divider bg-neutral-content/20 h-px my-2"></div>

                        <div class="text-sm font-black text-white mt-2">TOTAL HARUS DIBAYAR</div>
                        <div class="text-4xl font-black text-success mb-6">Rp <span id="total-bersih-display">0</span></div>
                        
                        <div class="form-control mt-2 text-left" id="div-uang-dibayar">
                            <label class="label pb-1"><span class="label-text text-white font-bold">Uang Diterima dari Pelanggan</span></label>
                            <div class="join w-full">
                                <span class="join-item pointer-events-none inline-flex items-center justify-center bg-base-200 px-4 text-xl font-bold text-base-content">Rp</span>
                                <input type="text" id="uang-dibayar-input" class="input input-lg input-bordered join-item w-full font-black text-2xl text-right text-base-content" placeholder="0">
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mt-4 p-3 bg-neutral-focus rounded-lg border border-neutral-content/20" id="div-kembalian">
                            <span class="text-sm font-bold opacity-80">Kembalian Kasir:</span>
                            <span class="text-2xl font-bold">Rp <span id="kembalian-display">0</span></span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg mt-6 w-full shadow-lg text-white">Simpan Cetak (Enter)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php  ?>