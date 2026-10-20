<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_produk_stok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $kode = trim($_POST['kode_produk']);
    $barcode = trim($_POST['barcode'] ?? '');
    if ($barcode === '') { $barcode = null; }
    $nama = $_POST['nama_produk'];
    $satuan = $_POST['satuan'];
    $harga_beli = (float)str_replace('.', '', $_POST['harga_beli']);
    $harga_jual = (float)str_replace('.', '', $_POST['harga_jual'] ?? '0');
    $stok = (int)$_POST['stok'];
    $stok_minimum = (int)$_POST['stok_minimum'];
    $jenis_barang = $_POST['jenis_barang'] ?? 'Barang Dagangan';
    $id_produk_induk = !empty($_POST['id_produk_induk']) ? (int)$_POST['id_produk_induk'] : null;
    $kuantitas_konversi = !empty($_POST['kuantitas_konversi']) ? (int)$_POST['kuantitas_konversi'] : null;

    if (empty($kode)) {
        $kode = 'ITM-' . strtoupper(substr(uniqid(), -5));
    }

    $sql = "INSERT INTO produk (kode_produk, barcode, nama_produk, satuan, harga_beli, harga_rata_rata, harga_jual, stok, stok_minimum, id_produk_induk, kuantitas_konversi, jenis_barang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$kode, $barcode, $nama, $satuan, $harga_beli, $harga_beli, $harga_jual, $stok, $stok_minimum, $id_produk_induk, $kuantitas_konversi, $jenis_barang], 'ssssdddiiiis');
    
    if ($affected_rows > 0) {
        catatLog('Tambah Barang', 'Barang baru ditambahkan: ' . $nama . ' (' . $kode . ')');
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . urlencode($jenis_barang) . '&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . urlencode($jenis_barang) . '&status=gagal');
    }
    exit;
}