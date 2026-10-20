# Administrasi: Jurnal Penyesuaian Akhir Bulan

Menu ini berisi alat bantu untuk membuat jurnal penyesuaian pada akhir periode akuntansi. Jurnal ini penting untuk menyesuaikan saldo beberapa akun agar sesuai dengan kondisi sebenarnya (prinsip pencatatan pendapatan dan beban sesuai periode berjalan).

Nama menu: **Administrasi & Pengaturan -> Jurnal Penyesuaian Akhir Bulan**.

## Pencatatan Otomatis

Sistem menyediakan 4 fitur jurnal penyesuaian otomatis:

### 1. Penyusutan & Amortisasi Otomatis
Menghitung beban penyusutan untuk **semua aset tetap** (dan beban amortisasi aset tak berwujud) yang tercatat pada bulan tersebut.
1. Pilih periode bulan yang diinginkan.
2. Klik **"Buat Jurnal Penyusutan"**.

### 2. Penyesuaian Pemakaian Perlengkapan
Perlengkapan yang dibeli dicatat sebagai **Aset**. Di akhir bulan Anda memasukkan **sisa fisik persediaan ATK**, lalu sistem menjurnal selisihnya sebagai **Beban Pemakaian** sehingga beban dan persediaan selalu sinkron dengan kenyataan.
1. Isi **Tanggal Jurnal**.
2. Isi **Sisa Fisik (Rp)** — nilai persediaan perlengkapan yang masih ada di gudang pada tanggal tersebut. Bandingkan dengan "Saldo Perlengkapan di Sistem" yang ditampilkan di atas form.
3. Klik **"Catat Beban Pemakaian ATK"**.
   *(Tombol nonaktif jika saldo perlengkapan di sistem sudah 0.)*

### 3. Penyisihan Kerugian Piutang
Melakukan analisis umur piutang (analisis umur piutang) pada kredit anggota yang macet, lalu membuat jurnal cadangan kerugian sesuai kebijakan BUMDes pada menu **Pemetaan Akun Otomatis**.
1. Isi **Tanggal Tutup Buku / Jurnal** (terisi otomatis: 31 Desember tahun sebelumnya).
2. Klik **"Buat Jurnal Penyisihan"**.

### 4. Bunga Simpanan Sukarela
Menghitung porsi bunga bulanan untuk tiap anggota berdasarkan saldo tabungannya, lalu mendistribusikan penambahan saldo tersebut secara otomatis.
1. Pilih periode bulan dan persentase bunga tahunan (%).
2. Klik **"Bagikan Bunga"**.

## Jurnal Manual

Untuk penyesuaian lain yang tidak dicakup fitur otomatis:

* Beban dibayar di muka (misalnya sewa dibayar untuk satu tahun).
* Pendapatan diterima di muka.
* Beban yang masih harus dibayar (beban yang sudah terjadi tetapi belum dibayar).

**Cara membuat jurnal manual:**
1. Klik **"Tambah Baris"** untuk menambah baris entri jurnal.
2. Isi **Tanggal Catatan** dan **Keterangan Umum**.
3. Pada setiap baris, pilih **Akun** lalu isi kolom **Debit** atau **Kredit**.
4. Pastikan **Total Debit sama dengan Total Kredit**.
5. Klik **"Simpan Jurnal Manual"**.

## Catatan Penting

* Sistem **tidak** menyusutkan aset secara otomatis setiap hari. Anda wajib menjalankan fitur ini secara berkala — bulanan, atau sekaligus satu tahun penuh di akhir tahun (tombol **"Penyesuaian Akhir Bulan/Tahun"** juga tersedia di halaman Tutup Buku).
* Jika Anda membuka halaman **Tutup Buku** tanpa jurnal penyusutan untuk tahun yang ditutup, sistem akan menampilkan peringatan **"Belum Ada Jurnal Penyusutan!"**.
