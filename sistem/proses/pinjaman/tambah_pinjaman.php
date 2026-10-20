<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pinjaman');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $nama = $_POST['nama_kreditur'];
        $tanggal = $_POST['tanggal_pinjaman'];
        $jumlah = (float)str_replace('.', '', $_POST['jumlah_pokok']);
        $keterangan = $_POST['keterangan'];
        
        $sql = "INSERT INTO pinjaman_diterima (nama_kreditur, tanggal_pinjaman, jumlah_pokok, keterangan) VALUES (?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$nama, $tanggal, $jumlah, $keterangan]);
        
        $pinjaman_id = db_insert_id($koneksi);
        if (!$pinjaman_id) { throw new Exception("Gagal menyimpan data pinjaman."); }

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1111';
        $akun_utang_bank = $_PENGATURAN['akun_map_utang_bank'] ?? '2211';

        $nomor_bukti = 'PD-' . $pinjaman_id;
        $keterangan_jurnal = "Penerimaan Pinjaman dari " . $nama;
        $entri_jurnal = [
            ['kode_akun' => $akun_kas, 'debit' => $jumlah, 'kredit' => 0],
            ['kode_akun' => $akun_utang_bank, 'debit' => 0, 'kredit' => $jumlah]
        ];
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Tambah Pinjaman Diterima', 'Mencatat pinjaman dari ' . $nama . ' sebesar Rp ' . number_format($jumlah));
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman/lihat_pinjaman&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman/lihat_pinjaman&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}