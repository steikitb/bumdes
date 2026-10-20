<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_anggota');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $jenis_entitas = $_POST['jenis_entitas'] ?? 'Individu';
    $nama = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $tanggal = $_POST['tanggal_bergabung'];
    
    $tanggal_lahir = null;
    $jenis_kelamin = null;
    $pekerjaan = null;
    
    if ($jenis_entitas === 'Individu') {
        $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : null;
        $pekerjaan = $_POST['pekerjaan'];
    }

    $sql = "UPDATE anggota SET nama_lengkap = ?, alamat = ?, telepon = ?, tanggal_bergabung = ?, tanggal_lahir = ?, jenis_kelamin = ?, pekerjaan = ?, jenis_entitas = ? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$nama, $alamat, $telepon, $tanggal, $tanggal_lahir, $jenis_kelamin, $pekerjaan, $jenis_entitas, $id], 'ssssssssi');

    if ($affected_rows > 0) {
        catatLog('Edit Anggota', 'Data entitas ID ' . $id . ' (' . $nama . ') telah diperbarui.');
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=edit_tidak_ada_perubahan');
    }
    exit;
}