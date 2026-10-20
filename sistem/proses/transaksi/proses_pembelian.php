<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_pembelian');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal_pembelian = $_POST['tanggal_pembelian'];
        cekTutupBuku($tanggal_pembelian);
        
        $pemasok_id = (int)$_POST['pemasok_id'];
        $nomor_faktur_pemasok = $_POST['nomor_faktur_pemasok'];
        $total = (float)$_POST['total'];
        $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Kredit';
        
        $tanggal_jatuh_tempo = null;
        $kode_akun_bank = null;

        if ($metode_pembayaran === 'Kredit') {
            $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;
        } elseif ($metode_pembayaran === 'Transfer') {
            $kode_akun_bank = !empty($_POST['kode_akun_bank']) ? $_POST['kode_akun_bank'] : null;
            if (!$kode_akun_bank) {
                throw new Exception("Rekening Bank Sumber belum dipilih untuk metode Transfer/QRIS.");
            }
        }
        
        if ($total <= 0) { throw new Exception("Total transaksi tidak valid."); }
        
        $pemasok_data = query("SELECT nama_pemasok FROM pemasok WHERE id = ?", [$pemasok_id]);
        if(empty($pemasok_data)) { throw new Exception("Pemasok tidak valid."); }
        $nama_pemasok = $pemasok_data[0]['nama_pemasok'];

        $sql_pembelian = "INSERT INTO pembelian (nomor_faktur_pemasok, tanggal_pembelian, pemasok_id, total, metode_pembayaran, tanggal_jatuh_tempo, kode_akun_bank) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_pembelian = $koneksi->prepare($sql_pembelian);
        $stmt_pembelian->execute([$nomor_faktur_pemasok, $tanggal_pembelian, $pemasok_id, $total, $metode_pembayaran, $tanggal_jatuh_tempo, $kode_akun_bank]);
        $pembelian_id = db_insert_id($koneksi);
        
        $produk_ids = $_POST['produk_id'];
        $kuantitas_arr = $_POST['kuantitas'];
        $harga_arr = $_POST['harga'];
        
        $total_pembelian_persediaan = 0;
        $total_pembelian_perlengkapan = 0;

        $sql_detail = "INSERT INTO detail_pembelian (pembelian_id, produk_id, kuantitas, harga_saat_transaksi, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $koneksi->prepare($sql_detail);

        for ($i = 0; $i < count($produk_ids); $i++) {
            $produk_id = (int)$produk_ids[$i];
            $kuantitas = (int)$kuantitas_arr[$i];
            $harga = (float)$harga_arr[$i];
            $subtotal = $kuantitas * $harga;

            $stmt_detail->execute([$pembelian_id, $produk_id, $kuantitas, $harga, $subtotal]);
            
            $produk_q = query("SELECT stok, harga_rata_rata, jenis_barang FROM produk WHERE id = ?", [$produk_id]);
            $stok_lama = (int)$produk_q[0]['stok'];
            $harga_rata_lama = (float)$produk_q[0]['harga_rata_rata'];
            $jenis = $produk_q[0]['jenis_barang'] ?? 'Barang Dagangan';
            
            if ($jenis === 'Perlengkapan') {
                $total_pembelian_perlengkapan += $subtotal;
            } else {
                $total_pembelian_persediaan += $subtotal;
            }

            $total_stok_baru = $stok_lama + $kuantitas;
            $harga_rata_baru = (($stok_lama * $harga_rata_lama) + ($kuantitas * $harga)) / $total_stok_baru;

            eksekusi("UPDATE produk SET stok = ?, harga_rata_rata = ? WHERE id = ?", [$total_stok_baru, $harga_rata_baru, $produk_id]);
        }

        // Map Account
        $akun_persediaan = $_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01';
        $akun_perlengkapan = $_PENGATURAN['akun_map_perlengkapan'] ?? '1.1.06.01'; 
        $akun_utang_usaha = $_PENGATURAN['akun_map_utang_usaha'] ?? '2.1.01.01';
        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';

        if ($metode_pembayaran === 'Tunai') {
            $akun_kredit = $akun_kas;
        } elseif ($metode_pembayaran === 'Transfer') {
            $akun_kredit = $kode_akun_bank;
        } else {
            $akun_kredit = $akun_utang_usaha;
        }

        $entri_jurnal = [];
        if ($total_pembelian_persediaan > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_persediaan, 'debit' => $total_pembelian_persediaan, 'kredit' => 0];
        }
        if ($total_pembelian_perlengkapan > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_perlengkapan, 'debit' => $total_pembelian_perlengkapan, 'kredit' => 0];
        }
        
        $entri_jurnal[] = ['kode_akun' => $akun_kredit, 'debit' => 0, 'kredit' => $total];

        buatJurnal($nomor_faktur_pemasok, $tanggal_pembelian, "Pembelian Stok dari " . $nama_pemasok, $entri_jurnal);
        db_commit($koneksi);
        catatLog('Proses Pembelian', 'Mencatat pembelian ke Pemasok: ' . $nama_pemasok . ' Rp ' . number_format($total));
        header('Location: ' . BASE_URL . 'index.php?halaman=pembelian/lihat_pembelian&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pembelian/form_pembelian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}