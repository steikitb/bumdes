<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $sumber_data = $_POST['sumber_data'] ?? 'anggota';
    $jab_id = (int)$_POST['jabatan_id'];
    $tgl = $_POST['tanggal_masuk'];
    $status = isset($_POST['status_aktif']) ? 1 : 0;
    
    if ($sumber_data === 'anggota') {
        $affected = eksekusi("UPDATE mst_karyawan SET jabatan_id=?, tanggal_masuk=?, status_aktif=? WHERE id=?", [$jab_id, $tgl, $status, $id]);
    } else {
        $nama_karyawan = $_POST['nama_karyawan'];
        $telepon = $_POST['telepon'];
        $alamat = $_POST['alamat'];
        
        $affected = eksekusi("UPDATE mst_karyawan SET nama_karyawan=?, telepon=?, alamat=?, jabatan_id=?, tanggal_masuk=?, status_aktif=? WHERE id=?", [$nama_karyawan, $telepon, $alamat, $jab_id, $tgl, $status, $id]);
    }
    
    if ($affected > 0) {
        catatLog('Edit Karyawan', "Mengubah data penugasan karyawan ID $id");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_karyawan&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_karyawan&status=edit_tidak_ada_perubahan');
    }
    exit;
}