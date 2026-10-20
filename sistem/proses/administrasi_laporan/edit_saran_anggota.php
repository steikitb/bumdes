<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $tanggal = $_POST['tanggal'];
    $anggota_id = (int)$_POST['anggota_id'];
    $isi_saran = $_POST['isi_saran'];
    $tanggapan_pengurus = $_POST['tanggapan_pengurus'];
    
    $ditanggapi_oleh = null;
    $tanggal_tanggapan = null;

    if (!empty($tanggapan_pengurus)) {
        $ditanggapi_oleh = $_SESSION['user_data']['id'];
        $tanggal_tanggapan = date('Y-m-d');
    }

    $sql = "UPDATE adm_saran_anggota SET tanggal=?, anggota_id=?, isi_saran=?, tanggapan_pengurus=?, ditanggapi_oleh=?, tanggal_tanggapan=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$tanggal, $anggota_id, $isi_saran, $tanggapan_pengurus, $ditanggapi_oleh, $tanggal_tanggapan, $id], 'sissssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_saran_anggota&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_saran_anggota&status=edit_tidak_ada_perubahan');
    }
    exit;
}