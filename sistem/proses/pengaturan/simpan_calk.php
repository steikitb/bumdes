<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pengaturan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    
    $sql = "UPDATE pengaturan SET pengaturan_value = ? WHERE pengaturan_key = ?";
    $stmt = $koneksi->prepare($sql);
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'calk_') === 0) {
            $stmt->execute([$value, $key]);
        }
    }
    
    catatLog('Simpan CaLK', 'Teks naratif CaLK telah diperbarui.');
    header('Location: ' . BASE_URL . 'index.php?halaman=calk/form_edit_calk&status=sukses');
    exit;
}