<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_produk_stok');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $produk_id = (int)$_POST['produk_id'];
        $total_penyesuaian = (float)$_POST['total_penyesuaian'];
        $jenis_penyesuaian = $_POST['jenis_penyesuaian']; // 'penurunan' atau 'pemulihan'

        if ($produk_id <= 0 || $total_penyesuaian <= 0) {
            throw new Exception('Data tidak valid untuk diproses.');
        }

        $produk = query("SELECT nama_produk FROM produk WHERE id = ?", [$produk_id], 'i');
        if (empty($produk)) {
            throw new Exception('Produk tidak ditemukan.');
        }
        $nama_produk = $produk[0]['nama_produk'];
        
        // Akun penampung penurunan nilai WAJIB akun CADANGAN (contra-aset), bukan akun
        // Persediaan itu sendiri. Jika dijurnalkan ke Persediaan, akun yang sama dipakai
        // untuk pembelian, penjualan, dan konversi dus sehingga saldo persediaan jadi
        // bercampur dan tidak bisa diaudit.
        $akun_beban = $_PENGATURAN['akun_map_beban_penurunan_nilai_persediaan'] ?? '7.2.05.01';
        $akun_cadangan = $_PENGATURAN['akun_map_cadangan_penurunan_nilai_persediaan'] ?? '1.1.05.05';
        
        // Akun pendapatan untuk pemulihan nilai persediaan.
        // TIDAK boleh memakai "LIMIT 1" tanpa filter nama: urutan kode pertama pada
        // kelompok Pendapatan Lain-lain adalah "Pendapatan Bunga Bank", sehingga
        // pemulihan write-down persediaan sempat terkredit ke akun bunga bank.
        $akun_pendapatan_pemulihan = $_PENGATURAN['akun_map_pendapatan_pemulihan_nilai'] ?? null;
        if (empty($akun_pendapatan_pemulihan)) {
            $cocok = query("SELECT kode_akun FROM daftar_akun
                             WHERE kelompok_akun = 'Pendapatan Lain-lain'
                               AND (nama_akun LIKE '%Pemulihan%' OR nama_akun LIKE '%Penyesuaian%' OR nama_akun LIKE '%Persediaan%')
                             ORDER BY kode_akun LIMIT 1");
            $akun_pendapatan_pemulihan = !empty($cocok) ? $cocok[0]['kode_akun'] : '7.1.99.99';
        } 

        $tanggal = date('Y-m-d');
                // Validasi periode: jangan izinkan penurunan/pemulihan nilai (jurnal persediaan) pada periode ditutup/dikunci
                cekTutupBuku($tanggal);
                $entri_jurnal = [];

        if ($jenis_penyesuaian === 'penurunan') {
            $nomor_bukti = 'PNP-STOK-' . $produk_id . '-' . date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 6);
            $keterangan_jurnal = "Penurunan nilai persediaan (Loss): " . $nama_produk;
            $entri_jurnal = [
                ['kode_akun' => $akun_beban, 'debit' => $total_penyesuaian, 'kredit' => 0],
                ['kode_akun' => $akun_cadangan, 'debit' => 0, 'kredit' => $total_penyesuaian]
            ];
        } elseif ($jenis_penyesuaian === 'pemulihan') {
            // Hitung akumulasi penurunan yang belum dipulihkan untuk produk ini.
            // Tanpa guard ini operator bisa "memulihkan" nilai yang tidak pernah
            // diturunkan, sehingga akun Persediaan tergelembung dan muncul
            // pendapatan palsu.
            // WAJIB diskop per produk: pola nomor buktiJvual bernilai "PNP-STOK-{id}-..."
            // tanpa penyaringan ini,Penurunan satu produk mencemari produk lain.
            $pola_penurunan = 'PNP-STOK-' . $produk_id . '-%';
            $pola_pemulihan = 'REV-PNP-STOK-' . $produk_id . '-%';
            $akum_q = query(
                "SELECT
                    COALESCE(SUM(CASE WHEN nomor_bukti LIKE ? THEN kredit ELSE 0 END), 0)
                  - COALESCE(SUM(CASE WHEN nomor_bukti LIKE ? THEN debit ELSE 0 END), 0) AS sisa_penurunan
                 FROM jurnal_umum
                 WHERE kode_akun = ? AND (nomor_bukti LIKE ? OR nomor_bukti LIKE ?)",
                [$pola_penurunan, $pola_pemulihan, $akun_cadangan, $pola_penurunan, $pola_pemulihan], 'sssss'
            );
            $sisa_penurunan = (float)($akum_q[0]['sisa_penurunan'] ?? 0);
            if ($sisa_penurunan <= 0) {
                throw new Exception("Tidak ada penurunan nilai yang tercatat untuk produk ini, sehingga tidak ada yang dapat dipulihkan.");
            }
            if ($total_penyesuaian - $sisa_penurunan > 0.5) {
                throw new Exception("Jumlah pemulihan (Rp " . number_format($total_penyesuaian, 0, ',', '.')
                    . ") melebihi sisa penurunan nilai yang tercatat (Rp " . number_format($sisa_penurunan, 0, ',', '.') . ").");
            }

            $nomor_bukti = 'REV-PNP-STOK-' . $produk_id . '-' . date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 6);
            $keterangan_jurnal = "Pemulihan penurunan nilai persediaan (Reversal): " . $nama_produk;
            $entri_jurnal = [
                ['kode_akun' => $akun_cadangan, 'debit' => $total_penyesuaian, 'kredit' => 0],
                ['kode_akun' => $akun_pendapatan_pemulihan, 'debit' => 0, 'kredit' => $total_penyesuaian]
            ];
        } else {
            throw new Exception('Jenis penyesuaian tidak valid.');
        }

        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Penyesuaian Nilai Persediaan', "Mencatat {$jenis_penyesuaian} nilai untuk produk {$nama_produk} sebesar Rp " . number_format($total_penyesuaian));
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/lihat_stok&status=penyesuaian_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=produk_stok/form_penurunan_nilai&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}