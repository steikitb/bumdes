<?php
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal_jurnal = $_POST['tanggal_jurnal'];
        $tahun = date('Y', strtotime($tanggal_jurnal));
        $nomor_bukti = 'PENYISIHAN-' . $tahun;
        
        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti], 's');
        if (!empty($cek_jurnal)) {
            throw new Exception('Jurnal penyisihan piutang untuk tahun ' . $tahun . ' sudah ada.');
        }

        if (MODE_ENTITAS == 'BUMDes') {
            $bucket1_hari = 30; $bucket1_persen = 0.5;
            $bucket2_hari = 90; $bucket2_persen = 10;
            $bucket3_hari = 180; $bucket3_persen = 50;
            $bucket4_persen = 100;
        } else {
            $bucket1_hari = (int)($_PENGATURAN['piutang_aging_bucket_1_hari'] ?? 30);
            $bucket1_persen = (float)($_PENGATURAN['piutang_aging_bucket_1_persen'] ?? 0);
            $bucket2_hari = (int)($_PENGATURAN['piutang_aging_bucket_2_hari'] ?? 90);
            $bucket2_persen = (float)($_PENGATURAN['piutang_aging_bucket_2_persen'] ?? 5);
            $bucket3_hari = (int)($_PENGATURAN['piutang_aging_bucket_3_hari'] ?? 180);
            $bucket3_persen = (float)($_PENGATURAN['piutang_aging_bucket_3_persen'] ?? 25);
            $bucket4_persen = (float)($_PENGATURAN['piutang_aging_bucket_4_persen'] ?? 50);
        }

        $sql = "
            SELECT 
                p.anggota_id, 
                p.jumlah_pokok - COALESCE(SUM(pa.pokok_dibayar), 0) AS sisa_pokok,
                CAST(julianday(?) - julianday(p.tanggal_pinjaman) AS INTEGER) as umur_hari
            FROM pinjaman_anggota p
            LEFT JOIN pembayaran_angsuran pa ON p.id = pa.pinjaman_anggota_id
            GROUP BY p.id
            HAVING sisa_pokok > 0
        ";

        $piutang_list = query($sql, [$tanggal_jurnal]);

        $total_lancar = 0;
        $total_bucket2 = 0;
        $total_bucket3 = 0;
        $total_bucket4 = 0;

        foreach($piutang_list as $piutang) {
            $sisa = $piutang['sisa_pokok'];
            $umur = $piutang['umur_hari'];
            if ($umur <= $bucket1_hari) {
                $total_lancar += $sisa;
            } elseif ($umur <= $bucket2_hari) {
                $total_bucket2 += $sisa;
            } elseif ($umur <= $bucket3_hari) {
                $total_bucket3 += $sisa;
            } else {
                $total_bucket4 += $sisa;
            }
        }
        
        $estimasi_penyisihan_wajib = ($total_lancar * ($bucket1_persen / 100)) + 
                                     ($total_bucket2 * ($bucket2_persen / 100)) +
                                     ($total_bucket3 * ($bucket3_persen / 100)) +
                                     ($total_bucket4 * ($bucket4_persen / 100));

        $akun_beban_piutang = $_PENGATURAN['akun_map_beban_kerugian_piutang'] ?? '5151';
        $akun_penyisihan_piutang = $_PENGATURAN['akun_map_penyisihan_piutang'] ?? '1142';
        
        $saldo_penyisihan_sekarang = getSaldoAkun($akun_penyisihan_piutang, $tanggal_jurnal);
        $penyesuaian_diperlukan = $estimasi_penyisihan_wajib - $saldo_penyisihan_sekarang;

        if ($penyesuaian_diperlukan > 0) {
            $entri_jurnal = [
                ['kode_akun' => $akun_beban_piutang, 'debit' => $penyesuaian_diperlukan, 'kredit' => 0],
                ['kode_akun' => $akun_penyisihan_piutang, 'debit' => 0, 'kredit' => $penyesuaian_diperlukan]
            ];
            buatJurnal($nomor_bukti, $tanggal_jurnal, 'Penyisihan Piutang Tak Tertagih Tahun ' . $tahun, $entri_jurnal);
        } else {
             throw new Exception('Tidak ada penyesuaian penyisihan piutang yang diperlukan.');
        }
        db_commit($koneksi);
        catatLog('Proses Penyisihan Piutang', 'Membuat jurnal penyisihan piutang tahun ' . $tahun . ' sebesar Rp ' . number_format($penyesuaian_diperlukan));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}