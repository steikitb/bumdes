<?php
cekLogin();
cekAkses('kelola_hibah');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    $hibah_id = (int)$_POST['id'];
    db_begin_transaction($koneksi);

    try {
        $hibah_q = query("SELECT * FROM hibah_pemerintah WHERE id = ? AND status_syarat = 'Belum Terpenuhi'", [$hibah_id], 'i');
        if (empty($hibah_q)) {
            throw new Exception("Hibah tidak ditemukan atau syarat sudah terpenuhi.");
        }
        $hibah = $hibah_q[0];
        $jumlah = $hibah['jumlah'];
        $tanggal_pengakuan = date('Y-m-d');
        $nomor_bukti = 'HBH-ACK-' . $hibah_id;
        $keterangan_jurnal = 'Pengakuan Pendapatan Hibah: ' . $hibah['keterangan'];
        
        $akun_liabilitas = $_PENGATURAN['akun_map_hibah_diterima_dimuka'] ?? '2311';
        $akun_pendapatan = $_PENGATURAN['akun_map_pendapatan_hibah'] ?? '4311';

        // 1. Buat Jurnal Pengakuan Pendapatan
        $entri_jurnal = [
            ['kode_akun' => $akun_liabilitas, 'debit' => $jumlah, 'kredit' => 0],
            ['kode_akun' => $akun_pendapatan, 'debit' => 0, 'kredit' => $jumlah]
        ];
        buatJurnal($nomor_bukti, $tanggal_pengakuan, $keterangan_jurnal, $entri_jurnal);

        // 2. Update status di tabel hibah
        $sql_update = "UPDATE hibah_pemerintah SET status_syarat = 'Terpenuhi', tanggal_syarat_terpenuhi = ?, nomor_bukti_pengakuan = ? WHERE id = ?";
        eksekusi($sql_update, [$tanggal_pengakuan, $nomor_bukti, $hibah_id], 'ssi');

        db_commit($koneksi);
        catatLog('Pengakuan Pendapatan Hibah', 'Syarat hibah ID ' . $hibah_id . ' terpenuhi.');
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/lihat_hibah&status=edit_sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/lihat_hibah&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}