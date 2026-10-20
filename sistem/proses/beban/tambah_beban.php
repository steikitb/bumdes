<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_beban');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal = $_POST['tanggal_beban'];
        cekTutupBuku($tanggal);
        
        $keterangan = $_POST['keterangan'];
        $kategori_id = (int)$_POST['kategori_beban_id'];
        $jumlah_kas_keluar = (float)str_replace('.', '', $_POST['jumlah']);

        if ($jumlah_kas_keluar <= 0) {
            throw new Exception("Jumlah pengeluaran kas harus lebih besar dari 0.");
        }

        $total_beban = $jumlah_kas_keluar;

        // Perbaikan: Menggunakan PDO prepare bawaan database_fungsi yang benar
        $sql_beban = "INSERT INTO beban_operasional (tanggal_beban, keterangan, kategori_beban_id, jumlah) VALUES (?, ?, ?, ?)";
        $stmt_beban = $koneksi->prepare($sql_beban);
        $stmt_beban->execute([$tanggal, $keterangan, $kategori_id, $total_beban]);
        $beban_id = db_insert_id($koneksi);
        
        if (!$beban_id) { throw new Exception("Gagal menyimpan data beban."); }

        $kategori_q = query("SELECT kode_akun_terkait FROM kategori_beban WHERE id = ?", [$kategori_id]);
        if(empty($kategori_q)) { throw new Exception("Kategori beban tidak valid."); }
        $kode_akun_beban = $kategori_q[0]['kode_akun_terkait'];

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        $nomor_bukti = 'BBN-' . $beban_id;
        
        $entri_jurnal = [
            ['kode_akun' => $kode_akun_beban, 'debit' => $total_beban, 'kredit' => 0],
            ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $jumlah_kas_keluar]
        ];

        buatJurnal($nomor_bukti, $tanggal, $keterangan, $entri_jurnal);
        db_commit($koneksi);
        
        catatLog('Tambah Beban', 'Beban baru: ' . $keterangan . ' (Kas Keluar: Rp '.number_format($jumlah_kas_keluar).')');
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/lihat_beban&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/form_beban&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}