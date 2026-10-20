<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    
    try {
        $tanggal = $_POST['tanggal_neraca'];
        $akuns = $_POST['kode_akun'];
        $debits = $_POST['debit'];
        $kredits = $_POST['kredit'];
        
        $total_debit = 0;
        $total_kredit = 0;
        $entri_jurnal = [];
        
        for ($i = 0; $i < count($akuns); $i++) {
            $d = (float)str_replace('.', '', $debits[$i]);
            $k = (float)str_replace('.', '', $kredits[$i]);
            
            if ($d > 0 || $k > 0) {
                $entri_jurnal[] = [
                    'kode_akun' => $akuns[$i],
                    'debit' => $d,
                    'kredit' => $k
                ];
                $total_debit += $d;
                $total_kredit += $k;
            }
        }
        
        if (round($total_debit, 2) !== round($total_kredit, 2)) {
            throw new Exception("Total Debit (Rp " . number_format($total_debit, 0, ',', '.') . ") tidak sama dengan Total Kredit (Rp " . number_format($total_kredit, 0, ',', '.') . "). Neraca harus seimbang.");
        }
        
        // Hapus saldo awal yang lama jika ada
        eksekusi("DELETE FROM jurnal_umum WHERE nomor_bukti = 'SALDO-AWAL'");
        
        if (!empty($entri_jurnal)) {
            buatJurnal('SALDO-AWAL', $tanggal, 'Migrasi Saldo Awal / Setoran Modal Awal', $entri_jurnal);
        }
        
        db_commit($koneksi);
        catatLog('Migrasi Saldo Awal', 'Menyimpan saldo awal neraca sebesar Rp ' . number_format($total_debit, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_migrasi&status=sukses');
        
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_migrasi&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}