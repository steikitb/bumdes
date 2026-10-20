<?php
$judulHalaman = 'Form Kategori Beban';
cekAkses('kelola_pengaturan');

$data = ['id' => '', 'nama_kategori' => '', 'kode_akun_terkait' => ''];
$judulForm = 'Tambah Kategori Beban';
$aksiForm = 'sistem/proses/pengaturan/tambah_kategori_beban.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT * FROM kategori_beban WHERE id = ?", [$id], 'i');
    if (!empty($data_q)) {
        $data = $data_q[0];
        $judulForm = 'Edit Kategori Beban';
        $aksiForm = 'sistem/proses/pengaturan/edit_kategori_beban.php';
    }
}
$daftar_akun_beban = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kelompok_akun LIKE 'Beban%' ORDER BY kode_akun ASC");
?>

<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>
<p class="mb-6 text-base-content/80">Atur kategori biaya operasional. Kategori ini akan dipilih saat mencatat pengeluaran harian dan dipetakan ke akun yang benar di buku besar.</p>

<div class="card bg-base-100 shadow-xl max-w-2xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">
            <input type="hidden" name="anggaran_tahunan" value="0">
            
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Kategori Beban</span></label>
                <input type="text" name="nama_kategori" placeholder="Contoh: Beban Listrik, Air, dan Telepon" class="input input-bordered" required value="<?= htmlspecialchars($data['nama_kategori']) ?>">
            </div>
            
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Akun Akuntansi Terkait (Pemetaan)</span></label>
                <select name="kode_akun_terkait" class="select select-akun" required>
                    <option value="">-- Pilih Akun Beban --</option>
                    <?php foreach ($daftar_akun_beban as $akun): ?>
                        <option value="<?= $akun['kode_akun'] ?>" <?= ($data['kode_akun_terkait'] == $akun['kode_akun']) ? 'selected' : '' ?>><?= $akun['kode_akun'] ?> - <?= htmlspecialchars($akun['nama_akun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex justify-end gap-2 mt-8 border-t border-base-200 pt-6">
                <a href="index.php?halaman=pengaturan/lihat_kategori_beban" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>