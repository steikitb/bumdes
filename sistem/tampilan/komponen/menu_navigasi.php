<?php
function is_active($path, $halaman) {
    if (is_array($path)) {
        foreach ($path as $p) {
            if ($halaman === $p || strpos($halaman, $p . '/') === 0) { return 'active'; }
        }
    } else {
        if ($halaman === $path || strpos($halaman, $path . '/') === 0) { return 'active'; }
    }
    return '';
}
function is_open($paths, $halaman) {
    foreach ($paths as $path) { if (strpos($halaman, $path) === 0) return 'open'; }
    return '';
}
$halaman = $_GET['halaman'] ?? 'dasbor';
$jenis_stok = $_GET['jenis'] ?? ''; // Menangkap parameter jenis untuk persediaan/perlengkapan

$lbl_anggota = MODE_ENTITAS === 'BUMDes' ? 'Penyerta Modal / Pelanggan' : 'Daftar Anggota';
$lbl_simpanan = MODE_ENTITAS === 'BUMDes' ? 'Penyertaan Modal BUMDes' : 'Simpanan Pokok & Wajib';
$lbl_shu = MODE_ENTITAS === 'BUMDes' ? 'Laba Bersih / Bagi Hasil' : 'Distribusi SHU';
$lbl_tutup = MODE_ENTITAS === 'BUMDes' ? 'Tutup Buku / Hitung Laba' : 'Tutup Buku Akhir Tahun';
?>
<div class="drawer-side z-10 print:hidden">
    <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay lg:hidden"></label>
    <ul class="menu p-4 w-80 min-h-full bg-base-100 text-base-content">
        <li class="menu-title text-xl mb-2 text-base-content font-bold">
            <?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'BUMDes') ?>
        </li>
        <li>
            <details>
                <summary class="text-base">
                    <div class="avatar placeholder"><div class="bg-neutral text-neutral-content rounded-full w-8"><span><?= strtoupper(substr($_SESSION['user_data']['nama_lengkap'], 0, 1)) ?></span></div></div>
                    <div><?= htmlspecialchars($_SESSION['user_data']['nama_lengkap']) ?></div>
                </summary>
                <ul>
                    <li><a href="index.php?halaman=pengguna/ubah_kata_sandi_sendiri">Ubah Kata Sandi</a></li>
                    <li><button onclick="toggleTheme()" class="justify-between">Ganti Tema<i class="fas fa-sun" id="theme-icon-desktop"></i></button></li>
                    <li><button id="logout-btn">Keluar</button></li>
                </ul>
            </details>
        </li>
        
        <li><a href="index.php?halaman=dasbor" class="<?= is_active('dasbor', $halaman) ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg> Dasbor Utama</a></li>
        
        <li><a href="index.php?halaman=panduan" class="<?= is_active('panduan', $halaman) ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0-3.332.477-4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg> Buku Saku (Panduan)</a></li>
        
        <li>
            <details <?= is_open(['anggota', 'produk_stok', 'aset_tetap', 'pemasok'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Data Master Entitas</summary>
                <ul>
                    <li><a href="index.php?halaman=anggota/lihat_anggota" class="<?= is_active('anggota', $halaman) ?>"><?= $lbl_anggota ?></a></li>
                    <li><a href="index.php?halaman=produk_stok/lihat_stok&jenis=Barang+Dagangan" class="<?= ($halaman == 'produk_stok/lihat_stok' && $jenis_stok == 'Barang Dagangan') ? 'active' : '' ?>">Persediaan Barang Dagang</a></li>
                    <li><a href="index.php?halaman=produk_stok/lihat_stok&jenis=Perlengkapan" class="<?= ($halaman == 'produk_stok/lihat_stok' && $jenis_stok == 'Perlengkapan') ? 'active' : '' ?>">Perlengkapan & Habis Pakai</a></li>
                    <li><a href="index.php?halaman=pemasok/lihat_pemasok" class="<?= is_active('pemasok', $halaman) ?>">Daftar Pemasok (Supplier)</a></li>
                    <li><a href="index.php?halaman=aset_tetap/lihat_aset" class="<?= is_active('aset_tetap', $halaman) ?>">Daftar Aset & Inventaris</a></li>
                </ul>
            </details>
        </li>

        <li>
            <details <?= is_open(['karyawan'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg> SDM & Penggajian</summary>
                <ul>
                    <li class="menu-title"><span>Master SDM</span></li>
                    <li><a href="index.php?halaman=karyawan/lihat_jabatan" class="<?= is_active('karyawan/lihat_jabatan', $halaman) ?>">Daftar Jabatan</a></li>
                    <li><a href="index.php?halaman=karyawan/lihat_karyawan" class="<?= is_active('karyawan/lihat_karyawan', $halaman) ?>">Data Karyawan / Pengurus</a></li>
                    <li class="menu-title"><span>Transaksi Penggajian</span></li>
                    <li><a href="index.php?halaman=karyawan/lihat_penggajian" class="<?= is_active('karyawan/lihat_penggajian', $halaman) ?>">Riwayat Penggajian / THR</a></li>
                    <li><a href="index.php?halaman=karyawan/form_penggajian" class="<?= is_active('karyawan/form_penggajian', $halaman) ?>">Bayar Gaji / THR Baru</a></li>
                </ul>
            </details>
        </li>

        <li>
            <details <?= is_open(['penjualan', 'pembelian', 'beban', 'pendapatan', 'pinjaman_anggota', 'pinjaman', 'simpanan_sukarela', 'simpanan', 'hibah'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> Pencatatan Transaksi</summary>
                <ul>
                    <li class="menu-title"><span>Aktivitas Operasional</span></li>
                    <li><a href="index.php?halaman=penjualan/lihat_penjualan" class="<?= is_active('penjualan', $halaman) ?>">Penjualan Barang Toko</a></li>
                    <li><a href="index.php?halaman=pembelian/lihat_pembelian" class="<?= is_active('pembelian', $halaman) ?>">Pembelian Persediaan (Stok)</a></li>
                    <li><a href="index.php?halaman=pendapatan/lihat_pendapatan" class="<?= is_active('pendapatan', $halaman) ?>">Penerimaan Jasa / PPOB / Sewa</a></li>
                    <li><a href="index.php?halaman=beban/lihat_beban" class="<?= is_active('beban', $halaman) ?>">Pengeluaran Beban (Biaya)</a></li>
                    <li class="menu-title"><span>Aktivitas Pendanaan / Modal</span></li>
                    <li><a href="index.php?halaman=simpanan/lihat_simpanan" class="<?= is_active('simpanan', $halaman) ?>"><?= $lbl_simpanan ?></a></li>
                    <li><a href="index.php?halaman=hibah/lihat_hibah" class="<?= is_active('hibah', $halaman) ?>">Penerimaan Hibah / Bantuan</a></li>
                    <li><a href="index.php?halaman=pinjaman/lihat_pinjaman" class="<?= is_active('pinjaman', $halaman) ?>">Utang Bank / Pinjaman Diterima</a></li>
                    <li class="menu-title"><span>Unit Simpan Pinjam</span></li>
                    <!-- Dihold untuk entitas BUMDes <li><a href="index.php?halaman=simpanan_sukarela/lihat_saldo" class="< ?= is_active('simpanan_sukarela', $halaman) ? >">Tabungan Masyarakat</a></li> -->
                    <li><a href="index.php?halaman=pinjaman_anggota/lihat_pinjaman" class="<?= is_active('pinjaman_anggota', $halaman) ?>">Piutang Simpan Pinjam</a></li>
                </ul>
            </details>
        </li>

        <li>
            <details <?= is_open(['laporan', 'jurnal/lihat_jurnal', 'calk'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg> Laporan Keuangan</summary>
                <ul>
                    <li><a href="index.php?halaman=laporan/ekspor_audit" class="<?= is_active('laporan/ekspor_audit', $halaman) ?> font-bold text-primary">Cetak Laporan Lengkap (PDF)</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_perhitungan_hasil_usaha" class="<?= is_active('laporan/laporan_perhitungan_hasil_usaha', $halaman) ?>">Laporan Laba Rugi</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_posisi_keuangan" class="<?= is_active('laporan/laporan_posisi_keuangan', $halaman) ?>">Laporan Posisi Keuangan (Neraca)</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_perubahan_ekuitas" class="<?= is_active('laporan/laporan_perubahan_ekuitas', $halaman) ?>">Laporan Perubahan Ekuitas</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_arus_kas" class="<?= is_active('laporan/laporan_arus_kas', $halaman) ?>">Laporan Arus Kas</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_buku_kas_umum" class="<?= is_active('laporan/laporan_buku_kas_umum', $halaman) ?>">Buku Kas Umum (BKU)</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_tingkat_kesehatan" class="<?= is_active('laporan/laporan_tingkat_kesehatan', $halaman) ?>">Penilaian Tingkat Kesehatan</a></li>
                    <li><a href="index.php?halaman=jurnal/lihat_jurnal" class="<?= is_active('jurnal/lihat_jurnal', $halaman) ?>">Buku Jurnal Umum</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_utang_usaha" class="<?= is_active('laporan/laporan_utang_usaha', $halaman) ?>">Buku Pembantu Utang Dagang</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_umur_piutang" class="<?= is_active('laporan/laporan_umur_piutang', $halaman) ?>">Analisis Umur Piutang</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_kebutuhan_stok" class="<?= is_active('laporan/laporan_kebutuhan_stok', $halaman) ?>">Laporan Kebutuhan Stok</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_barang_terlaris" class="<?= is_active('laporan/laporan_barang_terlaris', $halaman) ?>">Laporan Barang Terlaris</a></li>
                    <li><a href="index.php?halaman=calk/lihat_calk" class="<?= is_active('calk/lihat_calk', $halaman) ?>">Catatan atas Laporan Keuangan (CaLK)</a></li>
                    <li><a href="index.php?halaman=laporan/laporan_anggota_keluar" class="<?= is_active('laporan/laporan_anggota_keluar', $halaman) ?>">Pengembalian Dana Anggota Keluar</a></li>
                </ul>
            </details>
        </li>
        
        <li>
            <details <?= is_open(['administrasi_laporan'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h4v-1a1 1 0 011-1h4a1 1 0 011 1v1h4" /></svg> Buku Administrasi Entitas</summary>
                <ul>
                    <li class="menu-title"><span>Kelembagaan</span></li>
                    <li><a href="index.php?halaman=administrasi_laporan/laporan_daftar_anggota" class="<?= is_active('administrasi_laporan/laporan_daftar_anggota', $halaman) ?>">Daftar Pelanggan/Mitra</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/laporan_daftar_pengurus" class="<?= is_active('administrasi_laporan/laporan_daftar_pengurus', $halaman) ?>">Buku Daftar Pengurus</a></li>
                    <li class="menu-title"><span>Aset & Inventaris</span></li>
                    <li><a href="index.php?halaman=administrasi_laporan/laporan_buku_simpanan" class="<?= is_active('administrasi_laporan/laporan_buku_simpanan', $halaman) ?>">Buku Simpanan (Tabungan)</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/laporan_inventaris" class="<?= is_active('administrasi_laporan/laporan_inventaris', $halaman) ?>">Buku Inventaris Barang</a></li>
                    <li class="menu-title"><span>Agenda & Notulensi</span></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_surat_masuk" class="<?= is_active('administrasi_laporan/lihat_surat_masuk', $halaman) ?>">Agenda Surat Masuk</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_surat_keluar" class="<?= is_active('administrasi_laporan/lihat_surat_keluar', $halaman) ?>">Agenda Surat Keluar</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_buku_tamu" class="<?= is_active('administrasi_laporan/lihat_buku_tamu', $halaman) ?>">Buku Tamu</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_kejadian_penting" class="<?= is_active('administrasi_laporan/lihat_kejadian_penting', $halaman) ?>">Catatan Kejadian Penting</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_rapat_anggota" class="<?= is_active('administrasi_laporan/lihat_rapat_anggota', $halaman) ?>">Notulen Musyawarah / RAT</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_rapat_pengurus" class="<?= is_active('administrasi_laporan/lihat_rapat_pengurus', $halaman) ?>">Notulen Rapat Pengurus</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_rapat_pengawas" class="<?= is_active('administrasi_laporan/lihat_rapat_pengawas', $halaman) ?>">Notulen Rapat Pengawas</a></li>
                    <li class="menu-title"><span>Pengawasan</span></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_saran_anggota" class="<?= is_active('administrasi_laporan/lihat_saran_anggota', $halaman) ?>">Saran Masyarakat/Anggota</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_catatan_pengawas" class="<?= is_active('administrasi_laporan/lihat_catatan_pengawas', $halaman) ?>">Catatan Pengawas</a></li>
                    <li><a href="index.php?halaman=administrasi_laporan/lihat_anjuran_pejabat" class="<?= is_active('administrasi_laporan/lihat_anjuran_pejabat', $halaman) ?>">Anjuran Pembina/Pejabat</a></li>
                </ul>
            </details>
        </li>

        <li>
            <details <?= is_open(['tutup_buku', 'pengaturan', 'jurnal/form_penyesuaian', 'jurnal/form_pajak', 'administrasi'], $halaman) ?>>
                <summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg> Administrasi & Pengaturan</summary>
                <ul>
                    <li><a href="index.php?halaman=pengaturan/form_pengaturan" class="<?= is_active('pengaturan/form_pengaturan', $halaman) ?>">Pengaturan Identitas Entitas</a></li>
                    <li><a href="index.php?halaman=pengaturan/form_migrasi" class="<?= is_active('pengaturan/form_migrasi', $halaman) ?>">Setup Awal & Migrasi Saldo</a></li>
                    <li><a href="index.php?halaman=pengaturan/lihat_akun" class="<?= is_active(['pengaturan/lihat_akun', 'pengaturan/form_akun'], $halaman) ?>">Master Bagan Akun (COA)</a></li>
                    <li><a href="index.php?halaman=pengaturan/lihat_kategori_beban" class="<?= is_active(['pengaturan/lihat_kategori_beban', 'pengaturan/form_kategori_beban'], $halaman) ?>">Pengaturan Kategori Beban</a></li>
                    <li><a href="index.php?halaman=pengaturan/form_pengaturan_kebijakan" class="<?= is_active('pengaturan/form_pengaturan_kebijakan', $halaman) ?>">Pemetaan Akun Otomatis</a></li>
                    <?php if (MODE_ENTITAS === 'Koperasi'): ?>
                    <li><a href="index.php?halaman=pengaturan/form_pengaturan_shu" class="<?= is_active('pengaturan/form_pengaturan_shu', $halaman) ?>">Pengaturan Kebijakan SHU</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?halaman=jurnal/form_pajak" class="<?= is_active('jurnal/form_pajak', $halaman) ?>">Pencatatan Pajak (PPh Badan)</a></li>
                    <li><a href="index.php?halaman=jurnal/form_penyesuaian" class="<?= is_active('jurnal/form_penyesuaian', $halaman) ?>">Jurnal Penyesuaian Akhir Bulan</a></li>
                    <li><a href="index.php?halaman=tutup_buku/form_tutup_buku" class="<?= is_active('tutup_buku/form_tutup_buku', $halaman) ?>"><?= $lbl_tutup ?></a></li>
                    <li><a href="index.php?halaman=tutup_buku/form_distribusi_shu" class="<?= is_active('tutup_buku/form_distribusi_shu', $halaman) ?>"><?= $lbl_shu ?></a></li>
                    <li><a href="index.php?halaman=administrasi/lihat_log_aktivitas" class="<?= is_active('administrasi/lihat_log_aktivitas', $halaman) ?>">Log Aktivitas Sistem</a></li>
                    <?php if (($_SESSION['user_data']['role'] ?? '') === 'admin'): ?>
                    <li><a href="index.php?halaman=pengguna/lihat_pengguna" class="<?= is_active('pengguna/lihat_pengguna', $halaman) ?> font-bold text-primary">Manajemen Pengguna</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?halaman=pengaturan/pemeliharaan" class="<?= is_active('pengaturan/pemeliharaan', $halaman) ?>">Pemeliharaan Data (Backup/Restore)</a></li>
                </ul>
            </details>
        </li>
        
        <li class="mt-4">
            <a href="index.php?halaman=tentang" class="<?= is_active('tentang', $halaman) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Tentang Aplikasi
            </a>
        </li>
    </ul>
</div>