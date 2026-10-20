<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal = $_POST['tanggal'];
    $nama_tamu = $_POST['nama_tamu'];
    $alamat = $_POST['alamat'];
    $bertemu_dengan = $_POST['bertemu_dengan'];
    $keperluan = $_POST['keperluan'];
    $sql = "INSERT INTO adm_buku_tamu (tanggal, nama_tamu, alamat, bertemu_dengan, keperluan) VALUES (?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal, $nama_tamu, $alamat, $bertemu_dengan, $keperluan], 'sssss');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_buku_tamu&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_buku_tamu&status=gagal');
    }
    exit;
}