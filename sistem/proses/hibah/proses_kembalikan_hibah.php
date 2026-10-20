<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_hibah');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    $hibah_id = (int)$_POST['id'];
    db_begin_transaction($koneksi);

    try {
        $hibah_q = query("SELECT * FROM hibah_pemerintah WHERE id = ? AND status_syarat = 'Belum Terpenuhi'", [$hibah_id]);
        if (empty($hibah_q)) {
            throw new Exception("Hibah tidak ditemukan atau tidak dapat dikembalikan.");
        }
        $hibah = $hibah_q[0];
        
        cekTutupBuku($hibah['tanggal_terima']);

        $nomor_bukti_lama = $hibah['nomor_bukti_penerimaan'];
        $jurnal_lama = query("SELECT * FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti_lama]);
        
        if (empty($jurnal_lama)) {
            throw new Exception("Jurnal asli penerimaan hibah tidak ditemukan. Reversal dibatalkan.");
        }

        $entri_reversal = [];
        foreach ($jurnal_lama as $j) {
            $entri_reversal[] = [
                'kode_akun' => $j['kode_akun'],
                'debit' => $j['kredit'],
                'kredit' => $j['debit']
            ];
        }

        $tanggal_pengembalian = date('Y-m-d');
        $nomor_bukti_baru = 'HBH-RET-' . $hibah_id . '-' . time();
        $keterangan_jurnal = 'Pengembalian / Penarikan Hibah (Gagal Syarat): ' . $hibah['keterangan'];
        
        buatJurnal($nomor_bukti_baru, $tanggal_pengembalian, $keterangan_jurnal, $entri_reversal);

        $sql_update = "UPDATE hibah_pemerintah SET status_syarat = 'Dikembalikan' WHERE id = ?";
        eksekusi($sql_update, [$hibah_id]);

        db_commit($koneksi);
        catatLog('Pengembalian Hibah', 'Aset/Dana hibah ID ' . $hibah_id . ' ditarik karena gagal memenuhi syarat.');
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/lihat_hibah&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/lihat_hibah&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}