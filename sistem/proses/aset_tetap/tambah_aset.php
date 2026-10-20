<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_aset_tetap');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $nama = $_POST['nama_aset'];
        $kategori = $_POST['kategori_aset'];
        $tanggal = $_POST['tanggal_perolehan'];
        $harga = (float)str_replace('.', '', $_POST['harga_perolehan']);
        $masa_manfaat = (int)$_POST['masa_manfaat_tahun'];
        $nilai_residu = (float)str_replace('.', '', $_POST['nilai_residu']);
                $asal_perolehan = $_POST['asal_perolehan'] ?? 'baru';

                if (empty($tanggal)) { throw new Exception("Tanggal perolehan wajib diisi."); }
                cekTutupBuku($tanggal);

        $sql_aset = "INSERT INTO aset_tetap (nama_aset, kategori_aset, tanggal_perolehan, harga_perolehan, masa_manfaat_tahun, nilai_residu) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_aset = $koneksi->prepare($sql_aset);
        $stmt_aset->execute([$nama, $kategori, $tanggal, $harga, $masa_manfaat, $nilai_residu]);
        $aset_id = db_insert_id($koneksi);

        if ($asal_perolehan === 'baru') {
                    // Map COA Berdasarkan KELAS aset (Kepmendesa: Tanah/Kendaraan/Peralatan/Bangunan/KDP/ATB)
                    $map_aset = mapKelasAsetAkun($kategori);
                    $akun_aset = $map_aset[0] ?? ($_PENGATURAN['akun_map_aset_peralatan'] ?? '1.3.03.01');

                    $akun_kas = $_PENGATURAN['akun_map_kas'] ?? '1.1.01.01';

            $keterangan_jurnal = "Pembelian Aset Tetap Baru: " . $nama;
            $nomor_bukti = 'AST-' . $aset_id;
            $entri_jurnal = [
                ['kode_akun' => $akun_aset, 'debit' => $harga, 'kredit' => 0],
                ['kode_akun' => $akun_kas, 'debit' => 0, 'kredit' => $harga]
            ];
            buatJurnal($nomor_bukti, $tanggal, $keterangan_jurnal, $entri_jurnal);
            catatLog('Tambah Aset Tetap', 'Aset baru dibeli: ' . $nama . ' (ID: ' . $aset_id . ') senilai Rp ' . number_format($harga));
        } else {
            catatLog('Migrasi Aset Tetap', 'Data master Aset Lama ditambahkan: ' . $nama . ' (ID: ' . $aset_id . ') senilai Rp ' . number_format($harga));
        }

        db_commit($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=aset_tetap/lihat_aset&status=gagal');
    }
    exit;
}