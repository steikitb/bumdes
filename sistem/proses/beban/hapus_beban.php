<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_beban');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $beban_q = query("SELECT bo.*, kb.kode_akun_terkait FROM beban_operasional bo JOIN kategori_beban kb ON bo.kategori_beban_id = kb.id WHERE bo.id = ?", [$id]);
        if (empty($beban_q)) { throw new Exception("Data beban tidak ditemukan."); }
        $beban = $beban_q[0];

        if ($beban['status_batal'] == 1) {
            throw new Exception("Beban ini sudah pernah dibatalkan.");
        }

        cekTutupBuku($beban['tanggal_beban']);

        // Akun beban harus diambil dari JURNAL ASLI (nomor bukti BBN-{id}), bukan dari
        // pemetaan kategori saat ini. Kalau pemetaan kategori diubah setelah beban
        // dicatat, memakai kode_akun_terkait membuat pembatalan masuk ke akun yang
        // salah dan  sisa saldo di akun lama.
        $nomor_bukti_asli = 'BBN-' . $id;
        $akun_asli_q = query(
            "SELECT DISTINCT kode_akun FROM jurnal_umum
              WHERE nomor_bukti = ? AND kode_akun != ?
              LIMIT 1",
            [$nomor_bukti_asli, $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01'], 'ss'
        );
        if (!empty($akun_asli_q)) {
            $kode_akun_beban = $akun_asli_q[0]['kode_akun'];
        } else {
            $kode_akun_beban = $beban['kode_akun_terkait'];
        }
        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1111';

        $nomor_bukti = 'BATAL-BBN-' . $id;
        $keterangan_jurnal = "Pembatalan Beban: " . $beban['keterangan'];
        // Tanggal pembatalan harus sama dengan tanggal beban aslinya. Jika memakai
        // tanggal hari ini, laporan tahun asal tetap menampilkan beban yang sudah
        // dibatalkan, sedangkan pembalikannya muncul di tahun lain.
        $tanggal = $beban['tanggal_beban'];
        
        $entri_jurnal = [
            ['kode_akun' => $akun_kas, 'debit' => $beban['jumlah'], 'kredit' => 0],
            ['kode_akun' => $kode_akun_beban, 'debit' => 0, 'kredit' => $beban['jumlah']]
        ];
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);

        // 1. Perbaikan Immutable Ledger: Menggunakan Soft Delete (Update status_batal = 1)
        $affected_rows = eksekusi("UPDATE beban_operasional SET status_batal = 1 WHERE id = ?", [$id]);
        if ($affected_rows <= 0) { throw new Exception("Gagal membatalkan beban operasional.");}
        
        db_commit($koneksi);
        catatLog('Hapus Beban', 'Beban ID ' . $id . ' (' . $beban['keterangan'] . ') dibatalkan.');
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/lihat_beban&status=hapus_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/lihat_beban&status=hapus_gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}