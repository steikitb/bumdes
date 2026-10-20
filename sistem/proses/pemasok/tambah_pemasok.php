<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pemasok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $nama = $_POST['nama_pemasok'];
    $keterangan = $_POST['keterangan'] ?? '';
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $email = $_POST['email'];

    $sql = "INSERT INTO pemasok (nama_pemasok, keterangan, alamat, telepon, email) VALUES (?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$nama, $keterangan, $alamat, $telepon, $email], 'sssss');

    if ($affected_rows > 0) {
        catatLog('Tambah Pemasok', 'Pemasok baru ditambahkan: ' . $nama);
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/form_pemasok&status=gagal');
    }
    exit;
}