<?php
cekAkses('kelola_buku_administrasi');
$data = ['id' => '', 'tanggal' => date('Y-m-d'), 'anggota_id' => '', 'isi_saran' => '', 'tanggapan_pengurus' => '', 'ditanggapi_oleh' => '', 'tanggal_tanggapan' => ''];
$judulForm = 'Tambah Saran Anggota';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_saran_anggota.php';
$isEdit = false;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_saran_anggota WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit/Tanggapi Saran Anggota';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_saran_anggota.php';
        $isEdit = true;
    }
}
$judulHalaman = $judulForm;
$anggota_list = query("SELECT id, nama_lengkap FROM anggota ORDER BY nama_lengkap ASC");
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
                <div class="form-control"><label class="label"><span class="label-text">Nama Anggota</span></label>
                    <select name="anggota_id" class="select select-bordered" required>
                        <option value="">-- Pilih Anggota --</option>
                        <?php foreach($anggota_list as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($data['anggota_id'] == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Isi Saran</span></label><textarea name="isi_saran" class="textarea textarea-bordered" required><?= htmlspecialchars($data['isi_saran']) ?></textarea></div>

            <?php if ($isEdit): ?>
            <div class="divider">Tanggapan Pengurus</div>
            <div class="form-control mt-4"><label class="label"><span class="label-text">Isi Tanggapan</span></label><textarea name="tanggapan_pengurus" class="textarea textarea-bordered"><?= htmlspecialchars($data['tanggapan_pengurus']) ?></textarea></div>
            <?php endif; ?>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_saran_anggota" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>