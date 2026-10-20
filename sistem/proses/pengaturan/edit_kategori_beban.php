<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pengaturan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $nama_kategori = $_POST['nama_kategori'];
    $kode_akun_terkait = $_POST['kode_akun_terkait'];
    $anggaran_tahunan = (float)str_replace('.', '', $_POST['anggaran_tahunan'] ?? '0');

    $sql = "UPDATE kategori_beban SET nama_kategori = ?, kode_akun_terkait = ?, anggaran_tahunan = ? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$nama_kategori, $kode_akun_terkait, $anggaran_tahunan, $id], 'ssdi');

    if ($affected_rows > 0) {
        catatLog('Edit Kategori Beban', 'Kategori ID ' . $id . ' diubah.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_kategori_beban&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_kategori_beban&status=edit_tidak_ada_perubahan');
    }
    exit;
}