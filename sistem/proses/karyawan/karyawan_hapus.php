<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    
    $affected = eksekusi("UPDATE mst_karyawan SET status_aktif = 0 WHERE id=?", [$id]);
    if ($affected > 0) {
        catatLog('Nonaktif Karyawan', "Menonaktifkan karyawan ID $id");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_karyawan&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_karyawan&status=gagal');
    }
    exit;
}