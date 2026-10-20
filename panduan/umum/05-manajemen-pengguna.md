# Manajemen Pengguna

Menu **Manajemen Pengguna** dipakai Administrator untuk membuat dan mengatur akun yang bisa masuk ke aplikasi. Aplikasi memang berjalan di satu perangkat, tetapi setiap petugas sebaiknya memakai akun sendiri agar Log Aktivitas mencatat siapa melakukan apa.

> Menu ini hanya muncul untuk akun dengan role **Administrator**. Role lain tidak dapat membuka halaman ini.

## Membuka Menu

1. Masuk sebagai Administrator.
2. Buka grup **Administrasi & Pengaturan**.
3. Pilih **Manajemen Pengguna**.

Daftar akun menampilkan kolom **Username**, **Nama Lengkap**, **Role / Hak Akses**, dan **Status**:

*   **Anda** — menandai akun yang sedang dipakai.
*   **Aktif** — akun siap dipakai.
*   **Wajib ganti sandi** — akun masih memakai sandi awal/reset dan akan dipaksa mengganti sandi saat login berikutnya.

## Menambah Pengguna

1. Klik **Tambah Pengguna**.
2. Isi formulir:
    *   **Username** — 3–30 karakter, boleh huruf, angka, titik, garis bawah, dan strip (contoh: `kasir1`). Harus unik; username yang sudah dipakai akan ditolak.
    *   **Nama Lengkap** — nama yang tampil di aplikasi dan di Log Aktivitas.
    *   **Role / Hak Akses** — menentukan menu dan tombol yang boleh dipakai (lihat tabel role di bawah).
    *   **Kata Sandi** — minimal 8 karakter dan wajib mengandung huruf serta angka.
    *   **Konfirmasi Kata Sandi** — ulangi kata sandi yang sama.
3. Klik **Simpan Pengguna**.
4. Sampaikan username dan sandi awal kepada petugas. Saat login pertama, sistem otomatis meminta petugas mengganti sandi.

## Daftar Role

| Role | Cocok untuk | Akses utama |
| --- | --- | --- |
| Administrator (akses penuh) | Pemilik / kepala unit | Semua menu, termasuk Manajemen Pengguna, Pengaturan, Tutup Buku, dan Backup |
| Kasir Penjualan | Petugas kasir | Penjualan toko, scan barcode, cetak struk, lihat stok |
| Staf Pembelian | Petugas belanja | Pembelian persediaan dan pemasok |
| Staf Beban Operasional | Bendahara | Catat beban operasional |
| Staf Persediaan / Produk | Petugas gudang | Master barang, stok opname, konversi, penurunan nilai |
| Staf Anggota | Administrasi anggota | Data anggota/pelanggan |
| Staf Simpanan | Bendahara | Simpanan pokok, wajib, dan sukarela |
| Staf Pinjaman | Bendahara | Pinjaman anggota dan angsuran |
| Staf Aset Tetap | Bendahara | Aset tetap dan penyusutan |
| Staf Pemasok | Petugas belanja | Data pemasok |
| Staf Bagan Akun (COA) | Akuntan | Daftar akun dan pemetaan akun |
| Pengaturan Sistem | Operator | Pengaturan identitas dan kebijakan |
| Staf Hibah | Bendahara | Penerimaan hibah |
| Staf Buku Administrasi | Sekretariat | Surat, tamu, rapat, saran, catatan pengawas |
| Lihat Laporan Keuangan | Pengurus / pengawas | Membaca seluruh laporan |
| Proses Tutup Buku | Bendahara senior | Tutup buku tahunan |
| Jurnal Penyesuaian | Akuntan | Jurnal penyesuaian dan koreksi |
| Pengurus (jabatan, tanpa akses modul) | Pengurus | Penanda jabatan pada laporan; tanpa modul transaksi |
| Pengawas (jabatan, tanpa akses modul) | Pengawas | Penanda jabatan pada laporan; tanpa modul transaksi |

> Role bersifat **satu per akun**. Jika satu orang merangkap beberapa tugas, buat akun terpisah atau berikan role yang paling luas cakupannya.

## Mengedit Pengguna

1. Klik **Edit** pada baris akun.
2. Ubah nama lengkap, role, atau kata sandi.
3. **Kata Sandi Baru (opsional):** kosongkan bila tidak ingin mengganti sandi. Bila diisi, akun akan diminta mengganti sandi lagi saat login berikutnya.
4. Klik **Simpan Perubahan**.

Yang perlu diperhatikan:

*   **Role akun sendiri tidak bisa diubah.** Kolom role dikunci agar Administrator tidak kehilangan akses ke Manajemen Pengguna.
*   Username tetap harus unik. Mengubah username berarti petugas harus memakai username baru saat login.
*   Setelah reset sandi, sampaikan sandi baru kepada petugas; jangan memakai sandi yang sama untuk beberapa akun.

## Menghapus Pengguna

1. Klik **Hapus** pada baris akun.
2. Baca pesan konfirmasi, lalu setujui.

Pembatasan keamanan:

*   **Akun sendiri tidak bisa dihapus.** Tombol Hapus dinonaktifkan untuk akun yang sedang dipakai.
*   **Administrator terakhir tidak bisa dihapus.** Minimal harus ada satu akun Administrator yang tersisa agar sistem tetap bisa diatur.
*   Penghapusan akun tidak menghapus transaksi yang pernah dicatatnya. Riwayat transaksi dan Log Aktivitas tetap tersimpan.

## Alur Setup Awal yang Disarankan

1. Login sebagai Administrator, lalu ganti sandi bawaan.
2. Buat akun untuk setiap petugas: kasir, staf pembelian, bendahara, dan pengurus.
3. Minta setiap petugas login dan mengganti sandi masing-masing.
4. Gunakan akun Administrator hanya untuk pengaturan, bukan untuk transaksi harian.
5. Periksa **Log Aktivitas Sistem** secara berkala untuk melihat aksi di luar kebiasaan.

> **Jangan berbagi akun.** Satu akun yang dipakai bergantian membuat Log Aktivitas tidak lagi menunjukkan siapa pelakunya. Jika petugas lupa sandi, Administrator cukup membuka **Edit** dan mengisi kata sandi baru.
