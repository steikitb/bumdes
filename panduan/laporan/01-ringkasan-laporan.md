# Laporan Keuangan

Menu **Laporan Keuangan** adalah output utama dari semua pencatatan yang telah Anda lakukan. Sistem dapat menghasilkan laporan keuangan standar secara otomatis dan real-time.

## Laporan Keuangan Inti

| Menu | Istilah Lain | Isi |
| --- | --- | --- |
| **Cetak Laporan Lengkap (PDF)** | Ekspor Audit | Semua laporan inti digabung dalam 1 dokumen PDF. Lihat [Ekspor Laporan Audit Lengkap](laporan/14-ekspor-audit.md). |
| **Laporan Laba Rugi** | Perhitungan Hasil Usaha | Pendapatan, HPP, dan Beban → SHU/Laba Bersih. |
| **Laporan Posisi Keuangan (Neraca)** | Laporan Kekayaan | Aset = Liabilitas + Ekuitas pada satu tanggal. |
| **Laporan Perubahan Ekuitas** | Perubahan Modal | Pergerakan ekuitas dari awal sampai akhir periode. |
| **Laporan Arus Kas** | — | Arus kas dikelompokkan Operasi, Investasi, Pendanaan. |
| **Buku Kas Umum (BKU)** | — | Rekapan kronologis penerimaan & pengeluaran Kas/Bank. |
| **Penilaian Tingkat Kesehatan** | — | Skor kesehatan BUMDes dari 8 rasio keuangan. |
| **Buku Jurnal Umum** | Rincian Jurnal | Rincian debit/kredit setiap transaksi. |
| **Buku Pembantu Utang Dagang** | Utang Usaha | Saldo utang per pemasok + fitur pelunasan. |
| **Analisis Umur Piutang** | Analisis Umur Piutang | Pengelompokan sisa piutang berdasarkan umur. |
| **Laporan Kebutuhan Stok** | Daftar Belanja | Barang dengan stok di bawah minimum. |
| **Laporan Barang Terlaris** | — | Peringkat barang berdasarkan unit terjual. |
| **Catatan atas Laporan Keuangan (CaLK)** | CaLK | Penjelasan naratif + rincian angka laporan. |
| **Pengembalian Dana Anggota Keluar** | — | Pengembalian simpanan pokok anggota yang keluar. |

## Struktur Laporan Utama

### 1. Laba Rugi / Hasil Usaha
Menampilkan kinerja selama periode tertentu. Komponennya berurutan: **Pendapatan Usaha** → dikurangi **HPP** (menghasilkan Laba Kotor) → dikurangi **Beban Usaha** (menghasilkan Laba Operasi) → ditambah/dikurangi **Pendapatan & Beban Lain-lain** (menghasilkan Laba Sebelum Pajak) → dikurangi **Beban Pajak PPh** (menghasilkan **Laba Bersih / SHU**).

### 2. Neraca (Posisi Keuangan)
Menjawab: "Apa saja yang dimiliki, apa saja utangnya, dan berapa modalnya pada tanggal ini?" Selalu seimbang: **Total Aset = Total Liabilitas + Total Ekuitas**.

### 3. Perubahan Ekuitas
Menjawab: "Mengapa modal awal periode berbeda dari modal akhir periode?"

### 4. Arus Kas
Menjawab: "Dari mana kas berasal dan untuk apa kas digunakan?" Tiga aktivitas: **Operasi**, **Investasi**, **Pendanaan**.

## Filter Unit Usaha

Tiga laporan (Laba Rugi dan Neraca) mendukung filter **Unit Usaha**:

* **Konsolidasi (Gabungan)** — Semua unit digabung. Ini yang dipakai untuk laporan standar.
* **Unit Toko / Waserda** — Hanya pendapatan penjualan dan beban operasional unit toko.
* **Unit Simpan Pinjam (USP)** — Hanya pendapatan bunga pinjaman dan beban administrasi kredit.

Berguna bila entitas Anda diwajibkan menyajikan laporan terpisah per unit.

## Cara Menggunakan

1. Pilih jenis laporan dari grup **Laporan Keuangan**.
2. Tentukan periode laporan:
   * Laporan **berkala** (Laba Rugi, Arus Kas, Perubahan Ekuitas, Barang Terlaris, Ekspor Audit) memakai **Tanggal Mulai** dan **Tanggal Selesai**.
   * Laporan **posisi** (Neraca, Umur Piutang, CaLK) memakai satu **Tanggal Laporan**.
3. Klik tombol untuk menampilkan laporan:
   * **"Tampilkan Laporan"** — pada sebagian besar laporan.
   * **"Tampilkan"** — pada Buku Pembantu Utang Dagang.
   * **"Tampilkan BKU"** — pada Buku Kas Umum.
   * **"Hitung & Evaluasi"** — pada Penilaian Tingkat Kesehatan.
4. Ekspor hasil dengan tombol **"Cetak"** / **"Cetak PDF / Printer"** dan opsional **"Ekspor CSV"**.

> **Seluruh halaman laporan di grup ini menyediakan tombol "Ekspor CSV"** sehingga angka dapat diolah lebih lanjut di Excel.
