<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin(); cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $karyawan_id = (int)$_POST['karyawan_id'];
        $tanggal = $_POST['tanggal_pembayaran'];
        $periode = $_POST['periode_bulan'];
        $jenis = $_POST['jenis_pembayaran'];
        $metode = $_POST['metode_pembayaran'];
        
        $gapok = (float)str_replace('.', '', $_POST['gaji_pokok']);
        $tunj = (float)str_replace('.', '', $_POST['tunjangan']);
        $pot = (float)str_replace('.', '', $_POST['potongan']);
        $ket = $_POST['keterangan'];

        $total_bersih = $gapok + $tunj - $pot;

        if ($total_bersih <= 0) {
            throw new Exception("Total bersih harus lebih dari 0.");
        }
        
        cekTutupBuku($tanggal);

                // Guard duplikat: satu karyawan+jenis dalam periode yang sama hanya boleh dibayar sekali (status_batal=0)
                $cek_dup = query(
                    "SELECT id FROM trx_penggajian WHERE karyawan_id = ? AND periode_bulan = ? AND jenis_pembayaran = ? AND status_batal = 0 LIMIT 1",
                    [$karyawan_id, $periode, $jenis]
                );
                if (!empty($cek_dup)) {
                    throw new Exception("Gaji $jenis untuk karyawan ini pada periode $periode sudah pernah dibayar (ID " . $cek_dup[0]['id'] . "). Batalkan yang lama terlebih dahulu untuk membayar ulang.");
                }

                $sql = "INSERT INTO trx_penggajian (karyawan_id, tanggal_pembayaran, periode_bulan, jenis_pembayaran, gaji_pokok, tunjangan, potongan, total_bersih, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->execute([$karyawan_id, $tanggal, $periode, $jenis, $gapok, $tunj, $pot, $total_bersih, $ket]);
        $trx_id = db_insert_id($koneksi);
        
        $no_bukti = 'GAJI-' . $trx_id;
        eksekusi("UPDATE trx_penggajian SET nomor_bukti = ? WHERE id = ?", [$no_bukti, $trx_id]);

        $akun_beban = $_PENGATURAN['akun_map_beban_gaji'] ?? '6.1.01.01';
        $akun_kas = ($metode === 'Transfer') ? ($_PENGATURAN['akun_map_bank'] ?? '1.1.01.02') : ($_PENGATURAN['akun_map_kas'] ?? '1.1.01.01');

        $karyawan = query("SELECT a.nama_lengkap FROM mst_karyawan k JOIN anggota a ON k.anggota_id = a.id WHERE k.id = ?", [$karyawan_id]);
        $nama = $karyawan[0]['nama_lengkap'] ?? 'Karyawan';
        
        $ket_jurnal = "Pembayaran $jenis Periode $periode a.n $nama";
        
        $entri = [
            ['kode_akun' => $akun_beban, 'debit' => $total_bersih, 'kredit' => 0],
            ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $total_bersih]
        ];
        
        buatJurnal($no_bukti, $tanggal, $ket_jurnal, $entri);
        
        db_commit($koneksi);
        catatLog('Penggajian', "Membayar $jenis kepada $nama (Rp " . number_format($total_bersih) . ")");
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/lihat_penggajian&status=sukses');
    } catch(Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=karyawan/form_penggajian&status=gagal&error='.urlencode($e->getMessage()));
    }
    exit;
}