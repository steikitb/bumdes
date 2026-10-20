<?php
cekLogin();
cekAkses('kelola_pengaturan');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    
    $sql = "DELETE FROM kategori_beban WHERE id = ?";
    $affected_rows = eksekusi($sql, [$id], 'i');
    
    if ($affected_rows > 0) {
        catatLog('Hapus Kategori Beban', 'Kategori dengan ID ' . $id . ' telah dihapus.');
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_kategori_beban&status=hapus_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_kategori_beban&status=hapus_gagal');
    }
    exit;
}