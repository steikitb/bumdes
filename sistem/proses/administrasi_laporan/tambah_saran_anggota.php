<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal = $_POST['tanggal'];
    $anggota_id = (int)$_POST['anggota_id'];
    $isi_saran = $_POST['isi_saran'];
    $sql = "INSERT INTO adm_saran_anggota (tanggal, anggota_id, isi_saran) VALUES (?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal, $anggota_id, $isi_saran], 'sis');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_saran_anggota&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_saran_anggota&status=gagal');
    }
    exit;
}