<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $nama = $_POST['nama_jabatan'];
    $gapok = (float)str_replace('.', '', $_POST['gaji_pokok']);
    $tunj = (float)str_replace('.', '', $_POST['tunjangan_jabatan']);
    
    $affected = eksekusi("INSERT INTO mst_jabatan (nama_jabatan, gaji_pokok, tunjangan_jabatan) VALUES (?, ?, ?)", [$nama, $gapok, $tunj]);
    if ($affected > 0) {
        catatLog('Tambah Jabatan', "Menambah jabatan $nama");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_jabatan&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/form_jabatan&status=gagal');
    }
    exit;
}