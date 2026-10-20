<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_produk_stok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);

    try {
        $id = (int)$_POST['id'];
        $kode = $_POST['kode_produk'];
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

        $produk_lama_q = query("SELECT harga_rata_rata FROM produk WHERE id = ?", [$id], 'i');
                $harga_rata_lama = $produk_lama_q[0]['harga_rata_rata'] ?? $harga_beli;

                // JANGAN timpa biaya rata-rata tertimbang (harga_rata_rata dibangun dari pembelian)
                // dengan harga_beli saat edit master. Harga_rata hanya berubah lewat transaksi pembelian.
                $harga_rata_rata_baru = $harga_rata_lama;

        $sql = "UPDATE produk SET kode_produk = ?, barcode = ?, nama_produk = ?, satuan = ?, harga_beli = ?, harga_rata_rata = ?, harga_jual = ?, stok = ?, stok_minimum = ?, id_produk_induk = ?, kuantitas_konversi = ?, jenis_barang = ? WHERE id = ?";
        $affected_rows = eksekusi($sql, [$kode, $barcode, $nama, $satuan, $harga_beli, $harga_rata_rata_baru, $harga_jual, $stok, $stok_minimum, $id_produk_induk, $kuantitas_konversi, $jenis_barang, $id], 'ssssdddiiiisi');

        if ($id_produk_induk === null) {
            $produk_turunan = query("SELECT * FROM produk WHERE id_produk_induk = ?", [$id], 'i');
            foreach ($produk_turunan as $turunan) {
                $konversi = (int)$turunan['kuantitas_konversi'];
                if ($konversi > 0) {
                    $harga_beli_turunan_baru = $harga_rata_rata_baru / $konversi;
                    eksekusi(
                        "UPDATE produk SET harga_beli = ?, harga_rata_rata = ? WHERE id = ?",
                        [$harga_beli_turunan_baru, $harga_beli_turunan_baru, $turunan['id']],
                        'ddi'
                    );
                }
            }
        }

        db_commit($koneksi);
        catatLog('Edit Barang', 'Data barang ID ' . $id . ' (' . $nama . ') telah diperbarui.');
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . urlencode($jenis_barang) . '&status=edit_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        $jenis_redirect = $_POST['jenis_barang'] ?? 'Barang Dagangan';
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&jenis=' . urlencode($jenis_redirect) . '&status=edit_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}