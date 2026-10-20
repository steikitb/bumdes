# Master Data: Aset Tetap

Modul ini digunakan untuk mencatat dan mengelola aset berwujud yang dimiliki entitas dengan masa manfaat lebih dari satu tahun, seperti tanah, bangunan, kendaraan, atau peralatan kantor.

## Menambah Aset Tetap 

Terdapat dua cara/skenario untuk mencatat aset tetap di dalam sistem:

1. **Aset Beli Baru (Kas Keluar)**
   Pilih opsi ini jika Anda melakukan pembelian aset hari ini. Sistem akan secara otomatis:
   * Menambah aset ke dalam daftar (Buku Inventaris).
   * Membuat Jurnal Pengeluaran Kas dan penambahan saldo aset di Neraca.

2. **Aset Lama (Setup Awal / Migrasi)**
   Pilih opsi ini saat Anda pertama kali menggunakan sistem dan ingin memasukkan daftar aset yang **sudah dibeli di masa lalu**.
   * Sistem **TIDAK** akan membuat Jurnal Kas Keluar.
   * Aset ini hanya akan masuk ke Buku Inventaris.
   * *Catatan Penting:* Nilai Harga Perolehan dan Akumulasi Penyusutannya di bidang keuangan **wajib Anda isi sendiri secara manual melalui menu Administrasi & Pengaturan -> Setup Awal & Migrasi Saldo** di awal periode.

**Langkah-langkah:**
1. Dari menu **Data Master Entitas**, pilih **Daftar Aset & Inventaris**.
2. Klik tombol **"Tambah Aset Baru / Aset Lama"**.
3. Pilih *Status Perolehan (Akuntansi)* sesuai kondisi di atas.
4. Isi formulir dengan detail aset:
    * **Nama Aset:** Nama yang jelas (contoh: Komputer Kasir, Gedung Kantor).
    * **Kategori Aset:** Pilih kategori (Peralatan dan Mesin, Bangunan, dll) sesuai standar bagan akun SAK.
    * **Tanggal Perolehan:** Tanggal pembelian aset.
    * **Harga Perolehan (Rp):** Total biaya yang dikeluarkan untuk mendapatkan aset.
    * **Masa Manfaat (Tahun):** Estimasi umur ekonomis. Digunakan untuk menghitung beban penyusutan.
5. Klik **"Simpan Master Aset"**.

## Penyusutan Aset
Sistem tidak menyusutkan aset secara *real-time* setiap hari agar performa tetap cepat. 
Sebagai gantinya, Anda **wajib** menjalankan proses Penyusutan di menu **Administrasi -> Jurnal Penyesuaian Akhir Bulan** (Bisa dilakukan secara bulanan, atau sekaligus satu tahun penuh di akhir tahun). Sistem akan menghitung otomatis untuk Anda.

## Melepas Aset (Menjual / Dibuang)

Ketika sebuah aset dijual, hilang, atau sudah tidak digunakan lagi, Anda harus melepasnya dari catatan.
Aksi ini akan secara otomatis membuat **Jurnal Pelepasan Aset**, menghapus nilai buku aset dari neraca, dan mencatat Laba/Rugi pelepasan.

1. Di halaman daftar aset, klik tombol **"Lepas Aset"**.
2. Masukkan tanggal transaksi dan **Harga Jual (Uang Masuk)**. *Jika dibuang/rusak karena bencana, biarkan 0.*
3. Klik **"Eksekusi Jurnal Pelepasan"**.