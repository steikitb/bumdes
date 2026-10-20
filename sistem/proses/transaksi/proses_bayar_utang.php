<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_pembelian');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $pemasok_id = (int)$_POST['pemasok_id'];
        $nama_pemasok = trim($_POST['nama_pemasok']);
        $tanggal_bayar = $_POST['tanggal_bayar'];
        $jumlah_bayar = (float)str_replace('.', '', $_POST['jumlah_bayar']);
        $keterangan_user = trim($_POST['keterangan']);

        if ($jumlah_bayar <= 0) {
                    throw new Exception("Jumlah pembayaran harus lebih besar dari 0.");
                }

                // Validasi periode: jangan bayar ke periode yang sudah ditutup/dikunci
                cekTutupBuku($tanggal_bayar);

                // Guard overpay: jumlah bayar tidak boleh melebihi sisa utang pemasok
                // (total pembelian kredit − total pembayaran yang sudah tercatat untuk pemasok ini)
                $cek_sisa = query(
                    "SELECT (SELECT COALESCE(SUM(total),0) FROM pembelian WHERE pemasok_id = ? AND metode_pembayaran = 'Kredit' AND (status_batal IS NULL OR status_batal = 0))
                          - (SELECT COALESCE(SUM(ju.debit),0) FROM jurnal_umum ju WHERE ju.nomor_bukti LIKE ?) AS sisa",
                    [$pemasok_id, 'BYR-UTANG-' . $pemasok_id . '-%']
                );
                $sisa_utang = (float)($cek_sisa[0]['sisa'] ?? 0);
                if ($jumlah_bayar > $sisa_utang) {
                    throw new Exception('Jumlah pembayaran (Rp ' . number_format($jumlah_bayar) . ') melebihi sisa utang pemasok (Rp ' . number_format(max(0, $sisa_utang)) . ').');
                }

                $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
                $akun_utang = $_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01';

        $nomor_bukti = 'BYR-UTANG-' . $pemasok_id . '-' . time();
        $keterangan_jurnal = "Pembayaran Utang Usaha ke " . $nama_pemasok . " (Ref: " . $keterangan_user . ")";

        $entri_jurnal = [
            ['kode_akun' => $akun_utang, 'debit' => $jumlah_bayar, 'kredit' => 0],
            ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $jumlah_bayar]
        ];

        buatJurnal($nomor_bukti, $tanggal_bayar, $keterangan_jurnal, $entri_jurnal);

        db_commit($koneksi);
        catatLog('Bayar Utang Usaha', "Membayar utang ke pemasok {$nama_pemasok} sebesar Rp " . number_format($jumlah_bayar, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=laporan/laporan_utang_usaha&status=bayar_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=laporan/laporan_utang_usaha&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}