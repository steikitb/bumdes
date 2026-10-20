# Administrasi: Pemetaan Akun & Kebijakan Sistem

Menu ini **hanya dapat diakses oleh Admin**. Halaman ini sangat krusial karena berisi aturan-aturan bisnis yang dipakai sistem untuk menjurnal transaksi secara otomatis dan menghitung beberapa angka secara otomatis.

Nama menu di layar: **Administrasi & Pengaturan -> Pemetaan Akun Otomatis**.

Halaman ini terdiri dari tiga bagian.

## 1. Kebijakan Penyisihan Piutang (Analisis Umur)

Menentukan persentase taksiran kerugian piutang berdasarkan lamanya pinjaman belum lunas.

| Kolom | Isi |
| --- | --- |
| Lancar (0 - X hari) | batas umur dan persentase penyisihan |
| Kurang Lancar (X - Y hari) | batas umur dan persentase penyisihan |
| Diragukan (Y - Z hari) | batas umur dan persentase penyisihan |
| Macet (> Z hari) | persentase penyisihan |

Persentase ini dipakai saat membuat jurnal **Penyisihan Kerugian Piutang** di akhir tahun, dan saat menghitung beban di Laporan Laba Rugi.

> **Mode BUMDes:** persentase penyisihan mengikuti aturan baku Kepmendesa 136/2022 dan angka yang Anda isi di sini akan diabaikan oleh sistem. Pengaturan ini baru berpengaruh pada **Mode Koperasi**.

## 2. Kebijakan Skor Kredit Anggota

> **Hanya muncul pada Mode Koperasi.** Bagian ini mengatur bagaimana sistem menilai kelayakan kredit anggota di menu **Pencatatan Transaksi -> Piutang Simpan Pinjam**. Pada Mode BUMDes bagian ini tidak ditampilkan karena penilaian kelayakan kredit tidak dipakai pada mode tersebut.

Skor dihitung dari empat sumber, lalu dijumlahkan:

| Sumber Poin | Keterangan |
| --- | --- |
| **Poin per Pembayaran Angsuran** | Diberikan setiap kali angsuran anggota tercatat. |
| **Poin per Tahun Keanggotaan** | Diberikan untuk setiap 1 tahun anggota sudah bergabung. |
| **Poin jika Aktif Belanja** | Diberikan bila anggota ada transaksi belanja dalam 3 bulan terakhir. |
| **Poin dari Rasio Simpanan ÷ Sisa Pinjaman** | Dihitung dari perbandingan total simpanannya dengan sisa pinjamannya. |

### Cara Rasio Ditentukan

Rasio = total simpanan anggota (pokok, wajib, dan tabungan) dibagi sisa pinjamannya.

| Kondisi Rasio | Poin yang diberikan |
| --- | --- |
| Di atas **Batas Rasio Atas** | **Poin untuk Rasio di Atas Batas Atas** |
| Di atas **Batas Rasio Bawah** | **Poin untuk Rasio di Atas Batas Bawah** |
| Di bawah Batas Rasio Bawah | **Poin untuk Rasio di Bawah Batas Bawah** |

### Ambang Batas Rekomendasi

| Nilai skor | Rekomendasi sistem |
| --- | --- |
| Di atas **Ambang Batas "Baik"** | "Baik" — pengajuan tetap perlu diverifikasi staf |
| Di atas **Ambang Batas "Sangat Baik"** | "Sangat Baik" — pengajuan otomatis disetujui |
| Di bawah kedua ambang | "Perlu Tinjauan Lanjut" |

Sistem memakai skor ini sebagai **rekomendasi**, bukan persetujuan otomatis. Anda tetap hak menolak.

> **Jika kedua ambang diisi `0`,** tidak ada anggota yang otomatis lolos — setiap pengajuan perlu Anda periksa satu per satu. Isilah kedua ambang sesuai aturan koperasi Anda.

## 3. Pemetaan Akun Otomatis

Bagian ini menghubungkan **setiap jenis transaksi otomatis** dengan Kode Akun yang sesuai di Daftar Akun. Inilah yang membuat penjualan, pembelian, beban, gaji, pajak, dan tutup buku bisa terjurnal tanpa Anda perlu memilih akun satu per satu.

Ada 40 pemetaan, dikelompokkan menjadi:
* **Aset** — kas, bank, piutang usaha, piutang pinjaman, persediaan, aset tetap
* **Liabilitas** — utang usaha, utang pajak, tabungan masyarakat, utang dana pengurus, utang dana sosial, utang bank
* **Ekuitas** — simpanan pokok, simpanan wajib, cadangan, SHU belum dibagi, ikhtisar laba rugi
* **Pendapatan** — penjualan barang, potongan penjualan, bunga pinjaman, hibah
* **Beban** — HPP, gaji, penyusutan, penyisihan piutang, pajak, penyesuaian stok

> **Penting:** Pastikan setiap pemetaan terisi dengan benar sesuai Daftar Akun entitas Anda. Kesalahan di sini akan menjadi kesalahan pencatatan di seluruh laporan keuangan.

## Menyesuaikan Istilah Akun dengan Mode Entitas

Daftar Akun bawaan mengikuti standar **Kepmendesa 136/2022**, sehingga beberapa nama akun berbau BUMDes. Bila memakai **Mode Koperasi**, sebaiknya Anda ganti nama beberapa akun berikut lewat tombol **"Edit"** di menu **Master Bagan Akun (COA)**:

| Kode | Nama Bawaan | Nama yang Cocok untuk Koperasi |
| --- | --- | --- |
| `3.1.01.01` | Penyertaan Modal Desa | Simpanan Pokok / Modal Awal |
| `3.1.02.01` | Penyertaan Modal Masyarakat | Simpanan Wajib Anggota |
| `2.1.02.04` | PPh 29 | Utang Pajak Penghasilan |

> **Jangan lupa cek juga** pemetaan **Beban Pajak Penghasilan (PPh Badan)** di halaman ini. Pastikan menunjuk ke akun pajak **badan usaha** (umumnya `7.3.01.06`), bukan akun pajak orang pribadi.
