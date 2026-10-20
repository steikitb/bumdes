<?php
cekLogin();
cekAkses('kelola_anggota');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    verifyCsrfToken();
    $id = (int)$_POST['id'];

    $sisa_pinjaman_q = query("
        SELECT p.id, (p.jumlah_pokok - COALESCE(SUM(pa.pokok_dibayar), 0)) as sisa_pokok
        FROM pinjaman_anggota p
        LEFT JOIN pembayaran_angsuran pa ON p.id = pa.pinjaman_anggota_id
        WHERE p.anggota_id = ?
        GROUP BY p.id
        HAVING sisa_pokok > 0
    ", [$id], 'i');

    if (!empty($sisa_pinjaman_q)) {
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=hapus_gagal_terkait');
        exit;
    }

    $sql = "UPDATE anggota SET status_keanggotaan = 'Keluar', tanggal_keluar = ? WHERE id = ?";
    $affected_rows = eksekusi($sql, [date('Y-m-d'), $id], 'si');

    if ($affected_rows > 0) {
        catatLog('Proses Anggota Keluar', 'Anggota dengan ID ' . $id . ' telah diproses keluar.');
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=edit_sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=anggota/lihat_anggota&status=edit_gagal');
    }
    exit;
}