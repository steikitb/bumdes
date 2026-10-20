<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pinjaman');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $pinjaman_id = (int)$_POST['pinjaman_id'];
        $tanggal_bayar = $_POST['tanggal_bayar'];
        $pokok_dibayar = (float)str_replace('.', '', $_POST['pokok_dibayar']);
        $bunga_dibayar = (float)str_replace('.', '', $_POST['bunga_dibayar']);
        $keterangan = $_POST['keterangan'];
                $total_pembayaran = $pokok_dibayar + $bunga_dibayar;

                // Validasi periode: jangan izinkan bayar ke periode yang sudah ditutup/dikunci
                cekTutupBuku($tanggal_bayar);

                // Validasi server sisa pokok: tolak overpay / bayar ke pinjaman yang sudah lunas
                if ($pokok_dibayar <= 0) {
                    throw new Exception('Nominal pokok angsuran harus lebih dari nol.');
                }
                if ($bunga_dibayar < 0) {
                    throw new Exception('Nominal bunga tidak boleh negatif.');
                }
                $pinjaman_sisa_q = query(
                    "SELECT p.jumlah_pokok - COALESCE(SUM(pa.pokok_dibayar), 0) AS sisa
                     FROM pinjaman_anggota p
                     LEFT JOIN pembayaran_angsuran pa ON pa.pinjaman_anggota_id = p.id
                     WHERE p.id = ? GROUP BY p.id",
                    [$pinjaman_id]
                );
                $sisa_pokok = (float)($pinjaman_sisa_q[0]['sisa'] ?? 0);
                if ($sisa_pokok <= 0) {
                    throw new Exception('Pinjaman ini sudah lunas; tidak ada sisa pokok yang dapat dibayar.');
                }
                if ($pokok_dibayar > $sisa_pokok) {
                    throw new Exception('Nominal pokok angsuran (Rp ' . number_format($pokok_dibayar) . ') melebihi sisa pinjaman (Rp ' . number_format($sisa_pokok) . ').');
                }
        
        $sql = "INSERT INTO pembayaran_angsuran (pinjaman_anggota_id, tanggal_bayar, pokok_dibayar, bunga_dibayar, keterangan) VALUES (?, ?, ?, ?, ?)";
        // Perbaikan: Menggunakan PDO prepare langsung tanpa parameter tipe MySQLi
        $stmt = $koneksi->prepare($sql);
        if (!$stmt->execute([$pinjaman_id, $tanggal_bayar, $pokok_dibayar, $bunga_dibayar, $keterangan])) { 
            throw new Exception("Gagal menyimpan angsuran."); 
        }
        $pembayaran_id = db_insert_id($koneksi);

        // Deteksi jenis pinjaman (SPP/UEP) untuk pemetaan piutang yang benar saat pembayaran
        $pinjaman_data = query("SELECT jenis_pinjaman, anggota_id FROM pinjaman_anggota WHERE id = ?", [$pinjaman_id]);
        $jenis_pinjaman = !empty($pinjaman_data) ? $pinjaman_data[0]['jenis_pinjaman'] : 'Umum';
        
        $akun_piutang = $_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04'; 
        if ($jenis_pinjaman === 'UEP') {
            $akun_piutang = '1.1.03.05'; 
        } elseif ($jenis_pinjaman === 'SPP') {
            $akun_piutang = '1.1.03.04'; 
        }

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        $akun_pendapatan_bunga = $_PENGATURAN['akun_map_pendapatan_bunga_pinjaman'] ?? '4.1.08.01';

        $nomor_bukti = 'ANGS-PA-' . $pembayaran_id;
        $keterangan_jurnal = "Pembayaran Angsuran Pinjaman ID: " . $pinjaman_id;
        
        $entri_jurnal = [
            ['kode_akun' => $akun_kas, 'debit' => $total_pembayaran, 'kredit' => 0]
        ];
        
        if ($pokok_dibayar > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_piutang, 'debit' => 0, 'kredit' => $pokok_dibayar];
        }
        
        if ($bunga_dibayar > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_pendapatan_bunga, 'debit' => 0, 'kredit' => $bunga_dibayar];
        }
        
        buatJurnal($nomor_bukti, $tanggal_bayar, $keterangan_jurnal, $entri_jurnal);
        
        if(!empty($pinjaman_data)) {
            $anggota_id = $pinjaman_data[0]['anggota_id'];
            $poin_per_rupiah_usaha = (int)($_PENGATURAN['shu_poin_per_rupiah_usaha'] ?? 10000);
            if ($poin_per_rupiah_usaha > 0) {
                $poin_usaha = floor($total_pembayaran / $poin_per_rupiah_usaha);
                tambahPoinPPA($anggota_id, 'usaha', $poin_usaha, 'Bayar Angsuran Pinjaman ID: ' . $pinjaman_id, $pembayaran_id, $tanggal_bayar);
            }
        }
        
        db_commit($koneksi);
        catatLog('Pembayaran Angsuran', 'Mencatat pembayaran angsuran pinjaman anggota ID ' . $pinjaman_id . ' sebesar Rp ' . number_format($total_pembayaran));
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman_anggota/detail_pinjaman&id=' . $pinjaman_id . '&status=bayar_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman_anggota/detail_pinjaman&id=' . $pinjaman_id . '&status=bayar_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}