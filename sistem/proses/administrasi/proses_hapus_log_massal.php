<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['log_ids'])) {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    
    $ids = $_POST['log_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    try {
        $sql = "DELETE FROM log_aktivitas WHERE id IN ($placeholders)";
        eksekusi($sql, $ids);
        
        // Log that mass deletion occurred (if admin hasn't deleted all logs)
        catatLog('Hapus Log Massal', 'Menghapus ' . count($ids) . ' baris log aktivitas.');
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi/lihat_log_aktivitas&status=hapus_sukses');
    } catch (Exception $e) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi/lihat_log_aktivitas&status=hapus_gagal');
    }
    exit;
} else {
    header('Location: ' . BASE_URL . 'index.php?halaman=administrasi/lihat_log_aktivitas');
    exit;
}