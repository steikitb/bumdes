<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $perihal = $_POST['perihal'];
    $asal_surat = $_POST['asal_surat'];
    $sql = "UPDATE adm_surat_masuk SET tanggal_masuk=?, nomor_surat=?, tanggal_surat=?, perihal=?, asal_surat=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal_masuk, $nomor_surat, $tanggal_surat, $perihal, $asal_surat, $id], 'sssssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_surat_masuk&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_surat_masuk&status=edit_tidak_ada_perubahan');
    }
    exit;
}