# Administrasi: Setup Awal & Migrasi Saldo Awal

Menu ini digunakan khusus **satu kali saja**, yaitu saat Anda pertama kali menggunakan sistem ini (cut-off pembukuan) atau saat mencatat penyertaan modal awal pendirian entitas (seperti BUMDes).

Fungsi utamanya adalah memindahkan angka saldo akhir dari pembukuan lama (Excel/Manual) ke dalam sistem digital ini, sehingga neraca memiliki nilai pembuka.

## Aturan Penting

1. **Format Wizard 3 Tahap:** Halaman ini tersusun dalam 3 tahap berurutan. Anda harus menyelesaikan tahap 1 sebelum bisa pindah ke tahap 2.
2. **Persamaan Dasar Akuntansi:** Total nilai pada kolom Debit **WAJIB SAMA** dengan total nilai pada kolom Kredit. Jika tidak seimbang, tombol simpan akan ditolak.
3. **Satu Jurnal Saja:** Proses ini hanya menghasilkan satu "Jurnal Saldo Awal" (dengan nomor bukti "Saldo Awal"). Jika Anda mengulang proses migrasi, jurnal saldo awal yang lama akan ditapus dan digantikan dengan yang baru.

## Langkah-langkah

### Tahap 1: Evaluasi Data Master

Sistem mencoba menghitung estimasi saldo awal secara otomatis dari master data yang sudah Anda isi.

1. Buka menu **Administrasi & Pengaturan -> Setup Awal & Migrasi Saldo**.
2. Pada tab **"Tahap 1: Evaluasi Data Master"**, periksa estimasi yang dihitung sistem untuk masing-masing pos:
   * **Persediaan & Barang** — estimasi nilai stok dari kartu stok / master barang.
   * **Aset Tetap Inventaris** — daftar aset tetap beserta akumulasi penyusutan lampau.
     > **Penting:** Nilai penyusutan lampau (akumulasi) **tidak** dihitung otomatis dan wajib Anda isi sendiri pada Tahap 2.
   * **Penyertaan Modal** — estimasi dari simpanan pokok/wajib.
3. Klik **"Lanjutkan ke Tahap 2"**.

### Tahap 2: Susun Neraca Saldo Awal

1. Klik tombol **"Tarik Nilai Master Data ke Form"** untuk menyalin angka estimasi dari Tahap 1 ke tabel Neraca secara otomatis.
2. Lengkapi angka yang belum ada, terutama pos-pos kasir yang tidak dihitung sistem:
   * **Uang Kas Tunai** (uang di brankas)
   * **Saldo Bank** (rekening giro)
   * **Utang / Hutang Usaha Masa Lalu**
   * **Akumulasi Penyusutan Aset Tetap**
3. Tips pengisian kolom:
   * *Kas, Bank, Piutang, Persediaan, dan Aset Tetap → kolom **Debit**.*
   * *Utang dan Modal (Ekuitas) → kolom **Kredit**.*
   * Badge kecil di samping nama akun menunjukkan **Saldo Normal** akun tersebut sebagai petunjuk.
4. Perhatikan tiga indicator di bagian bawah layar: **Total Debit**, **Total Kredit**, dan **Selisih (Balance)**. Selisih harus **Rp 0**.

### Langkah Final

1. Setelah seimbang, klik **"Simpan Ke Sistem"**.
2. Kembali ke menu utama untuk memverifikasi hasilnya di **Laporan Keuangan -> Laporan Posisi Keuangan (Neraca)**.
