<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_daftar_akun');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $kode_akun = trim($_POST['kode_akun']);
    $nama_akun = trim($_POST['nama_akun']);
    $kelompok_akun = $_POST['kelompok_akun'];
    $unit_usaha = $_POST['unit_usaha'];
    $saldo_normal = $_POST['saldo_normal'];
    $kategori_arus_kas = !empty($_POST['kategori_arus_kas']) ? $_POST['kategori_arus_kas'] : null;
    $urutan_laporan = !empty($_POST['urutan_laporan']) ? (int)$_POST['urutan_laporan'] : null;

    if (MODE_ENTITAS == 'BUMDes') {
        if (!preg_match('/^\d\.\d\.\d{2}\.\d{2}$/', $kode_akun)) {
            header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_akun&status=gagal&error=Format Kode Akun BUMDes wajib 4 level (Contoh: 1.1.01.01)');
            exit;
        }
    }

    $cek_duplikat = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun = ?", [$kode_akun]);
    if (!empty($cek_duplikat)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_akun&status=gagal_duplikat');
        exit;
    }

    $sql = "INSERT INTO daftar_akun (kode_akun, nama_akun, kelompok_akun, unit_usaha, saldo_normal, kategori_arus_kas, urutan_laporan) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$kode_akun, $nama_akun, $kelompok_akun, $unit_usaha, $saldo_normal, $kategori_arus_kas, $urutan_laporan]);

    if ($affected_rows > 0) {
        catatLog('Tambah Daftar Akun', 'Akun baru ditambahkan: ' . $kode_akun . ' - ' . $nama_akun);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_akun&status=gagal');
    }
    exit;
}