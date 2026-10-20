<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_produk_stok');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    
    // Get jenis_barang before deleting/hiding for redirect purpose
    $prod_info = query("SELECT jenis_barang FROM produk WHERE id = ?", [$id]);
    $jenis_barang = !empty($prod_info) ? $prod_info[0]['jenis_barang'] : 'Barang Dagangan';
    $jenisUrl = urlencode($jenis_barang);

    $cek_penjualan = query("SELECT id FROM detail_penjualan WHERE produk_id = ? LIMIT 1", [$id]);
    $cek_pembelian = query("SELECT id FROM detail_pembelian WHERE produk_id = ? LIMIT 1", [$id]);
    
    if (!empty($cek_penjualan) || !empty($cek_pembelian)) {
        eksekusi("UPDATE produk SET status_aktif = 0 WHERE id = ?", [$id]);
        catatLog('Nonaktif Produk', 'Produk dengan ID ' . $id . ' dinonaktifkan (karena punya riwayat).');
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . $jenisUrl . '&status=hapus_sukses');
        exit;
    }
    
    $sql = "DELETE FROM produk WHERE id = ?";
    $affected_rows = eksekusi($sql, [$id]);
    if ($affected_rows > 0) {
        catatLog('Hapus Produk', 'Produk dengan ID ' . $id . ' telah dihapus permanen.');
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . $jenisUrl . '&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . $jenisUrl . '&status=hapus_gagal&error=' . urlencode('Produk tidak ditemukan atau sudah dihapus.'));
    }
    exit;
}