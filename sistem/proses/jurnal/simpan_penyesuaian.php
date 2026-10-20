<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal = $_POST['tanggal'];
        cekTutupBuku($tanggal);

        $keterangan = $_POST['keterangan'];
        $nomor_bukti = 'JP-' . time();
        $kode_akuns = $_POST['kode_akun'];
        $debits = $_POST['debit'];
        $kredits = $_POST['kredit'];
        $entri_jurnal = [];
        $total_debit = 0;
        $total_kredit = 0;
        for ($i = 0; $i < count($kode_akuns); $i++) {
            $debit = (float)str_replace('.', '', $debits[$i] ?? 0);
            $kredit = (float)str_replace('.', '', $kredits[$i] ?? 0);
            if ($debit > 0 || $kredit > 0) {
                $entri_jurnal[] = ['kode_akun' => $kode_akuns[$i], 'debit' => $debit, 'kredit' => $kredit];
                $total_debit += $debit;
                $total_kredit += $kredit;
            }
        }
        if (empty($entri_jurnal)) { throw new Exception("Jurnal kosong, tidak ada data untuk disimpan."); }
        if (round($total_debit, 2) !== round($total_kredit, 2)) { throw new Exception("Jurnal tidak seimbang. Debit: $total_debit, Kredit: $total_kredit");}
        buatJurnal($nomor_bukti, $tanggal, $keterangan, $entri_jurnal);
        db_commit($koneksi);
        catatLog('Simpan Jurnal Penyesuaian', 'Membuat jurnal manual: ' . $keterangan . ' sebesar Rp ' . number_format($total_debit));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}