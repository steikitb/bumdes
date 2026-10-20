<?php
cekAkses('kelola_pemasok');
$data = ['id' => '', 'nama_pemasok' => '', 'alamat' => '', 'telepon' => '', 'email' => '', 'keterangan' => ''];
$judulForm = 'Tambah Pemasok';
$aksiForm = 'sistem/proses/pemasok/tambah_pemasok.php';
$isEdit = false;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM pemasok WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit Pemasok';
        $aksiForm = 'sistem/proses/pemasok/edit_pemasok.php';
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
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Pemasok</span></label>
                <input type="text" name="nama_pemasok" placeholder="Masukkan nama pemasok" class="input input-bordered" required value="<?= htmlspecialchars($data['nama_pemasok']) ?>">
            </div>
            
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Keterangan Pemasok (Opsional)</span></label>
                <input type="text" name="keterangan" placeholder="Contoh: Distributor Beras, Grosir ATK, dll." class="input input-bordered" value="<?= htmlspecialchars($data['keterangan'] ?? '') ?>">
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Alamat Lengkap</span></label>
                <textarea name="alamat" class="textarea textarea-bordered" placeholder="Alamat lengkap pemasok"><?= htmlspecialchars($data['alamat']) ?></textarea>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nomor Telepon</span></label>
                    <input type="text" name="telepon" placeholder="Nomor telepon yang bisa dihubungi" class="input input-bordered" value="<?= htmlspecialchars($data['telepon']) ?>">
                </div>
                 <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" placeholder="Alamat email pemasok" class="input input-bordered" value="<?= htmlspecialchars($data['email']) ?>">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-8 border-t border-base-300 pt-6">
                <a href="index.php?halaman=pemasok/lihat_pemasok" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>