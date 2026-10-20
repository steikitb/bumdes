<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_hibah');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);

    try {
        $tanggal_terima = $_POST['tanggal_terima'];
        $pemberi_hibah = $_POST['pemberi_hibah'] ?? '';
        $bentuk_hibah = $_POST['bentuk_hibah'] ?? 'Tunai';
        $kode_akun_aset = $_POST['kode_akun_aset'] ?? null;
        
        $jumlah = (float)str_replace('.', '', $_POST['jumlah']);
                $keterangan = $_POST['keterangan'];
                $jenis_hibah = $_POST['jenis_hibah'];

                if (empty($tanggal_terima)) { throw new Exception("Tanggal penerimaan wajib diisi."); }
                cekTutupBuku($tanggal_terima);

        $akun_debit = '';
        if ($bentuk_hibah === 'Tunai') {
            $akun_debit = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        } elseif ($bentuk_hibah === 'Bank') {
            $akun_debit = $_PENGATURAN['akun_map_bank'] ?? '1.1.01.02';
        } elseif ($bentuk_hibah === 'Aset') {
            $akun_debit = $kode_akun_aset;
            if (empty($akun_debit)) throw new Exception("Akun Aset belum dipilih.");
        }

        $nomor_bukti = 'HBH-IN-' . time();
        $entri_jurnal = [];
        $akun_kredit = '';
        $status_syarat = 'Tidak Relevan';

        if ($jenis_hibah === 'Tanpa Syarat') {
                    $akun_kredit = $_PENGATURAN['akun_map_pendapatan_hibah'] ?? '7.1.99.99';
                    $status_syarat = 'Terpenuhi';
                } elseif ($jenis_hibah === 'Bersyarat') {
                    $akun_kredit = $_PENGATURAN['akun_map_hibah_diterima_dimuka'] ?? '2.2.99.99';
                    $status_syarat = 'Belum Terpenuhi';
        } else {
            throw new Exception("Jenis hibah tidak valid.");
        }

        // 1. Buat Jurnal Penerimaan
        $entri_jurnal = [
            ['kode_akun' => $akun_debit, 'debit' => $jumlah, 'kredit' => 0],
            ['kode_akun' => $akun_kredit, 'debit' => 0, 'kredit' => $jumlah]
        ];
        buatJurnal($nomor_bukti, $tanggal_terima, $keterangan, $entri_jurnal);

        // 2. Simpan ke tabel hibah_pemerintah
        $sql = "INSERT INTO hibah_pemerintah (tanggal_terima, keterangan, jumlah, jenis_hibah, status_syarat, nomor_bukti_penerimaan, pemberi_hibah, bentuk_hibah, kode_akun_aset) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        eksekusi($sql, [$tanggal_terima, $keterangan, $jumlah, $jenis_hibah, $status_syarat, $nomor_bukti, $pemberi_hibah, $bentuk_hibah, $kode_akun_aset]);

        db_commit($koneksi);
        catatLog('Tambah Hibah', 'Mencatat penerimaan hibah: ' . $keterangan . ' sejumlah Rp ' . number_format($jumlah));
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/lihat_hibah&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=hibah/form_hibah&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}