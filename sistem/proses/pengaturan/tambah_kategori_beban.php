<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pengaturan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $nama_kategori = $_POST['nama_kategori'];
    $kode_akun_terkait = $_POST['kode_akun_terkait'];
    $anggaran_tahunan = (float)str_replace('.', '', $_POST['anggaran_tahunan'] ?? '0');

    $sql = "INSERT INTO kategori_beban (nama_kategori, kode_akun_terkait, anggaran_tahunan) VALUES (?, ?, ?)";
    $affected_rows = eksekusi($sql, [$nama_kategori, $kode_akun_terkait, $anggaran_tahunan], 'ssd');

    if ($affected_rows > 0) {
        catatLog('Tambah Kategori Beban', 'Kategori baru: ' . $nama_kategori);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_kategori_beban&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_kategori_beban&status=gagal');
    }
    exit;
}