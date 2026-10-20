<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

try {
    $koneksi = getKoneksi();
    // Optimasi Database SQLite (Mengecilkan file)
    $koneksi->exec('VACUUM;');
    catatLog('Optimalisasi Database', 'Admin menjalankan proses VACUUM pada database SQLite.');
    header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=sukses');
} catch(Exception $e) {
    error_log("Vacuum Database Error: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=' . urlencode($e->getMessage()));
}
exit;