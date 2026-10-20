<?php
cekLogin();
cekAkses('kelola_produk_stok');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $produk_id = (int)$_POST['produk_id'];
        $stok_sebenarnya = (int)$_POST['stok_sebenarnya'];

        if ($produk_id <= 0 || $stok_sebenarnya < 0) { throw new Exception('Data tidak valid: jumlah stok riil tidak boleh negatif.'); }

        $produk = query("SELECT stok, harga_rata_rata FROM produk WHERE id = ?", [$produk_id], 'i');
        if (empty($produk)) { throw new Exception('Produk tidak ditemukan. Muat ulang daftar barang lalu coba lagi.'); }
        
        $stok_saat_ini = $produk[0]['stok'];
        $penyesuaian = $stok_sebenarnya - $stok_saat_ini;
        
        if ($penyesuaian == 0) {
             header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&status=edit_tidak_ada_perubahan');
             exit;
        }

        $sql_update = "UPDATE produk SET stok = ? WHERE id = ?";
        $affected_rows = eksekusi($sql_update, [$stok_sebenarnya, $produk_id], 'ii');

        if ($affected_rows <= 0) { throw new Exception("Gagal update stok."); }
        
        $nilai_penyesuaian = abs($penyesuaian) * $produk[0]['harga_rata_rata'];
        $nomor_bukti = 'ADJ-STOK-' . $produk_id . '-' . time();
                $keterangan_jurnal = "Penyesuaian Stok Produk ID: " . $produk_id . " dari " . $stok_saat_ini . " menjadi " . $stok_sebenarnya;
                $tanggal = date('Y-m-d');
                // Validasi periode: jangan izinkan menyesuaikan stok pada periode yang sudah ditutup/dikunci
                cekTutupBuku($tanggal);
        
                $akun_persediaan = $_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01';
                $akun_pendapatan_stok = $_PENGATURAN['akun_map_pendapatan_stok_lebih'] ?? '7.1.99.99';
                $akun_beban_stok = $_PENGATURAN['akun_map_beban_stok_kurang'] ?? '7.2.99.99';

        if ($penyesuaian > 0) {
            $entri_jurnal = [
                ['kode_akun' => $akun_persediaan, 'debit' => $nilai_penyesuaian, 'kredit' => 0], 
                ['kode_akun' => $akun_pendapatan_stok, 'debit' => 0, 'kredit' => $nilai_penyesuaian]
            ];
        } else {
            $entri_jurnal = [
                ['kode_akun' => $akun_beban_stok, 'debit' => $nilai_penyesuaian, 'kredit' => 0], 
                ['kode_akun' => $akun_persediaan, 'debit' => 0, 'kredit' => $nilai_penyesuaian]
            ];
        }
        
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Penyesuaian Stok', 'Stok produk ID ' . $produk_id . ' disesuaikan dari ' . $stok_saat_ini . ' menjadi ' . $stok_sebenarnya);
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&status=penyesuaian_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        $status = $e->getMessage() ?: 'penyesuaian_gagal';
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&status=' . $status);
    }
    exit;
}