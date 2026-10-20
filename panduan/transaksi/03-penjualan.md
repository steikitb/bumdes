# Transaksi: Penjualan Toko (Kasir)

Gunakan menu ini untuk mencatat semua transaksi penjualan barang dari unit usaha waserda (warung serba ada) koperasi / BUMDes.

## Cara Membedakan Barang Grosir dan Eceran
Sistem ini menggunakan Master Data Produk yang sama untuk grosir dan eceran. 
Pastikan saat mengetik atau scan barcode, Anda **memperhatikan satuan barang** yang tertera di layar pilihan (Misalnya: Indomie (Dus) vs Indomie (Pcs)).
*Catatan: Jika pelanggan membeli eceran namun stok eceran kosong, sistem akan otomatis membongkar stok dus di latar belakang.*

## Scan Barcode (Scanner USB/Bluetooth)

Form kasir memiliki kotak **Scan Barcode** khusus yang sudah aktif saat halaman dibuka.

1. Pastikan scanner dalam mode **keyboard/HID** dan diakhiri **Enter** (pengaturan bawaan scanner umumnya sudah begitu).
2. Tembakkan scanner ke barcode barang. Barang langsung masuk keranjang dengan kuantitas 1, lalu fokus otomatis kembali ke kotak scan.
3. Untuk membeli lebih dari satu unit, ketik dulu angka pada kolom **Qty** sebelum scan, atau ubah qty di keranjang setelah barang masuk.
4. Scan barang berikutnya tanpa perlu klik apa pun. Jika barcode tidak dikenali, muncul peringatan dan barang tidak ditambahkan.
5. Barcode yang belum ada di master barang tetap bisa dicari lewat **kode barang** (contoh: `BRG001`), karena sistem mencocokkan barcode lalu kode barang.

**Tombol pintas saat melayani antrean:**

| Tombol | Fungsi |
| --- | --- |
| **F2** | Pindah fokus ke pencarian produk manual (nama/kode) |
| **F3** | Kembali ke kotak Scan Barcode |
| **F8** | Pindah ke kolom Uang Diterima |
| **Enter** di kolom Uang Diterima | Simpan transaksi dan cetak struk |

## Membuat Transaksi Penjualan Baru

1.  Dari menu **Pencatatan Transaksi**, pilih **Penjualan Barang Toko**.
2.  **Tambah Item ke Keranjang:**
    *   Scan barcode pada kotak **Scan Barcode**, atau ketik nama/kode produk pada kolom pencarian.
    *   Ubah kuantitas (Qty) jika pelanggan membeli lebih dari satu.
    *   Tekan tombol Enter atau klik tombol **"Tambah"**. Ulangi untuk barang lain.
3.  **Identitas Pembeli:**
    *   Jika pelanggan adalah **Anggota Terdaftar**, pilih namanya. Transaksi ini akan terekam ke buku anggota dan menghasilkan Poin SHU (khusus Koperasi).
    *   Jika pelanggan **Umum**, biarkan pilihan di "Pelanggan Umum". Anda bisa mengetik namanya di kolom "Nama Pembeli Umum" jika diinginkan.
4.  **Metode Pelunasan (Penting):**
    *   **Uang Tunai (Kasir):** Transaksi dibayar tunai. Uang masuk ke akun Kas Tunai Utama.
    *   **Transfer / QRIS:** Akan muncul pilihan **Rekening Tujuan**. Pilih bank mana yang menerima uang tersebut.
    *   **Kredit / Kasbon:** Transaksi tidak dibayar sekarang. Akan muncul pilihan **Tanggal Jatuh Tempo**. Piutang ini akan muncul sebagai peringatan di Dasbor jika sudah dekat waktu tagihnya.
5.  **Simpan & Cetak:**
    *   Masukkan uang yang diberikan pelanggan di kolom *Uang Diterima dari Pelanggan* untuk melihat kembalian.
    *   Klik **"Simpan Cetak (Enter)"**.
    *   Tab/jendela baru akan terbuka otomatis berisi Struk Kasir Thermal yang siap diprint. Dialog cetak muncul sendiri; tab struk **tidak menutup sendiri** — tutup manual setelah struk selesai dicetak.

## Membatalkan Penjualan (Retur)
Jika terjadi kesalahan input atau barang dikembalikan utuh oleh pelanggan:
1. Dari menu **Pencatatan Transaksi -> Penjualan Barang Toko**, buka daftar riwayat penjualan.
2. Klik tombol **"Rincian Struk"** pada baris penjualan yang bersangkutan.
3. Di halaman detail, klik tombol merah **"Batalkan & Retur"**.
4. Konfirmasi dialog. Aksi ini akan mengembalikan stok produk ke gudang, membatalkan poin PPA anggota, dan membuat jurnal pembalik (reversal) secara utuh. Transaksi **tidak dapat diedit sebagian** — jika hanya sebagian barang yang bermasalah, buat transaksi penjualan baru (return sebagian) sebagai gantinya.