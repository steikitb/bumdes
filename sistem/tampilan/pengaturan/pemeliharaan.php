<?php
$judulHalaman = 'Pemeliharaan Data (Backup, Restore & Reset)';
cekAkses('admin');

$db_dir = getenv('DATABASE_DIR') ?: SISTEM_PATH . '/database';
$backup_config_path = $db_dir . '/backup_config.json';
$auto_backup_path = '';

if (file_exists($backup_config_path)) {
    $json = json_decode(file_get_contents($backup_config_path), true);
    if(isset($json['backup_path']) && !empty($json['backup_path'])) {
        $auto_backup_path = $json['backup_path'];
    }
}
?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Kelola dan lindungi data keuangan Anda. Mengingat aplikasi ini berjalan secara offline (luring) pada perangkat Anda, maka keamanan data dari kerusakan *hardware* atau serangan virus/ransomware adalah tanggung jawab pengguna. Lakukan pencadangan (backup) secara rutin.</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Card Backup -->
    <div class="card bg-base-100 shadow-xl border-t-4 border-success">
        <div class="card-body">
            <h2 class="card-title text-success"><i class="fas fa-download mr-2"></i> 1. Backup Data (Cadangkan)</h2>
            <p class="text-sm my-2">Unduh seluruh basis data (database) yang berisi transaksi, master data, dan pengaturan Anda saat ini dalam satu file <code>.sqlite</code>.</p>
            
            <?php if(!empty($auto_backup_path)): ?>
            <div class="alert alert-info shadow-sm p-3 my-2 text-sm">
                <i class="fas fa-info-circle mr-1"></i> Sistem Anda telah diatur untuk melakukan <b>Auto-Backup</b> setiap aplikasi ditutup ke folder: <br><code class="mt-1 block"><?= htmlspecialchars($auto_backup_path) ?></code>
            </div>
            <?php endif; ?>

            <div class="card-actions justify-end mt-4">
                <a href="<?= BASE_URL ?>sistem/proses/pengaturan/backup_database.php" class="btn btn-success text-white w-full"><i class="fas fa-download mr-2"></i> Unduh File Database (.sqlite)</a>
            </div>
        </div>
    </div>

    <!-- Card Restore -->
    <div class="card bg-base-100 shadow-xl border-t-4 border-info">
        <div class="card-body">
            <h2 class="card-title text-info"><i class="fas fa-upload mr-2"></i> 2. Restore Data (Pulihkan)</h2>
            <p class="text-sm my-2">Jika terjadi kerusakan data atau Anda memindahkan aplikasi ke komputer baru, Anda dapat memulihkan (me-restore) data menggunakan file backup <code>.sqlite</code> yang pernah Anda unduh.</p>
            <div class="alert alert-warning shadow-sm p-3 my-2 text-sm">
                <i class="fas fa-exclamation-triangle mr-1"></i> Peringatan: Seluruh data yang ada di aplikasi saat ini akan hilang ditimpa oleh file backup.
            </div>
            <div class="card-actions justify-end mt-4">
                <button type="button" onclick="modal_restore_db.showModal()" class="btn btn-info text-white w-full"><i class="fas fa-upload mr-2"></i> Unggah File & Restore</button>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Card Reset -->
    <div class="card bg-base-100 shadow-xl border-t-4 border-error">
        <div class="card-body">
            <h2 class="card-title text-error"><i class="fas fa-trash-alt mr-2"></i> 3. Reset (Kosongkan Data)</h2>
            <p class="text-sm my-2">Menghapus <strong>seluruh data transaksi dan master data</strong> untuk mengembalikan sistem ke kondisi awal (Factory Reset). Sangat berguna jika Anda baru selesai melakukan simulasi/uji coba dan siap menggunakan aplikasi untuk pencatatan asli (Live).</p>
            <p class="text-xs opacity-70 mb-4">*Akun Administrator dan Pengaturan Identitas akan tetap dipertahankan.</p>
            <div class="card-actions justify-end mt-auto">
                <button type="button" onclick="modal_reset_db.showModal()" class="btn btn-error text-white w-full"><i class="fas fa-exclamation-circle mr-2"></i> Reset Seluruh Data</button>
            </div>
        </div>
    </div>

    <!-- Card Vacuum -->
    <div class="card bg-base-100 shadow-xl border-t-4 border-warning">
        <div class="card-body">
            <h2 class="card-title text-warning"><i class="fas fa-broom mr-2"></i> 4. Optimalisasi Database (Vacuum)</h2>
            <p class="text-sm my-2">Seiring waktu, operasi hapus dan ubah (update/delete) akan menyisakan ruang kosong atau sampah data di dalam file database.</p>
            <p class="text-sm mb-4">Proses <em>Vacuum</em> akan menata ulang struktur internal database, menghapus ruang kosong, dan memperkecil ukuran file sehingga aplikasi tetap berjalan cepat dan responsif. Disarankan dilakukan sebulan sekali.</p>
            <div class="card-actions justify-end mt-auto">
                <a href="<?= BASE_URL ?>sistem/proses/pengaturan/vacuum_database.php" onclick="return confirm('Proses optimalisasi (Vacuum) akan dijalankan di latar belakang. Lanjutkan?')" class="btn btn-outline btn-warning w-full"><i class="fas fa-bolt mr-2"></i> Jalankan Optimalisasi</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Restore -->
<dialog id="modal_restore_db" class="modal">
  <div class="modal-box">
    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button></form>
    <h3 class="font-bold text-lg text-info">Restore (Pulihkan) Database</h3>
    <p class="py-4 text-sm">Unggah file backup <code class="bg-base-200 px-1 rounded">.sqlite</code> atau <code class="bg-base-200 px-1 rounded">.db</code> Anda.</p>
    <div class="alert alert-error mb-4">
        <span><strong>PERINGATAN KRITIKAL:</strong> Semua data transaksi, jurnal, dan anggota yang Anda catat hari ini akan langsung terhapus dan ditimpa (di-replace) oleh data dari file yang Anda unggah. Tindakan ini tidak bisa dibatalkan!</span>
    </div>
    <form action="<?= BASE_URL ?>sistem/proses/pengaturan/restore_database.php" method="POST" enctype="multipart/form-data">
        <?php csrfInput(); ?>
        <div class="form-control mb-6">
            <label class="label"><span class="label-text font-bold">Pilih File Backup Database:</span></label>
            <input type="file" name="file_db" accept=".sqlite,.db" class="file-input file-input-bordered file-input-info w-full" required>
        </div>
        <div class="modal-action">
            <button type="button" class="btn" onclick="modal_restore_db.close()">Batal</button>
            <button type="submit" class="btn btn-info text-white" onclick="return confirm('SANGAT PENTING: Anda yakin ingin menimpa database sistem saat ini secara permanen?')">Mulai Restore Data</button>
        </div>
    </form>
  </div>
</dialog>

<!-- Modal Reset -->
<dialog id="modal_reset_db" class="modal">
  <div class="modal-box">
    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button></form>
    <h3 class="font-bold text-lg text-error">Reset / Kosongkan Seluruh Data!</h3>
    <p class="py-4 text-sm">Tindakan ini akan <strong>MENGHAPUS SEMUA DATA TRANSAKSI</strong> (Jurnal Kas, Neraca, Penjualan, Pembelian, Anggota, Pinjaman, dll) kembali ke titik Nol (Factory Reset).</p>
    
    <div class="bg-base-200 p-4 rounded-lg mb-4 text-sm">
        <span class="font-bold">Data yang AKAN DIHAPUS secara permanen:</span>
        <ul class="list-disc list-inside mt-2 text-error font-semibold">
            <li>Seluruh riwayat Buku Jurnal, Kas & Neraca</li>
            <li>Daftar Anggota, Pemasok & Karyawan</li>
            <li>Daftar Produk, Stok, & Aset Tetap</li>
            <li>Data Penjualan & Pembelian</li>
            <li>Data Simpanan & Pinjaman</li>
            <li>Buku Administrasi (Surat, Rapat, dll)</li>
        </ul>
    </div>
    
    <form action="<?= BASE_URL ?>sistem/proses/pengaturan/reset_database.php" method="POST">
        <?php csrfInput(); ?>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text font-bold">Ketik kalimat <code class="text-error bg-base-200 px-1">RESET DATA</code> untuk konfirmasi:</span></label>
            <input type="text" name="konfirmasi" class="input input-bordered border-error font-mono text-center" placeholder="RESET DATA" required autocomplete="off">
        </div>
        <div class="modal-action">
            <button type="button" class="btn" onclick="modal_reset_db.close()">Batal</button>
            <button type="submit" class="btn btn-error text-white">Hapus Seluruh Data</button>
        </div>
    </form>
  </div>
</dialog>