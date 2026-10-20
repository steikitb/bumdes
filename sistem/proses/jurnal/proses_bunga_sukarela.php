<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);

    try {
        $periode = $_POST['periode_bulan'];
        $bunga_persen = (float)$_POST['bunga_persen_tahun'];
        
        $tahun = date('Y', strtotime($periode));
        $bulan = date('m', strtotime($periode));
        $tanggal_jurnal = date('Y-m-t', strtotime($periode));
        
        $nomor_bukti = 'BUNGA-SS-' . $tahun . $bulan;

        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti]);
        if (!empty($cek_jurnal)) {
            throw new Exception('Bunga simpanan sukarela untuk bulan ini sudah dibagikan.');
        }

        if ($bunga_persen <= 0) {
            throw new Exception("Persentase bunga harus lebih besar dari 0.");
        }

        // Ambil daftar anggota yang punya saldo simpanan sukarela di akhir bulan
        $anggota_list = query("
            SELECT anggota_id, 
            SUM(CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE -jumlah END) as saldo_akhir
            FROM simpanan_sukarela_transaksi
            WHERE tanggal_transaksi <= ?
            GROUP BY anggota_id
            HAVING saldo_akhir > 0
        ", [$tanggal_jurnal]);

        $total_bunga = 0;
        $bunga_per_bulan = $bunga_persen / 12 / 100;

        $sql_simpanan = "INSERT INTO simpanan_sukarela_transaksi (anggota_id, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) VALUES (?, 'Setor', ?, ?, ?)";
        // Perbaikan: Menggunakan PDO prepare langsung
        $stmt_simpanan = $koneksi->prepare($sql_simpanan);

        foreach ($anggota_list as $a) {
            $bunga_didapat = $a['saldo_akhir'] * $bunga_per_bulan;
            
            if ($bunga_didapat > 0) {
                $keterangan_transaksi = "Bunga Simpanan Sukarela " . date('M Y', strtotime($periode));
                $stmt_simpanan->execute([$a['anggota_id'], $bunga_didapat, $tanggal_jurnal, $keterangan_transaksi]);
                $total_bunga += $bunga_didapat;
            }
        }

        if ($total_bunga <= 0) {
            throw new Exception("Tidak ada saldo simpanan sukarela yang memenuhi syarat untuk mendapatkan bunga.");
        }

        $akun_beban_bunga = $_PENGATURAN['akun_map_beban_bunga_simpanan'] ?? '5142'; // Asumsi, admin bisa memetakan
        $akun_utang_sukarela = $_PENGATURAN['akun_map_simpanan_sukarela'] ?? '2121';

        $entri_jurnal = [
            ['kode_akun' => $akun_beban_bunga, 'debit' => $total_bunga, 'kredit' => 0],
            ['kode_akun' => $akun_utang_sukarela, 'debit' => 0, 'kredit' => $total_bunga]
        ];

        buatJurnal($nomor_bukti, $tanggal_jurnal, 'Beban Bunga Simpanan Sukarela ' . date('M Y', strtotime($periode)), $entri_jurnal);

        db_commit($koneksi);
        catatLog('Proses Bunga Sukarela', 'Membagikan bunga simpanan sukarela bulan ' . date('M Y', strtotime($periode)) . ' total Rp ' . number_format($total_bunga, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}