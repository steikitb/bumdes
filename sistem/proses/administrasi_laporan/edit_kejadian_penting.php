<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal = $_POST['tanggal'];
    $jenis_kegiatan = $_POST['jenis_kegiatan'];
    $tempat = $_POST['tempat'];
    $uraian = $_POST['uraian'];
    $sql = "UPDATE adm_kejadian_penting SET tanggal=?, jenis_kegiatan=?, tempat=?, uraian=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal, $jenis_kegiatan, $tempat, $uraian, $id], 'ssssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_kejadian_penting&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_kejadian_penting&status=edit_tidak_ada_perubahan');
    }
    exit;
}