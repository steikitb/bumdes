<?php
cekLogin();
cekAkses('proses_penyesuaian_jurnal');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal = $_POST['tanggal'];
        $jumlah = (float)$_POST['jumlah'];
        $keterangan = $_POST['keterangan'];
        if ($jumlah <= 0) {
            throw new Exception('Jumlah pajak harus lebih dari nol.');
        }

        $akun_beban_pajak = $_PENGATURAN['akun_map_beban_pajak_kini'] ?? '5411';
        $akun_utang_pajak = $_PENGATURAN['akun_map_utang_pajak'] ?? '2141';

        $nomor_bukti = 'PPH-B-' . date('Ym', strtotime($tanggal));
        $entri_jurnal = [
            ['kode_akun' => $akun_beban_pajak, 'debit' => $jumlah, 'kredit' => 0],
            ['kode_akun' => $akun_utang_pajak, 'debit' => 0, 'kredit' => $jumlah]
        ];
        buatJurnal($nomor_bukti, $tanggal, $keterangan, $entri_jurnal);
        db_commit($koneksi);
        catatLog('Proses Pajak Penghasilan', 'Membuat jurnal pajak: ' . $keterangan . ' sebesar Rp ' . number_format($jumlah));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/lihat_jurnal&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_pajak&status=gagal');
    }
    exit;
}