<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    
    try {
        $tanggal_jurnal = $_POST['tanggal_jurnal'];
        cekTutupBuku($tanggal_jurnal);

        $saldo_buku = (float)$_POST['saldo_buku'];
        $sisa_fisik = (float)str_replace('.', '', $_POST['sisa_fisik']);

        if ($sisa_fisik < 0) {
            throw new Exception("Sisa fisik tidak boleh bernilai negatif.");
        }

        if ($sisa_fisik > $saldo_buku) {
            throw new Exception("Sisa fisik (Rp " . number_format($sisa_fisik, 0, ',', '.') . ") tidak logis karena lebih besar dari saldo buku di sistem (Rp " . number_format($saldo_buku, 0, ',', '.') . "). Gunakan Jurnal Manual jika terjadi kesalahan input sebelumnya.");
        }

        $nilai_pemakaian = $saldo_buku - $sisa_fisik;

        if ($nilai_pemakaian <= 0) {
            throw new Exception("Tidak ada selisih pemakaian (Saldo buku dan fisik sama). Jurnal tidak perlu dibuat.");
        }

        $akun_perlengkapan = $_PENGATURAN['akun_map_perlengkapan'] ?? '1.1.06.01';
        $akun_beban_atk = $_PENGATURAN['akun_map_beban_atk'] ?? '6.1.02.01'; 
        // Menggunakan default COA 6.1.02.01 untuk ATK, pastikan tersetting atau ambil dari mapping beban umum jika tidak ada

        $nomor_bukti = 'ATK-' . date('Ym', strtotime($tanggal_jurnal)) . '-' . time();
        $keterangan = "Pembebanan Pemakaian Perlengkapan / ATK";

        $entri_jurnal = [
            ['kode_akun' => $akun_beban_atk, 'debit' => $nilai_pemakaian, 'kredit' => 0],
            ['kode_akun' => $akun_perlengkapan, 'debit' => 0, 'kredit' => $nilai_pemakaian]
        ];

        buatJurnal($nomor_bukti, $tanggal_jurnal, $keterangan, $entri_jurnal);

        db_commit($koneksi);
        catatLog('Penyesuaian Perlengkapan', 'Membuat jurnal pemakaian ATK sebesar Rp ' . number_format($nilai_pemakaian, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}