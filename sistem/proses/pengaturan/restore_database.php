<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    if (!isset($_FILES['file_db']) || $_FILES['file_db']['error'] !== UPLOAD_ERR_OK) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=Pilih file database yang valid/diunggah dengan benar.');
        exit;
    }

    $file = $_FILES['file_db'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'sqlite' && strtolower($ext) !== 'db') {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=Format file harus berakhiran .sqlite atau .db');
        exit;
    }

    $db_path = defined('DB_PATH') ? DB_PATH : SISTEM_PATH . '/database/koperasi.sqlite';
    
    // Backup database yang ada sekarang sebelum ditimpa (Jaga-jaga jika korup)
    $backup_temp = sys_get_temp_dir() . '/pre_restore_backup_' . time() . '.sqlite';
    copy($db_path, $backup_temp);

    // Replace DB
    if (move_uploaded_file($file['tmp_name'], $db_path)) {
        catatLog('Restore Database', 'Admin melakukan pemulihan (restore) database dari file: ' . $file['name']);
        
        // Hancurkan sesi agar admin login ulang (karena ID/Password admin mungkin berbeda di db lama)
        session_destroy();
        
        // Redirect ke login dengan pesan sukses
        header('Location: ' . BASE_URL . 'index.php?halaman=login&status=sukses&error=' . urlencode('Restore database berhasil. Silakan login kembali menggunakan akun yang ada di database lama Anda.'));
    } else {
        // Gagal replace, kembalikan yang lama
        copy($backup_temp, $db_path);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=Gagal memindahkan dan menimpa file database ke sistem.');
    }
    exit;
}