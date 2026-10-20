<?php
$judulHalaman = 'Form Jabatan';
cekAkses('admin');
$data = ['id' => '', 'nama_jabatan' => '', 'gaji_pokok' => '0', 'tunjangan_jabatan' => '0'];
$aksiForm = 'sistem/proses/karyawan/jabatan_tambah.php';
if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM mst_jabatan WHERE id = ?", [$id]);
    if(!empty($data_q)) {
        $data = $data_q[0];
        $aksiForm = 'sistem/proses/karyawan/jabatan_edit.php';
    }
}
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $data['id'] ? 'Edit Jabatan' : 'Tambah Jabatan Baru' ?></h1>
<div class="card bg-base-100 shadow-xl max-w-2xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Jabatan</span></label>
                <input type="text" name="nama_jabatan" class="input input-bordered" required value="<?= htmlspecialchars($data['nama_jabatan']) ?>" placeholder="Contoh: Manager Operasional">
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Default Gaji Pokok (Rp)</span></label>
                <input type="text" name="gaji_pokok" class="input input-bordered format-rupiah" required value="<?= $data['gaji_pokok'] ?>">
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Default Tunjangan Jabatan (Rp)</span></label>
                <input type="text" name="tunjangan_jabatan" class="input input-bordered format-rupiah" required value="<?= $data['tunjangan_jabatan'] ?>">
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <a href="index.php?halaman=karyawan/lihat_jabatan" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Jabatan</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>