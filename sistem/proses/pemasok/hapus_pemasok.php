<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pemasok');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    
    $cek_pembelian = query("SELECT id FROM pembelian WHERE pemasok_id = ? LIMIT 1", [$id]);
    if (!empty($cek_pembelian)) {
        // A6. Soft Delete Pemasok
        eksekusi("UPDATE pemasok SET status_aktif = 0 WHERE id = ?", [$id]);
        catatLog('Nonaktif Pemasok', 'Pemasok dengan ID ' . $id . ' dinonaktifkan (karena punya riwayat).');
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=hapus_sukses');
        exit;
    }
    
    $sql = "DELETE FROM pemasok WHERE id = ?";
    $affected_rows = eksekusi($sql, [$id]);
    
    if ($affected_rows > 0) {
        catatLog('Hapus Pemasok', 'Pemasok dengan ID ' . $id . ' telah dihapus permanen.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pemasok/lihat_pemasok&status=hapus_gagal&error=' . urlencode('Pemasok tidak ditemukan atau sudah dihapus.'));
    }
    exit;
}