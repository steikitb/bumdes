<?php
$judulHalaman = 'Formulir Pinjaman Diterima';
$aksiForm = '../sistem/proses/pinjaman/tambah_pinjaman.php';
?>
<?php  ?>

<h1 class="text-3xl font-bold mb-6">Catat Pinjaman Baru</h1>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/pinjaman/tambah_pinjaman.php" method="POST">
    <?php csrfInput(); ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Kreditur (Pemberi Pinjaman)</span></label>
                <input type="text" name="nama_kreditur" placeholder="Contoh: Bank ABC" class="input input-bordered" required>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Pinjaman Diterima</span></label>
                    <input type="date" name="tanggal_pinjaman" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Jumlah Pokok Pinjaman (Rp)</span></label>
                    <input type="text" name="jumlah_pokok" placeholder="0" class="input input-bordered format-rupiah" required>
                </div>
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Keterangan (Opsional)</span></label>
                <textarea name="keterangan" class="textarea textarea-bordered" placeholder="Nomor kontrak, tujuan pinjaman, dll."></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=pinjaman/lihat_pinjaman" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php  ?>