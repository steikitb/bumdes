<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $nomor_bukti = $_POST['id']; // Menggunakan nomor_bukti sebagai ID reference
    $koneksi = getKoneksi();
    
    db_begin_transaction($koneksi);
    try {
        $jurnal_q = query("SELECT tanggal, keterangan FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", [$nomor_bukti], 's');
        if (empty($jurnal_q)) { throw new Exception("Data pendapatan tidak ditemukan."); }
        
        cekTutupBuku($jurnal_q[0]['tanggal']);

        // Hapus seluruh baris jurnal (Debit & Kredit) dengan nomor bukti ini
        $stmt = $koneksi->prepare("DELETE FROM jurnal_umum WHERE nomor_bukti = ?");
        $stmt->execute([$nomor_bukti]);
        $deleted_rows = $stmt->rowCount();
        
        if ($deleted_rows === 0) {
            throw new Exception("Gagal menghapus baris jurnal dari database.");
        }
        
        db_commit($koneksi);
        catatLog('Hapus Pendapatan', 'Membatalkan penerimaan: ' . $jurnal_q[0]['keterangan']);
        header('Location: ' . BASE_URL . 'index.php?halaman=pendapatan/lihat_pendapatan&status=hapus_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pendapatan/lihat_pendapatan&status=hapus_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}