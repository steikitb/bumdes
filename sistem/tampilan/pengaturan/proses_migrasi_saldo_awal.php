<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    $TANGGAL_SALDO_AWAL = $_POST['tanggal_saldo_awal'];
    db_begin_transaction($koneksi);

    try {
        db_query_raw($koneksi, "DELETE FROM jurnal_umum WHERE nomor_bukti = 'SALDO-AWAL'");

        $semua_akun = query("SELECT kode_akun, saldo_normal, kelompok_akun FROM daftar_akun WHERE kode_akun NOT IN ('1111', '3311')");
        
        $entri_saldo_awal = [];
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($semua_akun as $akun) {
            $saldo = getSaldoAkun($akun['kode_akun'], $TANGGAL_SALDO_AWAL);
            if ($saldo != 0) {
                if ($akun['saldo_normal'] === 'Debit') {
                    $entri_saldo_awal[] = ['kode_akun' => $akun['kode_akun'], 'debit' => $saldo, 'kredit' => 0];
                    $total_debit += $saldo;
                } else {
                    $entri_saldo_awal[] = ['kode_akun' => $akun['kode_akun'], 'debit' => 0, 'kredit' => $saldo];
                    $total_kredit += $saldo;
                }
            }
        }
        
        $saldo_kas = $total_kredit - $total_debit;
        if ($saldo_kas < 0) {
            throw new Exception("Neraca tidak seimbang (Debit > Kredit). Tidak dapat membuat saldo awal.");
        }
        $entri_saldo_awal[] = ['kode_akun' => '1111', 'debit' => $saldo_kas, 'kredit' => 0];

        buatJurnal('SALDO-AWAL', $TANGGAL_SALDO_AWAL, 'Jurnal Saldo Awal per ' . $TANGGAL_SALDO_AWAL, $entri_saldo_awal);
        
        db_commit($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_migrasi&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_migrasi&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}