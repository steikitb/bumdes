<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_penjualan');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $idPenjualan = (int)$_POST['id'];
        
        $penjualan_q = query("SELECT nomor_faktur, tanggal_penjualan, status_batal FROM penjualan WHERE id = ?", [$idPenjualan]);
        if (empty($penjualan_q)) {
            throw new Exception("Data penjualan tidak ditemukan.");
        }
        if ($penjualan_q[0]['status_batal'] == 1) {
            throw new Exception("Penjualan sudah pernah dibatalkan.");
        }

        $nomor_faktur = $penjualan_q[0]['nomor_faktur'];
        $tanggal_penjualan = $penjualan_q[0]['tanggal_penjualan'];

        cekTutupBuku($tanggal_penjualan);

        $jurnal_lama = query("SELECT * FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_faktur]);
        if (empty($jurnal_lama)) {
            throw new Exception("Jurnal penjualan asli tidak ditemukan, tidak bisa di-reverse.");
        }

        $entri_reversal = [];
        foreach ($jurnal_lama as $j) {
            $entri_reversal[] = [
                'kode_akun' => $j['kode_akun'],
                'debit' => $j['kredit'],
                'kredit' => $j['debit']
            ];
        }
        $nomor_faktur_retur = 'RET-' . $idPenjualan . '-' . time();
        buatJurnal($nomor_faktur_retur, date('Y-m-d'), 'Pembatalan/Retur Penjualan: ' . $nomor_faktur, $entri_reversal);

        $details = query("SELECT * FROM detail_penjualan WHERE penjualan_id = ?", [$idPenjualan]);
        foreach ($details as $d) {
            eksekusi("UPDATE produk SET stok = stok + ? WHERE id = ?", [$d['kuantitas'], $d['produk_id']]);
        }

        eksekusi("DELETE FROM histori_poin_ppa WHERE id_transaksi_terkait = ? AND jenis_poin = 'usaha' AND keterangan LIKE 'Belanja Produk Faktur: %'", [$idPenjualan]);

        // 1. Perbaikan Immutable Ledger: Hanya melakukan Soft Delete agar jejak audit PDF tidak lenyap
        eksekusi("UPDATE penjualan SET status_batal = 1 WHERE id = ?", [$idPenjualan]);

        db_commit($koneksi);
        catatLog('Retur Penjualan', "Membatalkan seluruh transaksi penjualan faktur " . $nomor_faktur);
        header('Location: ' . BASE_URL . 'index.php?halaman=penjualan/lihat_penjualan&status=hapus_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        error_log("Retur Penjualan Error: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?halaman=penjualan/detail_penjualan&id=' . $idPenjualan . '&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}