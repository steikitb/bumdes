<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_penjualan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);

    try {
        $tanggal_penjualan = $_POST['tanggal_penjualan'];
        cekTutupBuku($tanggal_penjualan);
        
        $anggota_id = !empty($_POST['anggota_id']) ? (int)$_POST['anggota_id'] : null;
        $nama_pelanggan_umum = $_POST['nama_pelanggan_umum'] ?? null;
        $total = (float)$_POST['total'];
        $diskon = (float)str_replace('.', '', $_POST['diskon'] ?? '0');
        $metode_pembayaran = $_POST['metode_pembayaran'];
        $total_bersih = $total - $diskon;

        $tanggal_jatuh_tempo = null;
        $kode_akun_bank = null;

        if ($metode_pembayaran === 'Kredit') {
            $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;
        } elseif ($metode_pembayaran === 'Transfer') {
            $kode_akun_bank = !empty($_POST['kode_akun_bank']) ? $_POST['kode_akun_bank'] : null;
            if (!$kode_akun_bank) {
                throw new Exception("Rekening Bank Penerima belum dipilih untuk metode Transfer/QRIS.");
            }
        }

        if ($total <= 0 || $total_bersih < 0) {
            throw new Exception("Total transaksi tidak valid.");
        }

        $nomor_faktur = 'INV/' . date('Ymd') . '/' . time();
        $sql_penjualan = "INSERT INTO penjualan (nomor_faktur, tanggal_penjualan, anggota_id, nama_pelanggan_umum, total, diskon, metode_pembayaran, status_batal, tanggal_jatuh_tempo, kode_akun_bank) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";
        $stmt_penjualan = $koneksi->prepare($sql_penjualan);
        $stmt_penjualan->execute([$nomor_faktur, $tanggal_penjualan, $anggota_id, $nama_pelanggan_umum, $total, $diskon, $metode_pembayaran, $tanggal_jatuh_tempo, $kode_akun_bank]);
        
        $penjualan_id = db_insert_id($koneksi);
        if (!$penjualan_id) { throw new Exception("Gagal menyimpan data penjualan utama."); }
        
        $produk_ids = $_POST['produk_id'];
        $kuantitas_arr = $_POST['kuantitas'];
        $harga_arr = $_POST['harga'];
        $total_hpp = 0;
        
        $akun_persediaan = $_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01';
        
        $sql_detail = "INSERT INTO detail_penjualan (penjualan_id, produk_id, kuantitas, harga_saat_transaksi, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $koneksi->prepare($sql_detail);
        
        for ($i = 0; $i < count($produk_ids); $i++) {
            $produk_id = (int)$produk_ids[$i];
            $kuantitas_dijual = (int)$kuantitas_arr[$i];
            $harga = (float)$harga_arr[$i];

            if ($kuantitas_dijual <= 0 || $harga < 0) {
                throw new Exception("Kuantitas atau harga barang tidak valid.");
            }

            $subtotal = $kuantitas_dijual * $harga;
            
            $data_produk_q = query("SELECT * FROM produk WHERE id = ?", [$produk_id]);
            $data_produk = $data_produk_q[0];
            
            if ($data_produk['stok'] < $kuantitas_dijual) {
                if (!empty($data_produk['id_produk_induk'])) {
                    $stok_dibutuhkan = $kuantitas_dijual - $data_produk['stok'];
                    $kuantitas_konversi = (int)$data_produk['kuantitas_konversi'];
                    $induk_dipecah = (int)ceil($stok_dibutuhkan / $kuantitas_konversi);
                    
                    $induk_q = query("SELECT * FROM produk WHERE id = ?", [$data_produk['id_produk_induk']]);
                    $produk_induk = $induk_q[0];

                    if ($produk_induk['stok'] < $induk_dipecah) {
                        throw new Exception("Stok produk induk ({$produk_induk['nama_produk']}) tidak mencukupi untuk memenuhi penjualan eceran.");
                    }
                    
                    $stok_induk_baru = $produk_induk['stok'] - $induk_dipecah;
                    $stok_turunan_bertambah = $induk_dipecah * $kuantitas_konversi;
                    $stok_turunan_baru = $data_produk['stok'] + $stok_turunan_bertambah;
                    
                    $nilai_konversi = $induk_dipecah * $produk_induk['harga_rata_rata'];
                    $nilai_turunan_lama = $data_produk['stok'] * $data_produk['harga_rata_rata'];
                    $total_nilai_turunan_baru = $nilai_turunan_lama + $nilai_konversi;
                    $harga_rata_rata_turunan_baru = $total_nilai_turunan_baru / $stok_turunan_baru;
                    
                    eksekusi("UPDATE produk SET stok = ? WHERE id = ?", [$stok_induk_baru, $produk_induk['id']]);
                    eksekusi("UPDATE produk SET stok = ?, harga_rata_rata = ? WHERE id = ?", [$stok_turunan_baru, $harga_rata_rata_turunan_baru, $data_produk['id']]);
                    
                    $data_produk['stok'] = $stok_turunan_baru;
                    $data_produk['harga_rata_rata'] = $harga_rata_rata_turunan_baru;
                    
                    $keterangan_jurnal_konversi = "Otomatis konversi {$induk_dipecah} {$produk_induk['satuan']} {$produk_induk['nama_produk']}";
                    buatJurnal('AUTO-KONV-'.$penjualan_id, $tanggal_penjualan, $keterangan_jurnal_konversi, [
                        ['kode_akun' => $akun_persediaan, 'debit' => $nilai_konversi, 'kredit' => 0], 
                        ['kode_akun' => $akun_persediaan, 'debit' => 0, 'kredit' => $nilai_konversi]
                    ]);
                } else {
                    throw new Exception("Stok tidak mencukupi untuk produk: " . $data_produk['nama_produk']);
                }
            }

            $stmt_detail->execute([$penjualan_id, $produk_id, $kuantitas_dijual, $harga, $subtotal]);
            
            eksekusi("UPDATE produk SET stok = stok - ? WHERE id = ?", [$kuantitas_dijual, $produk_id]);
            $total_hpp += $data_produk['harga_rata_rata'] * $kuantitas_dijual;
        }

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        $akun_piutang_anggota = $_PENGATURAN['akun_map_piutang_usaha_anggota'] ?? '1.1.03.01';
        $akun_piutang_non_anggota = $_PENGATURAN['akun_map_piutang_usaha_anggota'] ?? '1.1.03.01'; 
        $akun_pendapatan_anggota = $_PENGATURAN['akun_map_pendapatan_penjualan_anggota'] ?? '4.2.01.01';
        $akun_pendapatan_non_anggota = $_PENGATURAN['akun_map_pendapatan_penjualan_anggota'] ?? '4.2.01.01';
        $akun_potongan = $_PENGATURAN['akun_map_potongan_penjualan'] ?? '4.2.01.99';
        $akun_hpp = $_PENGATURAN['akun_map_hpp'] ?? '5.1.01.01';
        
        $keterangan_jurnal = "Penjualan " . ($anggota_id ? "Anggota" : "Umum") . " Faktur: " . $nomor_faktur;
        
        if ($metode_pembayaran === 'Transfer') {
            $akun_debit_penjualan = $kode_akun_bank; // Menggunakan bank yang dipilih kasir
        } elseif ($metode_pembayaran === 'Kredit') {
            $akun_debit_penjualan = $anggota_id ? $akun_piutang_anggota : $akun_piutang_non_anggota;
        } else {
            $akun_debit_penjualan = $akun_kas;
        }
        
        $akun_pendapatan = $anggota_id ? $akun_pendapatan_anggota : $akun_pendapatan_non_anggota;
        
        $entri_jurnal = [
            ['kode_akun' => $akun_debit_penjualan, 'debit' => $total_bersih, 'kredit' => 0]
        ];

        if ($diskon > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_potongan, 'debit' => $diskon, 'kredit' => 0];
        }

        $entri_jurnal[] = ['kode_akun' => $akun_pendapatan, 'debit' => 0, 'kredit' => $total];
        $entri_jurnal[] = ['kode_akun' => $akun_hpp, 'debit' => $total_hpp, 'kredit' => 0];
        $entri_jurnal[] = ['kode_akun' => $akun_persediaan, 'debit' => 0, 'kredit' => $total_hpp];
        
        buatJurnal($nomor_faktur, $tanggal_penjualan, $keterangan_jurnal, $entri_jurnal);
        
        if ($anggota_id) {
            $poin_per_rupiah_usaha = (int)($_PENGATURAN['shu_poin_per_rupiah_usaha'] ?? 10000);
            if($poin_per_rupiah_usaha > 0) {
                $poin_usaha = floor($total_bersih / $poin_per_rupiah_usaha);
                tambahPoinPPA($anggota_id, 'usaha', $poin_usaha, 'Belanja Produk Faktur: ' . $nomor_faktur, $penjualan_id, $tanggal_penjualan);
            }
        }
        
        db_commit($koneksi);
        catatLog('Proses Penjualan', 'Mencatat penjualan (Faktur: ' . $nomor_faktur . ') sebesar Rp ' . number_format($total_bersih));
        header('Location: ' . BASE_URL . 'index.php?halaman=penjualan/lihat_penjualan&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=penjualan/form_penjualan&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}