<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('proses_penyesuaian_jurnal');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['periode_bulan'])) {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $periode = $_POST['periode_bulan'];
        $tahun = date('Y', strtotime($periode));
        $bulan = date('m', strtotime($periode));
        $tanggal_jurnal = date('Y-m-t', strtotime($periode));
        $nomor_bukti = 'PENYUSUTAN-BLN-' . $tahun . $bulan;

        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ?", [$nomor_bukti]);
        if (!empty($cek_jurnal)) {
            throw new Exception('Jurnal penyusutan untuk periode ' . date('F Y', strtotime($periode)) . ' sudah ada.');
        }

        $cek_tahunan = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ['PENYUSUTAN-'.$tahun]);
        if (!empty($cek_tahunan)) {
            throw new Exception('Penyusutan tahunan sudah dilakukan untuk tahun ini. Tidak bisa lagi melakukan penyusutan bulanan.');
        }

        $aset_tetap = query("SELECT * FROM aset_tetap WHERE masa_manfaat_tahun > 0 AND tanggal_perolehan <= ?", [$tanggal_jurnal]);

        $debit_per_akun = [];   // kode_akun_beban => total
        $kredit_per_akun = [];  // kode_akun_akumulasi => total
        $total_beban = 0;

        $sql_rekap = "INSERT INTO aset_tetap_penyusutan (aset_id, kategori_aset, kode_akun_akumulasi, beban, tanggal, nomor_bukti, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_rekap = $koneksi->prepare($sql_rekap);

        $jml_hari_bulan = (int)date('t', strtotime($periode));

        foreach ($aset_tetap as $aset) {
            if ($aset['kategori_aset'] == 'Konstruksi Dalam Pengerjaan (KDP)') {
                continue; // KDP tidak disusutkan
            }

            $map = mapKelasAsetAkun($aset['kategori_aset']);
            $kode_aset = $map[0];
            $kode_akumulasi = $map[1];
            $kode_beban = $map[2];
            if (!$kode_akumulasi || !$kode_beban) {
                continue; // mis. Tanah tidak disusutkan
            }

            $nilai_residu = (MODE_ENTITAS == 'BUMDes') ? 0 : (float)$aset['nilai_residu'];
            $batas_penyusutan = (float)$aset['harga_perolehan'] - $nilai_residu;
            if ($batas_penyusutan <= 0) { continue; }

            $penyusutan_bulanan = ($batas_penyusutan / (int)$aset['masa_manfaat_tahun']) / 12;

            // Prorata bulan pertama bila aset diperoleh di tengah bulan
            if (date('Y-m', strtotime($aset['tanggal_perolehan'])) === date('Y-m', strtotime($periode))) {
                $hari_act = (int)$jml_hari_bulan - (int)date('j', strtotime($aset['tanggal_perolehan'])) + 1;
                $penyusutan_bulanan *= ($jml_hari_bulan > 0) ? ($hari_act / $jml_hari_bulan) : 1;
            }

            // Cap per-ASET: gunakan akumulasi milik aset ini, bukan agregat kategori
            $akum_aset = getAkumulasiAset($aset['id']);
            if ($akum_aset >= $batas_penyusutan) { continue; }
            if (($akum_aset + $penyusutan_bulanan) > $batas_penyusutan) {
                $penyusutan_bulanan = $batas_penyusutan - $akum_aset;
            }

            if ($penyusutan_bulanan <= 0) { continue; }

            if (!isset($debit_per_akun[$kode_beban])) { $debit_per_akun[$kode_beban] = 0; }
            if (!isset($kredit_per_akun[$kode_akumulasi])) { $kredit_per_akun[$kode_akumulasi] = 0; }
            $debit_per_akun[$kode_beban] += $penyusutan_bulanan;
            $kredit_per_akun[$kode_akumulasi] += $penyusutan_bulanan;
            $total_beban += $penyusutan_bulanan;

            $stmt_rekap->execute([$aset['id'], $aset['kategori_aset'], $kode_akumulasi, $penyusutan_bulanan, $tanggal_jurnal, $nomor_bukti, 'Penyusutan ' . date('F Y', strtotime($periode))]);
        }

        if ($total_beban <= 0) {
            throw new Exception('Tidak ada beban penyusutan/amortisasi untuk diproses bulan ini.');
        }

        $entri_jurnal = [];
        foreach ($debit_per_akun as $kode => $jml) { $entri_jurnal[] = ['kode_akun' => $kode, 'debit' => $jml, 'kredit' => 0]; }
        foreach ($kredit_per_akun as $kode => $jml) { $entri_jurnal[] = ['kode_akun' => $kode, 'debit' => 0, 'kredit' => $jml]; }

        buatJurnal($nomor_bukti, $tanggal_jurnal, 'Penyusutan Aset & Amortisasi Bulan ' . date('F Y', strtotime($periode)), $entri_jurnal);
        db_commit($koneksi);
        $tot = $total_beban;
        catatLog('Proses Penyusutan Bulanan', 'Membuat jurnal penyusutan & amortisasi bulanan periode ' . $periode . ' sebesar Rp ' . number_format($tot));
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=jurnal/form_penyesuaian&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}