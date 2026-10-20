<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_simpanan');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $simpanan_q = query("SELECT * FROM simpanan WHERE id = ?", [$id]);
        if (empty($simpanan_q)) { throw new Exception("Data simpanan tidak ditemukan."); }
        $simpanan = $simpanan_q[0];
        
        cekTutupBuku($simpanan['tanggal_setor']);

        $nomor_bukti = 'SIMP-' . $id;
        $jurnal_lama = query("SELECT * FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti]);
        
        if (empty($jurnal_lama)) {
            throw new Exception("Jurnal asli tidak ditemukan, tidak bisa membalikkan transaksi.");
        }

        $entri_reversal = [];
        foreach ($jurnal_lama as $j) {
            $entri_reversal[] = [
                'kode_akun' => $j['kode_akun'],
                'debit' => $j['kredit'],
                'kredit' => $j['debit']
            ];
        }
        
        $nomor_bukti_batal = 'BATAL-SIMP-' . $id . '-' . time();
        $tanggal = date('Y-m-d');
        buatJurnal($nomor_bukti_batal, $tanggal, "Pembatalan Setoran Modal ID: " . $id, $entri_reversal);

        $deleted_rows = eksekusi("DELETE FROM simpanan WHERE id = ?", [$id]);
        if ($deleted_rows <= 0) { throw new Exception("Gagal menghapus data simpanan dari database."); }
        
        db_commit($koneksi);
        catatLog('Hapus Simpanan', 'Simpanan/Modal ID ' . $id . ' (' . $simpanan['jenis_simpanan'] . ') dibatalkan/dihapus.');
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan/lihat_simpanan&status=hapus_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan/lihat_simpanan&status=hapus_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}