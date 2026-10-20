<?php
cekLogin();
cekAkses('kelola_anggota');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    header('Location: ' . BASE_URL . 'index.php?halaman=laporan/laporan_anggota_keluar');
    exit;
}

verifyCsrfToken();
$koneksi = getKoneksi();
$anggota_id = (int)$_POST['id'];
db_begin_transaction($koneksi);

try {
    $pokok_q = query("SELECT COALESCE(SUM(jumlah), 0) as total FROM simpanan WHERE anggota_id = ? AND jenis_simpanan = 'Pokok'", [$anggota_id], 'i');
    $wajib_q = query("SELECT COALESCE(SUM(jumlah), 0) as total FROM simpanan WHERE anggota_id = ? AND jenis_simpanan = 'Wajib'", [$anggota_id], 'i');

    $total_pokok = (float)($pokok_q[0]['total'] ?? 0);
    $total_wajib = (float)($wajib_q[0]['total'] ?? 0);
    $total_pengembalian = $total_pokok + $total_wajib;

    if ($total_pengembalian <= 0) {
        throw new Exception("Tidak ada dana simpanan yang bisa dikembalikan.");
    }

    $akun_pokok = $_PENGATURAN['akun_map_simpanan_pokok'] ?? '3111';
    $akun_wajib = $_PENGATURAN['akun_map_simpanan_wajib'] ?? '3121';
    $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1111';
    
    $nomor_bukti = 'KLR-DANA-' . $anggota_id;
    $tanggal_proses = date('Y-m-d');
    $keterangan_jurnal = "Pengembalian Dana Simpanan Anggota Keluar ID: " . $anggota_id;

    $entri_jurnal = [];
    if ($total_pokok > 0) {
        $entri_jurnal[] = ['kode_akun' => $akun_pokok, 'debit' => $total_pokok, 'kredit' => 0];
    }
    if ($total_wajib > 0) {
        $entri_jurnal[] = ['kode_akun' => $akun_wajib, 'debit' => $total_wajib, 'kredit' => 0];
    }
    $entri_jurnal[] = ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $total_pengembalian];
    
    buatJurnal($nomor_bukti, $tanggal_proses, $keterangan_jurnal, $entri_jurnal);

    $sql_update = "UPDATE anggota SET dana_dikembalikan = 'Sudah' WHERE id = ?";
    eksekusi($sql_update, [$anggota_id], 'i');

    db_commit($koneksi);
    catatLog('Pengembalian Dana', 'Memproses pengembalian dana simpanan anggota keluar ID ' . $anggota_id . ' sebesar Rp ' . number_format($total_pengembalian));
    header('Location: ' . BASE_URL . 'index.php?halaman=laporan/laporan_anggota_keluar&status=sukses');
    
} catch (Exception $e) {
    db_rollback($koneksi);
    header('Location: ' . BASE_URL . 'index.php?halaman=laporan/laporan_anggota_keluar&status=gagal&error=' . urlencode($e->getMessage()));
}
exit;