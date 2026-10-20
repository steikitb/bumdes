<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    
    try {
        $tahun = (int)$_POST['tahun'];

        $cek_distribusi = query("SELECT id FROM histori_shu_anggota WHERE tahun = ? LIMIT 1", [$tahun], 'i');
                if (!empty($cek_distribusi)) {
                    throw new Exception("Tidak dapat membatalkan Tutup Buku karena SHU Jasa Anggota sudah didistribusikan. Hapus distribusi SHU tersebut secara manual terlebih dahulu.");
                }

                // Cegah pembatalan yang meninggalkan jurnal pembayaran menggantung (BYR-SHU / BYR-PADES)
                $cek_byr = query("SELECT nomor_bukti FROM jurnal_umum WHERE nomor_bukti IN (?, ?) LIMIT 1", ["BYR-SHU-$tahun", "BYR-PADES-$tahun"], 'ss');
                if (!empty($cek_byr)) {
                    throw new Exception("Tutup Buku tidak dapat dibatalkan karena SHU tahun $tahun sudah dicairkan (bukti " . $cek_byr[0]['nomor_bukti'] . "). Batalkan pencairan terlebih dahulu.");
                }

        $tanggal_mulai = $tahun . '-01-01';
        $tanggal_selesai = $tahun . '-12-31';

        eksekusi("DELETE FROM jurnal_umum WHERE nomor_bukti = 'JT-{$tahun}' AND tanggal = ?", [$tanggal_selesai], 's');
        
        eksekusi("DELETE FROM jurnal_umum WHERE nomor_bukti = 'ALOKASI-SHU-{$tahun}' AND tanggal = ?", [$tanggal_selesai], 's');

        eksekusi("DELETE FROM histori_shu WHERE tahun = ?", [$tahun], 'i');

        db_commit($koneksi);
        catatLog('Batal Tutup Buku', "Membatalkan proses tutup buku untuk tahun {$tahun}");
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_histori&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_histori&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}