<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_simpanan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $anggota_id = (int)$_POST['anggota_id'];
        $jenis = $_POST['jenis_simpanan'];
        $bentuk_setoran = $_POST['bentuk_setoran'] ?? 'Tunai';
        $kode_akun_aset = $_POST['kode_akun_aset'] ?? null;
        $keterangan = $_POST['keterangan'] ?? '';
        
        $jumlah = (float)str_replace('.', '', $_POST['jumlah']);
                $tanggal = $_POST['tanggal_setor'];

                if (empty($tanggal)) { throw new Exception("Tanggal setoran wajib diisi."); }
                cekTutupBuku($tanggal);

                $sql = "INSERT INTO simpanan (anggota_id, jenis_simpanan, jumlah, tanggal_setor, bentuk_setoran, kode_akun_aset, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$anggota_id, $jenis, $jumlah, $tanggal, $bentuk_setoran, $kode_akun_aset, $keterangan]);
        
        $simpanan_id = db_insert_id($koneksi);
        if (!$simpanan_id) { throw new Exception("Gagal menyimpan data simpanan."); }

        $akun_pokok = $_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01';
                $akun_wajib = $_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01';
        
        $akun_debit = '';
        if ($bentuk_setoran === 'Tunai') {
            $akun_debit = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        } elseif ($bentuk_setoran === 'Bank') {
            $akun_debit = $_PENGATURAN['akun_map_bank'] ?? '1.1.01.02';
        } elseif ($bentuk_setoran === 'Aset') {
            $akun_debit = $kode_akun_aset;
            if (empty($akun_debit)) throw new Exception("Akun Aset penerima belum dipilih.");
        }

        $kode_akun_simpanan = ($jenis === 'Pokok') ? $akun_pokok : $akun_wajib;
        $nomor_bukti = 'SIMP-' . $simpanan_id;
        $keterangan_jurnal = "Penerimaan Modal " . $jenis . " ID: " . $anggota_id . ($keterangan ? ' - ' . $keterangan : '');
        
        $entri_jurnal = [
            ['kode_akun' => $akun_debit, 'debit' => $jumlah, 'kredit' => 0],
            ['kode_akun' => $kode_akun_simpanan, 'debit' => 0, 'kredit' => $jumlah]
        ];
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Tambah Simpanan', 'Mencatat modal ' . $jenis . ' anggota ID ' . $anggota_id . ' sejumlah Rp ' . number_format($jumlah));
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan/lihat_simpanan&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan/lihat_simpanan&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}