<?php
function cekLogin() {
    if (!isset($_SESSION['user_login'])) {
        header('Location: ' . BASE_URL . 'index.php?halaman=login');
        exit;
    }
}

function cekLoginAjax() {
    if (!isset($_SESSION['user_login'])) {
        http_response_code(401); 
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sesi tidak valid atau telah berakhir. Silakan muat ulang halaman dan login kembali.']);
        exit;
    }
}

// 4. Perbaikan RBAC: Validasi akses berdasarkan Role pengguna
function cekAkses($permission = null) {
    cekLogin();
    if ($permission) {
        $user_role = $_SESSION['user_data']['role'] ?? '';
        if ($user_role !== 'admin' && $user_role !== $permission) {
            header('Location: ' . BASE_URL . 'index.php?halaman=dasbor&status=akses_ditolak');
            exit;
        }
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrfInput() {
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function verifyCsrfToken() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("Aksi tidak diizinkan karena token tidak valid.");
    }
}

function catatLog($aksi, $keterangan = '') {
    $koneksi = getKoneksi();
    $user_id = $_SESSION['user_data']['id'] ?? null;
    $nama_pengguna = $_SESSION['user_data']['nama_lengkap'] ?? 'Guest/Sistem';
    
    if ($aksi === 'Login Gagal' && empty($_SESSION['user_login'])) {
        if (isset($_POST['nama_pengguna'])) {
            $nama_pengguna = 'Percobaan: ' . substr($_POST['nama_pengguna'], 0, 50);
        }
    } else if (!isset($_SESSION['user_login'])) {
        return; 
    }

    $role = $_SESSION['user_data']['role'] ?? 'Guest'; 
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    // PERBAIKAN ZONA WAKTU:
    // Ambil waktu lokal PHP (Asia/Jakarta) lalu paksa simpan ke database.
    // Jika tidak diset manual, SQLite akan menggunakan CURRENT_TIMESTAMP bawaannya yang selalu UTC (GMT+0).
    $waktu_lokal = date('Y-m-d H:i:s');
    
    try {
        $sql = "INSERT INTO log_aktivitas (timestamp, user_id, nama_pengguna, role_pengguna, aksi, keterangan, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$waktu_lokal, $user_id, $nama_pengguna, $role, $aksi, $keterangan, $ip]);
    } catch (Exception $e) {
    }
}

function punyaAkses($permission = null) {
    return isset($_SESSION['user_login']);
}

function daftarRolePengguna() {
    return [
        'admin'                    => 'Administrator (akses penuh)',
        'transaksi_penjualan'      => 'Kasir Penjualan',
        'transaksi_pembelian'      => 'Staf Pembelian',
        'transaksi_beban'          => 'Staf Beban Operasional',
        'kelola_produk_stok'       => 'Staf Persediaan / Produk',
        'kelola_anggota'           => 'Staf Anggota',
        'kelola_simpanan'          => 'Staf Simpanan',
        'kelola_pinjaman'          => 'Staf Pinjaman',
        'kelola_aset_tetap'        => 'Staf Aset Tetap',
        'kelola_pemasok'           => 'Staf Pemasok',
        'kelola_daftar_akun'       => 'Staf Bagan Akun (COA)',
        'kelola_pengaturan'        => 'Pengaturan Sistem',
        'kelola_hibah'             => 'Staf Hibah',
        'kelola_buku_administrasi' => 'Staf Buku Administrasi',
        'lihat_laporan'            => 'Lihat Laporan Keuangan',
        'proses_tutup_buku'        => 'Proses Tutup Buku',
        'proses_penyesuaian_jurnal'=> 'Jurnal Penyesuaian',
        'pengurus'                 => 'Pengurus (jabatan, tanpa akses modul)',
        'pengawas'                 => 'Pengawas (jabatan, tanpa akses modul)',
    ];
}
