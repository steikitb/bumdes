<?php
function getKoneksi() {
    static $koneksi = null;
    if ($koneksi === null) {
        try {
            $db_path = defined('DB_PATH') ? DB_PATH : SISTEM_PATH . '/database/koperasi.sqlite';
            $dir = dirname($db_path);
            
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception("Gagal membuat direktori database di: $dir.");
                }
            }
            if (!is_writable($dir)) {
                throw new Exception("Folder database tidak dapat ditulis: $dir");
            }
            $koneksi = new PDO("sqlite:" . $db_path);
            $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $koneksi->exec('PRAGMA foreign_keys = ON;');
            $koneksi->exec('PRAGMA journal_mode = WAL;');
        } catch (Exception $e) {
            die("Koneksi ke database gagal: " . $e->getMessage());
        }
    }
    return $koneksi;
}

// 5. Perbaikan: Hapus parameter array binding type ala MySQLi untuk efisiensi
function query($sql, $params = []) {
    $koneksi = getKoneksi();
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("[DATABASE ERROR] query failed: " . $e->getMessage() . " | SQL: " . $sql);
        return [];
    }
}

function eksekusi($sql, $params = []) {
    $koneksi = getKoneksi();
    try {
        $stmt = $koneksi->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch(Exception $e) {
        error_log("[DATABASE ERROR] eksekusi failed: " . $e->getMessage());
        throw new Exception("Terjadi kesalahan sistem saat menyimpan data.");
    }
}

function escape($string) {
    return $string;
}

function cekTutupBuku($tanggal) {
    global $_PENGATURAN;
    $tahun = date('Y', strtotime($tanggal));
    $cek = query("SELECT id FROM histori_shu WHERE tahun = ?", [$tahun]);
    if (!empty($cek)) {
        throw new Exception("Aksi ditolak: Tahun buku $tahun telah ditutup. Transaksi pada periode ini tidak dapat diubah.");
    }
    
    $bulan_kunci = $_PENGATURAN['periode_akuntansi_kunci_bulan'] ?? '';
    if (!empty($bulan_kunci)) {
        $waktu_trx = strtotime(date('Y-m', strtotime($tanggal)));
        $waktu_kunci = strtotime($bulan_kunci);
        if ($waktu_trx <= $waktu_kunci) {
            throw new Exception("Aksi ditolak: Bulan pembukuan ini telah dikunci (Lock Date) oleh Admin.");
        }
    }
}

function buatJurnal($nomor_bukti, $tanggal, $keterangan, $entri_jurnal) {
    $koneksi = getKoneksi();
    $total_debit = 0;
    $total_kredit = 0;
    $akuns = [];
    
    foreach ($entri_jurnal as $entri) {
        $total_debit += $entri['debit'] ?? 0;
        $total_kredit += $entri['kredit'] ?? 0;
        $akuns[] = $entri['kode_akun'];
    }
    
    if (round($total_debit, 2) !== round($total_kredit, 2)) {
        throw new Exception("Jurnal tidak seimbang. Debit: $total_debit, Kredit: $total_kredit");
    }

    $akuns = array_unique($akuns);
    $placeholders = implode(',', array_fill(0, count($akuns), '?'));
    $cek_akun = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun IN ($placeholders)", $akuns);
    if (count($cek_akun) !== count($akuns)) {
        throw new Exception("Validasi Gagal: Terdapat Kode Akun yang tidak terdaftar di Master Data Akun.");
    }

    $sql = "INSERT INTO jurnal_umum (tanggal, nomor_bukti, kode_akun, keterangan, debit, kredit) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    foreach ($entri_jurnal as $entri) {
        $kode_akun = $entri['kode_akun'];
        $debit = $entri['debit'] ?? 0;
        $kredit = $entri['kredit'] ?? 0;
        if (!$stmt->execute([$tanggal, $nomor_bukti, $kode_akun, $keterangan, $debit, $kredit])) {
            throw new Exception("Gagal mengeksekusi jurnal.");
        }
    }
    return true;
}

function getSaldoAkun($kode_akun, $tanggal_akhir, $tanggal_mulai = null, $unit_usaha = null) {
    $info_akun_q = query("SELECT saldo_normal FROM daftar_akun WHERE kode_akun = ?", [$kode_akun]);
    if (empty($info_akun_q)) { return 0; }
    $saldo_normal = $info_akun_q[0]['saldo_normal'];
    $sql_jurnal = "
        SELECT SUM(ju.debit) as total_debit, SUM(ju.kredit) as total_kredit 
        FROM jurnal_umum ju
        JOIN daftar_akun da ON ju.kode_akun = da.kode_akun
        WHERE ju.kode_akun = ? AND ju.tanggal <= ?";
    $params = [$kode_akun, $tanggal_akhir];
    if ($tanggal_mulai) {
        $sql_jurnal .= " AND ju.tanggal >= ?";
        $params[] = $tanggal_mulai;
    }
    if ($unit_usaha && $unit_usaha !== 'Konsolidasi') {
        $sql_jurnal .= " AND (da.unit_usaha = ? OR da.unit_usaha = 'Konsolidasi')";
        $params[] = $unit_usaha;
    }
    $data_q = query($sql_jurnal, $params);
    $data = $data_q[0] ?? ['total_debit' => 0, 'total_kredit' => 0];
    $total_debit = $data['total_debit'] ?? 0;
    $total_kredit = $data['total_kredit'] ?? 0;
    if ($saldo_normal === 'Debit') {
        $saldo = $total_debit - $total_kredit;
    } else {
        $saldo = $total_kredit - $total_debit;
    }
    return $saldo;
}

function getSemuaPengaturan() {
    $pengaturan = [];
    $rows = query("SELECT pengaturan_key, pengaturan_value FROM pengaturan");
    if (empty($rows)) { return []; }
    foreach ($rows as $row) {
        $pengaturan[$row['pengaturan_key']] = $row['pengaturan_value'];
    }
    return $pengaturan;
}

function query_paginasi($sql_select_from, $sql_where, $params = [], $default_per_halaman = 25) {
    $halaman_sekarang = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
    $per_halaman = isset($_GET['limit']) ? (int)$_GET['limit'] : $default_per_halaman;
    // Lindungi dari argumen non-numerik (mis. string binding PDO yang tak sengaja
    // diteruskan ke parameter $default_per_halaman). Tanpa guard ini, nilai
    // seperti 'sss' lolos dari perbandingan "< 1" lalu menggagalkan ceil() dengan
    // TypeError "int / string" -> halaman kosong tanpa pesan.
    if (!is_numeric($per_halaman)) $per_halaman = 25;
    $per_halaman = (int)$per_halaman;
    if ($halaman_sekarang < 1) $halaman_sekarang = 1;
    if ($per_halaman < 1 || $per_halaman > 500) $per_halaman = 25;

    if (!is_array($params)) $params = [];

    $sql_total = "SELECT COUNT(*) as total FROM ($sql_select_from $sql_where) as subquery";
    $total_rows_data = query($sql_total, $params);
    $total_rows = $total_rows_data[0]['total'] ?? 0;
    $total_halaman = $total_rows > 0 ? ceil($total_rows / $per_halaman) : 1;
    if ($halaman_sekarang > $total_halaman) $halaman_sekarang = $total_halaman;
    $offset = ($halaman_sekarang - 1) * $per_halaman;
    
    $sql_data = "$sql_select_from $sql_where LIMIT ? OFFSET ?";
    $data_params = array_merge($params, [$per_halaman, $offset]);
    $rows = query($sql_data, $data_params);
    
    return ['data' => $rows, 'total_halaman' => $total_halaman, 'halaman_sekarang' => $halaman_sekarang, 'limit' => $per_halaman];
}

function render_paginasi($total_halaman, $halaman_sekarang) {
    if ($total_halaman <= 1) return;
    $params = $_GET;
    echo '<div class="join mt-6">';
    if ($halaman_sekarang > 1) {
        $params['pg'] = $halaman_sekarang - 1;
        echo '<a href="?' . http_build_query($params) . '" class="join-item btn">«</a>';
    }
    $params['pg'] = $halaman_sekarang;
    echo '<button class="join-item btn">Halaman ' . $halaman_sekarang . ' dari ' . $total_halaman . '</button>';
    if ($halaman_sekarang < $total_halaman) {
        $params['pg'] = $halaman_sekarang + 1;
        echo '<a href="?' . http_build_query($params) . '" class="join-item btn">»</a>';
    }
    echo '</div>';
}

function mapKelasAsetAkun($kategori_aset) {
    global $_PENGATURAN;
    $default_map = [
        'Tanah'                      => ['1.3.01.01', null, null],
        'Kendaraan'                  => ['1.3.02.01', $_PENGATURAN['akun_map_akumulasi_kendaraan'] ?? '1.3.07.01', $_PENGATURAN['akun_map_beban_penyusutan_kendaraan'] ?? '6.1.07.02'],
        'Peralatan dan Mesin'        => ['1.3.03.01', $_PENGATURAN['akun_map_akumulasi_peralatan'] ?? '1.3.07.02', $_PENGATURAN['akun_map_beban_penyusutan'] ?? '6.1.07.03'],
        'Bangunan'                   => ['1.3.05.01', $_PENGATURAN['akun_map_akumulasi_bangunan'] ?? '1.3.07.04', $_PENGATURAN['akun_map_beban_penyusutan_gedung'] ?? '6.1.07.05'],
        'Aset Tak Berwujud'          => ['1.4.01.01', $_PENGATURAN['akun_map_akumulasi_amortisasi'] ?? '1.4.02.01', $_PENGATURAN['akun_map_beban_amortisasi'] ?? '6.1.07.06'],
    ];
    return $default_map[$kategori_aset] ?? ['1.3.03.01', $_PENGATURAN['akun_map_akumulasi_peralatan'] ?? '1.3.07.02', $_PENGATURAN['akun_map_beban_penyusutan'] ?? '6.1.07.03'];
}

// Akumulasi penyusutan PER ASET (dari tabel pelacakan), bukan per-kategori.
function getAkumulasiAset($aset_id) {
    $q = query("SELECT COALESCE(SUM(beban), 0) AS akum FROM aset_tetap_penyusutan WHERE aset_id = ?", [$aset_id]);
    return (float)($q[0]['akum'] ?? 0);
}

function tambahPoinPPA($anggota_id, $jenis, $poin, $keterangan, $transaksi_id = null, $tanggal = null) {
    if ($poin <= 0) return;
    $tanggal_perolehan = $tanggal ?? date('Y-m-d');
    $sql = "INSERT INTO histori_poin_ppa (anggota_id, jenis_poin, jumlah_poin, keterangan, id_transaksi_terkait, tanggal_perolehan) VALUES (?, ?, ?, ?, ?, ?)";
    eksekusi($sql, [$anggota_id, $jenis, $poin, $keterangan, $transaksi_id, $tanggal_perolehan]);
}

function hitungSkorKredit($id) {
    global $_PENGATURAN; 
    $skor = 0;
    $rincian = [];
    
    $pembayaran_q = query("SELECT COUNT(pa.id) as total FROM pembayaran_angsuran pa JOIN pinjaman_anggota p ON pa.pinjaman_anggota_id = p.id WHERE p.anggota_id = ?", [$id]);
    $poin_pembayaran = ($pembayaran_q[0]['total'] ?? 0) * (int)($_PENGATURAN['skor_kredit_poin_per_pembayaran'] ?? 10);
    $skor += $poin_pembayaran;
    $rincian['Poin dari Pembayaran Angsuran'] = $poin_pembayaran;
    
    $simpanan_q = query("SELECT (SELECT COALESCE(SUM(jumlah), 0) FROM simpanan WHERE anggota_id = ?) + (SELECT COALESCE(SUM(CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE -jumlah END), 0) FROM simpanan_sukarela_transaksi WHERE anggota_id = ?) AS total_simpanan", [$id, $id]);
    $total_pinjaman_q = query("SELECT COALESCE(SUM(jumlah_pokok), 0) as total_pokok FROM pinjaman_anggota WHERE anggota_id = ?", [$id]);
    $total_pembayaran_pokok_q = query("SELECT COALESCE(SUM(pa.pokok_dibayar), 0) as total_bayar FROM pembayaran_angsuran pa JOIN pinjaman_anggota p ON pa.pinjaman_anggota_id = p.id WHERE p.anggota_id = ?", [$id]);
    
    $total_simpanan = $simpanan_q[0]['total_simpanan'] ?? 0;
    $sisa_pinjaman = ($total_pinjaman_q[0]['total_pokok'] ?? 0) - ($total_pembayaran_pokok_q[0]['total_bayar'] ?? 0);
    $poin_rasio = 0;

    if ($sisa_pinjaman > 0 && $total_simpanan > 0) {
        $rasio = $total_simpanan / $sisa_pinjaman;
        if ($rasio > (float)($_PENGATURAN['skor_kredit_rasio_batas_atas'] ?? 1)) {
            $poin_rasio = (int)($_PENGATURAN['skor_kredit_poin_rasio_atas'] ?? 100);
        } elseif ($rasio > (float)($_PENGATURAN['skor_kredit_rasio_batas_bawah'] ?? 0.5)) {
            $poin_rasio = (int)($_PENGATURAN['skor_kredit_poin_rasio_bawah'] ?? 50);
        } else {
            $poin_rasio = (int)($_PENGATURAN['skor_kredit_poin_rasio_dasar'] ?? 20);
        }
    } elseif ($total_simpanan > 0) {
        $poin_rasio = (int)($_PENGATURAN['skor_kredit_poin_rasio_atas'] ?? 100);
    }
    $skor += $poin_rasio;
    $rincian['Poin dari Rasio Simpanan vs Pinjaman'] = $poin_rasio;

    $anggota_q = query("SELECT tanggal_bergabung FROM anggota WHERE id = ?", [$id]);
    if(!empty($anggota_q)){
        $tgl_gabung = new DateTime($anggota_q[0]['tanggal_bergabung']);
        $tgl_sekarang = new DateTime();
        $lama_tahun = $tgl_gabung->diff($tgl_sekarang)->y;
        $poin_keanggotaan = $lama_tahun * (int)($_PENGATURAN['skor_kredit_poin_per_tahun_anggota'] ?? 5);
        $skor += $poin_keanggotaan;
        $rincian['Poin dari Lama Keanggotaan'] = $poin_keanggotaan;
    }

    $tgl_3bln_lalu = date('Y-m-d', strtotime('-3 months'));
    $belanja_q = query("SELECT id FROM penjualan WHERE anggota_id = ? AND tanggal_penjualan >= ? LIMIT 1", [$id, $tgl_3bln_lalu]);
    $poin_belanja = 0;
    if(!empty($belanja_q)){
        $poin_belanja = (int)($_PENGATURAN['skor_kredit_poin_aktivitas_belanja'] ?? 50);
        $skor += $poin_belanja;
    }
    $rincian['Poin dari Aktivitas Belanja'] = $poin_belanja;

    $rekomendasi = 'Perlu Tinjauan Lanjut';
    if($skor > (int)($_PENGATURAN['skor_kredit_batas_sangat_baik'] ?? 85)) {
        $rekomendasi = 'Sangat Baik (Rekomendasi disetujui otomatis)';
    } elseif($skor > (int)($_PENGATURAN['skor_kredit_batas_baik'] ?? 70)) {
        $rekomendasi = 'Baik (Rekomendasi verifikasi oleh staf)';
    }

    return ['skor' => round($skor), 'rekomendasi' => $rekomendasi, 'rincian' => $rincian];
}

function db_begin_transaction($koneksi) { $koneksi->beginTransaction(); }
function db_commit($koneksi) { $koneksi->commit(); }
function db_rollback($koneksi) { $koneksi->rollBack(); }
function db_insert_id($koneksi) { return $koneksi->lastInsertId(); }
function db_query_raw($koneksi, $sql) { return $koneksi->query($sql); }

// 3. Solusi N+1 Query: Hitung SHU/Laba Bersih menggunakan 1 query agregasi langsung ke SQLite
function hitungSHU($tanggal_mulai, $tanggal_selesai, $unit_usaha = 'Konsolidasi') {
    $params = [$tanggal_mulai, $tanggal_selesai];
    $where_unit = "";
    if ($unit_usaha !== 'Konsolidasi') {
        $where_unit = " AND (da.unit_usaha = ? OR da.unit_usaha = 'Konsolidasi') ";
        $params[] = $unit_usaha;
    }
    
    $sql = "
        SELECT SUM(ju.kredit - ju.debit) as shu
        FROM jurnal_umum ju
        JOIN daftar_akun da ON ju.kode_akun = da.kode_akun
        WHERE (da.kelompok_akun LIKE 'Pendapatan%' OR da.kelompok_akun LIKE 'Beban%')
          AND ju.tanggal BETWEEN ? AND ? $where_unit
    ";
    
    $result = query($sql, $params);
    return (float)($result[0]['shu'] ?? 0);
}