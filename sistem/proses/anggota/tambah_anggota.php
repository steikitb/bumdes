<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_anggota');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $jenis_entitas = $_POST['jenis_entitas'] ?? 'Individu';
    $nama = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $tanggal = $_POST['tanggal_bergabung'];
    $submit_action = $_POST['submit_action'] ?? 'save';
    
    $tanggal_lahir = null;
    $jenis_kelamin = null;
    $pekerjaan = null;
    
    if ($jenis_entitas === 'Individu') {
        $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : null;
        $pekerjaan = $_POST['pekerjaan'];
    }

    $sql = "INSERT INTO anggota (nama_lengkap, alamat, telepon, tanggal_bergabung, tanggal_lahir, jenis_kelamin, pekerjaan, jenis_entitas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$nama, $alamat, $telepon, $tanggal, $tanggal_lahir, $jenis_kelamin, $pekerjaan, $jenis_entitas], 'ssssssss');

    if ($affected_rows > 0) {
        catatLog('Tambah Anggota', 'Entitas baru ditambahkan: ' . $nama);
        if ($submit_action === 'save_and_new') {
            header('Location: ' . BASE_URL . 'index.php?halaman=anggota/form_anggota&status=sukses_tambah_lagi');
        } else {
            header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=sukses');
        }
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/form_anggota&status=gagal');
    }
    exit;
}