<?php
define('ENVIRONMENT', getenv('APP_ENV') ?: 'production');

// Catatan: pesan error TIDAK pernah ditampilkan ke user di produksi (agar tidak
// membocorkan path/kredensial), TAPI harus tetap tercatat di berkas log. Hosting
// bersama umumnya menyetel log_errors=Off, sehingga error fatal PHP lama-lama
// menjadi "HTTP 500 halaman kosong" tanpa jejak apa pun.
if (!defined('LOG_ERRORS_DIR')) define('LOG_ERRORS_DIR', dirname(__DIR__) . '/database');
$logErrorFile = LOG_ERRORS_DIR . '/php_error.log';
if (!is_dir(LOG_ERRORS_DIR)) @mkdir(LOG_ERRORS_DIR, 0775, true);

ini_set('log_errors', '1');
ini_set('error_log', $logErrorFile);
// Batasi ukuran agar log tidak membengkak tanpa batas.
if (is_file($logErrorFile) && filesize($logErrorFile) > 2 * 1024 * 1024) {
    @rename($logErrorFile, $logErrorFile . '.1');
}

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
}

// Tangkap error fatal/warning yang tidak tertangani (TypeError, ArgumentCountError,
// dsb.) agar tetap ditulis ke log berapa pun PHP tidak sempat memprosesnya.
register_shutdown_function(function () use ($logErrorFile) {
    $e = error_get_last();
    if ($e === null) return;
    if (!($e['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR))) return;
    $waktu = date('Y-m-d H:i:s');
    $baris = sprintf(
        "[%s] FATAL(%d): %s | %s:%d | UA=%s | URI=%s\n",
        $waktu,
        $e['type'],
        $e['message'],
        $e['file'],
        $e['line'],
        substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 120),
        substr($_SERVER['REQUEST_URI'] ?? '-', 0, 200)
    );
    @file_put_contents($logErrorFile, $baris, FILE_APPEND | LOCK_EX);
});

// Set zona waktu ke Waktu Indonesia Barat (DKI Jakarta)
date_default_timezone_set('Asia/Jakarta');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';

$basePath = '/';
if (php_sapi_name() !== 'cli-server') {
    $scriptName = $_SERVER['SCRIPT_NAME']; 
    $baseDir = dirname($scriptName); 
    $basePath = preg_replace('#/publik/?$#i', '', $baseDir);
    $basePath = rtrim($basePath, '/') . '/';
}

define('BASE_URL', $protocol . $domainName . $basePath);
define('SISTEM_PATH', dirname(__DIR__));

require_once SISTEM_PATH . '/../vendor/autoload.php';
require_once SISTEM_PATH . '/konfigurasi/database.php';
require_once SISTEM_PATH . '/fungsi/database_fungsi.php';
require_once SISTEM_PATH . '/fungsi/autentikasi_fungsi.php';

function auto_migrate_database() {
    try {
        $koneksi = getKoneksi();
        
        $schemas = [
            "CREATE TABLE IF NOT EXISTS daftar_akun (kode_akun TEXT PRIMARY KEY, nama_akun TEXT NOT NULL, kelompok_akun TEXT NOT NULL, unit_usaha TEXT DEFAULT 'Konsolidasi', saldo_normal TEXT NOT NULL, kategori_arus_kas TEXT NULL, urutan_laporan INTEGER NULL)",
            "CREATE TABLE IF NOT EXISTS pengaturan (pengaturan_key TEXT PRIMARY KEY, pengaturan_value TEXT)",
            "CREATE TABLE IF NOT EXISTS pengguna (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_pengguna TEXT UNIQUE, kata_sandi TEXT, nama_lengkap TEXT, role TEXT DEFAULT 'admin', wajib_ganti_sandi INTEGER DEFAULT 0, anggota_id INTEGER NULL, tanggal_lahir TEXT NULL, jenis_kelamin TEXT NULL, tanggal_masuk TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS anggota (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_lengkap TEXT, alamat TEXT, telepon TEXT, tanggal_bergabung TEXT, tanggal_lahir TEXT, jenis_kelamin TEXT, pekerjaan TEXT, status_keanggotaan TEXT DEFAULT 'Aktif', tanggal_keluar TEXT, dana_dikembalikan TEXT DEFAULT 'Belum', jenis_entitas TEXT DEFAULT 'Individu')",
            "CREATE TABLE IF NOT EXISTS jurnal_umum (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, nomor_bukti TEXT, kode_akun TEXT, keterangan TEXT, debit REAL DEFAULT 0, kredit REAL DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS kategori_beban (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_kategori TEXT, kode_akun_terkait TEXT, anggaran_tahunan REAL DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS produk (id INTEGER PRIMARY KEY AUTOINCREMENT, kode_produk TEXT, barcode TEXT NULL, nama_produk TEXT, satuan TEXT, harga_beli REAL, harga_rata_rata REAL, harga_jual REAL, stok INTEGER DEFAULT 0, stok_minimum INTEGER DEFAULT 0, id_produk_induk INTEGER NULL, kuantitas_konversi INTEGER NULL, jenis_barang TEXT DEFAULT 'Barang Dagangan', status_aktif INTEGER DEFAULT 1)",
            "CREATE TABLE IF NOT EXISTS pemasok (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_pemasok TEXT, alamat TEXT, telepon TEXT, email TEXT, status_aktif INTEGER DEFAULT 1, keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS pembelian (id INTEGER PRIMARY KEY AUTOINCREMENT, nomor_faktur_pemasok TEXT, tanggal_pembelian TEXT, pemasok_id INTEGER, total REAL, metode_pembayaran TEXT DEFAULT 'Kredit', tanggal_jatuh_tempo TEXT NULL, kode_akun_bank TEXT NULL, status_batal INTEGER DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS detail_pembelian (id INTEGER PRIMARY KEY AUTOINCREMENT, pembelian_id INTEGER, produk_id INTEGER, kuantitas INTEGER, harga_saat_transaksi REAL, subtotal REAL)",
            "CREATE TABLE IF NOT EXISTS penjualan (id INTEGER PRIMARY KEY AUTOINCREMENT, nomor_faktur TEXT, tanggal_penjualan TEXT, anggota_id INTEGER NULL, nama_pelanggan_umum TEXT NULL, total REAL, diskon REAL DEFAULT 0, metode_pembayaran TEXT DEFAULT 'Tunai', status_batal INTEGER DEFAULT 0, tanggal_jatuh_tempo TEXT NULL, kode_akun_bank TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS detail_penjualan (id INTEGER PRIMARY KEY AUTOINCREMENT, penjualan_id INTEGER, produk_id INTEGER, kuantitas INTEGER, harga_saat_transaksi REAL, subtotal REAL)",
            "CREATE TABLE IF NOT EXISTS beban_operasional (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_beban TEXT, keterangan TEXT, kategori_beban_id INTEGER, jumlah REAL, status_batal INTEGER DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS aset_tetap (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_aset TEXT, kategori_aset TEXT, tanggal_perolehan TEXT, harga_perolehan REAL, masa_manfaat_tahun INTEGER, nilai_residu REAL)",
            "CREATE TABLE IF NOT EXISTS simpanan (id INTEGER PRIMARY KEY AUTOINCREMENT, anggota_id INTEGER, jenis_simpanan TEXT, jumlah REAL, tanggal_setor TEXT, bentuk_setoran TEXT DEFAULT 'Tunai', kode_akun_aset TEXT NULL, keterangan TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS simpanan_sukarela_transaksi (id INTEGER PRIMARY KEY AUTOINCREMENT, anggota_id INTEGER, jenis_transaksi TEXT, jumlah REAL, tanggal_transaksi TEXT, keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS pinjaman_anggota (id INTEGER PRIMARY KEY AUTOINCREMENT, anggota_id INTEGER, tanggal_pinjaman TEXT, jumlah_pokok REAL, jangka_waktu_bulan INTEGER, bunga_persen_tahun REAL, keterangan TEXT, jenis_pinjaman TEXT DEFAULT 'Umum', biaya_admin REAL DEFAULT 0, metode_pencairan TEXT DEFAULT 'Tunai', kode_akun_bank TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS pembayaran_angsuran (id INTEGER PRIMARY KEY AUTOINCREMENT, pinjaman_anggota_id INTEGER, tanggal_bayar TEXT, pokok_dibayar REAL CHECK(pokok_dibayar >= 0), bunga_dibayar REAL CHECK(bunga_dibayar >= 0), keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS pinjaman_diterima (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_kreditur TEXT, tanggal_pinjaman TEXT, jumlah_pokok REAL, keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS pembayaran_pinjaman (id INTEGER PRIMARY KEY AUTOINCREMENT, pinjaman_id INTEGER, tanggal_bayar TEXT, pokok_dibayar REAL CHECK(pokok_dibayar >= 0), bunga_dibayar REAL CHECK(bunga_dibayar >= 0), keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS hibah_pemerintah (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_terima TEXT, keterangan TEXT, jumlah REAL, jenis_hibah TEXT, status_syarat TEXT, tanggal_syarat_terpenuhi TEXT NULL, nomor_bukti_penerimaan TEXT NULL, nomor_bukti_pengakuan TEXT NULL, pemberi_hibah TEXT NULL, bentuk_hibah TEXT DEFAULT 'Tunai', kode_akun_aset TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS histori_shu (id INTEGER PRIMARY KEY AUTOINCREMENT, tahun INTEGER, total_shu REAL, alokasi_cadangan REAL, alokasi_jasa_anggota REAL, alokasi_dana_pengurus REAL, alokasi_dana_sosial REAL, tanggal_proses DATETIME DEFAULT CURRENT_TIMESTAMP)",
            "CREATE TABLE IF NOT EXISTS histori_shu_anggota (id INTEGER PRIMARY KEY AUTOINCREMENT, histori_shu_id INTEGER, anggota_id INTEGER, tahun INTEGER, jasa_modal_diterima REAL, jasa_usaha_diterima REAL, total_poin_modal INTEGER, total_poin_usaha INTEGER, nilai_per_poin_modal REAL, nilai_per_poin_usaha REAL, total_diterima REAL)",
            "CREATE TABLE IF NOT EXISTS histori_poin_ppa (id INTEGER PRIMARY KEY AUTOINCREMENT, anggota_id INTEGER, jenis_poin TEXT, jumlah_poin INTEGER, keterangan TEXT, id_transaksi_terkait INTEGER NULL, tanggal_perolehan TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_surat_masuk (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_masuk TEXT, nomor_surat TEXT, tanggal_surat TEXT, perihal TEXT, asal_surat TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_surat_keluar (id INTEGER PRIMARY KEY AUTOINCREMENT, nomor_surat TEXT, tanggal_surat TEXT, perihal TEXT, tanggal_kirim TEXT, tujuan_surat TEXT, keterangan TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_buku_tamu (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, nama_tamu TEXT, alamat TEXT, bertemu_dengan TEXT, keperluan TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_kejadian_penting (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, jenis_kegiatan TEXT, tempat TEXT, uraian TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_rapat_anggota (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_rapat TEXT, tempat_rapat TEXT, jenis_rapat TEXT, jumlah_anggota_total INTEGER, jumlah_hadir INTEGER, pimpinan_rapat TEXT, undangan_hadir TEXT, acara_rapat TEXT, keputusan_rapat TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_rapat_pengurus (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_rapat TEXT, tempat_rapat TEXT, jenis_rapat TEXT, jumlah_pengurus_total INTEGER, jumlah_hadir INTEGER, pimpinan_rapat TEXT, undangan_hadir TEXT, acara_rapat TEXT, keputusan_rapat TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_rapat_pengawas (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal_rapat TEXT, tempat_rapat TEXT, jenis_rapat TEXT, jumlah_pengawas_total INTEGER, jumlah_hadir INTEGER, pimpinan_rapat TEXT, undangan_hadir TEXT, acara_rapat TEXT, keputusan_rapat TEXT)",
            "CREATE TABLE IF NOT EXISTS adm_catatan_pengawas (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, pengawas_id INTEGER, isi_catatan TEXT, tanggapan_pengurus TEXT, ditanggapi_oleh INTEGER NULL, tanggal_tanggapan TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS adm_saran_anggota (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, anggota_id INTEGER, isi_saran TEXT, tanggapan_pengurus TEXT, ditanggapi_oleh INTEGER NULL, tanggal_tanggapan TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS adm_anjuran_pejabat (id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, nama_pejabat TEXT, jabatan_dan_kantor TEXT, isi_anjuran TEXT, tanggapan_pengurus TEXT, ditanggapi_oleh INTEGER NULL, tanggal_tanggapan TEXT NULL)",
            "CREATE TABLE IF NOT EXISTS log_aktivitas (id INTEGER PRIMARY KEY AUTOINCREMENT, timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, user_id INTEGER NULL, nama_pengguna TEXT, role_pengguna TEXT, aksi TEXT, keterangan TEXT, ip_address TEXT)",
            "CREATE TABLE IF NOT EXISTS mst_jabatan (id INTEGER PRIMARY KEY AUTOINCREMENT, nama_jabatan TEXT, gaji_pokok REAL DEFAULT 0, tunjangan_jabatan REAL DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS mst_karyawan (id INTEGER PRIMARY KEY AUTOINCREMENT, anggota_id INTEGER NULL, nama_karyawan TEXT NULL, telepon TEXT NULL, alamat TEXT NULL, jabatan_id INTEGER, tanggal_masuk TEXT, status_aktif INTEGER DEFAULT 1)",
            "CREATE TABLE IF NOT EXISTS trx_penggajian (id INTEGER PRIMARY KEY AUTOINCREMENT, karyawan_id INTEGER, tanggal_pembayaran TEXT, periode_bulan TEXT, jenis_pembayaran TEXT, gaji_pokok REAL, tunjangan REAL, potongan REAL, total_bersih REAL, keterangan TEXT, nomor_bukti TEXT, status_batal INTEGER DEFAULT 0)",
                        "CREATE TABLE IF NOT EXISTS aset_tetap_penyusutan (id INTEGER PRIMARY KEY AUTOINCREMENT, aset_id INTEGER NOT NULL, kategori_aset TEXT, kode_akun_akumulasi TEXT, beban REAL DEFAULT 0, tanggal TEXT, nomor_bukti TEXT, keterangan TEXT)"
                    ];

        foreach ($schemas as $q) {
            $koneksi->exec($q);
        }

        $cekAdmin = query("SELECT id FROM pengguna LIMIT 1");
        if (empty($cekAdmin)) {
            $defaultPass = password_hash('admin', PASSWORD_DEFAULT);
            eksekusi("INSERT INTO pengguna (nama_pengguna, kata_sandi, nama_lengkap, role, wajib_ganti_sandi) VALUES ('admin', ?, 'Administrator', 'admin', 0)", [$defaultPass]);
        }

        // Alter tabel Pinjaman Anggota
        $colsPinjaman = query("PRAGMA table_info(pinjaman_anggota)");
        $colNamesPinjaman = array_column($colsPinjaman, 'name');
        if (!empty($colNamesPinjaman)) {
            if (!in_array('jenis_pinjaman', $colNamesPinjaman)) { eksekusi("ALTER TABLE pinjaman_anggota ADD COLUMN jenis_pinjaman TEXT DEFAULT 'Umum'"); }
            if (!in_array('biaya_admin', $colNamesPinjaman)) { eksekusi("ALTER TABLE pinjaman_anggota ADD COLUMN biaya_admin REAL DEFAULT 0"); }
            if (!in_array('metode_pencairan', $colNamesPinjaman)) { eksekusi("ALTER TABLE pinjaman_anggota ADD COLUMN metode_pencairan TEXT DEFAULT 'Tunai'"); }
            if (!in_array('kode_akun_bank', $colNamesPinjaman)) { eksekusi("ALTER TABLE pinjaman_anggota ADD COLUMN kode_akun_bank TEXT NULL"); }
        }

        $colsSimpanan = query("PRAGMA table_info(simpanan)");
        $colNamesSimpanan = array_column($colsSimpanan, 'name');
        if (!empty($colNamesSimpanan)) {
            if (!in_array('bentuk_setoran', $colNamesSimpanan)) { eksekusi("ALTER TABLE simpanan ADD COLUMN bentuk_setoran TEXT DEFAULT 'Tunai'"); }
            if (!in_array('kode_akun_aset', $colNamesSimpanan)) { eksekusi("ALTER TABLE simpanan ADD COLUMN kode_akun_aset TEXT NULL"); }
            if (!in_array('keterangan', $colNamesSimpanan)) { eksekusi("ALTER TABLE simpanan ADD COLUMN keterangan TEXT NULL"); }
        }

        $colsHibah = query("PRAGMA table_info(hibah_pemerintah)");
                $colNamesHibah = array_column($colsHibah, 'name');
                if (!empty($colNamesHibah)) {
                    if (!in_array('pemberi_hibah', $colNamesHibah)) { eksekusi("ALTER TABLE hibah_pemerintah ADD COLUMN pemberi_hibah TEXT NULL"); }
                    if (!in_array('bentuk_hibah', $colNamesHibah)) { eksekusi("ALTER TABLE hibah_pemerintah ADD COLUMN bentuk_hibah TEXT DEFAULT 'Tunai'"); }
                    if (!in_array('kode_akun_aset', $colNamesHibah)) { eksekusi("ALTER TABLE hibah_pemerintah ADD COLUMN kode_akun_aset TEXT NULL"); }
                }

                // Kolom jatuh tempo & bank utk penjualan kredit / pembelian kredit (laporan umur piutang & utang)
                $colsPembelian = query("PRAGMA table_info(pembelian)");
                $colNamesPembelian = array_column($colsPembelian, 'name');
                if (!empty($colNamesPembelian)) {
                    if (!in_array('tanggal_jatuh_tempo', $colNamesPembelian)) { eksekusi("ALTER TABLE pembelian ADD COLUMN tanggal_jatuh_tempo TEXT NULL"); }
                    if (!in_array('kode_akun_bank', $colNamesPembelian)) { eksekusi("ALTER TABLE pembelian ADD COLUMN kode_akun_bank TEXT NULL"); }
                    // Retur pembelian memakai soft delete (sama seperti penjualan) agar jejak audit & hitungan utang usaha tetap utuh.
                    if (!in_array('status_batal', $colNamesPembelian)) { eksekusi("ALTER TABLE pembelian ADD COLUMN status_batal INTEGER DEFAULT 0"); }
                }

                $colsPenjualan = query("PRAGMA table_info(penjualan)");
                $colNamesPenjualan = array_column($colsPenjualan, 'name');
                if (!empty($colNamesPenjualan)) {
                    if (!in_array('tanggal_jatuh_tempo', $colNamesPenjualan)) { eksekusi("ALTER TABLE penjualan ADD COLUMN tanggal_jatuh_tempo TEXT NULL"); }
                    if (!in_array('kode_akun_bank', $colNamesPenjualan)) { eksekusi("ALTER TABLE penjualan ADD COLUMN kode_akun_bank TEXT NULL"); }
                }

                $colsPemasok = query("PRAGMA table_info(pemasok)");
                $colNamesPemasok = array_column($colsPemasok, 'name');
                if (!empty($colNamesPemasok)) {
                    if (!in_array('keterangan', $colNamesPemasok)) { eksekusi("ALTER TABLE pemasok ADD COLUMN keterangan TEXT NULL"); }
                }

                // mst_karyawan: kode memakai nama_karyawan & alamat (schema lama pakai nama_lengkap, tanpa alamat)
                $colsKaryawan = query("PRAGMA table_info(mst_karyawan)");
                $colNamesKaryawan = array_column($colsKaryawan, 'name');
                if (!empty($colNamesKaryawan)) {
                    if (!in_array('nama_karyawan', $colNamesKaryawan)) { eksekusi("ALTER TABLE mst_karyawan ADD COLUMN nama_karyawan TEXT NULL"); }
                    if (!in_array('alamat', $colNamesKaryawan)) { eksekusi("ALTER TABLE mst_karyawan ADD COLUMN alamat TEXT NULL"); }
                    // backfill nama_karyawan dari kolom lama nama_lengkap jika memang hanya itu sumbernya
                    if (in_array('nama_lengkap', $colNamesKaryawan)) {
                        eksekusi("UPDATE mst_karyawan SET nama_karyawan = nama_lengkap WHERE nama_karyawan IS NULL OR nama_karyawan = ''");
                    }
                }
        
                // anggota: kolom jenis_entitas (2026) - backfill utk DB lama
                $colsAnggota = query("PRAGMA table_info(anggota)");
                $colNamesAnggota = array_column($colsAnggota, 'name');
                if (!empty($colNamesAnggota)) {
                    if (!in_array('jenis_entitas', $colNamesAnggota)) { eksekusi("ALTER TABLE anggota ADD COLUMN jenis_entitas TEXT DEFAULT 'Individu'"); }
                }



                // Barcode produk (scan kasir & pembelian) - dipisah dari kode_produk
                $colsProduk = query("PRAGMA table_info(produk)");
                $colNamesProduk = array_column($colsProduk, 'name');
                if (!empty($colNamesProduk)) {
                    if (!in_array('barcode', $colNamesProduk)) { eksekusi("ALTER TABLE produk ADD COLUMN barcode TEXT NULL"); }
                    eksekusi("CREATE INDEX IF NOT EXISTS idx_produk_barcode ON produk(barcode)");
                }
        // Memasukkan Default Chart of Account (CoA) sesuai Kepmendesa 136 Tahun 2022
        $kepmendesa_coa = [
            ['1.1.01.01', 'Kas Tunai', 'Aset', 'Debit', 'Operasi'],
            ['1.1.01.02', 'Kas di Bank BSI', 'Aset', 'Debit', 'Operasi'],
            ['1.1.01.03', 'Kas di Bank Mandiri', 'Aset', 'Debit', 'Operasi'],
            ['1.1.01.04', 'Kas di Bank BRI', 'Aset', 'Debit', 'Operasi'],
            ['1.1.01.98', 'Kas Kecil (Petty Cash)', 'Aset', 'Debit', 'Operasi'],
            ['1.1.03.01', 'Piutang Usaha', 'Aset', 'Debit', 'Operasi'],
            ['1.1.03.04', 'Piutang Kepada Masyarakat (SPP)', 'Aset', 'Debit', 'Operasi'],
            ['1.1.03.05', 'Piutang Kepada Masyarakat (UEP)', 'Aset', 'Debit', 'Operasi'], // Tambahan untuk UEP
            ['1.1.04.01', 'Penyisihan Piutang Usaha Tak Tertagih', 'Aset', 'Kredit', null],
            ['1.1.05.01', 'Persediaan Barang Dagangan', 'Aset', 'Debit', 'Operasi'],
            ['1.1.06.01', 'Alat Tulis Kantor (ATK)', 'Aset', 'Debit', 'Operasi'],
            ['1.1.07.01', 'Sewa Dibayar Dimuka', 'Aset', 'Debit', 'Operasi'],
            ['1.1.99.00', 'RK Unit Usaha', 'Aset', 'Debit', 'Operasi'],
            ['1.3.01.01', 'Tanah', 'Aset', 'Debit', 'Investasi'],
            ['1.3.02.01', 'Kendaraan', 'Aset', 'Debit', 'Investasi'],
            ['1.3.03.01', 'Peralatan dan Mesin', 'Aset', 'Debit', 'Investasi'],
            ['1.3.05.01', 'Gedung dan Bangunan', 'Aset', 'Debit', 'Investasi'],
            ['1.3.06.01', 'Konstruksi Dalam Pengerjaan (KDP)', 'Aset', 'Debit', 'Investasi'],
            ['1.3.07.01', 'Akumulasi Penyusutan Kendaraan', 'Aset', 'Kredit', 'Investasi'],
            ['1.3.07.02', 'Akumulasi Penyusutan Peralatan dan Mesin', 'Aset', 'Kredit', 'Investasi'],
            ['1.3.07.04', 'Akumulasi Penyusutan Gedung dan Bangunan', 'Aset', 'Kredit', 'Investasi'],
            ['1.4.01.01', 'Software', 'Aset', 'Debit', 'Investasi'],
            ['1.4.02.01', 'Amortisasi Aset takberwujud', 'Aset', 'Kredit', null],
            ['2.1.01.01', 'Utang Usaha', 'Liabilitas', 'Kredit', 'Operasi'],
            ['2.1.02.01', 'PPN Keluaran', 'Liabilitas', 'Kredit', 'Operasi'],
            ['2.1.02.04', 'PPh 29', 'Liabilitas', 'Kredit', 'Operasi'],
            ['2.1.03.01', 'Utang Gaji dan Tunjangan', 'Liabilitas', 'Kredit', 'Operasi'],
            ['2.1.05.01', 'Utang kepada Pihak Ketiga Jk. Pendek', 'Liabilitas', 'Kredit', 'Pendanaan'],
            ['2.1.05.99', 'Utang kepada Pihak Ketiga Jk. Pendek Lainnya', 'Liabilitas', 'Kredit', 'Pendanaan'],
            ['2.1.09.99', 'Utang Jangka Pendek Lainnya', 'Liabilitas', 'Kredit', 'Pendanaan'],
            ['2.2.01.01', 'Utang Ke Bank', 'Liabilitas', 'Kredit', 'Pendanaan'],
            ['2.2.99.99', 'Utang Jangka Panjang Lainnya', 'Liabilitas', 'Kredit', 'Pendanaan'],
            ['3.1.01.01', 'Penyertaan Modal Desa', 'Ekuitas', 'Kredit', null],
            ['3.1.02.01', 'Penyertaan Modal Masyarakat A', 'Ekuitas', 'Kredit', null],
            ['3.3.01.01', 'Saldo Laba Tidak Dicadangkan', 'Ekuitas', 'Kredit', null],
            ['3.3.01.02', 'Saldo Laba Dicadangkan untuk Pembelian Aset Tetap', 'Ekuitas', 'Kredit', null],
            ['3.4.01.01', 'Modal Donasi/Sumbangan', 'Ekuitas', 'Kredit', null],
            ['3.8.00.00', 'RK Pusat', 'Ekuitas', 'Kredit', null],
            ['3.9.01.01', 'Ikhtisar Laba Rugi', 'Ekuitas', 'Kredit', null],
            ['4.1.08.01', 'Pendapatan Simpan Pinjam', 'Pendapatan', 'Kredit', 'Operasi'],
            ['4.1.08.02', 'Pendapatan Administrasi Pinjaman', 'Pendapatan', 'Kredit', 'Operasi'], // Tambahan untuk fee admin
            ['4.2.01.01', 'Pendapatan Penjualan Makanan/Minuman', 'Pendapatan', 'Kredit', 'Operasi'],
            ['4.2.01.99', 'Diskon Penjualan Barang Dagangan', 'Pendapatan', 'Debit', 'Operasi'],
            ['5.1.01.01', 'Harga Pokok Penjualan Barang Dagangan', 'Beban', 'Debit', 'Operasi'],
            ['6.1.01.01', 'Beban Gaji dan Tunjangan Bag. Adum', 'Beban', 'Debit', 'Operasi'],
            ['6.1.02.01', 'Beban Alat Tulis Kantor (ATK)', 'Beban', 'Debit', 'Operasi'],
            ['6.1.04.01', 'Beban Listrik', 'Beban', 'Debit', 'Operasi'],
            ['6.1.05.01', 'Beban Sewa', 'Beban', 'Debit', 'Operasi'],
            ['6.1.07.01', 'Beban Penyisihan Piutang Tak Tertagih', 'Beban', 'Debit', 'Operasi'],
            ['6.1.07.02', 'Beban Penyusutan Kendaraan', 'Beban', 'Debit', 'Operasi'],
            ['6.1.07.03', 'Beban Penyusutan Peralatan dan Mesin', 'Beban', 'Debit', 'Operasi'],
            ['6.1.07.05', 'Beban Penyusutan Gedung dan Bangunan', 'Beban', 'Debit', 'Operasi'],
            ['6.1.07.06', 'Beban Amortisasi Aset takberwujud', 'Beban', 'Debit', 'Operasi'],
            ['7.1.01.01', 'Pendapatan Bunga Bank', 'Pendapatan Lain-lain', 'Kredit', 'Operasi'],
            ['7.1.05.01', 'Keuntungan Penjualan Aset Tetap', 'Pendapatan Lain-lain', 'Kredit', 'Operasi'],
            ['7.1.99.99', 'Pendapatan Lain-lain lainnya', 'Pendapatan Lain-lain', 'Kredit', 'Operasi'],
            ['7.2.01.01', 'Beban Administrasi Bank', 'Beban Lain-lain', 'Debit', 'Operasi'],
            ['7.2.02.01', 'Beban Bunga', 'Beban Lain-lain', 'Debit', 'Operasi'],
            ['7.2.04.01', 'Kerugian Penjualan Aset Tetap', 'Beban Lain-lain', 'Debit', 'Operasi'],
            ['7.2.99.99', 'Beban Lain-lain lainnya', 'Beban Lain-lain', 'Debit', 'Operasi'],
            ['7.3.01.09', 'Beban PPh 29', 'Beban Lain-lain', 'Debit', 'Operasi']
        ];
        
        foreach ($kepmendesa_coa as $acc) {
            $cek = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun = ?", [$acc[0]]);
            if (empty($cek)) {
                eksekusi("INSERT INTO daftar_akun (kode_akun, nama_akun, kelompok_akun, unit_usaha, saldo_normal, kategori_arus_kas) VALUES (?, ?, ?, 'Konsolidasi', ?, ?)", $acc);
            }
        }
    } catch (Exception $e) {
        error_log("Auto Migrate Warning: " . $e->getMessage());
    }
}
auto_migrate_database();

generateCsrfToken();
global $_PENGATURAN;
$_PENGATURAN = getSemuaPengaturan();
define('MODE_ENTITAS', $_PENGATURAN['mode_entitas'] ?? 'Koperasi');