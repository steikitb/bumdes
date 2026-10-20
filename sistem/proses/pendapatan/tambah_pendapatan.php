<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal = $_POST['tanggal'];
        cekTutupBuku($tanggal);

        $nomor_bukti = trim($_POST['nomor_bukti'] ?? '');
        if (empty($nomor_bukti)) {
            $nomor_bukti = 'INC-' . time();
        }

        $keterangan = trim($_POST['keterangan']);
        $kode_akun_pendapatan = $_POST['kode_akun'];
        $metode_penerimaan = $_POST['metode_penerimaan'];
        $nominal = (float)str_replace('.', '', $_POST['nominal']);

        if ($nominal <= 0) {
            throw new Exception("Nominal penerimaan harus lebih besar dari 0.");
        }

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1111';
        $akun_bank = $_PENGATURAN['akun_map_bank'] ?? '1112';

        $akun_debit = ($metode_penerimaan === 'Bank') ? $akun_bank : $akun_kas;

        $entri_jurnal = [
            ['kode_akun' => $akun_debit, 'debit' => $nominal, 'kredit' => 0],
            ['kode_akun' => $kode_akun_pendapatan, 'debit' => 0, 'kredit' => $nominal]
        ];
        
        buatJurnal($nomor_bukti, $tanggal, $keterangan, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Catat Pendapatan', 'Penerimaan pendapatan: ' . $keterangan . ' sejumlah Rp ' . number_format($nominal));
        header('Location: ' . BASE_URL . 'index.php?halaman=pendapatan/lihat_pendapatan&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pendapatan/form_pendapatan&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}