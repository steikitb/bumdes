<?php
cekLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $user_id = $_SESSION['user_data']['id'];
    $kata_sandi_lama = $_POST['kata_sandi_lama'];
    $kata_sandi_baru = $_POST['kata_sandi_baru'];
    $konfirmasi_kata_sandi_baru = $_POST['konfirmasi_kata_sandi_baru'];

    if ($kata_sandi_baru !== $konfirmasi_kata_sandi_baru) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/ubah_kata_sandi_sendiri&status=password_mismatch');
        exit;
    }
    
    if (strlen($kata_sandi_baru) < 8 || !preg_match('/[0-9]/', $kata_sandi_baru) || !preg_match('/[A-Za-z]/', $kata_sandi_baru)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/ubah_kata_sandi_sendiri&status=password_weak');
        exit;
    }

    $user_data = query("SELECT kata_sandi FROM pengguna WHERE id = ?", [$user_id], 'i');
    if (empty($user_data) || !password_verify($kata_sandi_lama, $user_data[0]['kata_sandi'])) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/ubah_kata_sandi_sendiri&status=old_password_wrong');
        exit;
    }

    $kata_sandi_hashed = password_hash($kata_sandi_baru, PASSWORD_DEFAULT);
    $sql_update = "UPDATE pengguna SET kata_sandi = ?, wajib_ganti_sandi = 0 WHERE id = ?";
    $affected_rows = eksekusi($sql_update, [$kata_sandi_hashed, $user_id], 'si');

    if ($affected_rows > 0) {
        $_SESSION['wajib_ganti_sandi'] = 0;
        catatLog('Ubah Kata Sandi', 'Pengguna mengubah kata sandinya sendiri.');
        header('Location: ' . BASE_URL . 'index.php?halaman=dasbor&status=password_changed_success');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/ubah_kata_sandi_sendiri&status=failed');
    }
    exit;
}