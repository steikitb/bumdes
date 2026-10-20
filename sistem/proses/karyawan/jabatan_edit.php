<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $nama = $_POST['nama_jabatan'];
    $gapok = (float)str_replace('.', '', $_POST['gaji_pokok']);
    $tunj = (float)str_replace('.', '', $_POST['tunjangan_jabatan']);
    
    $affected = eksekusi("UPDATE mst_jabatan SET nama_jabatan=?, gaji_pokok=?, tunjangan_jabatan=? WHERE id=?", [$nama, $gapok, $tunj, $id]);
    if ($affected > 0) {
        catatLog('Edit Jabatan', "Mengubah jabatan $nama");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=edit_tidak_ada_perubahan');
    }
    exit;
}