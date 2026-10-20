<?php
cekLogin();
cekAkses('kelola_aset_tetap');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $nama = $_POST['nama_aset'];
    $kategori = $_POST['kategori_aset'];
    $tanggal = $_POST['tanggal_perolehan'];
    $harga = (float)str_replace('.', '', $_POST['harga_perolehan']);
    $masa_manfaat = (int)$_POST['masa_manfaat_tahun'];
    $nilai_residu = (float)str_replace('.', '', $_POST['nilai_residu']);

    $sql = "UPDATE aset_tetap SET nama_aset = ?, kategori_aset = ?, tanggal_perolehan = ?, harga_perolehan = ?, masa_manfaat_tahun = ?, nilai_residu = ? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$nama, $kategori, $tanggal, $harga, $masa_manfaat, $nilai_residu, $id], 'ssssidi');
    
    if ($affected_rows > 0) {
        catatLog('Edit Aset Tetap', 'Data aset ID ' . $id . ' (' . $nama . ') telah diperbarui.');
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=edit_gagal');
    }
    exit;
}