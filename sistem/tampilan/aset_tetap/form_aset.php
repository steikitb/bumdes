<?php
$dataAset = ['id' => '', 'nama_aset' => '', 'kategori_aset' => 'Peralatan dan Mesin', 'tanggal_perolehan' => date('Y-m-d'), 'harga_perolehan' => '', 'masa_manfaat_tahun' => '', 'nilai_residu' => '0'];
$judulForm = 'Tambah Aset / Inventaris Tetap';
$aksiForm = 'sistem/proses/aset_tetap/tambah_aset.php';
$isEdit = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idAset = (int)$_GET['id'];
    $data = query("SELECT * FROM aset_tetap WHERE id = ?", [$idAset], 'i');
    if (!empty($data)) {
        $dataAset = $data[0];
        $judulForm = 'Edit Aset Tetap';
        $aksiForm = 'sistem/proses/aset_tetap/edit_aset.php';
        $isEdit = true;
    }
}
$judulHalaman = $judulForm;
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>
<div class="card bg-base-100 shadow-xl border-t-4 border-primary">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= $dataAset['id'] ?>">
            
            <?php if (!$isEdit): ?>
            <div class="form-control mb-6 md:col-span-full bg-base-200 p-4 rounded-lg border border-base-300">
                <label class="label"><span class="label-text font-bold text-primary">Status Perolehan (Akuntansi)</span></label>
                <div class="flex flex-col md:flex-row gap-4">
                    <label class="cursor-pointer flex items-center gap-3 bg-base-100 p-3 rounded-lg shadow-sm w-full">
                        <input type="radio" name="asal_perolehan" value="baru" class="radio radio-primary" checked onclick="document.getElementById('info_aset_lama').classList.add('hidden')">
                        <div>
                            <span class="font-bold block">Beli Baru (Transaksi)</span>
                            <span class="text-xs opacity-70">Akan membuat Jurnal Pengeluaran Uang Kas sebesar Harga Perolehan.</span>
                        </div>
                    </label>
                    <label class="cursor-pointer flex items-center gap-3 bg-base-100 p-3 rounded-lg shadow-sm w-full">
                        <input type="radio" name="asal_perolehan" value="lama" class="radio radio-primary" onclick="document.getElementById('info_aset_lama').classList.remove('hidden')">
                        <div>
                            <span class="font-bold block text-warning">Aset Lama (Setup Awal / Migrasi)</span>
                            <span class="text-xs opacity-70">Hanya mencatat daftar barang. Jurnal keuangan harus dimasukkan manual di menu Migrasi.</span>
                        </div>
                    </label>
                </div>
                <div id="info_aset_lama" class="hidden mt-3 alert alert-warning shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-sm">Anda memilih "Aset Lama". Pastikan total Harga Perolehan (akumulasi seluruh aset) dan Akumulasi Penyusutannya diinput dengan seimbang di menu <b>Migrasi Saldo Awal Neraca</b>. Sistem akan melanjutkan penyusutan pada bulan ini secara normal.</span>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nama Aset / Proyek / Lisensi</span></label>
                    <input type="text" name="nama_aset" placeholder="Contoh: Komputer Kantor, Software POS" class="input input-bordered" required value="<?= htmlspecialchars($dataAset['nama_aset']) ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Kategori Aset (Sesuai COA SAK)</span></label>
                    <select name="kategori_aset" class="select select-bordered" required>
                        <option value="Tanah" <?= ($dataAset['kategori_aset'] == 'Tanah') ? 'selected' : '' ?>>Tanah (Aset Tetap)</option>
                        <option value="Bangunan" <?= ($dataAset['kategori_aset'] == 'Bangunan') ? 'selected' : '' ?>>Bangunan (Aset Tetap)</option>
                        <option value="Peralatan dan Mesin" <?= ($dataAset['kategori_aset'] == 'Peralatan dan Mesin' || $dataAset['kategori_aset'] == 'Mesin dan Kendaraan' || $dataAset['kategori_aset'] == 'Peralatan dan Inventaris') ? 'selected' : '' ?>>Peralatan dan Mesin (Kendaraan, Elektronik, Inventaris)</option>
                        <option value="Aset Tak Berwujud" <?= ($dataAset['kategori_aset'] == 'Aset Tak Berwujud') ? 'selected' : '' ?>>Aset Tak Berwujud (Software/Lisensi/Paten)</option>
                        <option value="Konstruksi Dalam Pengerjaan (KDP)" <?= ($dataAset['kategori_aset'] == 'Konstruksi Dalam Pengerjaan (KDP)') ? 'selected' : '' ?>>Konstruksi Dalam Pengerjaan (KDP)</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                 <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Perolehan / Mulai</span></label>
                    <input type="date" name="tanggal_perolehan" class="input input-bordered" required value="<?= htmlspecialchars($dataAset['tanggal_perolehan']) ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold">Harga Perolehan / Biaya Total (Rp)</span></label>
                    <input type="text" name="harga_perolehan" placeholder="0" class="input input-bordered format-rupiah" required value="<?= htmlspecialchars($dataAset['harga_perolehan']) ?>">
                </div>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-base-300 pt-4 mt-2">
                 <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Masa Manfaat / Amortisasi (Tahun)</span></label>
                    <input type="number" name="masa_manfaat_tahun" placeholder="Isi 0 jika Tanah/KDP" class="input input-bordered" required value="<?= htmlspecialchars($dataAset['masa_manfaat_tahun']) ?>" min="0">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nilai Residu (Sisa di akhir umur) Rp</span></label>
                    <?php if (MODE_ENTITAS == 'BUMDes'): ?>
                        <input type="text" name="nilai_residu" value="0" class="input input-bordered format-rupiah" readonly>
                        <label class="label"><span class="label-text-alt text-info">Sesuai Kepmendesa 136/2022, penyusutan BUMDes tidak menggunakan nilai residu.</span></label>
                    <?php else: ?>
                        <input type="text" name="nilai_residu" placeholder="0" class="input input-bordered format-rupiah" required value="<?= htmlspecialchars($dataAset['nilai_residu']) ?>">
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=aset_tetap/lihat_aset" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Master Aset</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>