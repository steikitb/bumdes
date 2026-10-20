<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pinjaman');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $anggota_id = (int)$_POST['anggota_id'];
        
        if (MODE_ENTITAS !== 'BUMDes') {
            $skor_data = hitungSkorKredit($anggota_id);
            $batas_baik = (int)($_PENGATURAN['skor_kredit_batas_baik'] ?? 70);
            if ($skor_data['skor'] < $batas_baik) { 
                throw new Exception("Skor kredit anggota ini terlalu rendah ({$skor_data['skor']} poin). Pinjaman ditolak oleh sistem karena di bawah batas kelayakan ($batas_baik poin).");
            }
        }

        $jenis_pinjaman = $_POST['jenis_pinjaman'] ?? 'Umum';
                $tanggal = $_POST['tanggal_pinjaman'];
                $jumlah_pokok = round((float)str_replace('.', '', $_POST['jumlah_pokok']), 0);
                $biaya_admin = round((float)str_replace('.', '', $_POST['biaya_admin']), 0);
                $jangka_waktu = (int)$_POST['jangka_waktu_bulan'];
                $bunga = (float)$_POST['bunga_persen_tahun'];
                $keterangan = trim($_POST['keterangan'] ?? '');
                $metode_pencairan = $_POST['metode_pencairan'] ?? 'Tunai';
                $kode_akun_bank = $_POST['kode_akun_bank'] ?? null;

                // Validasi server (tidak hanya atribut min di form)
                if (empty($tanggal)) { throw new Exception("Tanggal pencairan wajib diisi."); }
                if ($jangka_waktu < 1) { throw new Exception("Jangka waktu pinjaman minimal 1 bulan."); }
                if ($bunga < 0) { throw new Exception("Suku bunga tidak boleh negatif."); }
                cekTutupBuku($tanggal);

                if ($jumlah_pokok <= 0) {
            throw new Exception("Jumlah pokok pinjaman harus lebih dari 0.");
        }
        if ($biaya_admin >= $jumlah_pokok) {
            throw new Exception("Biaya admin tidak boleh melebihi atau sama dengan jumlah plafon pinjaman.");
        }
        
        $sql = "INSERT INTO pinjaman_anggota (anggota_id, tanggal_pinjaman, jumlah_pokok, jangka_waktu_bulan, bunga_persen_tahun, keterangan, jenis_pinjaman, biaya_admin, metode_pencairan, kode_akun_bank) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$anggota_id, $tanggal, $jumlah_pokok, $jangka_waktu, $bunga, $keterangan, $jenis_pinjaman, $biaya_admin, $metode_pencairan, $kode_akun_bank]);
        $pinjaman_id = db_insert_id($koneksi);
        
        if (!$pinjaman_id) { throw new Exception("Gagal menyimpan pinjaman."); }

        // Map Akun Debit Piutang
        $akun_piutang = $_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04'; // Default SPP/Umum
        if ($jenis_pinjaman === 'UEP') {
            $akun_piutang = '1.1.03.05'; // Default COA Kepmendesa UEP
        } elseif ($jenis_pinjaman === 'SPP') {
            $akun_piutang = '1.1.03.04'; 
        }

        // Map Akun Kredit (Sumber Dana Keluar)
        $akun_kas_kredit = '';
        if ($metode_pencairan === 'Transfer') {
            if (empty($kode_akun_bank)) throw new Exception("Rekening bank sumber dana belum dipilih.");
            $akun_kas_kredit = $kode_akun_bank;
        } else {
            $akun_kas_kredit = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
        }

        // Map Akun Pendapatan Admin
        $akun_pendapatan_admin = $_PENGATURAN['akun_map_pendapatan_admin_pinjaman'] ?? '4.1.08.02';

        $nomor_bukti = 'PIUT-' . $pinjaman_id;
        $keterangan_jurnal = "Pencairan Piutang $jenis_pinjaman ID: " . $anggota_id . ($keterangan ? ' - ' . $keterangan : '');
        
        $entri_jurnal = [];
        // Debit: Menambah Piutang Penuh sesuai Plafon
        $entri_jurnal[] = ['kode_akun' => $akun_piutang, 'debit' => $jumlah_pokok, 'kredit' => 0];
        
        // Kredit: Uang Kas Keluar (Plafon dikurangi Biaya Admin yang ditahan)
        $netto = $jumlah_pokok - $biaya_admin;
        if ($netto > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_kas_kredit, 'debit' => 0, 'kredit' => $netto];
        }

        // Kredit: Mengakui Pendapatan Biaya Admin langsung
        if ($biaya_admin > 0) {
            $entri_jurnal[] = ['kode_akun' => $akun_pendapatan_admin, 'debit' => 0, 'kredit' => $biaya_admin];
        }

        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Tambah Piutang/Pinjaman', 'Mencairkan pinjaman '.$jenis_pinjaman.' ID ' . $pinjaman_id . ' sebesar Rp ' . number_format($jumlah_pokok));
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman_anggota/lihat_pinjaman&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=pinjaman_anggota/form_pinjaman&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}