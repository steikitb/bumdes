# Administrasi: Manajemen Kategori Beban

Menu ini **hanya dapat diakses oleh Admin**. Fungsinya adalah untuk mengelola daftar kategori pengeluaran (beban) yang dapat dipilih saat melakukan pencatatan di menu **Biaya Operasional**.

Pengelolaan yang baik di sini akan membuat Laporan Untung Rugi menjadi lebih rapi dan mudah dianalisis.

## Konsep Utama

Setiap kategori beban yang Anda buat **wajib dipetakan (dihubungkan)** ke salah satu **Akun Beban** yang ada di Daftar Akun Anda.

**Contoh:**
*   Kategori "Gaji & Imbalan Kerja" dipetakan ke Akun 5121 - Beban Gaji.
*   Kategori "Listrik, Air & Internet" dipetakan ke Akun 5122 - Beban Listrik, Air & Internet.
*   Kategori "Transportasi Dinas" juga bisa dipetakan ke Akun 5123 - Beban Operasional Lainnya.

Dengan cara ini, petugas kasir cukup memilih kategori yang mudah dipahami, dan sistem akan otomatis melakukan penjurnalan ke akun akuntansi yang benar.

## Menambah Kategori Baru

1.  Dari menu **Administrasi & Pengaturan**, pilih **Pengaturan Kategori Beban**.
2.  Klik tombol **"Tambah Kategori"**.
3.  Isi formulir:
    *   **Nama Kategori Beban:** Nama yang mudah dimengerti oleh petugas (contoh: "Biaya ATK", "Iuran Kebersihan").
    *   **Akun Akuntansi Terkait:** Pilih akun dari Daftar Akun yang sesuai untuk menampung beban ini.
4.  Klik **"Simpan Kategori"**.

## Menambah & Membatalkan Biaya Operasional

1.  Dari menu **Pencatatan Transaksi**, pilih **Biaya Operasional**.
2.  Klik tombol **"Catat Biaya Baru"**.
3.  Pilih **kategori beban**, isi tanggal, keterangan, dan jumlah.
4.  Klik **"Simpan"**.

### Membatalkan Biaya yang Salah Catat

Di daftar biaya, klik tombol **"Batalkan"** pada baris yang keliru.

Yang perlu diketahui:
- Stok atau aset lain **tidak terpengaruh**. Yang dibalik hanya catatan pengeluarannya.
- Biaya yang dibatalkan **tidak muncul lagi** di daftar biaya dan tidak bisa dibatalkan dua kali. Riwayatnya tetap tersimpan di riwayat aktivitas.
- Biaya pada tahun buku yang sudah ditutup tidak bisa dibatalkan. Buka tahun bukunya lebih dulu di menu **Tutup Buku**.
- Kalau pemetaan akun pada kategorinya diubah setelah biaya dicatat, pembatalan tetap membalik ke akun yang dipakai saat pencatatan, sehingga angka laporan tidak berantakan.

## Mengubah & Menghapus Kategori

*   **Mengubah:** Klik tombol **"Edit"** pada baris kategori yang ingin diubah. Anda bisa mengubah nama atau pemetaan akunnya.
*   **Menghapus:** Klik tombol **"Hapus"** pada kategori yang tidak lagi digunakan.

> **Penting:** Pastikan semua kategori yang dibutuhkan oleh operasional harian sudah terdaftar dan dipetakan dengan benar untuk memastikan akurasi pencatatan akuntansi.

> **Jika belum yakin pemetaannya:** sebelum menyimpan, cek dulu nama akun yang muncul di kolom **Akun Akuntansi Terkait**. Pastikan nama akunnya sesuai dengan jenis pengeluarannya, misalnya "Gaji" jangan sampai dipetakan ke akun "Beban Sewa".
