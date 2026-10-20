<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_produk_stok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    
    try {
        $id_induk = (int)$_POST['id_induk'];
        $id_turunan = (int)$_POST['id_turunan'];
        $jumlah_induk_dipecah = (int)$_POST['jumlah_dipecah'];

        if ($jumlah_induk_dipecah <= 0) throw new Exception("Jumlah dipecah harus lebih besar dari 0.");

        $induk_q = query("SELECT nama_produk, stok, satuan, harga_rata_rata FROM produk WHERE id = ?", [$id_induk], 'i');
        if (empty($induk_q)) throw new Exception("Produk induk tidak ditemukan.");
        $induk = $induk_q[0];

        if ($induk['stok'] < $jumlah_induk_dipecah) {
            throw new Exception("Stok produk induk tidak mencukupi. (Sisa: {$induk['stok']})");
        }

        $turunan_q = query("SELECT nama_produk, stok, satuan, kuantitas_konversi, harga_rata_rata FROM produk WHERE id = ? AND id_produk_induk = ?", [$id_turunan, $id_induk], 'ii');
        if (empty($turunan_q)) throw new Exception("Produk turunan tidak valid untuk induk yang dipilih.");
        $turunan = $turunan_q[0];

        $kuantitas_konversi = (int)$turunan['kuantitas_konversi'];
        if ($kuantitas_konversi <= 0) throw new Exception("Pengaturan kuantitas konversi produk turunan salah.");

        $stok_induk_baru = $induk['stok'] - $jumlah_induk_dipecah;
        $stok_turunan_bertambah = $jumlah_induk_dipecah * $kuantitas_konversi;
        $stok_turunan_baru = $turunan['stok'] + $stok_turunan_bertambah;

        // Moving Average HPP Rekalkulasi
        $nilai_konversi_dari_induk = $jumlah_induk_dipecah * $induk['harga_rata_rata'];
        $nilai_turunan_lama = $turunan['stok'] * $turunan['harga_rata_rata'];
        $total_nilai_turunan_baru = $nilai_turunan_lama + $nilai_konversi_dari_induk;
        $harga_rata_rata_turunan_baru = $total_nilai_turunan_baru / $stok_turunan_baru;

        eksekusi("UPDATE produk SET stok = ? WHERE id = ?", [$stok_induk_baru, $id_induk], 'ii');
        eksekusi("UPDATE produk SET stok = ?, harga_rata_rata = ? WHERE id = ?", [$stok_turunan_baru, $harga_rata_rata_turunan_baru, $id_turunan], 'idi');

        // Jurnal Akuntansi Konversi (Memindahkan nilai dalam akun persediaan yang sama, dicatat sebagai jejak)
        $akun_persediaan = $_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01';
        $nomor_bukti = 'KONV-' . time();
        $keterangan_jurnal = "Konversi (Pecah) {$jumlah_induk_dipecah} {$induk['satuan']} {$induk['nama_produk']} menjadi {$stok_turunan_bertambah} {$turunan['satuan']} {$turunan['nama_produk']}";
        
        $entri_jurnal = [
                    ['kode_akun' => $akun_persediaan, 'debit' => $nilai_konversi_dari_induk, 'kredit' => 0], 
                    ['kode_akun' => $akun_persediaan, 'debit' => 0, 'kredit' => $nilai_konversi_dari_induk]
                ];
                // Validasi periode: jangan izinkan pecah barang (jurnal persediaan) pada periode yang sudah ditutup/dikunci
                cekTutupBuku(date('Y-m-d'));
                buatJurnal($nomor_bukti, date('Y-m-d'), $keterangan_jurnal, $entri_jurnal);

        db_commit($koneksi);
        catatLog('Konversi Produk', "Pecah barang {$induk['nama_produk']} sebanyak {$jumlah_induk_dipecah}.");
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/form_pecah_barang&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}