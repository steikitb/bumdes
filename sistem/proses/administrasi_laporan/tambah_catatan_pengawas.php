<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal = $_POST['tanggal'];
    $pengawas_id = (int)$_POST['pengawas_id'];
    $isi_catatan = $_POST['isi_catatan'];
    $sql = "INSERT INTO adm_catatan_pengawas (tanggal, pengawas_id, isi_catatan) VALUES (?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal, $pengawas_id, $isi_catatan], 'sis');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_catatan_pengawas&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_catatan_pengawas&status=gagal');
    }
    exit;
}