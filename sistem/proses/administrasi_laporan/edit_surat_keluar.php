<?php
cekLogin();
cekAkses('kelola_buku_administrasi');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $nomor_surat = $_POST['nomor_surat'];
    $tanggal_surat = $_POST['tanggal_surat'];
    $perihal = $_POST['perihal'];
    $tanggal_kirim = $_POST['tanggal_kirim'];
    $tujuan_surat = $_POST['tujuan_surat'];
    $keterangan = $_POST['keterangan'];
    $sql = "UPDATE adm_surat_keluar SET nomor_surat=?, tanggal_surat=?, perihal=?, tanggal_kirim=?, tujuan_surat=?, keterangan=? WHERE id = ?";
    $affected_rows = eksekusi($sql, [$nomor_surat, $tanggal_surat, $perihal, $tanggal_kirim, $tujuan_surat, $keterangan, $id], 'ssssssi');
    if ($affected_rows > 0) {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_surat_keluar&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=administrasi_laporan/lihat_surat_keluar&status=edit_tidak_ada_perubahan');
    }
    exit;
}