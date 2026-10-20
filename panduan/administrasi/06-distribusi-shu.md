# Administrasi: Eksekusi Bagi Hasil / Distribusi SHU

Setelah Anda berhasil melakukan proses [Tutup Buku Tahunan](administrasi/05-tutup-buku.md), dana laba bersih sudah dialokasikan ke dalam akun utang bagi hasil.

Nama menu untuk modul ini berubah mengikuti **Mode Entitas** (lihat [Tentang Aplikasi](umum/03-tentang-aplikasi.md)):

| Mode Entitas | Nama Menu |
| --- | --- |
| **BUMDes** | **Laba Bersih / Bagi Hasil** |
| **Koperasi** | **Distribusi SHU** |

Menu ini berada di grup **Administrasi & Pengaturan**, tepat di bawah item Tutup Buku.

> **Nama tombol pada halaman ini juga menyesuaikan mode:** Mode BUMDes memakai istilah "PADes", mode Koperasi memakai istilah "SHU".

## Langkah 1: Buka Menu Distribusi

1. Buka **Administrasi & Pengaturan -> Laba Bersih / Bagi Hasil** (BUMDes) atau **-> Distribusi SHU** (Koperasi).
2. Pilih **Tahun Buku** yang akan diproses pada daftar pilihan, lalu klik **"Pilih Tahun"**.
3. Setelah tahun dipilih, sistem menampilkan ringkasan alokasi laba bersih berdasarkan persentase yang Anda masukkan saat Tutup Buku.

## A. Mode BUMDes — Penyetoran PADes ke RKD

Dana yang menjadi hak Pemerintah Desa dikenal sebagai **Pendapatan Asli Desa (PADes)**.

1. Klik tombol **"Buka Menu Penyetoran Kas PADes"** untuk masuk ke daftar status penyetoran.
2. Sistem menampilkan **Status Penyetoran PADes Tahun** beserta nominal yang masih terutang.
3. **Lakukan transfer uang secara riil** dari rekening BUMDes ke **Rekening Kas Desa (RKD)**.
4. Setelah transfer sukses, kembali ke sistem dan klik tombol **"Catat Setor PADes ke RKD"**.
5. Isi tanggal bukti transfer bank pada form, lalu klik **"Proses Penyetoran"**.
6. Sistem akan menjurnal pelunasan utang tersebut dan mengurangi saldo rekening bank BUMDes.

## B. Mode Koperasi — Distribusi Individu

Dana "Jasa Anggota" dipecah dan dibagikan otomatis ke ratusan/ribuan anggota.

### B1. Simulasi & Kalkulasi
1. Di halaman Distribusi, isi **Parameter Simulasi**: rentang tanggal dan persentase AD/ART (persis seperti saat Tutup Buku).
2. Klik **"Jalankan Simulasi"**.
3. Sistem mengumpulkan Poin Jasa Modal dan Poin Jasa Usaha seluruh anggota, lalu menampilkan:
   * **Ringkasan Alokasi Berdasarkan Persentase**
   * **Kalkulasi Nilai Poin Konversi**
   * **Detail Perkiraan Penerimaan per Pihak** (dalam Rupiah)
4. Gunakan tombol **"Cetak Simulasi"** untuk menyimpan hasil sebagai dokumen.

### B2. Kunci Distribusi
1. Klik **"Lakukan Distribusi Baru"** untuk mengunci hitungan ini. Data akan tersimpan permanen di histori.
2. Tombol **"Lihat Hasil"** akan muncul beserta link ke halaman pencairan.

### B3. Pencairan Massal (Transfer ke Tabungan)
SHU tidak mengurangi uang tunai Koperasi, melainkan dipindahkan ke tabungan anggota.
1. Di halaman **Laporan Pembagian Tahun**, klik **"Cairkan Secara Massal"**.
2. Pilih tanggal pencairan.
3. Klik **"Proses Pencairan Massal"**. Sistem akan mendebit "Utang Bagi Hasil (Jasa/PADes)" menjadi Rp 0 dan **mengkreditkannya ke saldo "Simpanan Sukarela"** masing-masing anggota.
4. Anggota bebas datang kapan saja untuk menarik saldo tabungan sukarela tersebut.
