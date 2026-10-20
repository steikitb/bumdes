# Administrasi: Pemeliharaan Data Sistem

Menu **Pemeliharaan Data (Backup/Restore)** sangat penting untuk menjaga keamanan data keuangan Anda, mengingat aplikasi ini berjalan tanpa internet sehingga seluruh data tersimpan hanya di komputer ini.

Menu ini **hanya dapat diakses oleh Administrator**. Terdapat empat fungsi utama pada halaman ini:

## 1. Cadangkan Data (Unduh)
Menyimpan salinan seluruh data Anda saat ini sebagai berkas cadangan.
*   **Cara kerja:** Klik tombol unduh pada kartu "1. Backup Data (Cadangkan)". Aplikasi akan mengunduh satu berkas ke folder unduhan komputer Anda. Berkas ini berisi seluruh transaksi, pengaturan, dan data induk Anda sampai detik saat diunduh.
*   **Kapan digunakan:** Lakukan secara rutin — **minimal seminggu sekali**, atau setiap kali Anda selesai memasukkan banyak transaksi. Simpan berkasnya di tempat aman seperti flashdisk, harddisk eksternal, atau Google Drive. Ini mencegah kehilangan data bila komputer utama rusak, hilang, atau terkena virus.

## 2. Pulihkan Data (Restore)
Mengembalikan data Anda ke kondisi sebelumnya memakai berkas cadangan yang pernah diunduh.
*   **Cara kerja:** Klik **"Unggah File & Restore"**, pilih berkas cadangan Anda, lalu klik **"Mulai Restore Data"**. Aplikasi akan **menimpa seluruh data yang ada sekarang** dengan isi berkas tersebut.
*   **Peringatan:** Semua transaksi yang terjadi *setelah* berkas cadangan dibuat akan hilang dan **tidak bisa dikembalikan**. Gunakan hanya bila terjadi kerusakan data parah, atau saat memindahkan aplikasi ke komputer/laptop baru.
*   Selalu buat cadangan data terbaru **sebelum** melakukan restore, supaya data yang ada sekarang tidak hilang tanpa jejak.

## 3. Kosongkan Data (Reset)
Membersihkan seluruh data transaksi secara massal dan mengembalikan aplikasi ke kondisi seperti baru dipasang.
*   **Apa yang dihapus:** Seluruh riwayat Penjualan, Pembelian, Jurnal Umum, Kas, Daftar Anggota, Pemasok, Produk, Aset, Pinjaman, dan Buku Administrasi.
*   **Apa yang dipertahankan:** Akun Administrator (akun yang Anda pakai), Pengaturan Identitas Entitas, dan Master Bagan Akun (COA).
*   **Cara menjalankan:** Klik **"Reset Seluruh Data"**, lalu ketik kalimat **RESET DATA** pada kolom konfirmasi, kemudian klik **"Hapus Seluruh Data"**. Pengetikan kalimat ini mencegah penghapusan tidak sengaja.
*   **Kapan digunakan:** Sangat berguna kalau Anda selama ini hanya melakukan simulasi/uji coba, dan sekarang siap mulai pembukuan sungguhan.

## 4. Percepat Aplikasi (Optimalisasi)
Seiring pemakaian, aplikasi bisa menjadi lambat karena berkas datanya menumpuk "ruang kosong" dari transaksi yang pernah dihapus atau diubah.
*   **Fungsi:** Tombol ini merapikan berkas data di latar belakang — membuang ruang kosong dan mengecilkan ukuran berkas, sehingga aplikasi kembali cepat.
*   **Kapan digunakan:** Lakukan **sekali sebulan** supaya aplikasi tetap ringan dan responsif.

## 5. Berkas Log Error

Sistem menulis catatan setiap kali terjadi kesalahan teknis ke berkas `sistem/database/php_error.log`.

Berkas ini berguna bila:

*   Ada halaman yang tiba-tiba tampil kosong atau gagal dibuka.
*   Ingin mengetahui penyebabnya tanpa harus masuk ke server.
*   Perlu menyertakan bukti kesalahan kepada pengembang.

**Cara membacanya:** masuk ke folder `sistem/database` di hosting, lalu buka berkas `php_error.log`. Setiap baris memuat waktu, jenis kesalahan, pesan, nama berkas, nomor baris, dan halaman yang sedang dibuka.

> **Catatan:** berkas ini bisa membesar bila ada kesalahan berulang. Sistem otomatis memindahkan log lama menjadi `php_error.log.1` saat ukurannya melebihi 2 MB, jadi tidak akan memenuhi disk.
