<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $trx_q = query("SELECT * FROM trx_penggajian WHERE id = ?", [$id]);
        if(empty($trx_q)) throw new Exception("Data tidak ditemukan.");
        $trx = $trx_q[0];
        
        if($trx['status_batal'] == 1) throw new Exception("Sudah dibatalkan sebelumnya.");
        cekTutupBuku($trx['tanggal_pembayaran']);

        $jurnal_lama = query("SELECT * FROM jurnal_umum WHERE nomor_bukti = ?", [$trx['nomor_bukti']]);
        if (empty($jurnal_lama)) throw new Exception("Jurnal asli tidak ditemukan.");

        $entri_reversal = [];
        foreach ($jurnal_lama as $j) {
            $entri_reversal[] = ['kode_akun' => $j['kode_akun'], 'debit' => $j['kredit'], 'kredit' => $j['debit']];
        }
        $no_bukti_retur = 'REV-GAJI-' . $id . '-' . time();
                // Reversal dicatat pada tanggal transaksi ASLI (bukan hari ini) agar tidak beda periode
                buatJurnal($no_bukti_retur, $trx['tanggal_pembayaran'], 'Pembatalan Penggajian: ' . $trx['nomor_bukti'], $entri_reversal);

        eksekusi("UPDATE trx_penggajian SET status_batal = 1 WHERE id = ?", [$id]);

        db_commit($koneksi);
        catatLog('Batal Penggajian', "Membatalkan penggajian " . $trx['nomor_bukti']);
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_penggajian&status=hapus_sukses');
    } catch(Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_penggajian&status=gagal&error='.urlencode($e->getMessage()));
    }
    exit;
}