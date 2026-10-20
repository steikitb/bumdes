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
        $tanggal_bayar = $_POST['tanggal_bayar'];

        $cek_jurnal = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ['BYR-SHU-'.$tahun]);
                if (!empty($cek_jurnal)) {
                    throw new Exception("SHU untuk tahun $tahun sudah dicairkan secara massal.");
                }

                // Saling-eksklusi: jika PADes sudah disetor dari dana ini, jangan cairkan ke anggota lagi (cegah pencairan ganda)
                $cek_pades = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ["BYR-PADES-$tahun"]);
                if (!empty($cek_pades)) {
                    throw new Exception("Setoran PADes untuk tahun $tahun sudah dilakukan dari dana SHU. Tidak dapat lagi mencairkan ke anggota dari dana yang sama (cegah pencairan ganda).");
                }

        $distribusi_list = query("SELECT anggota_id, total_diterima FROM histori_shu_anggota WHERE tahun = ?", [$tahun]);
        if (empty($distribusi_list)) {
            throw new Exception("Tidak ada rincian data SHU anggota untuk tahun $tahun yang bisa dicairkan.");
        }

        $total_shu_dibayar = 0;
        $sql_simpanan = "INSERT INTO simpanan_sukarela_transaksi (anggota_id, jenis_transaksi, jumlah, tanggal_transaksi, keterangan) VALUES (?, 'Setor', ?, ?, ?)";
        $stmt_simpanan = $koneksi->prepare($sql_simpanan);

        foreach ($distribusi_list as $item) {
            $anggota_id = $item['anggota_id'];
            $jumlah = $item['total_diterima'];
            $keterangan = "Pemindahan Hak SHU Tahun $tahun ke Simpanan Sukarela";

            if ($jumlah > 0) {
                $stmt_simpanan->execute([$anggota_id, $jumlah, $tanggal_bayar, $keterangan]);
                $total_shu_dibayar += $jumlah;
            }
        }

        $akun_utang_jasa_anggota = $_PENGATURAN['akun_map_utang_jasa_anggota'] ?? '2131';
        $akun_simpanan_sukarela = $_PENGATURAN['akun_map_simpanan_sukarela'] ?? '2121';

        $nomor_bukti = 'BYR-SHU-' . $tahun;
        $keterangan_jurnal = "Pencairan (Pemindahan) SHU Anggota Tahun $tahun ke Simpanan Sukarela";

        $entri_jurnal = [
            ['kode_akun' => $akun_utang_jasa_anggota, 'debit' => $total_shu_dibayar, 'kredit' => 0],
            ['kode_akun' => $akun_simpanan_sukarela, 'debit' => 0, 'kredit' => $total_shu_dibayar]
        ];

        buatJurnal($nomor_bukti, $tanggal_bayar, $keterangan_jurnal, $entri_jurnal);

        db_commit($koneksi);
        catatLog('Pencairan SHU', "Memproses pencairan massal SHU tahun $tahun ke Simpanan Sukarela sebesar Rp " . number_format($total_shu_dibayar, 0, ',', '.'));
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun=' . $tahun . '&status=sukses');

    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun=' . $tahun . '&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}