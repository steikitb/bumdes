<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna');
    exit;
}
verifyCsrfToken();

$username   = trim($_POST['nama_pengguna'] ?? '');
$nama       = trim($_POST['nama_lengkap'] ?? '');
$role       = $_POST['role'] ?? '';
$kata_sandi = $_POST['kata_sandi'] ?? '';
$konfirmasi = $_POST['konfirmasi_kata_sandi'] ?? '';
$daftar_role = daftarRolePengguna();

$balik = BASE_URL . 'index.php?halaman=pengguna/form_pengguna';

if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Username 3-30 karakter, hanya huruf, angka, titik, garis bawah, atau strip.'));
    exit;
}
if ($nama === '' || strlen($nama) > 100) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Nama lengkap wajib diisi (maksimal 100 karakter).'));
    exit;
}
if (!isset($daftar_role[$role])) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Role tidak valid.'));
    exit;
}
if ($kata_sandi !== $konfirmasi) {
    header('Location: ' . $balik . '&status=password_mismatch');
    exit;
}
if (strlen($kata_sandi) < 8 || !preg_match('/[0-9]/', $kata_sandi) || !preg_match('/[A-Za-z]/', $kata_sandi)) {
    header('Location: ' . $balik . '&status=password_weak');
    exit;
}

$cek = query("SELECT id FROM pengguna WHERE LOWER(nama_pengguna) = LOWER(?)", [$username]);
if (!empty($cek)) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Username sudah dipakai. Pilih username lain.'));
    exit;
}

$hash = password_hash($kata_sandi, PASSWORD_DEFAULT);
$sql = "INSERT INTO pengguna (nama_pengguna, kata_sandi, nama_lengkap, role, wajib_ganti_sandi) VALUES (?, ?, ?, ?, 1)";
$affected = eksekusi($sql, [$username, $hash, $nama, $role], 'ssss');

if ($affected > 0) {
    catatLog('Tambah Pengguna', 'Pengguna baru ditambahkan: ' . $username . ' (' . $role . ')');
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna&status=sukses');
} else {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Gagal menyimpan pengguna baru.'));
}
exit;
