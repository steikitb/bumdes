# Dasbor Utama

Dasbor Utama adalah halaman pertama yang akan Anda lihat setelah berhasil masuk ke sistem. Halaman ini memberikan ringkasan visual mengenai kondisi terkini entitas Anda. Isi dasbor menyesuaikan role akun: Administrator dan role laporan melihat seluruh ringkasan, sedangkan role operasional tetap dapat melihat ringkasan umum dan tombol aksi yang menjadi tugasnya.

## 1. Tombol Aksi Cepat (Operasional Harian)

Tiga tombol besar di bagian paling atas halaman memungkinkan Anda melompat langsung ke formulir yang paling sering digunakan:

| Tombol di Layar | Membuka Formulir |
| --- | --- |
| **Buat Penjualan Toko** | Penjualan Barang Toko |
| **Penerimaan Modal** | Penyertaan Modal / Simpanan Pokok & Wajib |
| **Catat Pengeluaran Kas** | Pengeluaran Beban (Biaya) |

> **Catatan:** Pada Mode BUMDes, tombol tengah berbunyi "Penerimaan Modal" karena natureza uangnya adalah penyertaan modal desa/masyarakat, bukan simpanan anggota.

## 2. Peringatan Jatuh Tempo (Piutang & Utang)

Jika ada transaksi kredit yang mendekati atau melewati tanggal jatuh tempo, dua banner besar muncul di bagian atas dasbor:

* **Piutang Toko Jatuh Tempo!** — Daftar pelanggan yang kasbonnya jatuh tempo dalam **7 hari ke depan** atau sudah lewat. Nominal, nomor faktur, dan tanggal jatuh tempo ditampilkan. Tersedia tombol "Buka Buku Utang" untuk mencatat pelunasan.
* **Utang Pemasok Jatuh Tempo!** — Daftar tagihan pemasok yang akan segera jatuh tempo, dengan tombol "Buka Buku Utang" untuk mencatat pelunasan.

Keduanya menampilkan maksimal 5 faktur terdekat. Arsipkan lewat **Laporan Keuangan -> Buku Pembantu Utang Dagang**.

## 3. Peringatan Keamanan Sistem

Kartu merah muncul **hanya jika** ada salah satu kondisi berikut (dan keduanya bisa muncul bersamaan):

* **Aktivitas di luar jam kerja normal (08:00 - 18:00):** Menampilkan 5 aktivitas terakhir di luar jam kerja dalam **7 hari terakhir**, beserta nama pengguna, aksi, dan waktunya. Sumber data: Log Aktivitas Sistem.
* **Aksi penghapusan masif:** Jika tercatat **lebih dari 5 aksi** berawalan "Hapus" dalam **24 jam terakhir**.

Tersedia tombol "Lihat Log Lengkap" untuk membuka Log Aktivitas Sistem.

> **Peringatan Waktu Tidak Sinkron (muncul terpisah):** Peringatan ini bukan bagian dari kartu di atas, melainkan muncul sebagai pesan kecil di pojok layar. Muncul bila selisih tanggal/jam komputer Anda dengan waktu sebenarnya **lebih dari 1 hari (24 jam)**. Perbaiki lewat pengaturan tanggal dan jam pada komputer Anda.

## 4. Informasi Stok Barang Menipis

Kartu informasi biru muncul otomatis jika ada barang persediaan/perlengkapan dengan jumlah stoknya sudah **sama dengan atau lebih kecil dari batas minimum** dan berstatus aktif. Nama barang beserta sisa stoknya ditampilkan, dengan tombol "Buka Daftar Belanja Lengkap" yang menuju ke Laporan Kebutuhan Stok.

## 5. Statistik Kunci

* **Kas & Bank Saat Ini** — Total saldo akun kas & bank .
* **Untung/Rugi Bulan Ini** — Akumulasi SHU/Laba Bersih sementara selama bulan berjalan (Pendapatan dikurangi Beban, langsung dari jurnal umum).
* **Total Penjualan Hari Ini** — Omset penjualan beserta jumlah struk transaksinya.
* **Total Entitas Terdaftar** — Jumlah seluruh anggota/entitas yang terdaftar (aktif maupun yang sudah keluar).

## 6. Grafik Kinerja 6 Bulan Terakhir

Visualisasi batang perbandingan **Pendapatan vs Beban** selama 6 bulan terakhir (termasuk bulan berjalan), dihitung langsung dari Jurnal Umum dengan pengelompokan akun. Berguna untuk melihat tren kinerja tanpa perlu membuka Laba Rugi.

## 7. Pengingat Laporan

Hitung mundur (dengan label "Terlambat X hari" bila sudah lewat):

* **Laporan Triwulan N** — batas akhir: **19 hari setelah pergantian triwulan** berikutnya.
* **Laporan Tahunan YYYY** — batas akhir: **30 April tahun berikutnya**.

## 8. Ringkasan Buku Administrasi

Tiga penghitung aktivitas últimos 7 hari / hari ini:

* **Surat Masuk (7 hr terakhir)**
* **Saran Masuk (7 hr terakhir)**
* **Tamu Berkunjung Hari Ini**

## 9. Informasi Penting Lainnya

* **Aktivitas Terakhir** — 5 transaksi terakhir, gabungan dari 4 jenis: **Penjualan**, **Pembelian**, **Biaya**, dan **Simpanan / Modal**. Menampilkan jenis, nominal, keterangan, dan waktu.
* **Barang Terlaris Bulan Ini** — 5 barang dengan total unit terjual terbanyak pada bulan berjalan.
* **Pelanggan Teraktif (Bulan Ini)** — 5 pelanggan dengan total nominal belanja tertinggi pada bulan berjalan.