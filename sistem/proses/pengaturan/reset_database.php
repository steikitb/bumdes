<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $konfirmasi = $_POST['konfirmasi'] ?? '';
    
    if (trim($konfirmasi) !== 'RESET DATA') {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=' . urlencode('Gagal memproses. Teks konfirmasi harus persis: RESET DATA'));
        exit;
    }

    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tables = [
            'anggota', 'pemasok', 'produk', 'aset_tetap', 'simpanan', 
            'simpanan_sukarela_transaksi', 'pinjaman_anggota', 'pembayaran_angsuran',
            'pinjaman_diterima', 'pembayaran_pinjaman', 'pembelian', 'detail_pembelian',
            'penjualan', 'detail_penjualan', 'beban_operasional', 'hibah_pemerintah',
            'jurnal_umum', 'histori_shu', 'histori_shu_anggota', 'histori_poin_ppa',
            'adm_surat_masuk', 'adm_surat_keluar', 'adm_buku_tamu', 'adm_kejadian_penting', 
            'adm_rapat_anggota', 'adm_rapat_pengurus', 'adm_rapat_pengawas', 
            'adm_catatan_pengawas', 'adm_saran_anggota', 'adm_anjuran_pejabat', 
            'log_aktivitas', 'mst_jabatan', 'mst_karyawan', 'trx_penggajian', 'kategori_beban'
        ];
        
        foreach($tables as $table) {
            $koneksi->exec("DELETE FROM $table");
            $koneksi->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
        }

        // Hapus semua pengguna kecuali akun admin (Asumsi admin memiliki ID 1)
        $koneksi->exec("DELETE FROM pengguna WHERE id != 1");

        db_commit($koneksi);
        catatLog('Reset Database', 'PENTING: Admin telah melakukan RESET / Pengosongan seluruh data transaksi ke titik Nol (Factory Reset).');
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/pemeliharaan&status=gagal&error=' . urlencode('Terjadi kesalahan saat menghapus tabel: ' . $e->getMessage()));
    }
    exit;
}