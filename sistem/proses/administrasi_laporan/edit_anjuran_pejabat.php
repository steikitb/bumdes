<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal = $_POST['tanggal'];
    $nama_pejabat = $_POST['nama_pejabat'];
    $jabatan_dan_kantor = $_POST['jabatan_dan_kantor'];
    $isi_anjuran = $_POST['isi_anjuran'];
    $tanggapan_pengurus = $_POST['tanggapan_pengurus'];
    
    $ditanggapi_oleh = null;
    $tanggal_tanggapan = null;

    if (!empty($tanggapan_pengurus)) {
        $ditanggapi_oleh = $_SESSION['user_data']['id'];
        $tanggal_tanggapan = date('Y-m-d');
    }

    $sql = "UPDATE adm_anjuran_pejabat SET tanggal=?, nama_pejabat=?, jabatan_dan_kantor=?, isi_anjuran=?, tanggapan_pengurus=?, ditanggapi_oleh=?, tanggal_tanggapan=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal, $nama_pejabat, $jabatan_dan_kantor, $isi_anjuran, $tanggapan_pengurus, $ditanggapi_oleh, $tanggal_tanggapan, $id], 'sssssssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_anjuran_pejabat&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_anjuran_pejabat&status=edit_tidak_ada_perubahan');
    }
    exit;
}