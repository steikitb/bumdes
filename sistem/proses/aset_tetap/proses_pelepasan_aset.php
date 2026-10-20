<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_aset_tetap');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $harga_jual = (float)str_replace('.', '', $_POST['harga_jual']);
    $tanggal = $_POST['tanggal_pelepasan'];
    
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $aset_q = query("SELECT * FROM aset_tetap WHERE id = ?", [$id], 'i');
        if (empty($aset_q)) {
            throw new Exception("Aset tidak ditemukan.");
        }
        $aset = $aset_q[0];
        
        $kode_akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
                $kode_akun_rugi = $_PENGATURAN['akun_map_rugi_pelepasan_aset'] ?? '7.2.04.01';
                $kode_akun_laba = $_PENGATURAN['akun_map_laba_pelepasan_aset'] ?? '7.1.05.01';

                // Pemetaan akun sesuai KELAS aset (Tanah/Kendaraan/Peralatan/Bangunan/Aset Tak Berwujud)
                $map = mapKelasAsetAkun($aset['kategori_aset']);
                $kode_akun_aset = $map[0] ?? ($_PENGATURAN['akun_map_aset_peralatan'] ?? '1.3.03.01');
                $kode_akun_akumulasi = $map[1] ?? ($_PENGATURAN['akun_map_akumulasi_peralatan'] ?? '1.3.07.02');

                // Akumulasi penyusutan PER ASET ini, bukan agregat seluruh kategori
                $akumulasi_penyusutan = getAkumulasiAset($aset['id']);
        
        $nilai_buku = $aset['harga_perolehan'] - $akumulasi_penyusutan;
        if ($nilai_buku < 0) $nilai_buku = 0;
        
        $laba_rugi = $harga_jual - $nilai_buku;

        $keterangan_jurnal = "Pelepasan Aset Tetap: " . $aset['nama_aset'];
        $nomor_bukti = 'DISP-AST-' . $id;
        
        $entri_jurnal = [
            ['kode_akun' => $kode_akun_aset, 'debit' => 0, 'kredit' => $aset['harga_perolehan']]
        ];

        if ($akumulasi_penyusutan > 0) {
            $entri_jurnal[] = ['kode_akun' => $kode_akun_akumulasi, 'debit' => $akumulasi_penyusutan, 'kredit' => 0];
        }

        if ($harga_jual > 0) {
            $entri_jurnal[] = ['kode_akun' => $kode_akun_kas, 'debit' => $harga_jual, 'kredit' => 0];
        }

        if ($laba_rugi < 0) {
            // Rugi
            $entri_jurnal[] = ['kode_akun' => $kode_akun_rugi, 'debit' => abs($laba_rugi), 'kredit' => 0];
        } elseif ($laba_rugi > 0) {
            // Laba
            $entri_jurnal[] = ['kode_akun' => $kode_akun_laba, 'debit' => 0, 'kredit' => abs($laba_rugi)];
        }
        
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        $deleted_rows = eksekusi("DELETE FROM aset_tetap WHERE id = ?", [$id], 'i');
                if ($deleted_rows <= 0) {
                     throw new Exception("Gagal menghapus data aset dari database.");
                }
                eksekusi("DELETE FROM aset_tetap_penyusutan WHERE aset_id = ?", [$id], 'i');
        
        db_commit($koneksi);
        catatLog('Lepas Aset Tetap', 'Aset ID ' . $id . ' (' . $aset['nama_aset'] . ') telah dilepas/dihapus dengan harga jual Rp ' . number_format($harga_jual, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=hapus_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=hapus_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}