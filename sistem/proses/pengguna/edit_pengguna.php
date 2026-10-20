<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna');
    exit;
}
verifyCsrfToken();

$id         = (int)($_POST['id'] ?? 0);
$username   = trim($_POST['nama_pengguna'] ?? '');
$nama       = trim($_POST['nama_lengkap'] ?? '');
$role       = $_POST['role'] ?? '';
$kata_sandi = $_POST['kata_sandi'] ?? '';
$konfirmasi = $_POST['konfirmasi_kata_sandi'] ?? '';
$daftar_role = daftarRolePengguna();
$self_id    = (int)($_SESSION['user_data']['id'] ?? 0);

$balik = BASE_URL . 'index.php?halaman=pengguna/form_pengguna&id=' . $id;

$target = query("SELECT id, nama_pengguna, role FROM pengguna WHERE id = ?", [$id], 'i');
if (empty($target)) {
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna&status=gagal&error=' . urlencode('Pengguna tidak ditemukan.'));
    exit;
}
$target = $target[0];

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
if ($id === $self_id && $role !== $target['role']) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Role akun sendiri tidak bisa diubah.'));
    exit;
}
if ($target['role'] === 'admin' && $role !== 'admin') {
    $adm = query("SELECT COUNT(*) AS n FROM pengguna WHERE role = 'admin'");
    if ((int)($adm[0]['n'] ?? 0) <= 1) {
        header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Tidak bisa menurunkan administrator terakhir.'));
        exit;
    }
}

$cek = query("SELECT id FROM pengguna WHERE LOWER(nama_pengguna) = LOWER(?) AND id != ?", [$username, $id], 'si');
if (!empty($cek)) {
    header('Location: ' . $balik . '&status=gagal&error=' . urlencode('Username sudah dipakai. Pilih username lain.'));
    exit;
}

if ($kata_sandi !== '' || $konfirmasi !== '') {
    if ($kata_sandi !== $konfirmasi) {
        header('Location: ' . $balik . '&status=password_mismatch');
        exit;
    }
    if (strlen($kata_sandi) < 8 || !preg_match('/[0-9]/', $kata_sandi) || !preg_match('/[A-Za-z]/', $kata_sandi)) {
        header('Location: ' . $balik . '&status=password_weak');
        exit;
    }
    $hash = password_hash($kata_sandi, PASSWORD_DEFAULT);
    $affected = eksekusi(
        "UPDATE pengguna SET nama_pengguna = ?, nama_lengkap = ?, role = ?, kata_sandi = ?, wajib_ganti_sandi = 1 WHERE id = ?",
        [$username, $nama, $role, $hash, $id],
        'ssssi'
    );
} else {
    $affected = eksekusi(
        "UPDATE pengguna SET nama_pengguna = ?, nama_lengkap = ?, role = ? WHERE id = ?",
        [$username, $nama, $role, $id],
        'sssi'
    );
}

if ($affected > 0 || true) {
    if ($id === $self_id) {
        $_SESSION['user_data']['nama_lengkap'] = $nama;
    }
    catatLog('Edit Pengguna', 'Data pengguna ID ' . $id . ' (' . $username . ') diperbarui.');
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna&status=edit_sukses');
} else {
    header('Location: ' . $balik . '&status=edit_gagal');
}
exit;
