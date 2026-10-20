<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    
    $cek = query("SELECT id FROM mst_karyawan WHERE jabatan_id = ?", [$id]);
    if(!empty($cek)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=gagal&error='.urlencode("Jabatan sedang digunakan oleh karyawan."));
        exit;
    }
    
    $affected = eksekusi("DELETE FROM mst_jabatan WHERE id=?", [$id]);
    if ($affected > 0) {
        catatLog('Hapus Jabatan', "Menghapus jabatan ID $id");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=hapus_gagal');
    }
    exit;
}