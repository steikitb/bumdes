<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tanggal = $_POST['tanggal'];
    $nama_pejabat = $_POST['nama_pejabat'];
    $jabatan_dan_kantor = $_POST['jabatan_dan_kantor'];
    $isi_anjuran = $_POST['isi_anjuran'];
    $sql = "INSERT INTO adm_anjuran_pejabat (tanggal, nama_pejabat, jabatan_dan_kantor, isi_anjuran) VALUES (?, ?, ?, ?)";
    $affected_rows = eksekusi($sql, [$tanggal, $nama_pejabat, $jabatan_dan_kantor, $isi_anjuran], 'ssss');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_anjuran_pejabat&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/form_anjuran_pejabat&status=gagal');
    }
    exit;
}