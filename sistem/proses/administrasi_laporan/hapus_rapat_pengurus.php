<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM adm_rapat_pengurus WHERE id = ?";
    $affected_rows = eksekusi($sql, [$id], 'i');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_rapat_pengurus&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_rapat_pengurus&status=hapus_gagal');
    }
    exit;
}