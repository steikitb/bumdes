<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_pembelian');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $idPembelian = (int)$_POST['id'];
        
        $pembelian_q = query("SELECT nomor_faktur_pemasok, tanggal_pembelian, status_batal FROM pembelian WHERE id = ?", [$idPembelian]);
        if (empty($pembelian_q)) {
            throw new Exception("Data pembelian tidak ditemukan.");
        }
        if ((int)($pembelian_q[0]['status_batal'] ?? 0) == 1) {
            throw new Exception("Pembelian sudah pernah dibatalkan.");
        }
        $nomor_faktur = $pembelian_q[0]['nomor_faktur_pemasok'];
        $tanggal_pembelian = $pembelian_q[0]['tanggal_pembelian'];

        cekTutupBuku($tanggal_pembelian);

        $jurnal_lama = query("SELECT * FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_faktur]);
        if (empty($jurnal_lama)) {
            throw new Exception("Jurnal pembelian asli tidak ditemukan, tidak bisa di-reverse.");
        }

        $entri_reversal = [];
        foreach ($jurnal_lama as $j) {
            $entri_reversal[] = [
                'kode_akun' => $j['kode_akun'],
                'debit' => $j['kredit'],
                'kredit' => $j['debit']
            ];
        }
        $nomor_faktur_retur = 'RET-PEMB-' . $idPembelian . '-' . time();
        buatJurnal($nomor_faktur_retur, date('Y-m-d'), 'Pembatalan/Retur Pembelian: ' . $nomor_faktur, $entri_reversal);

        $details = query("SELECT * FROM detail_pembelian WHERE pembelian_id = ?", [$idPembelian]);
        foreach ($details as $d) {
            $produk_id = $d['produk_id'];
            $kuantitas = $d['kuantitas'];
            $produk_info = query("SELECT stok, harga_rata_rata FROM produk WHERE id = ?", [$produk_id]);
            
            if (!empty($produk_info)) {
                $stok_sekarang = $produk_info[0]['stok'];
                $hpp_sekarang = $produk_info[0]['harga_rata_rata'];
                
                if ($stok_sekarang < $kuantitas) {
                    throw new Exception("Stok produk tidak mencukupi untuk di-retur (barang sudah terjual sebagian). Lakukan penyesuaian stok manual jika terpaksa.");
                }
                
                // A4. Kalkulasi Ulang HPP Saat Retur
                $stok_baru = $stok_sekarang - $kuantitas;
                $hpp_baru = $hpp_sekarang;
                if ($stok_baru > 0) {
                    $total_nilai_sekarang = $stok_sekarang * $hpp_sekarang;
                    $nilai_diretur = $kuantitas * $d['harga_saat_transaksi'];
                    $hpp_baru = ($total_nilai_sekarang - $nilai_diretur) / $stok_baru;
                    if ($hpp_baru < 0) $hpp_baru = 0;
                }
                
                eksekusi("UPDATE produk SET stok = ?, harga_rata_rata = ? WHERE id = ?", [$stok_baru, $hpp_baru, $produk_id]);
            }
        }

        // Soft delete: baris pembelian & detailnya sengaja TIDAK dihapus.
        // Laporan Buku Pembantu Utang Dagang menghitung retur dengan mencocokkan
        // keterangan jurnal ke nomor_faktur_pemasok; bila barisnya dihapus, join itu
        // selalu gagal sehingga retur tidak pernah dipotong dari sisa utang.
        eksekusi("UPDATE pembelian SET status_batal = 1 WHERE id = ?", [$idPembelian]);

        db_commit($koneksi);
        catatLog('Retur Pembelian', "Membatalkan seluruh transaksi pembelian faktur " . $nomor_faktur);
        header('Location: ' . BASE_URL . 'index.php?halaman=pembelian/lihat_pembelian&status=hapus_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        error_log("Retur Pembelian Error: " . $e->getMessage());
        header('Location: ' . BASE_URL . 'index.php?halaman=pembelian/detail_pembelian&id=' . $idPembelian . '&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}