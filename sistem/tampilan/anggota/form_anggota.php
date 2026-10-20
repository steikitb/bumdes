<?php
$dataAnggota = ['id' => '', 'nama_lengkap' => '', 'alamat' => '', 'telepon' => '', 'tanggal_bergabung' => date('Y-m-d'), 'tanggal_lahir' => '', 'jenis_kelamin' => '', 'pekerjaan' => '', 'jenis_entitas' => 'Individu'];
$judulForm = 'Tambah Anggota / Penyerta Modal';
$aksiForm = 'sistem/proses/anggota/tambah_anggota.php';
$isEdit = false;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idAnggota = (int)$_GET['id'];
    $data = query("SELECT * FROM anggota WHERE id = ?", [$idAnggota], 'i');
    if (!empty($data)) {
        $dataAnggota = $data[0];
        $judulForm = 'Edit Anggota / Penyerta Modal';
        $aksiForm = 'sistem/proses/anggota/edit_anggota.php';
        $isEdit = true;
    }
}
$judulHalaman = $judulForm;
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($dataAnggota['id'] ?? '') ?>">
            
            <div class="form-control mb-4 md:col-span-full">
                <label class="label"><span class="label-text font-bold">Kategori / Jenis Entitas</span></label>
                <div class="flex gap-4 p-3 bg-base-200 rounded-lg">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="jenis_entitas" value="Individu" class="radio radio-primary" <?= ($dataAnggota['jenis_entitas'] ?? 'Individu') == 'Individu' ? 'checked' : '' ?> onclick="document.getElementById('fields_individu').classList.remove('hidden')">
                        <span>Individu / Perorangan</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="jenis_entitas" value="Institusi" class="radio radio-primary" <?= ($dataAnggota['jenis_entitas'] ?? '') == 'Institusi' ? 'checked' : '' ?> onclick="document.getElementById('fields_individu').classList.add('hidden')">
                        <span>Institusi / Pemerintah Desa / Badan Usaha</span>
                    </label>
                </div>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Nama Lengkap / Institusi</span></label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama" class="input input-bordered" required value="<?= htmlspecialchars($dataAnggota['nama_lengkap'] ?? '') ?>">
            </div>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Alamat</span></label>
                <textarea name="alamat" class="textarea textarea-bordered" placeholder="Masukkan alamat" required><?= htmlspecialchars($dataAnggota['alamat'] ?? '') ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Nomor Telepon</span></label>
                    <input type="text" name="telepon" placeholder="Contoh: 08123456789" class="input input-bordered" required value="<?= htmlspecialchars($dataAnggota['telepon'] ?? '') ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Registrasi / Bergabung</span></label>
                    <input type="date" name="tanggal_bergabung" class="input input-bordered" required value="<?= htmlspecialchars($dataAnggota['tanggal_bergabung'] ?? '') ?>">
                </div>
            </div>

            <!-- Fields Specific to Individu -->
            <div id="fields_individu" class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-base-300 mt-2 pt-4 <?= ($dataAnggota['jenis_entitas'] == 'Institusi') ? 'hidden' : '' ?>">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Lahir</span></label>
                    <input type="date" name="tanggal_lahir" class="input input-bordered" value="<?= htmlspecialchars($dataAnggota['tanggal_lahir'] ?? '') ?>">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Jenis Kelamin</span></label>
                    <select name="jenis_kelamin" class="select select-bordered">
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" <?= ($dataAnggota['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= ($dataAnggota['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Pekerjaan</span></label>
                    <input type="text" name="pekerjaan" placeholder="Contoh: Wiraswasta" class="input input-bordered" value="<?= htmlspecialchars($dataAnggota['pekerjaan'] ?? '') ?>">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-8 border-t border-base-300 pt-6">
                <a href="index.php?halaman=anggota/lihat_anggota" class="btn">Batal</a>
                <?php if (!$isEdit): ?>
                    <button type="submit" name="submit_action" value="save_and_new" class="btn btn-secondary">Simpan & Tambah Lagi</button>
                <?php endif; ?>
                <button type="submit" name="submit_action" value="save" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>