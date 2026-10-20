<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tanggal_jurnal = $_POST['tanggal_penyusutan'];
        $tahun = date('Y', strtotime($tanggal_jurnal));
        $nomor_bukti = 'PENYUSUTAN-' . $tahun;

        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti]);
        if (!empty($cek_jurnal)) {
            throw new Exception('Jurnal penyusutan tahunan untuk tahun ' . $tahun . ' sudah ada.');
        }

        $cek_bulanan = query("SELECT id FROM jurnal_umum WHERE nomor_bukti LIKE ? LIMIT 1", ['PENYUSUTAN-BLN-'.$tahun.'%']);
        if (!empty($cek_bulanan)) {
            throw new Exception('Terdapat jurnal penyusutan bulanan pada tahun ' . $tahun . '. Anda tidak boleh melakukan penyusutan tahunan jika sudah menggunakan metode bulanan.');
        }

        $aset_tetap = query("SELECT * FROM aset_tetap WHERE masa_manfaat_tahun > 0 AND tanggal_perolehan <= ?", [$tanggal_jurnal]);

        $mulai_tahun = $tahun . '-01-01';
        $akhir_tahun = $tahun . '-12-31';
        $tgl_mulai_dt = strtotime($mulai_tahun);
        $tgl_akhir_dt = strtotime($tanggal_jurnal);
        $hari_dalam_tahun = (int)date('z', strtotime($akhir_tahun)) + 1; // 365/366

        $debit_per_akun = [];
        $kredit_per_akun = [];
        $total_beban = 0;

        $sql_rekap = "INSERT INTO aset_tetap_penyusutan (aset_id, kategori_aset, kode_akun_akumulasi, beban, tanggal, nomor_bukti, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_rekap = $koneksi->prepare($sql_rekap);

        foreach ($aset_tetap as $aset) {
            if ($aset['kategori_aset'] == 'Konstruksi Dalam Pengerjaan (KDP)') {
                continue; // KDP tidak disusutkan
            }

            $map = mapKelasAsetAkun($aset['kategori_aset']);
            $kode_akumulasi = $map[1];
            $kode_beban = $map[2];
            if (!$kode_akumulasi || !$kode_beban) {
                continue; // mis. Tanah tidak disusutkan
            }

            $nilai_residu = (MODE_ENTITAS == 'BUMDes') ? 0 : (float)$aset['nilai_residu'];
            $batas_penyusutan = (float)$aset['harga_perolehan'] - $nilai_residu;
            if ($batas_penyusutan <= 0) { continue; }

            $penyusutan_tahunan_full = $batas_penyusutan / (int)$aset['masa_manfaat_tahun'];

            // Prorata periode pecahan: lama aset dalam tahun ini (hari), dibatasi maks 1 tahun penuh
            $tgl_aset_dt = strtotime($aset['tanggal_perolehan']);
            $tgl_mulai_efektif_dt = max($tgl_aset_dt, $tgl_mulai_dt);
            $hari_dilayani = max(0, ((int)($tgl_akhir_dt) - (int)$tgl_mulai_efektif_dt) / 86400);
            $hari_dilayani = min($hari_dilayani, $hari_dalam_tahun);
            $proporsi_tahun = $hari_dalam_tahun > 0 ? ($hari_dilayani / $hari_dalam_tahun) : 0;
            $penyusutan_tahunan = $penyusutan_tahunan_full * $proporsi_tahun;

            // Cap per-ASET (akar dari bug agregat)
            $akum_aset = getAkumulasiAset($aset['id']);
            if ($akum_aset >= $batas_penyusutan) { continue; }
            if (($akum_aset + $penyusutan_tahunan) > $batas_penyusutan) {
                $penyusutan_tahunan = $batas_penyusutan - $akum_aset;
            }

            if ($penyusutan_tahunan <= 0) { continue; }

            if (!isset($debit_per_akun[$kode_beban])) { $debit_per_akun[$kode_beban] = 0; }
            if (!isset($kredit_per_akun[$kode_akumulasi])) { $kredit_per_akun[$kode_akumulasi] = 0; }
            $debit_per_akun[$kode_beban] += $penyusutan_tahunan;
            $kredit_per_akun[$kode_akumulasi] += $penyusutan_tahunan;
            $total_beban += $penyusutan_tahunan;

            $stmt_rekap->execute([$aset['id'], $aset['kategori_aset'], $kode_akumulasi, $penyusutan_tahunan, $tanggal_jurnal, $nomor_bukti, 'Penyusutan Tahunan ' . $tahun]);
        }

        if ($total_beban <= 0) {
            throw new Exception('Tidak ada beban penyusutan atau amortisasi untuk diproses pada aset yang aktif saat ini.');
        }

        $entri_jurnal = [];
        foreach ($debit_per_akun as $kode => $jml) { $entri_jurnal[] = ['kode_akun' => $kode, 'debit' => $jml, 'kredit' => 0]; }
        foreach ($kredit_per_akun as $kode => $jml) { $entri_jurnal[] = ['kode_akun' => $kode, 'debit' => 0, 'kredit' => $jml]; }

        buatJurnal($nomor_bukti, $tanggal_jurnal, 'Jurnal Penyusutan Aset & Amortisasi Tahun ' . $tahun, $entri_jurnal);
        db_commit($koneksi);
        $tot = $total_beban;
        catatLog('Proses Penyusutan Tahunan', 'Membuat jurnal penyusutan & amortisasi tahun ' . $tahun . ' sebesar Rp ' . number_format($tot));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}