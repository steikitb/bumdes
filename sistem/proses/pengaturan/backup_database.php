<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

$db_path = defined('DB_PATH') ? DB_PATH : SISTEM_PATH . '/database/koperasi.sqlite';

if (file_exists($db_path)) {
    // Copy dulu ke file temporer agar aman dari lock
    $temp_path = sys_get_temp_dir() . '/koperasi_backup_' . time() . '.sqlite';
    
    if (copy($db_path, $temp_path)) {
        catatLog('Backup Database', 'Admin mengunduh file database offline (.sqlite).');
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="backup_koperasi_offline_'.date('Ymd_His').'.sqlite"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($temp_path));
        
        if (ob_get_length()) {
            ob_clean();
        }
        
        readfile($temp_path);
        unlink($temp_path); // Hapus file temporer setelah diunduh
        exit;
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=Gagal+membuat+salinan+backup');
        exit;
    }
} else {
    header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=File+Database+Tidak+Ditemukan');
    exit;
}