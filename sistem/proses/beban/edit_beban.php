<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('transaksi_beban');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);

    try {
        $id = (int)$_POST['id'];
        $tanggal_baru = $_POST['tanggal_beban'];
        $keterangan_baru = $_POST['keterangan'];
        $kategori_id_baru = (int)$_POST['kategori_beban_id'];
        $jumlah_baru = (float)str_replace('.', '', $_POST['jumlah']);

        $beban_lama_q = query("SELECT bo.*, kb.kode_akun_terkait FROM beban_operasional bo JOIN kategori_beban kb ON bo.kategori_beban_id = kb.id WHERE bo.id = ?", [$id]);
        if (empty($beban_lama_q)) {
            throw new Exception("Data beban tidak ditemukan.");
        }
        $beban_lama = $beban_lama_q[0];

                // Validasi periode: jangan izinkan mengedit/reversal ke periode yang sudah ditutup/dikunci
                cekTutupBuku($beban_lama['tanggal_beban']);
                cekTutupBuku($tanggal_baru);

                $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        $nomor_bukti_balik = 'REV-BBN-' . $id;
        $keterangan_jurnal_balik = "Reversal untuk edit beban: " . $beban_lama['keterangan'];
        
        $entri_jurnal_balik = [
            ['kode_akun' => $akun_kas, 'debit' => $beban_lama['jumlah'], 'kredit' => 0],
            ['kode_akun' => $beban_lama['kode_akun_terkait'], 'debit' => 0, 'kredit' => $beban_lama['jumlah']]
        ];
        buatJurnal($nomor_bukti_balik, date('Y-m-d'), $keterangan_jurnal_balik, $entri_jurnal_balik);
        
        $sql_update = "UPDATE beban_operasional SET tanggal_beban = ?, keterangan = ?, kategori_beban_id = ?, jumlah = ? WHERE id = ?";
        $affected_rows = eksekusi($sql_update, [$tanggal_baru, $keterangan_baru, $kategori_id_baru, $jumlah_baru, $id]);

        if ($affected_rows <= 0) {
            throw new Exception("Tidak ada perubahan data beban.");
        }
        
        $kategori_baru_q = query("SELECT kode_akun_terkait FROM kategori_beban WHERE id = ?", [$kategori_id_baru]);
        if(empty($kategori_baru_q)) { throw new Exception("Kategori beban baru tidak valid."); }
        $kode_akun_beban_baru = $kategori_baru_q[0]['kode_akun_terkait'];

        $nomor_bukti_baru = 'BBN-' . $id . '-EDIT';
        $entri_jurnal_baru = [
            ['kode_akun' => $kode_akun_beban_baru, 'debit' => $jumlah_baru, 'kredit' => 0],
            ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $jumlah_baru]
        ];
        buatJurnal($nomor_bukti_baru, $tanggal_baru, $keterangan_baru, $entri_jurnal_baru);

        db_commit($koneksi);
        catatLog('Edit Beban', 'Beban ID ' . $id . ' (' . $keterangan_baru . ') berhasil diubah. Jurnal dikoreksi.');
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/lihat_beban&status=edit_sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        $status = ($e->getMessage() === "Tidak ada perubahan data beban.") ? 'edit_tidak_ada_perubahan' : 'edit_gagal';
        header('Location: ' . BASE_URL . 'index.php?halaman=beban/lihat_beban&status=' . $status);
    }
    exit;
}