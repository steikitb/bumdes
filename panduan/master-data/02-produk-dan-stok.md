# Data Utama: Data Barang & Stok

Menu ini berfungsi sebagai katalog digital untuk semua barang, baik yang dijual (Barang Dagangan) maupun yang digunakan sendiri untuk operasional (Perlengkapan / ATK). 

Aplikasi ini menggunakan metode pencatatan persediaan perpetual (segera mengupdate nilai setiap transaksi), dan metode perhitungan HPP rata-rata tertimbang (*moving average*).

## Menambah Master Barang Baru

> **PENTING UNTUK SETUP AWAL:** Isi angka **Stok Saat Ini** (Stok Awal) pada halaman penambahan barang ini **HANYA bisa dilakukan SATU KALI saat pembuatan barang baru**. Perubahan stok selanjutnya harus melalui transaksi (Pembelian/Penjualan) atau menggunakan fitur Hitung Ulang Stok.

1.  Dari menu **Data Master Entitas**, pilih **Persediaan Barang Dagang** atau **Perlengkapan & Habis Pakai**.
2.  Klik tombol **"Tambah Baru"**.
3.  Isi formulir data barang:
    *   **Klasifikasi Barang:** Pilih Barang Dagangan (jika dijual ke pelanggan) atau Perlengkapan (jika habis dipakai sendiri).
    *   **Kode & Nama:** Kode unik dan nama yang jelas (contoh: Gula Pasir 1 Kg). Tombol otomatis tersedia.
    *   **Barcode (Scan):** Kode barcode yang tercetak pada kemasan (opsional). Boleh dikosongkan. Isi bila barang memiliki barcode pabrik atau Anda menempelkan label barcode sendiri. Sebaiknya satu barcode hanya dipakai satu barang.
    *   **Harga Beli / Modal:** Harga perolehan per satuan. (Jika ini produk eceran dari induknya, kolom ini akan terkunci dan dihitung otomatis).
    *   **Harga Jual (Pengecer):** Harga jual ke pelanggan per satuan (Untuk perlengkapan kolom ini disembunyikan).
    *   **Stok Fisik Saat Ini:** (Hanya bisa diisi saat pertama kali buat). Masukkan stok awal riil yang ada di gudang.
    *   **Stok Minimum:** Batas jumlah barang di mana sistem akan menampilkan peringatan stok menipis.
    *   **Satuan:** Satuan dasar (contoh: Pcs, Kg, Dus, Box).
    *   **Relasi Konversi (Penting):** Jika barang yang Anda tambahkan adalah eceran (misal: "Bungkus") yang dibongkar dari grosir (misal: "Dus"), pilih produk Dus tersebut di bagian **Produk Induk** dan isi **Rasio Pecahan**.
4.  Klik **"Simpan Data Barang"**.

## Barcode: Menghubungkan Barang dengan Scanner

Barcode dipakai agar kasir dan petugas pembelian tidak perlu mengetik nama barang.

1. Saat membuat atau mengedit barang, isi kolom **Barcode (Scan)** lalu simpan. Untuk barang lama, buka **Edit** pada baris barang dan tambahkan barcodenya.
2. Scanner USB/Bluetooth bekerja seperti keyboard: arahkan kursor ke kolom **Scan Barcode** di halaman kasir atau pembelian, tembakkan scanner, dan karakter akan masuk otomatis diakhiri Enter.
3. Jika barang belum punya barcode, kolom scan tetap bisa diisi **kode barang** (contoh: `BRG001`), karena sistem mencocokkan barcode lebih dulu lalu kode barang.
4. Barcode hanya alat bantu pencarian. Stok, harga, dan jurnal tetap dihitung dari transaksi penjualan/pembelian seperti biasa.

> **Jika scan tidak menemukan barang:** sistem menampilkan peringatan bahwa barcode/kode belum terdaftar. Periksa ejaan barcode di master barang, lalu ulangi scan. Barang tanpa barcode tetap bisa dipilih manual dari daftar produk.

## Melihat Kartu Stok (Riwayat Mutasi)
Untuk mengetahui bagaimana sebuah barang bisa mencapai jumlah stoknya saat ini, Anda bisa melihat buku riwayatnya:
1. Di daftar stok, klik tombol dengan ikon **Titik Tiga** pada baris barang terkait.
2. Pilih menu **"Kartu Stok (Mutasi)"**.
3. Filter berdasarkan tanggal untuk melihat semua masuk/keluarnya barang dari Pembelian dan Penjualan.

## Fitur "Pecah Karung" (Konversi Otomatis)
Seringkali koperasi membeli barang partai besar (Dus) namun menjualnya eceran (Pcs). Fitur ini digunakan untuk memindahkan stok secara manual dari Induk ke Turunan.

1. Di halaman daftar barang, klik tombol **"Pecah Karung"** di bagian atas.
2. Pilih **Produk Induk**.
3. Sistem otomatis memunculkan produk turunannya.
4. Masukkan berapa jumlah Dus yang akan dibongkar. Sistem akan menunjukkan preview hasil ecerannya.
5. Klik **"Proses Pecah Barang"**.
    *Sistem otomatis mengurangi stok Dus, menambah stok Pcs, dan meratakan Harga Pokok (HPP) barang eceran tersebut.*

## Hitung Ulang Stok (Stok Opname / Penyesuaian Fisik)
Gunakan fitur ini jika jumlah stok di sistem berbeda dengan fisik di gudang akibat kehilangan, rusak, atau salah hitung masa lalu.

1. Di daftar barang, klik ikon titik tiga pada baris yang sesuai, lalu pilih **"Hitung Ulang Stok (Opname)"**.
2. Masukkan **Stok Riil (Hasil Hitung Fisik di Gudang)**.
3. Klik **"Eksekusi Penyesuaian"**. 
    *Sistem akan otomatis menghitung selisih dan membuat jurnal akuntansi selisih persediaan kurang/lebih.*

## Penyesuaian Nilai Barang (Loss/Reversal SAK EP)
Gunakan fitur ini jika barang Anda nilainya jatuh di pasar (usang, rusak, dekat kedaluwarsa). Sesuai SAK EP Bab 13, nilai buku persediaan tidak boleh melebihi Harga Jual dikurangi Biaya Penyelesaian (Nilai Realisasi Bersih/NRV).

1. Klik tombol **"Penyesuaian Nilai Barang"** di bagian atas daftar.
2. Pilih produk dan pilih "Pencatatan Penurunan Nilai (Loss)".
3. Masukkan harga NRV per unit (harga jatuh). Sistem akan menghitung otomatis kerugiannya.
4. Jika di masa depan harga barang naik lagi, Anda bisa melakukan pemulihan menggunakan menu yang sama dengan memilih opsi "Pemulihan Penurunan Nilai".

### Yang perlu diperhatikan
- Kerugian dicatat pada akun **Beban Penurunan Nilai Persediaan**, dan nilai barang diturunkan pada akun **Cadangan Penurunan Nilai Persediaan**. Account Persediaan sendiri tidak diubah, sehingga nilai stok di laporan tetap bisa dibandingkan dengan jumlah fisiknya.
- Pemulihan **tidak mungkin melebihi** jumlah penurunan yang pernah dicatat untuk produk tersebut. Kalau Anda memasukkan angka lebih besar, sistem menolak dan menampilkan sisa yang masih boleh dipulihkan.
- Pemulihan hanya bisa dilakukan untuk produk yang sama. Penurunan pada satu produk tidak dapat "dipulihkan" lewat produk lain.
- Penurunan dan pemulihan tidak bisa dilakukan pada tahun buku yang sudah ditutup. Buka tahun bukunya lebih dulu di menu **Tutup Buku**.