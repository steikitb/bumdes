<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_daftar_akun');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $kode_akun = $_POST['id'];

    $cek_transaksi = query("SELECT id FROM jurnal_umum WHERE kode_akun = ? LIMIT 1", [$kode_akun], 's');
    if (!empty($cek_transaksi)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=hapus_gagal_terkait');
        exit;
    }

    $cek_kategori = query("SELECT id FROM kategori_beban WHERE kode_akun_terkait = ? LIMIT 1", [$kode_akun], 's');
    if (!empty($cek_kategori)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=Akun ini sedang digunakan pada Kategori Beban.');
        exit;
    }

    $cek_pengaturan = query("SELECT pengaturan_key FROM pengaturan WHERE pengaturan_value = ? AND pengaturan_key LIKE 'akun_map_%' LIMIT 1", [$kode_akun], 's');
    if (!empty($cek_pengaturan)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=Akun ini sedang dipetakan di Pengaturan Kebijakan Sistem.');
        exit;
    }

    $sql = "DELETE FROM daftar_akun WHERE kode_akun = ?";
    $affected_rows = eksekusi($sql, [$kode_akun], 's');

    if ($affected_rows > 0) {
        catatLog('Hapus Daftar Akun', 'Akun dengan kode ' . $kode_akun . ' telah dihapus.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=hapus_gagal');
    }
    exit;
}