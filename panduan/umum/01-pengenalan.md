# Pengenalan Sistem Akuntansi Koperasi & BUMDes

Selamat datang di Panduan Sistem Informasi Akuntansi.

Aplikasi ini dirancang untuk dipakai **tanpa internet**. Semua data keuangan Anda tersimpan di satu tempat di komputer ini, tidak tersebar di banyak komputer dan tidak dikirim ke komputer milik pihak lain.

## Dukungan Multi-Entitas (Koperasi & BUMDes)
Aplikasi ini dapat menyesuaikan istilah, alur kerja, dan daftar akun secara otomatis mengikuti **Mode Entitas** yang Anda pilih di menu **Administrasi & Pengaturan -> Pengaturan Identitas Entitas**:
1. **Mode Koperasi** — memakai istilah dan alur baku koperasi: SHU, Simpanan Pokok dan Wajib, Jasa Anggota, serta Poin Partisipasi Anggota.
2. **Mode BUMDes** — sudah disesuaikan dengan **Kepmendesa PDTT Nomor 136 Tahun 2022** dan SAK EP, memakai istilah Penyertaan Modal Desa, Laba Bersih, dan Bagi Hasil PADes.

Istilah pada tombol dan menu ikut berubah otomatis mengikuti mode ini. Contohnya, "Daftar Anggota" menjadi "Penyerta Modal / Pelanggan", dan "Tutup Buku Akhir Tahun" menjadi "Tutup Buku / Hitung Laba".

## Tujuan Aplikasi

*   **Pencatatan Terpusat** — Semua data, mulai dari daftar anggota atau penyerta modal, transaksi simpan pinjam, sampai penjualan barang di toko, tercatat dalam satu tempat yang saling terhubung.
*   **Jurnal Otomatis** — Setiap transaksi yang Anda catat langsung menghasilkan jurnal akuntansi yang sesuai, sehingga Anda tidak perlu menghitung atau mengetik jurnal secara manual.
*   **Laporan Instan** — Laporan keuangan standar (Neraca, Laba Rugi, Arus Kas, Buku Kas Umum, dan lainnya) tersedia kapan saja, tanpa harus menyusun ulang dari nol.
*   **Jejak Audit** — Setiap aksi penting tercatat di menu **Log Aktivitas Sistem**, sehingga mudah ditelusuri siapa mengubah apa dan kapan.

## Cara Kerja Aplikasi

Aplikasi berjalan **offline pada satu perangkat**, tetapi dapat dipakai oleh **beberapa akun pengguna** yang dibuat oleh Administrator. Setiap akun punya username, kata sandi, dan **role** sendiri; role menentukan menu dan tombol aksi yang muncul.

*   **Administrator** — akses penuh, termasuk **Manajemen Pengguna**, Pengaturan, Migrasi Saldo, Tutup Buku, dan Pemeliharaan Data.
*   **Role operasional** — misalnya Kasir Penjualan, Staf Pembelian, Staf Persediaan, Staf Simpanan, Staf Pinjaman, Staf Aset Tetap, Staf Pemasok, Staf Hibah, dan Staf Buku Administrasi. Setiap role hanya membuka modul yang menjadi tugasnya.
*   **Role laporan & jabatan** — Lihat Laporan Keuangan, Jurnal Penyesuaian, Pengurus, dan Pengawas.
*   Akun baru dan akun yang kata sandinya direset wajib **mengganti kata sandi saat login pertama**. Kata sandi minimal 8 karakter dan harus memuat huruf serta angka.
*   Setiap pengguna dapat mengganti kata sandinya sendiri lewat menu **Ubah Kata Sandi**. Lihat [Mengubah Kata Sandi](umum/04-mengganti-kata-sandi.md).
*   Administrator mengelola akun di menu **Administrasi & Pengaturan -> Manajemen Pengguna**. Lihat [Manajemen Pengguna](umum/05-manajemen-pengguna.md).

> **Keamanan:** satu perangkat yang dipakai bergantian tetap aman selama setiap petugas memakai akun masing-masing dan tidak berbagi kata sandi. Log Aktivitas mencatat aksi berdasarkan akun yang sedang login.

## Isi Navigasi

Navigasi utama aplikasi dibagi menjadi 7 grup:

1. **Data Master Entitas** — Anggota, Barang dan Stok, Aset Tetap, Pemasok
2. **SDM dan Penggajian** — Jabatan, Karyawan, Riwayat dan Pembayaran Gaji
3. **Pencatatan Transaksi** — Penjualan, Pembelian, Beban, Pendapatan, Simpanan, Pinjaman, Hibah
4. **Laporan Keuangan** — Seluruh laporan keuangan dan operasional
5. **Buku Administrasi Entitas** — Buku-buku organisasi, notulen rapat, agenda surat
6. **Administrasi & Pengaturan** — Pengaturan, Migrasi Saldo, Tutup Buku, Log, **Manajemen Pengguna** (khusus Admin), Cadangan Data
7. **Tentang Aplikasi**

Menu yang tampil menyesuaikan role akun. Jika sebuah menu tidak terlihat, berarti role Anda tidak memiliki akses ke modul tersebut.

Silakan pilih topik dari daftar di samping, atau gunakan kotak pencarian di atas daftar, untuk mempelajari setiap fitur pencatatan dan pelaporan secara lebih mendalam.
