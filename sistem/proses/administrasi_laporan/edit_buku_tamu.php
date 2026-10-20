<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal = $_POST['tanggal'];
    $nama_tamu = $_POST['nama_tamu'];
    $alamat = $_POST['alamat'];
    $bertemu_dengan = $_POST['bertemu_dengan'];
    $keperluan = $_POST['keperluan'];
    $sql = "UPDATE adm_buku_tamu SET tanggal=?, nama_tamu=?, alamat=?, bertemu_dengan=?, keperluan=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal, $nama_tamu, $alamat, $bertemu_dengan, $keperluan, $id], 'sssssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_buku_tamu&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_buku_tamu&status=edit_tidak_ada_perubahan');
    }
    exit;
}