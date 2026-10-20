<?php
cekAkses('kelola_buku_administrasi');
$data = ['id' => '', 'tanggal' => date('Y-m-d\TH:i'), 'nama_tamu' => '', 'alamat' => '', 'bertemu_dengan' => '', 'keperluan' => ''];
$judulForm = 'Tambah Data Buku Tamu';
$aksiForm = 'sistem/proses/administrasi_laporan/tambah_buku_tamu.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM adm_buku_tamu WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $data['tanggal'] = date('Y-m-d\TH:i', strtotime($data['tanggal']));
        $judulForm = 'Edit Data Buku Tamu';
        $aksiForm = 'sistem/proses/administrasi_laporan/edit_buku_tamu.php';
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
            <div class="form-control mb-4"><label class="label"><span class="label-text">Tanggal & Waktu</span></label><input type="datetime-local" name="tanggal" class="input input-bordered" required value="<?= htmlspecialchars($data['tanggal']) ?>"></div>
            <div class="form-control mb-4"><label class="label"><span class="label-text">Nama Tamu</span></label><input type="text" name="nama_tamu" class="input input-bordered" required value="<?= htmlspecialchars($data['nama_tamu']) ?>"></div>
            <div class="form-control mb-4"><label class="label"><span class="label-text">Alamat</span></label><textarea name="alamat" class="textarea textarea-bordered" required><?= htmlspecialchars($data['alamat']) ?></textarea></div>
            <div class="form-control mb-4"><label class="label"><span class="label-text">Bertemu Dengan</span></label><input type="text" name="bertemu_dengan" class="input input-bordered" required value="<?= htmlspecialchars($data['bertemu_dengan']) ?>"></div>
            <div class="form-control mb-4"><label class="label"><span class="label-text">Keperluan</span></label><textarea name="keperluan" class="textarea textarea-bordered" required><?= htmlspecialchars($data['keperluan']) ?></textarea></div>
            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=administrasi_laporan/lihat_buku_tamu" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>