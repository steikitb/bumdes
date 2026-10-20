# Pencatatan: Pembelian Persediaan (Stok)

Menu ini digunakan untuk mencatat transaksi pembelian barang (baik Barang Dagangan maupun Perlengkapan ATK) dari pemasok (supplier) untuk menambah stok fisik unit usaha.

## Harga Beli Menggunakan Sistem Moving Average (Rata-rata Tertimbang)

Sistem ini dirancang menggunakan metode *Moving Average* sesuai standar SAK EP. 
Ini berarti Anda **tidak perlu khawatir jika harga beli barang dari supplier naik atau turun**.

* Saat Anda mengisi form pembelian, sistem akan memberikan rekomendasi (saran) harga dasar. Anda bebas mengubahnya sesuai faktur riil yang Anda bayar hari itu.
* Setelah disimpan, sistem akan secara otomatis mencampur (merata-ratakan) nilai stok lama dengan nilai stok baru di gudang Anda untuk mendapatkan Harga Pokok (HPP) yang baru.

## Mencatat Transaksi Pembelian Baru

1. Dari menu **Pencatatan Transaksi**, pilih **Pembelian Persediaan (Stok)**.
2. Klik tombol **"Catat Pembelian Baru"**.
3. **Lengkapi Informasi Faktur Utama:**
    * **Tanggal Pembelian:** Tanggal sesuai faktur dari pemasok.
    * **Pilih Pemasok:** Cari dan pilih nama pemasok. Jika pemasok belum ada, Anda bisa menambahkannya secara instan dengan menekan tombol **"+"** di sebelahnya.
    * **Nomor Faktur Pemasok:** Masukkan nomor unik faktur dari supplier (Contoh: INV-992/A).
    * **Metode Pembayaran:** 
        * **Tunai:** Membayar pakai uang kas fisik.
        * **Transfer Bank / QRIS:** Akan muncul pilihan **Pilih Rekening Sumber Dana**. Tentukan dari bank mana Anda mentransfer uang ke supplier tersebut agar sinkron dengan Buku Bank Anda.
        * **Kredit (Utang):** Akan muncul pilihan **Tanggal Jatuh Tempo Tagihan**. Sistem akan membuat notifikasi peringatan di halaman Dasbor Utama jika utang ini sudah mendekati waktu jatuh tempo.
4. **Tambah Daftar Barang:**
    * **Scan barcode** pada kotak **Scan Barcode (Terima Barang)** di atas form. Barang langsung masuk daftar dengan qty 1 dan harga beli dari master; fokus kembali ke kotak scan. Tombol **F3** memfokuskan ulang kotak scan kapan saja.
    * Atau pilih barang manual dari daftar **Pilih Barang dari Master**.
    * Sesuaikan **Harga Beli Satuan** bila harga faktur berbeda dari master.
    * Isi **Jumlah Qty** sebelum menekan **"Tambahkan ke Daftar Pembelian"** bila tidak memakai scan. Ulangi untuk barang lain di faktur yang sama.
    * Jika barcode belum dikenali, sistem menampilkan peringatan; tambahkan barcode lewat menu **Data Master Entitas -> Barang & Stok -> Edit**.
5. **Simpan Transaksi:**
    * Pastikan total nominal sesuai dengan kuitansi/faktur fisik.
    * Klik **"Simpan Transaksi Pembelian"**.

## Melunasi Utang Pembelian (Jika Kredit)

Jika sebelumnya Anda membeli secara kredit/utang, Anda harus mencatat pelunasannya saat mentransfer sisa uang ke supplier.

1. Buka menu **Laporan Keuangan** > **Buku Pembantu Utang Dagang**.
2. Cari nama pemasok tersebut. Sistem akan menampilkan total sisa utang Anda ke pemasok itu.
3. Klik tombol **"Bayar / Lunasi Utang"** berwarna hijau.
4. Masukkan tanggal bayar dan nominal yang Anda transfer.

### Yang perlu diperhatikan
- Nominal pelunasan **tidak boleh melebihi sisa utang**. Kelebihan nominal akan ditolak.
- Nominal **nol** juga ditolak. Gunakan nominal yang lebih kecil dari utang untuk membayar sebagian.
- Pembayaran yang sudah tercatat tidak bisa dihapus. Jika salah catat, catat pembatalan lewat menu **Jurnal Penyesuaian**.

## Membatalkan Pembelian (Retur Pembelian)

Jika seluruh isi satu faktur pembelian dikembalikan ke pemasok, batalkan faktur tersebut.

1. Buka menu **Pencatatan Transaksi** > **Pembelian Persediaan (Stok)**.
2. Pada baris faktur yang dibatalkan, klik ikon titik tiga, lalu pilih **"Retur / Batalkan Pembelian"**.
3. Masukkan alasan pembatalan, lalu konfirmasi.

### Yang perlu diperhatikan
- Tindakan ini mengembalikan seluruh stok faktur tersebut ke gudang dan menghapus sisa utangnya. Jika faktur dibayar sebagian, otomatis mengurangi sisa utang.
- Faktur yang sudah dibatalkan **tidak muncul lagi** di daftar pembelian, dan tidak bisa dibatalkan dua kali. Riwayatnya tetap tersimpan di riwayat aktivitas.
- Retur hanya untuk pembatalan **seluruh faktur**. Untuk mengembalikan sebagian barang saja, catat penyesuaian lewat menu **Hitung Ulang Stok (Opname)**.
- Pembelian pada tahun buku yang sudah ditutup tidak bisa dibatalkan. Buka tahun bukunya lebih dulu di menu **Tutup Buku**.