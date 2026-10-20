<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna');
    exit;
}
verifyCsrfToken();

$id = (int)$_POST['id'];
$self_id = (int)($_SESSION['user_data']['id'] ?? 0);
$balik = BASE_URL . 'index.php?halaman=pengguna/lihat_pengguna';

if ($id === $self_id) {
    header('Location: ' . $balik . '&status=hapus_gagal&error=' . urlencode('Tidak bisa menghapus akun sendiri.'));
    exit;
}

$target = query("SELECT id, nama_pengguna, role FROM pengguna WHERE id = ?", [$id], 'i');
if (empty($target)) {
    header('Location: ' . $balik . '&status=hapus_gagal&error=' . urlencode('Pengguna tidak ditemukan.'));
    exit;
}
$target = $target[0];

if ($target['role'] === 'admin') {
    $adm = query("SELECT COUNT(*) AS n FROM pengguna WHERE role = 'admin'");
    if ((int)($adm[0]['n'] ?? 0) <= 1) {
        header('Location: ' . $balik . '&status=hapus_gagal&error=' . urlencode('Tidak bisa menghapus administrator terakhir.'));
        exit;
    }
}

$affected = eksekusi("DELETE FROM pengguna WHERE id = ?", [$id], 'i');
if ($affected > 0) {
    catatLog('Hapus Pengguna', 'Pengguna dihapus: ' . $target['nama_pengguna'] . ' (ID ' . $id . ')');
    header('Location: ' . $balik . '&status=hapus_sukses');
} else {
    header('Location: ' . $balik . '&status=hapus_gagal&error=' . urlencode('Gagal menghapus pengguna.'));
}
exit;
