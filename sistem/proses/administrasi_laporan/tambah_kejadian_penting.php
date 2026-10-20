<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal = $_POST['tanggal'];
    $jenis_kegiatan = $_POST['jenis_kegiatan'];
    $tempat = $_POST['tempat'];
    $uraian = $_POST['uraian'];
    $sql = "INSERT INTO adm_kejadian_penting (tanggal, jenis_kegiatan, tempat, uraian) VALUES (?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal, $jenis_kegiatan, $tempat, $uraian], 'ssss');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_kejadian_penting&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_kejadian_penting&status=gagal');
    }
    exit;
}