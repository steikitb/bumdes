<?php
cekAkses('kelola_buku_administrasi');
$data = ['id' => '', 'tanggal_masuk' => date('Y-m-d'), 'nomor_surat' => '', 'tanggal_surat' => date('Y-m-d'), 'perihal' => '', 'asal_surat' => ''];
$judulForm = 'Tambah Data Surat Masuk';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_surat_masuk.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_surat_masuk WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit Data Surat Masuk';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_surat_masuk.php';
    }
}
$judulHalaman = $judulForm;
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Masuk</span></label><input type="date" name="tanggal_masuk" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal_masuk']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Surat</span></label><input type="date" name="tanggal_surat" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal_surat']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Nomor Surat</span></label><input type="text" name="nomor_surat" class="input input-bordered" required value="<?= htmlspecialchars($data['nomor_surat']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Asal Surat</span></label><input type="text" name="asal_surat" class="input input-bordered" required value="<?= htmlspecialchars($data['asal_surat']) ?>"></div>
            </div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Perihal</span></label><textarea name="perihal" class="textarea textarea-bordered" required><?= htmlspecialchars($data['perihal']) ?></textarea></div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_surat_masuk" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>