<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();

    // ==== Rate-limit login (anti brute-force) — per kombinasi IP + username, window 15 menit ====
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $login_key = md5($ip . '|' . strtolower(trim($_POST['nama_pengguna'] ?? '')));
    $max_attempts = 15;          // maksimal percobaan gagal
    $window_seconds = 900;       // dalam 15 menit
    $lockout_seconds = 900;      // durasi kunci setelah lewat max

    $attempt_key = 'login_attempts_' . $login_key;

    if (isset($_SESSION[$attempt_key])) {
        $att = $_SESSION[$attempt_key];
        // reset counter bila window lama sudah lewat
        if (time() - $att['timestamp'] >= $window_seconds) {
            $_SESSION[$attempt_key] = ['count' => 0, 'timestamp' => time()];
            $att = $_SESSION[$attempt_key];
        }
        if ($att['count'] >= $max_attempts) {
            $sisa = $lockout_seconds - (time() - $att['timestamp']);
            usleep(500000); // perlambat respons
            header('Location: ' . BASE_URL . 'index.php?halaman=login&status=terlalu_banyak_percobaan');
            exit;
        }
    } else {
        $_SESSION[$attempt_key] = ['count' => 0, 'timestamp' => time()];
    }

    $nama_pengguna = $_POST['nama_pengguna'];
    $kata_sandi = $_POST['kata_sandi'];

    // Perbaikan RBAC: Tarik data role dan simpan di session
    $sql = "SELECT id, nama_lengkap, kata_sandi, wajib_ganti_sandi, role FROM pengguna WHERE nama_pengguna = ?";
    $users = query($sql, [$nama_pengguna]);
    $user = $users[0] ?? null;

    if ($user && password_verify($kata_sandi, $user['kata_sandi'])) {
        // login sukses — reset counter percobaan untuk key ini
        unset($_SESSION[$attempt_key]);

        // ==== Enforce ubah password default/lemah ====
        // Daftar password yang dianggap default (untuk akun admin baru / yang belum diubah).
        $default_passwords = ['admin', 'admin123', 'password', '123456', '12345678', 'koperasi', 'bumdes'];
        $password_is_default = false;
        foreach ($default_passwords as $dp) {
            if (password_verify($dp, $user['kata_sandi'])) { $password_is_default = true; break; }
        }
        if ($password_is_default) {
            // Paksa ganti password pada sesi ini + persist ke DB
            eksekusi("UPDATE pengguna SET wajib_ganti_sandi = 1 WHERE id = ?", [$user['id']]);
            $user['wajib_ganti_sandi'] = 1;
        }

        session_regenerate_id(true);
        $_SESSION['user_login'] = true;
        $_SESSION['user_data'] = ['id' => $user['id'], 'nama_lengkap' => $user['nama_lengkap'], 'role' => $user['role']];
        $_SESSION['wajib_ganti_sandi'] = $user['wajib_ganti_sandi'];
        generateCsrfToken();
        catatLog('Login Berhasil', 'Pengguna berhasil masuk ke sistem.');
        header('Location: ' . BASE_URL . 'index.php?halaman=dasbor');
    } else {
        // login gagal — tambah counter
        $att = $_SESSION[$attempt_key];
        $att['count'] += 1;
        $att['timestamp'] = time();
        $_SESSION[$attempt_key] = $att;

        usleep(300000); // perlambat respons
        catatLog('Login Gagal', 'Percobaan login gagal untuk nama pengguna: ' . $nama_pengguna);
        header('Location: ' . BASE_URL . 'index.php?halaman=login&status=login_gagal');
    }
    exit;
}