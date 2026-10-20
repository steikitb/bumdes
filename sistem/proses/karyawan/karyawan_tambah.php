<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $sumber_data = $_POST['sumber_data'] ?? 'anggota';
    $jab_id = (int)$_POST['jabatan_id'];
    $tgl = $_POST['tanggal_masuk'];
    
    if ($sumber_data === 'anggota') {
        $anggota_id = (int)$_POST['anggota_id'];
        
        // Cek apakah anggota sudah menjadi karyawan (aktif maupun tidak)
        $cek = query("SELECT id FROM mst_karyawan WHERE anggota_id = ? AND status_aktif = 1", [$anggota_id]);
        if(!empty($cek)) {
            header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/form_karyawan&status=gagal&error='.urlencode("Anggota/Entitas ini sudah terdaftar sebagai karyawan aktif."));
            exit;
        }
        
        $affected = eksekusi("INSERT INTO mst_karyawan (anggota_id, jabatan_id, tanggal_masuk, status_aktif) VALUES (?, ?, ?, 1)", [$anggota_id, $jab_id, $tgl]);
        $pesan_log = "Mengangkat anggota ID $anggota_id menjadi pengurus/karyawan.";
        
    } else {
        $nama_karyawan = $_POST['nama_karyawan'];
        $telepon = $_POST['telepon'];
        $alamat = $_POST['alamat'];
        
        $affected = eksekusi("INSERT INTO mst_karyawan (nama_karyawan, telepon, alamat, jabatan_id, tanggal_masuk, status_aktif) VALUES (?, ?, ?, ?, ?, 1)", [$nama_karyawan, $telepon, $alamat, $jab_id, $tgl]);
        $pesan_log = "Mendaftarkan karyawan baru (Manual): $nama_karyawan.";
    }
    
    if ($affected > 0) {
        catatLog('Tambah Karyawan', $pesan_log);
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_karyawan&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/form_karyawan&status=gagal');
    }
    exit;
}