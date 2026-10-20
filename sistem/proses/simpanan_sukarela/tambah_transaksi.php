<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_simpanan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    $anggota_id = (int)$_POST['anggota_id'];
    try {
        $jenis = $_POST['jenis_transaksi'];
        $jumlah = (float)str_replace('.', '', $_POST['jumlah']);
        $tanggal = $_POST['tanggal_transaksi'];
                $keterangan = $_POST['keterangan'];

                if (empty($tanggal)) { throw new Exception("Tanggal transaksi wajib diisi."); }
                cekTutupBuku($tanggal);
        
        if ($jenis === 'Tarik') {
            $saldo_q = query("SELECT (SUM(CASE WHEN jenis_transaksi = 'Setor' THEN jumlah ELSE 0 END) - SUM(CASE WHEN jenis_transaksi = 'Tarik' THEN jumlah ELSE 0 END)) as saldo FROM simpanan_sukarela_transaksi WHERE anggota_id = ?", [$anggota_id]);
            $saldo_saat_ini = $saldo_q[0]['saldo'] ?? 0;
            if ($jumlah > $saldo_saat_ini) { throw new Exception('saldo_kurang'); }
        }
        
        $sql = "INSERT INTO simpanan_sukarela_transaksi (anggota_id, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) VALUES (?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        
        if (!$stmt->execute([$anggota_id, $jenis, $jumlah, $tanggal, $keterangan])) { 
            throw new Exception("Gagal menyimpan transaksi sukarela."); 
        }
        
        $transaksi_id = db_insert_id($koneksi);

        $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';
                $akun_sukarela = $_PENGATURAN['akun_map_simpanan_sukarela'] ?? '2.1.05.01';

        $nomor_bukti = 'SS-' . $transaksi_id;
        $keterangan_jurnal = "Simpanan Sukarela " . $jenis . " Anggota ID: " . $anggota_id;
        $entri_jurnal = ($jenis === 'Setor') ? 
            [['kode_akun' => $akun_kas, 'debit' => $jumlah, 'kredit' => 0], ['kode_akun' => $akun_sukarela, 'debit' => 0, 'kredit' => $jumlah]] : 
            [['kode_akun' => $akun_sukarela, 'debit' => $jumlah, 'kredit' => 0], ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $jumlah]];
            
        buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
        
        db_commit($koneksi);
        catatLog('Transaksi Simpanan Sukarela', 'Mencatat transaksi ' . $jenis . ' simpanan sukarela anggota ID ' . $anggota_id . ' sejumlah Rp ' . number_format($jumlah));
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan_sukarela/detail_anggota&id=' . $anggota_id . '&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        $status = $e->getMessage() ?: 'gagal';
        header('Location: ' . BASE_URL . 'index.php?halaman=simpanan_sukarela/detail_anggota&id=' . $anggota_id . '&status=' . $status);
    }
    exit;
}