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
        $histori_shu_id = (int)$_POST['histori_shu_id'];
        $tanggal_akhir_tahun = $tahun . '-12-31';

        $histori_shu_q = query("SELECT alokasi_jasa_anggota FROM histori_shu WHERE id = ?", [$histori_shu_id]);
        if (empty($histori_shu_q)) { throw new Exception("Data histori SHU tidak ditemukan."); }
        $total_alokasi_jasa_anggota = (float)$histori_shu_q[0]['alokasi_jasa_anggota'];

        $cek_distribusi = query("SELECT id FROM histori_shu_anggota WHERE histori_shu_id = ? LIMIT 1", [$histori_shu_id]);
        if (!empty($cek_distribusi)) { throw new Exception("Distribusi SHU untuk tahun $tahun sudah pernah diproses."); }

        $persen_jasa_modal = (float)($_PENGATURAN['shu_persen_jasa_modal'] ?? 30);
        $persen_jasa_usaha = (float)($_PENGATURAN['shu_persen_jasa_usaha'] ?? 70);
        $poin_per_rupiah_modal = (int)($_PENGATURAN['shu_poin_per_rupiah_modal'] ?? 1000);
        $min_keanggotaan_bulan = (int)($_PENGATURAN['shu_min_keanggotaan_bulan'] ?? 3);
        $tgl_batas_keanggotaan = date('Y-m-d', strtotime("$tanggal_akhir_tahun -$min_keanggotaan_bulan months"));

        $dana_jasa_modal = $total_alokasi_jasa_anggota * ($persen_jasa_modal / 100);
        $dana_jasa_usaha = $total_alokasi_jasa_anggota * ($persen_jasa_usaha / 100);

        $tahun_str = (string)$tahun;

        $total_poin_usaha_q = query("SELECT SUM(hppa.jumlah_poin) as grand_total FROM histori_poin_ppa hppa JOIN anggota a ON hppa.anggota_id = a.id WHERE hppa.jenis_poin = 'usaha' AND strftime('%Y', hppa.tanggal_perolehan) = ? AND a.tanggal_bergabung <= ? AND (a.status_keanggotaan = 'Aktif' OR (a.status_keanggotaan = 'Keluar' AND strftime('%Y', a.tanggal_keluar) = ?))", [$tahun_str, $tgl_batas_keanggotaan, $tahun_str]);
        $grand_total_poin_usaha = $total_poin_usaha_q[0]['grand_total'] ?? 0;

        $simpanan_anggota_q = query("
            SELECT anggota_id, SUM(jumlah) as total_simpanan 
            FROM simpanan s
            JOIN anggota a ON s.anggota_id = a.id
            WHERE s.jenis_simpanan IN ('Pokok', 'Wajib') 
            AND s.tanggal_setor <= ?
            AND a.tanggal_bergabung <= ?
            AND (a.status_keanggotaan = 'Aktif' OR (a.status_keanggotaan = 'Keluar' AND strftime('%Y', a.tanggal_keluar) = ?))
            GROUP BY s.anggota_id
        ", [$tanggal_akhir_tahun, $tgl_batas_keanggotaan, $tahun_str]);

        $grand_total_poin_modal = 0;
        $poin_modal_per_anggota = [];
        foreach($simpanan_anggota_q as $simpanan) {
            $poin = ($poin_per_rupiah_modal > 0) ? floor($simpanan['total_simpanan'] / $poin_per_rupiah_modal) : 0;
            $grand_total_poin_modal += $poin;
            $poin_modal_per_anggota[$simpanan['anggota_id']] = $poin;
        }

        $nilai_per_poin_usaha = ($grand_total_poin_usaha > 0) ? $dana_jasa_usaha / $grand_total_poin_usaha : 0;
        $nilai_per_poin_modal = ($grand_total_poin_modal > 0) ? $dana_jasa_modal / $grand_total_poin_modal : 0;

        $poin_usaha_per_anggota_q = query("SELECT anggota_id, SUM(jumlah_poin) as total_poin FROM histori_poin_ppa hppa JOIN anggota a ON hppa.anggota_id = a.id WHERE jenis_poin = 'usaha' AND strftime('%Y', tanggal_perolehan) = ? AND a.tanggal_bergabung <= ? AND (a.status_keanggotaan = 'Aktif' OR (a.status_keanggotaan = 'Keluar' AND strftime('%Y', a.tanggal_keluar) = ?)) GROUP BY anggota_id", [$tahun_str, $tgl_batas_keanggotaan, $tahun_str]);
        
        $sql = "INSERT INTO histori_shu_anggota (histori_shu_id, anggota_id, tahun, jasa_modal_diterima, jasa_usaha_diterima, total_poin_modal, total_poin_usaha, nilai_per_poin_modal, nilai_per_poin_usaha, total_diterima) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);

        $anggota_diproses = [];

        foreach ($poin_usaha_per_anggota_q as $item) {
            $anggota_id = $item['anggota_id'];
            $total_poin_usaha = $item['total_poin'];
            $total_poin_modal = $poin_modal_per_anggota[$anggota_id] ?? 0;

            $jasa_usaha_diterima = $total_poin_usaha * $nilai_per_poin_usaha;
            $jasa_modal_diterima = $total_poin_modal * $nilai_per_poin_modal;
            $total_diterima = $jasa_usaha_diterima + $jasa_modal_diterima;

            if ($total_diterima > 0) {
                if (!$stmt->execute([$histori_shu_id, $anggota_id, $tahun, $jasa_modal_diterima, $jasa_usaha_diterima, $total_poin_modal, $total_poin_usaha, $nilai_per_poin_modal, $nilai_per_poin_usaha, $total_diterima])) { 
                    throw new Exception("Gagal menyimpan data SHU untuk anggota ID: $anggota_id"); 
                }
            }
            $anggota_diproses[] = $anggota_id;
        }

        foreach ($poin_modal_per_anggota as $anggota_id => $total_poin_modal) {
            if (!in_array($anggota_id, $anggota_diproses)) {
                $jasa_modal_diterima = $total_poin_modal * $nilai_per_poin_modal;
                if ($jasa_modal_diterima > 0) {
                    if (!$stmt->execute([$histori_shu_id, $anggota_id, $tahun, $jasa_modal_diterima, 0, $total_poin_modal, 0, $nilai_per_poin_modal, $nilai_per_poin_usaha, $jasa_modal_diterima])) { 
                        throw new Exception("Gagal menyimpan data SHU (modal only) untuk anggota ID: $anggota_id"); 
                    }
                }
            }
        }

        db_commit($koneksi);
        catatLog('Distribusi SHU', 'Melakukan distribusi SHU tahun ' . $tahun . '. Total dana: Rp ' . number_format($total_alokasi_jasa_anggota));
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/lihat_distribusi_shu&tahun=' . $tahun . '&status=sukses');
    } catch (Exception $e) {
        db_rollback($koneksi);
        header('Location: ' . BASE_URL . 'index.php?halaman=tutup_buku/form_distribusi_shu&tahun=' . $tahun . '&status=gagal&error=' . urlencode($e->getMessage()));
    }
    exit;
}