<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $tahun = (int)$_POST['tahun'];
    $tanggal_bayar = $_POST['tanggal_bayar'];
    
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ["BYR-PADES-$tahun"], 's');
                if (!empty($cek_jurnal)) {
                    throw new Exception("Setoran PADes untuk tahun $tahun sudah pernah dilakukan.");
                }

                // Saling-eksklusi: jangan izinkan PADes diambil dari dana yang sudah dibayarkan ke anggota (BYR-SHU)
                $cek_paid_anggota = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ["BYR-SHU-$tahun"], 's');
                if (!empty($cek_paid_anggota)) {
                    throw new Exception("Dana Jasa Anggota SHU tahun $tahun sudah dicairkan ke anggota. Tidak dapat lagi melakukan Setoran PADes dari dana yang sama (cegah pencairan ganda).");
                }

        $histori_q = query("SELECT alokasi_jasa_anggota FROM histori_shu WHERE tahun = ?", [$tahun]);
        if(empty($histori_q)) {
            throw new Exception("Tutup buku tahun $tahun belum dilakukan atau data tidak ditemukan.");
        }
        
        $jumlah_pades = (float)$histori_q[0]['alokasi_jasa_anggota'];
        
        if ($jumlah_pades <= 0) {
            throw new Exception("Tidak ada nominal Bagi Hasil PADes yang bisa disetorkan.");
        }
        
        $akun_utang_pades = $_PENGATURAN['akun_map_utang_pades'] ?? '2.2.99.99';
        $akun_bank = $_PENGATURAN['akun_map_bank'] ?? '1.1.01.02';
        
        $entri = [
            ['kode_akun' => $akun_utang_pades, 'debit' => $jumlah_pades, 'kredit' => 0],
            ['kode_akun' => $akun_bank, 'debit' => 0, 'kredit' => $jumlah_pades]
        ];
        
        buatJurnal("BYR-PADES-$tahun", $tanggal_bayar, "Penyetoran PADes (Bagi Hasil Desa) Tahun $tahun ke Rekening Kas Desa", $entri);
        
        db_commit($koneksi);
        catatLog('Setor PADes BUMDes', "Mencatat penyetoran dana PADes tahun $tahun ke Kas Desa sebesar Rp " . number_format($jumlah_pades, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun='.$tahun.'&status=sukses');
    } catch(Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun='.$tahun.'&status=gagal&error='.urlencode($e->getMessage()));
    }
    exit;
}