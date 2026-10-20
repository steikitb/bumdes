<?php
cekLogin();
cekAkses('kelola_daftar_akun');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $kode_akun = trim($_POST['kode_akun_original']);
    $nama_akun = trim($_POST['nama_akun']);
    $kelompok_akun = $_POST['kelompok_akun'];
    $unit_usaha = $_POST['unit_usaha'];
    $saldo_normal = $_POST['saldo_normal'];
    $kategori_arus_kas = !empty($_POST['kategori_arus_kas']) ? $_POST['kategori_arus_kas'] : null;
    $urutan_laporan = !empty($_POST['urutan_laporan']) ? (int)$_POST['urutan_laporan'] : null;

    $sql = "UPDATE daftar_akun SET nama_akun = ?, kelompok_akun = ?, unit_usaha = ?, saldo_normal = ?, kategori_arus_kas = ?, urutan_laporan = ? WHERE kode_akun = ?";
    $params = [$nama_akun, $kelompok_akun, $unit_usaha, $saldo_normal, $kategori_arus_kas, $urutan_laporan, $kode_akun];
    $types = 'sssssis';

    $affected_rows = eksekusi($sql, $params, $types);

    if ($affected_rows > 0) {
        catatLog('Edit Daftar Akun', 'Data akun ' . $kode_akun . ' telah diperbarui.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=edit_tidak_ada_perubahan');
    }
    exit;
}