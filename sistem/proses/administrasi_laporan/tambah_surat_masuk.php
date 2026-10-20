<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $perihal = $_POST['perihal'];
    $asal_surat = $_POST['asal_surat'];
    $sql = "INSERT INTO adm_surat_masuk (tanggal_masuk, nomor_surat, tanggal_surat, perihal, asal_surat) VALUES (?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal_masuk, $nomor_surat, $tanggal_surat, $perihal, $asal_surat], 'sssss');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_surat_masuk&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_surat_masuk&status=gagal');
    }
    exit;
}