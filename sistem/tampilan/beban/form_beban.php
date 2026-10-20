<?php
$dataBeban = ['id' => '', 'tanggal_beban' => date('Y-m-d'), 'keterangan' => '', 'kategori_beban_id' => '', 'jumlah' => ''];
$judulForm = 'Catat Pengeluaran Beban (Biaya)';
$aksiForm = 'sistem/proses/beban/tambah_beban.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idBeban = (int)$_GET['id'];
    $data = query("SELECT * FROM beban_operasional WHERE id = ?", [$idBeban]);
    if (!empty($data)) {
        $dataBeban = $data[0];
        $judulForm = 'Edit Beban';
        $aksiForm = 'sistem/proses/beban/edit_beban.php';
    }
}
$judulHalaman = $judulForm;
$tahun_berjalan = date('Y', strtotime($dataBeban['tanggal_beban']));

$kategoriOptions = query("SELECT id, nama_kategori FROM kategori_beban ORDER BY nama_kategori ASC");
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>
<p class="mb-4 text-base-content/80">Gunakan form ini untuk mencatat pengeluaran operasional BUMDes/Koperasi (contoh: bayar listrik, air, internet, ATK, sewa, dll).</p>

<div class="card bg-base-100 shadow-xl max-w-3xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= $dataBeban['id'] ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Pengeluaran</span></label>
                    <input type="date" name="tanggal_beban" class="input input-bordered" required value="<?= htmlspecialchars($dataBeban['tanggal_beban']) ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Pilih Kategori Beban</span></label>
                    <select name="kategori_beban_id" id="kategori_beban_id" class="select select-bordered" required>
                        <option value="" disabled <?= empty($dataBeban['kategori_beban_id']) ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                        <?php foreach ($kategoriOptions as $kategori): ?>
                            <option value="<?= $kategori['id'] ?>" <?= ($dataBeban['kategori_beban_id'] == $kategori['id']) ? 'selected' : '' ?>><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Keterangan Singkat</span></label>
                <input type="text" name="keterangan" placeholder="Contoh: Pembayaran Listrik Kantor Bulan Januari" class="input input-bordered" required value="<?= htmlspecialchars($dataBeban['keterangan']) ?>">
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold text-lg">Nominal Pengeluaran Kas (Rp)</span></label>
                <input type="text" name="jumlah" id="jumlah_beban" placeholder="0" class="input input-lg input-bordered format-rupiah text-xl font-bold" required value="<?= htmlspecialchars($dataBeban['jumlah']) ?>">
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=beban/lihat_beban" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>