<?php
cekAkses('kelola_daftar_akun');

$dataAkun = [
    'kode_akun' => '', 'nama_akun' => '', 
    'kelompok_akun' => '', 'unit_usaha' => 'Konsolidasi', 'saldo_normal' => '', 
    'kategori_arus_kas' => null, 'urutan_laporan' => null
];
$judulForm = 'Tambah Akun Baru';
$aksiForm = 'sistem/proses/pengaturan/tambah_akun.php';
$isEdit = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $kodeAkun = $_GET['id'];
    $data = query("SELECT * FROM daftar_akun WHERE kode_akun = ?", [$kodeAkun]);
    if (!empty($data)) {
        $dataAkun = $data[0];
        $judulForm = 'Edit Akun';
        $aksiForm = 'sistem/proses/pengaturan/edit_akun.php';
        $isEdit = true;
    }
}
$judulHalaman = $judulForm;

// Mendefinisikan Sub-Kategori untuk memandu pengguna awam
$sub_kategori_options = [
    ['prefix' => '1.1.', 'label' => 'Aset Lancar (Kas, Bank, Piutang, Stok)', 'kelompok' => 'Aset', 'saldo' => 'Debit', 'arus' => 'Operasi'],
    ['prefix' => '1.3.', 'label' => 'Aset Tidak Lancar (Tanah, Bangunan, Mesin)', 'kelompok' => 'Aset', 'saldo' => 'Debit', 'arus' => 'Investasi'],
    ['prefix' => '2.1.', 'label' => 'Liabilitas Jangka Pendek (Utang Usaha/Pajak)', 'kelompok' => 'Liabilitas', 'saldo' => 'Kredit', 'arus' => 'Operasi'],
    ['prefix' => '2.2.', 'label' => 'Liabilitas Jangka Panjang (Utang Bank)', 'kelompok' => 'Liabilitas', 'saldo' => 'Kredit', 'arus' => 'Pendanaan'],
    ['prefix' => '3.1.', 'label' => 'Ekuitas (Modal / Simpanan Pokok & Wajib)', 'kelompok' => 'Ekuitas', 'saldo' => 'Kredit', 'arus' => ''],
    ['prefix' => '4.1.', 'label' => 'Pendapatan Usaha', 'kelompok' => 'Pendapatan', 'saldo' => 'Kredit', 'arus' => 'Operasi'],
    ['prefix' => '5.1.', 'label' => 'Harga Pokok Penjualan (HPP)', 'kelompok' => 'Beban', 'saldo' => 'Debit', 'arus' => 'Operasi'],
    ['prefix' => '6.1.', 'label' => 'Beban Operasional (Gaji, Listrik, Sewa)', 'kelompok' => 'Beban', 'saldo' => 'Debit', 'arus' => 'Operasi'],
    ['prefix' => '7.1.', 'label' => 'Pendapatan Lain-lain (Bunga Bank, Jasa)', 'kelompok' => 'Pendapatan Lain-lain', 'saldo' => 'Kredit', 'arus' => 'Operasi'],
    ['prefix' => '7.2.', 'label' => 'Beban Lain-lain (Bunga Pinjaman, Admin Bank)', 'kelompok' => 'Beban Lain-lain', 'saldo' => 'Debit', 'arus' => 'Operasi']
];

$unit_options = ['Konsolidasi', 'Toko', 'USP'];
$saldo_normal_options = ['Debit', 'Kredit'];
$arus_kas_options = ['Operasi', 'Investasi', 'Pendanaan'];
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulForm ?></h1>

<div class="card bg-base-100 shadow-xl max-w-4xl">
    <div class="card-body">
        <form action="<?= BASE_URL . $aksiForm ?>" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="kode_akun_original" value="<?= htmlspecialchars($dataAkun['kode_akun']) ?>">
            
            <?php if (!$isEdit): ?>
            <div class="form-control mb-6 p-4 border border-base-300 bg-base-50 rounded-lg">
                <label class="label"><span class="label-text font-bold text-primary">1. Pilih Kategori Klasifikasi Akun (Rekomendasi)</span></label>
                <select id="pilihan_sub_kategori" class="select select-bordered w-full">
                    <option value="">-- Pilih Jenis Akun yang Ingin Ditambahkan --</option>
                    <?php foreach($sub_kategori_options as $opt): ?>
                        <option value="<?= $opt['prefix'] ?>" data-kelompok="<?= $opt['kelompok'] ?>" data-saldo="<?= $opt['saldo'] ?>" data-arus="<?= $opt['arus'] ?>">
                            <?= $opt['label'] ?> (Kode: <?= rtrim($opt['prefix'], '.') ?>...)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="label"><span class="label-text-alt text-info">Sistem akan otomatis merekomendasikan Nomor Kode Akun, Kelompok, dan Saldo Normal yang benar.</span></label>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">2. Kode Akun</span></label>
                    <input type="text" name="kode_akun" id="input_kode_akun" placeholder="Contoh: 1.1.01.01" class="input input-bordered font-mono font-bold" required value="<?= htmlspecialchars($dataAkun['kode_akun']) ?>" <?= $isEdit ? 'readonly' : '' ?> <?= MODE_ENTITAS == 'BUMDes' ? 'pattern="^\d\.\d\.\d{1,2}\.\d{2}$" title="Format BUMDes harus X.X.XX.XX (Contoh: 1.1.01.01)"' : '' ?>>
                    <?php if ($isEdit): ?>
                        <label class="label"><p class="label-text-alt text-warning">Kode akun tidak dapat diubah setelah dibuat.</p></label>
                    <?php endif; ?>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">3. Nama Akun</span></label>
                    <input type="text" name="nama_akun" placeholder="Contoh: Kas Kecil" class="input input-bordered" required value="<?= htmlspecialchars($dataAkun['nama_akun']) ?>" autofocus>
                </div>
            </div>

            <div class="divider">Konfigurasi Lanjutan (Terisi Otomatis)</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="form-control">
                    <label class="label"><span class="label-text opacity-70">Kelompok Akun Utama</span></label>
                    <select name="kelompok_akun" id="select_kelompok_akun" class="select select-bordered" required>
                        <?php $klp_all = ['Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban', 'Pendapatan Lain-lain', 'Beban Lain-lain']; foreach($klp_all as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($dataAkun['kelompok_akun'] == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text opacity-70">Unit Usaha Laporan</span></label>
                    <select name="unit_usaha" class="select select-bordered" required>
                        <?php foreach($unit_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($dataAkun['unit_usaha'] == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text opacity-70">Sifat Saldo Normal</span></label>
                    <select name="saldo_normal" id="select_saldo_normal" class="select select-bordered" required>
                        <?php foreach($saldo_normal_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($dataAkun['saldo_normal'] == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="form-control">
                    <label class="label"><span class="label-text opacity-70">Kategori Arus Kas (Opsional)</span></label>
                    <select name="kategori_arus_kas" id="select_arus_kas" class="select select-bordered">
                        <option value="">-- Tidak Ada --</option>
                        <?php foreach($arus_kas_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($dataAkun['kategori_arus_kas'] == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text opacity-70">Urutan Laporan (Opsional)</span></label>
                    <input type="number" name="urutan_laporan" placeholder="Contoh: 10" class="input input-bordered" value="<?= htmlspecialchars($dataAkun['urutan_laporan'] ?? '') ?>">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=pengaturan/lihat_akun" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-lg">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>