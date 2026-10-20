<?php
cekAkses('admin');
$daftar_role = daftarRolePengguna();

$isEdit = false;
$dataPengguna = ['id' => '', 'nama_pengguna' => '', 'nama_lengkap' => '', 'role' => 'admin'];

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $idPengguna = (int)$_GET['id'];
    $row = query("SELECT id, nama_pengguna, nama_lengkap, role FROM pengguna WHERE id = ?", [$idPengguna], 'i');
    if (!empty($row)) {
        $dataPengguna = $row[0];
        $isEdit = true;
    }
}

$judulForm = $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna';
$aksiForm = $isEdit ? 'sistem/proses/pengguna/edit_pengguna.php' : 'sistem/proses/pengguna/tambah_pengguna.php';
$judulHalaman = $judulForm;
$self_id = (int)($_SESSION['user_data']['id'] ?? 0);
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold"><?= $judulForm ?></h1>
    <a href="index.php?halaman=pengguna/lihat_pengguna" class="btn btn-ghost">&larr; Kembali</a>
</div>

<div class="card bg-base-100 shadow-xl border-t-4 border-primary max-w-2xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST" autocomplete="off">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= (int)$dataPengguna['id'] ?>">

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">Username</span></label>
                <input type="text" name="nama_pengguna" class="input input-bordered font-mono" required
                       minlength="3" maxlength="30" pattern="[A-Za-z0-9._-]{3,30}"
                       value="<?= htmlspecialchars($dataPengguna['nama_pengguna']) ?>"
                       placeholder="contoh: kasir1">
                <label class="label"><span class="label-text-alt">3-30 karakter, boleh huruf, angka, titik, garis bawah, strip. Harus unik.</span></label>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">Nama Lengkap</span></label>
                <input type="text" name="nama_lengkap" class="input input-bordered" required maxlength="100"
                       value="<?= htmlspecialchars($dataPengguna['nama_lengkap']) ?>"
                       placeholder="Nama yang tampil di menu">
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">Role / Hak Akses</span></label>
                <select name="role" class="select select-bordered" required <?= ($isEdit && (int)$dataPengguna['id'] === $self_id) ? 'disabled' : '' ?>>
                    <?php foreach ($daftar_role as $kode => $label): ?>
                        <option value="<?= htmlspecialchars($kode) ?>" <?= $dataPengguna['role'] === $kode ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isEdit && (int)$dataPengguna['id'] === $self_id): ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($dataPengguna['role']) ?>">
                    <label class="label"><span class="label-text-alt text-warning">Role akun sendiri tidak bisa diubah agar tidak terkunci dari sistem.</span></label>
                <?php endif; ?>
            </div>

            <div class="divider"><?= $isEdit ? 'Ganti Kata Sandi' : 'Kata Sandi' ?></div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold"><?= $isEdit ? 'Kata Sandi Baru (opsional)' : 'Kata Sandi' ?></span></label>
                <input type="password" name="kata_sandi" class="input input-bordered" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password"
                       placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' ?>">
                <label class="label"><span class="label-text-alt">Minimal 8 karakter, wajib ada huruf dan angka.</span></label>
            </div>

            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">Konfirmasi Kata Sandi</span></label>
                <input type="password" name="konfirmasi_kata_sandi" class="input input-bordered" minlength="8" autocomplete="new-password"
                       placeholder="Ulangi kata sandi">
            </div>

            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary shadow-md">
                    <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' ?>
                </button>
            </div>
        </form>
    </div>
</div>
