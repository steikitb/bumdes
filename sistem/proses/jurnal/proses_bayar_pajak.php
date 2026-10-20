<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal_bayar = $_POST['tanggal_bayar'];
        $jumlah_bayar = (float)str_replace('.', '', $_POST['jumlah_bayar']);
        $keterangan = trim($_POST['keterangan']);

        if ($jumlah_bayar <= 0) {
            throw new Exception('Jumlah pembayaran pajak harus lebih dari nol.');
        }

        $akun_utang_pajak = $_PENGATURAN['akun_map_utang_pajak'] ?? '2141';
        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1111';

        $nomor_bukti = 'BYR-PPH-' . time();
        $entri_jurnal = [
            ['kode_akun' => $akun_utang_pajak, 'debit' => $jumlah_bayar, 'kredit' => 0],
            ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $jumlah_bayar]
        ];
        buatJurnal($nomor_bukti, $tanggal_bayar, 'Pelunasan/Setoran Pajak: ' . $keterangan, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Pembayaran Pajak', 'Mencatat setoran pajak ke negara (NTPN: ' . $keterangan . ') sebesar Rp ' . number_format($jumlah_bayar, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_pajak&status=bayar_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_pajak&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}