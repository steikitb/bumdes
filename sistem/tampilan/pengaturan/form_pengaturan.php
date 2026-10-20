<?php
$judulHalaman = 'Pengaturan Identitas Entitas BUMDes';
cekAkses('kelola_pengaturan');
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Pengaturan ini digunakan sebagai kop surat pada cetakan dokumen, nama pimpinan, serta menentukan periode akuntansi berjalan.</p>

<div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
    <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
            <form action="<?= BASE_URL ?>sistem/proses/pengaturan/simpan_pengaturan.php" method="POST" enctype="multipart/form-data">
                <?php csrfInput(); ?>
                
                <h3 class="font-bold border-b pb-2 mb-4">Profil Usaha</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-bold text-primary">Mode Entitas Utama</span></label>
                        <select name="mode_entitas" id="mode_entitas" class="select select-bordered" required>
                            <option value="Koperasi" <?= ($_PENGATURAN['mode_entitas'] ?? 'Koperasi') == 'Koperasi' ? 'selected' : '' ?>>Koperasi (Simpan Pinjam / Serba Usaha)</option>
                            <option value="BUMDes" <?= ($_PENGATURAN['mode_entitas'] ?? 'Koperasi') == 'BUMDes' ? 'selected' : '' ?>>BUMDes (Sesuai Kepmendesa 136/2022)</option>
                        </select>
                        <label class="label"><span class="label-text-alt">Menyesuaikan istilah, COA, dan format neraca otomatis.</span></label>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-bold text-primary">Jenis BUMDes / Koperasi</span></label>
                        <select name="jenis_bumdes" id="jenis_bumdes" class="select select-bordered" required>
                            <option value="Umum" <?= ($_PENGATURAN['jenis_bumdes'] ?? 'Umum') == 'Umum' ? 'selected' : '' ?>>Koperasi / BUM Desa (1 Desa)</option>
                            <option value="BUMDes Bersama" <?= ($_PENGATURAN['jenis_bumdes'] ?? 'Umum') == 'BUMDes Bersama' ? 'selected' : '' ?>>BUM Desa Bersama (Antar Desa)</option>
                            <option value="BUMDes LKD" <?= ($_PENGATURAN['jenis_bumdes'] ?? 'Umum') == 'BUMDes LKD' ? 'selected' : '' ?>>BUM Desa Bersama LKD (Eks PNPM)</option>
                        </select>
                        <label class="label"><span class="label-text-alt">Sangat penting untuk kelengkapan administrasi.</span></label>
                    </div>
                    <div class="form-control mb-4 md:col-span-2">
                        <label class="label"><span class="label-text">Nama Entitas / BUMDes / Koperasi</span></label>
                        <input type="text" name="nama_koperasi" class="input input-bordered" required value="<?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'BUMDes Maju Bersama') ?>">
                    </div>
                     <div class="form-control mb-4 md:col-span-2">
                        <label class="label"><span class="label-text">Alamat Lengkap</span></label>
                        <textarea name="alamat_koperasi" class="textarea textarea-bordered"><?= htmlspecialchars($_PENGATURAN['alamat_koperasi'] ?? '') ?></textarea>
                    </div>
                    <div class="form-control mb-4 md:col-span-2">
                        <label class="label"><span class="label-text">Logo BUMDes (Opsional)</span></label>
                        <input type="file" name="logo_koperasi" class="file-input file-input-bordered w-full" accept="image/png, image/jpeg, image/jpg" />
                        <label class="label"><span class="label-text-alt">Otomatis diresize ke maks 300px agar aplikasi tetap cepat. (Format: PNG/JPG).</span></label>
                    </div>
                </div>

                <h3 class="font-bold border-b pb-2 mt-6 mb-4">Pengaturan Entitas / Penanggung Jawab</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Nama Kepala Desa / Penasihat</span></label>
                        <input type="text" name="nama_kepala_desa" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['nama_kepala_desa'] ?? '') ?>">
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Nama Direktur / Pimpinan BUMDes</span></label>
                        <input type="text" name="nama_pimpinan" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['nama_pimpinan'] ?? '') ?>">
                    </div>
                    <div class="form-control mb-4 md:col-span-2">
                        <label class="label"><span class="label-text">Nama Bendahara / Akuntan</span></label>
                        <input type="text" name="nama_akuntan" class="input input-bordered" value="<?= htmlspecialchars($_PENGATURAN['nama_akuntan'] ?? '') ?>">
                    </div>
                </div>

                <h3 class="font-bold border-b pb-2 mt-6 mb-4">Periode Akuntansi & Penguncian</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Tanggal Mulai Periode</span></label>
                        <input type="date" name="periode_akuntansi_mulai" class="input input-bordered" required value="<?= htmlspecialchars($_PENGATURAN['periode_akuntansi_mulai'] ?? date('Y-01-01')) ?>">
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Tanggal Selesai Periode</span></label>
                        <input type="date" name="periode_akuntansi_selesai" class="input input-bordered" required value="<?= htmlspecialchars($_PENGATURAN['periode_akuntansi_selesai'] ?? date('Y-12-31')) ?>">
                    </div>
                    <div class="form-control mb-4 md:col-span-2">
                        <label class="label"><span class="label-text font-bold text-error">Kunci Transaksi Sampai Bulan:</span></label>
                        <input type="month" name="periode_akuntansi_kunci_bulan" class="input input-bordered border-error" value="<?= htmlspecialchars($_PENGATURAN['periode_akuntansi_kunci_bulan'] ?? '') ?>">
                        <label class="label"><span class="label-text-alt">Mencegah input/edit/hapus pada bulan yang dipilih dan sebelumnya. Kosongkan jika tidak dikunci.</span></label>
                    </div>
                </div>

                <?php
                $db_dir = getenv('DATABASE_DIR') ?: SISTEM_PATH . '/database';
                $backup_path_val = '';
                if (file_exists($db_dir . '/backup_config.json')) {
                    $json = json_decode(file_get_contents($db_dir . '/backup_config.json'), true);
                    if(isset($json['backup_path'])) $backup_path_val = $json['backup_path'];
                }
                ?>
                
                <h3 class="font-bold border-b pb-2 mt-6 mb-4 text-warning">Keamanan Data (Backup)</h3>
                <div class="form-control mb-4 md:col-span-2">
                    <label class="label"><span class="label-text">Folder Target Backup Otomatis (Cegah Ransomware)</span></label>
                    <input type="text" name="backup_path" class="input input-bordered border-warning" value="<?= htmlspecialchars($backup_path_val) ?>" placeholder="Contoh: D:\Backup_Koperasi atau E:\">
                    <label class="label"><span class="label-text-alt text-warning">Disarankan isi dengan lokasi Harddisk Eksternal/Flashdisk agar aman dari virus di Disk C. Kosongkan untuk menggunakan folder bawaan OS (Documents).</span></label>
                </div>

                <div class="flex justify-end gap-2 mt-8">
                    <button type="submit" class="btn btn-primary shadow-lg px-8"><i class="fas fa-save mr-2"></i> Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>