<?php
cekAkses('kelola_buku_administrasi');
$data = ['id' => '', 'tanggal' => date('Y-m-d'), 'nama_pejabat' => '', 'jabatan_dan_kantor' => '', 'isi_anjuran' => '', 'tanggapan_pengurus' => '', 'ditanggapi_oleh' => '', 'tanggal_tanggapan' => ''];
$judulForm = 'Tambah Anjuran Pejabat';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_anjuran_pejabat.php';
$isEdit = false;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_anjuran_pejabat WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit/Tanggapi Anjuran Pejabat';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_anjuran_pejabat.php';
        $isEdit = true;
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
                <div class="form-control"><label class="label"><span class="label-text">Tanggal</span></label><input type="date" name="tanggal" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Nama Pejabat</span></label><input type="text" name="nama_pejabat" class="input input-bordered" required value="<?= htmlspecialchars($data['nama_pejabat']) ?>"></div>
            </div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Jabatan dan Kantor</span></label><input type="text" name="jabatan_dan_kantor" class="input input-bordered" required value="<?= htmlspecialchars($data['jabatan_dan_kantor']) ?>"></div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Isi Anjuran</span></label><textarea name="isi_anjuran" class="textarea textarea-bordered" required><?= htmlspecialchars($data['isi_anjuran']) ?></textarea></div>

            <?php if ($isEdit): ?>
            <div class="divider">Tanggapan Pengurus</div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Isi Tanggapan</span></label><textarea name="tanggapan_pengurus" class="textarea textarea-bordered"><?= htmlspecialchars($data['tanggapan_pengurus']) ?></textarea></div>
            <?php endif; ?>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_anjuran_pejabat" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>