<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal_rapat = $_POST['tanggal_rapat'];
    $tempat_rapat = $_POST['tempat_rapat'];
    $jenis_rapat = $_POST['jenis_rapat'];
    $jumlah_anggota_total = (int)$_POST['jumlah_anggota_total'];
    $jumlah_hadir = (int)$_POST['jumlah_hadir'];
    $pimpinan_rapat = $_POST['pimpinan_rapat'];
    $undangan_hadir = $_POST['undangan_hadir'];
    $acara_rapat = $_POST['acara_rapat'];
    $keputusan_rapat = $_POST['keputusan_rapat'];
    $sql = "UPDATE adm_rapat_anggota SET tanggal_rapat=?, tempat_rapat=?, jenis_rapat=?, jumlah_anggota_total=?, jumlah_hadir=?, pimpinan_rapat=?, undangan_hadir=?, acara_rapat=?, keputusan_rapat=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal_rapat, $tempat_rapat, $jenis_rapat, $jumlah_anggota_total, $jumlah_hadir, $pimpinan_rapat, $undangan_hadir, $acara_rapat, $keputusan_rapat, $id], 'sssiissssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_rapat_anggota&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_rapat_anggota&status=edit_tidak_ada_perubahan');
    }
    exit;
}