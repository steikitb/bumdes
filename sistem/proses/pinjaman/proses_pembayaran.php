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

        // Validasi sisa pokok (sudah ada di modul Pinjaman Anggota, belum ada di sini):
        // tanpa guard ini angsuran melebihi sisa membuat Utang Ke Bank ber saldo debit
        // dan Kas Tunai menjadi negatif.
        if ($pokok_dibayar <= 0 && $bunga_dibayar <= 0) {
            throw new Exception("Isi nominal pokok dan/atau bunga yang akan dibayar.");
        }
        if ($pokok_dibayar < 0 || $bunga_dibayar < 0) {
            throw new Exception("Nominal pembayaran tidak boleh negatif.");
        }
        $sisa_q = query(
            "SELECT p.jumlah_pokok - COALESCE(SUM(pp.pokok_dibayar), 0) AS sisa
               FROM pinjaman_diterima p
               LEFT JOIN pembayaran_pinjaman pp ON pp.pinjaman_id = p.id
              WHERE p.id = ?
              GROUP BY p.jumlah_pokok",
            [$pinjaman_id], 'i'
        );
        if (empty($sisa_q)) {
            throw new Exception("Data pinjaman tidak ditemukan.");
        }
        $sisa_pokok = (float)($sisa_q[0]['sisa'] ?? 0);
        if ($sisa_pokok <= 0) {
            throw new Exception("Pinjaman ini sudah lunas; tidak ada sisa pokok yang dapat dibayar.");
        }
        if ($pokok_dibayar - $sisa_pokok > 0.5) {
            throw new Exception("Nominal pokok yang dibayar (Rp " . number_format($pokok_dibayar, 0, ',', '.')
                . ") melebihi sisa pokok (Rp " . number_format($sisa_pokok, 0, ',', '.') . ").");
        }

        $sql_pembayaran = "INSERT INTO pembayaran_pinjaman (pinjaman_id, tanggal_bayar, pokok_dibayar, bunga_dibayar, keterangan) VALUES (?, ?, ?, ?, ?)";
        $stmt_pembayaran = $koneksi->prepare($sql_pembayaran);
        
        if (!$stmt_pembayaran->execute([$pinjaman_id, $tanggal_bayar, $pokok_dibayar, $bunga_dibayar, $keterangan])) {
            throw new Exception("Gagal menyimpan data pembayaran pinjaman.");
        }
        $pembayaran_id = db_insert_id($koneksi);

        // Perbaikan Fallback Akun agar valid dengan Master COA Kepmendesa BUMDes
        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
                $akun_utang_bank = $_PENGATURAN['akun_map_utang_bank'] ?? '2.2.01.01';
                // Akun beban bunga pinjaman DITERIMA — key terpisah dari bunga simpanan (fallback: Beban Bunga 7.2.02.01)
                $akun_beban_bunga_pinjaman = $_PENGATURAN['akun_map_beban_bunga_pinjaman'] ?? '7.2.02.01';

        $nomor_bukti = 'BYR-PD-' . $pembayaran_id;
        $keterangan_jurnal = "Pembayaran Angsuran Pinjaman ID: " . $pinjaman_id . ". " . $keterangan;

        $entri_jurnal = [];
        if ($pokok_dibayar > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_utang_bank, 'debit' => $pokok_dibayar, 'kredit' => 0];
        }
        if ($bunga_dibayar > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_beban_bunga_pinjaman, 'debit' => $bunga_dibayar, 'kredit' => 0];
        }
        
        $entri_jurnal[] = ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $total_pembayaran];
        
        buatJurnal($nomor_bukti, $tanggal_bayar, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        
        catatLog('Pembayaran Pinjaman Diterima', 'Mencatat pembayaran pinjaman bank ID ' . $pinjaman_id . ' sebesar Rp ' . number_format($total_pembayaran));
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman/detail_pinjaman&id=' . $pinjaman_id . '&status=bayar_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman/detail_pinjaman&id=' . $pinjaman_id . '&status=bayar_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}