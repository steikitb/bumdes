<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pemasok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $nama = $_POST['nama_pemasok'];
    $keterangan = $_POST['keterangan'] ?? '';
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];
    $email = $_POST['email'];

    $sql = "UPDATE pemasok SET nama_pemasok = ?, keterangan = ?, alamat = ?, telepon = ?, email = ? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$nama, $keterangan, $alamat, $telepon, $email, $id], 'sssssi');

    if ($affected_rows > 0) {
        catatLog('Edit Pemasok', 'Data pemasok ID ' . $id . ' (' . $nama . ') telah diperbarui.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=edit_tidak_ada_perubahan');
    }
    exit;
}