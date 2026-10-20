<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    db_begin_transaction($koneksi);
    try {
        $tahun = (int)$_POST['tahun'];
        $tanggal_mulai = $tahun . '-01-01';
        $tanggal_selesai = $tahun . '-12-31';
        
        $cek = query("SELECT id FROM histori_shu WHERE tahun = ?", [$tahun]);
        if (!empty($cek)) { throw new Exception('sudah_diproses'); }
        
        $persen_cadangan = (float)$_POST['persen_cadangan'];
        $persen_jasa_anggota = (float)$_POST['persen_jasa_anggota'];
        $persen_dana_pengurus = (float)$_POST['persen_dana_pengurus'];
        $persen_dana_sosial = (float)$_POST['persen_dana_sosial'];
        $total_persen = $persen_cadangan + $persen_jasa_anggota + $persen_dana_pengurus + $persen_dana_sosial;
        if (round($total_persen, 2) != 100) { throw new Exception('persen_tidak_valid'); }

        $akun_ikhtisar_lr = $_PENGATURAN['akun_map_ikhtisar_lr'] ?? '3311';
        $akun_shu_belum_dibagi = $_PENGATURAN['akun_map_shu_belum_dibagi'] ?? '3221';
        $akun_cadangan = $_PENGATURAN['akun_map_cadangan'] ?? '3211';
        $akun_utang_jasa_anggota = $_PENGATURAN['akun_map_utang_jasa_anggota'] ?? '2131';
        $akun_utang_dana_pengurus = $_PENGATURAN['akun_map_utang_dana_pengurus'] ?? '2132';
        $akun_utang_dana_sosial = $_PENGATURAN['akun_map_utang_dana_sosial'] ?? '2133';

        $daftar_akun_pendapatan = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Pendapatan%'");
        $daftar_akun_beban = query("SELECT kode_akun, saldo_normal FROM daftar_akun WHERE kelompok_akun LIKE 'Beban%'");
        
        $total_shu = 0;
        $entri_jurnal_penutup = [];
        
        foreach($daftar_akun_pendapatan as $akun) {
            $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
            if ($saldo != 0) {
                if ($akun['saldo_normal'] == 'Kredit') {
                    $entri_jurnal_penutup[] = ['kode_akun' => $akun['kode_akun'], 'debit' => $saldo, 'kredit' => 0];
                    $total_shu += $saldo;
                } else {
                    $entri_jurnal_penutup[] = ['kode_akun' => $akun['kode_akun'], 'debit' => 0, 'kredit' => $saldo];
                    $total_shu -= $saldo;
                }
            }
        }
        foreach($daftar_akun_beban as $akun) {
            $saldo = getSaldoAkun($akun['kode_akun'], $tanggal_selesai, $tanggal_mulai);
            if ($saldo != 0) {
                if ($akun['saldo_normal'] == 'Debit') {
                    $entri_jurnal_penutup[] = ['kode_akun' => $akun['kode_akun'], 'debit' => 0, 'kredit' => $saldo];
                    $total_shu -= $saldo;
                } else {
                    $entri_jurnal_penutup[] = ['kode_akun' => $akun['kode_akun'], 'debit' => $saldo, 'kredit' => 0];
                    $total_shu += $saldo;
                }
            }
        }
        
        if (empty($entri_jurnal_penutup)) { throw new Exception('tidak_ada_transaksi'); }
        
        $entri_jurnal_penutup[] = ['kode_akun' => $akun_ikhtisar_lr, 'debit' => ($total_shu < 0 ? abs($total_shu) : 0), 'kredit' => ($total_shu > 0 ? $total_shu : 0)];
        buatJurnal('JT-'.$tahun, $tanggal_selesai, 'Jurnal Penutup Pendapatan & Beban Tahun ' . $tahun, $entri_jurnal_penutup);
        
        $entri_jurnal_shu = [
            ['kode_akun' => $akun_ikhtisar_lr, 'debit' => ($total_shu > 0 ? $total_shu : 0), 'kredit' => ($total_shu < 0 ? abs($total_shu) : 0)],
            ['kode_akun' => $akun_shu_belum_dibagi, 'debit' => ($total_shu < 0 ? abs($total_shu) : 0), 'kredit' => ($total_shu > 0 ? $total_shu : 0)]
        ];
        buatJurnal('JT-'.$tahun, $tanggal_selesai, 'Jurnal Penutup Ikhtisar L/R ke SHU Belum Dibagi Tahun ' . $tahun, $entri_jurnal_shu);
        
        $alokasi_cadangan = $total_shu * ($persen_cadangan / 100);
        $alokasi_jasa_anggota = $total_shu * ($persen_jasa_anggota / 100);
        $alokasi_dana_pengurus = $total_shu * ($persen_dana_pengurus / 100);
        $alokasi_dana_sosial = $total_shu * ($persen_dana_sosial / 100);
        
        $sql_histori = "INSERT INTO histori_shu (tahun, total_shu, alokasi_cadangan, alokasi_jasa_anggota, alokasi_dana_pengurus, alokasi_dana_sosial) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_histori = $koneksi->prepare($sql_histori);
        
        if (!$stmt_histori->execute([$tahun, $total_shu, $alokasi_cadangan, $alokasi_jasa_anggota, $alokasi_dana_pengurus, $alokasi_dana_sosial])) { 
            throw new Exception('gagal_simpan_histori'); 
        }
        
        if ($total_shu > 0) {
            $entri_jurnal_alokasi = [
                ['kode_akun' => $akun_shu_belum_dibagi, 'debit' => $total_shu, 'kredit' => 0],
                ['kode_akun' => $akun_cadangan, 'debit' => 0, 'kredit' => $alokasi_cadangan],
                ['kode_akun' => $akun_utang_jasa_anggota, 'debit' => 0, 'kredit' => $alokasi_jasa_anggota],
                ['kode_akun' => $akun_utang_dana_pengurus, 'debit' => 0, 'kredit' => $alokasi_dana_pengurus],
                ['kode_akun' => $akun_utang_dana_sosial, 'debit' => 0, 'kredit' => $alokasi_dana_sosial],
            ];
            buatJurnal('ALOKASI-SHU-'.$tahun, $tanggal_selesai, 'Jurnal Alokasi SHU Tahun ' . $tahun, $entri_jurnal_alokasi);
        }
        
        db_commit($koneksi);
        catatLog('Proses Tutup Buku', 'Melakukan tutup buku tahun ' . $tahun . '. Total SHU: Rp ' . number_format($total_shu));
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_histori&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        $status = $e->getMessage() ?: 'gagal';
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/form_tutup_buku&status=' . $status);
    }
    exit;
}