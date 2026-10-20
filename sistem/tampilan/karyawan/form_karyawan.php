<?php
$judulHalaman = 'Form Karyawan';
cekAkses('admin');
$jabatan = query("SELECT id, nama_jabatan FROM mst_jabatan ORDER BY nama_jabatan ASC");
$anggota_list = query("SELECT id, nama_lengkap, alamat, telepon FROM anggota WHERE status_keanggotaan = 'Aktif' ORDER BY nama_lengkap ASC");

$data = ['id' => '', 'anggota_id' => '', 'nama_karyawan' => '', 'telepon' => '', 'alamat' => '', 'jabatan_id' => '', 'tanggal_masuk' => date('Y-m-d'), 'status_aktif' => 1];
$aksiForm = 'sistem/proses/karyawan/karyawan_tambah.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $data_q = query("SELECT k.*, a.nama_lengkap as nama_anggota FROM mst_karyawan k LEFT JOIN anggota a ON k.anggota_id = a.id WHERE k.id = ?", [$id]);
    if(!empty($data_q)) {
        $data = $data_q[0];
        $aksiForm = 'sistem/proses/karyawan/karyawan_edit.php';
    }
}

$isAnggota = !empty($data['anggota_id']) || empty($data['id']); 
if ($data['id'] && empty($data['anggota_id'])) $isAnggota = false;

?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $data['id'] ? 'Edit Data Karyawan' : 'Tambah Karyawan / Pengurus Baru' ?></h1>
<div class="card bg-base-100 shadow-xl max-w-2xl">
    <div class="card-body">
        
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            
            <?php if (!$data['id']): ?>
            <div class="form-control mb-4">
                <label class="label"><span class="label-text font-bold">Sumber Data Identitas</span></label>
                <div class="flex gap-4 p-3 bg-base-200 rounded-lg">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="sumber_data" value="anggota" class="radio radio-primary" <?= $isAnggota ? 'checked' : '' ?> onclick="toggleSumberData('anggota')">
                        <span>Pilih dari Master Entitas / Anggota</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="sumber_data" value="manual" class="radio radio-primary" <?= !$isAnggota ? 'checked' : '' ?> onclick="toggleSumberData('manual')">
                        <span>Input Manual (Bukan Anggota)</span>
                    </label>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="sumber_data" value="<?= $isAnggota ? 'anggota' : 'manual' ?>">
            <?php endif; ?>

            <!-- Bagian jika sumber data dari Anggota -->
            <div id="div_anggota" class="<?= !$isAnggota ? 'hidden' : '' ?>">
                <div class="form-control mb-4 border border-base-300 p-4 rounded-lg bg-base-50">
                    <label class="label"><span class="label-text font-bold text-primary">Pilih Entitas (dari Daftar Master)</span></label>
                    <select name="anggota_id" id="pilihan-anggota" <?= $data['id'] ? 'disabled' : '' ?>>
                        <option value="">-- Ketik Nama Anggota / Entitas --</option>
                        <?php foreach($anggota_list as $a): ?>
                            <option value="<?= $a['id'] ?>" data-detail="<?= htmlspecialchars($a['telepon'] . ' - ' . substr($a['alamat'], 0, 30)) ?>" <?= ($data['anggota_id'] == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($data['id'] && $isAnggota): ?>
                        <input type="hidden" name="anggota_id" value="<?= $data['anggota_id'] ?>">
                        <label class="label"><span class="label-text-alt text-warning mt-2">Nama/identitas hanya bisa diubah melalui menu Master Data Anggota.</span></label>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bagian jika sumber data Manual (Bukan anggota) -->
            <div id="div_manual" class="<?= $isAnggota ? 'hidden' : '' ?>">
                <div class="form-control mb-4 border border-base-300 p-4 rounded-lg bg-base-50">
                    <div class="form-control mb-2">
                        <label class="label"><span class="label-text font-bold text-primary">Nama Lengkap Karyawan</span></label>
                        <input type="text" name="nama_karyawan" id="input_nama_karyawan" class="input input-bordered" value="<?= htmlspecialchars($data['nama_karyawan'] ?? '') ?>">
                    </div>
                    <div class="form-control mb-2">
                        <label class="label"><span class="label-text">Nomor Telepon</span></label>
                        <input type="text" name="telepon" class="input input-bordered" value="<?= htmlspecialchars($data['telepon'] ?? '') ?>">
                    </div>
                    <div class="form-control mb-2">
                        <label class="label"><span class="label-text">Alamat Domisili</span></label>
                        <textarea name="alamat" class="textarea textarea-bordered"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Pilih Jabatan / Posisi</span></label>
                    <select name="jabatan_id" class="select select-bordered" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach($jabatan as $j): ?>
                            <option value="<?= $j['id'] ?>" <?= $data['jabatan_id'] == $j['id'] ? 'selected' : '' ?>><?= htmlspecialchars($j['nama_jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Tanggal Diangkat / Masuk</span></label>
                    <input type="date" name="tanggal_masuk" class="input input-bordered" required value="<?= $data['tanggal_masuk'] ?>">
                </div>
            </div>
            
            <?php if($data['id']): ?>
            <div class="form-control mb-4">
                <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" name="status_aktif" value="1" class="checkbox checkbox-primary" <?= $data['status_aktif'] == 1 ? 'checked' : '' ?> />
                    <span class="label-text font-bold">Karyawan Masih Aktif Menjabat / Bekerja</span>
                </label>
            </div>
            <?php endif; ?>
            <div class="flex justify-end gap-2 mt-6">
                <a href="index.php?halaman=karyawan/lihat_karyawan" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Data Karyawan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSumberData(sumber) {
    const divAnggota = document.getElementById('div_anggota');
    const divManual = document.getElementById('div_manual');
    const selAnggota = document.getElementById('pilihan-anggota');
    const inputNama = document.getElementById('input_nama_karyawan');
    
    if (sumber === 'anggota') {
        divAnggota.classList.remove('hidden');
        divManual.classList.add('hidden');
        if (selAnggota && !selAnggota.disabled) selAnggota.setAttribute('required', 'required');
        if (inputNama) inputNama.removeAttribute('required');
    } else {
        divAnggota.classList.add('hidden');
        divManual.classList.remove('hidden');
        if (selAnggota) selAnggota.removeAttribute('required');
        if (inputNama) inputNama.setAttribute('required', 'required');
    }
}
// Set initial state
<?php if (!$data['id']): ?>
toggleSumberData('<?= $isAnggota ? 'anggota' : 'manual' ?>');
<?php endif; ?>
</script>
<?php  ?>