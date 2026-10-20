<?php
cekAkses('kelola_buku_administrasi');
$data = ['id' => '', 'tanggal' => date('Y-m-d'), 'jenis_kegiatan' => '', 'tempat' => '', 'uraian' => ''];
$judulForm = 'Tambah Catatan Kejadian Penting';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_kejadian_penting.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_kejadian_penting WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit Catatan Kejadian Penting';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_kejadian_penting.php';
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control"><label class="label"><span class="label-text">Tanggal</span></label><input type="date" name="tanggal" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Jenis Kegiatan/Kejadian</span></label><input type="text" name="jenis_kegiatan" class="input input-bordered" required value="<?= htmlspecialchars($data['jenis_kegiatan']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Tempat</span></label><input type="text" name="tempat" class="input input-bordered" required value="<?= htmlspecialchars($data['tempat']) ?>"></div>
            </div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Uraian</span></label><textarea name="uraian" class="textarea textarea-bordered" required><?= htmlspecialchars($data['uraian']) ?></textarea></div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_kejadian_penting" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>