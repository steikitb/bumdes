<?php
cekAkses('kelola_buku_administrasi');
$total_pengawas_q = query("SELECT COUNT(id) as total FROM pengguna WHERE role = 'pengawas'");
$total_pengawas = $total_pengawas_q[0]['total'] ?? 0;
$data = ['id' => '', 'tanggal_rapat' => date('Y-m-d'), 'tempat_rapat' => '', 'jenis_rapat' => 'Rapat Rutin', 'jumlah_pengawas_total' => $total_pengawas, 'jumlah_hadir' => '', 'pimpinan_rapat' => '', 'undangan_hadir' => '', 'acara_rapat' => '', 'keputusan_rapat' => ''];
$judulForm = 'Tambah Notulen Rapat Pengawas';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_rapat_pengawas.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_rapat_pengawas WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit Notulen Rapat Pengawas';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_rapat_pengawas.php';
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
                <div class="form-control"><label class="label"><span class="label-text">Tanggal Rapat</span></label><input type="date" name="tanggal_rapat" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal_rapat']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Tempat Rapat</span></label><input type="text" name="tempat_rapat" class="input input-bordered" required value="<?= htmlspecialchars($data['tempat_rapat']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Jenis Rapat</span></label><input type="text" name="jenis_rapat" class="input input-bordered" required value="<?= htmlspecialchars($data['jenis_rapat']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Jumlah Pengawas Total</span></label><input type="number" name="jumlah_pengawas_total" class="input input-bordered" required value="<?= htmlspecialchars($data['jumlah_pengawas_total']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Jumlah Pengawas Hadir</span></label><input type="number" name="jumlah_hadir" class="input input-bordered" required value="<?= htmlspecialchars($data['jumlah_hadir']) ?>"></div>
                <div class="form-control"><label class="label"><span class="label-text">Pimpinan Rapat</span></label><input type="text" name="pimpinan_rapat" class="input input-bordered" required value="<?= htmlspecialchars($data['pimpinan_rapat']) ?>"></div>
            </div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Undangan Yang Hadir (selain pengawas)</span></label><textarea name="undangan_hadir" class="textarea textarea-bordered"><?= htmlspecialchars($data['undangan_hadir']) ?></textarea></div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Acara Rapat</span></label><textarea name="acara_rapat" class="textarea textarea-bordered" required><?= htmlspecialchars($data['acara_rapat']) ?></textarea></div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Keputusan Rapat</span></label><textarea name="keputusan_rapat" class="textarea textarea-bordered" required><?= htmlspecialchars($data['keputusan_rapat']) ?></textarea></div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_rapat_pengawas" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>