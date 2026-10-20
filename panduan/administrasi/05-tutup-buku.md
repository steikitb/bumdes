# Administrasi: Tutup Buku Tahunan

Proses Tutup Buku (Closing) adalah proses akuntansi paling krusial yang **wajib** dilakukan satu kali di akhir setiap tahun buku (biasanya tanggal 31 Desember).

## Nama Menu (Bergantung Mode Entitas)

| Mode Entitas | Nama Menu |
| --- | --- |
| **BUMDes** | **Tutup Buku / Hitung Laba** |
| **Koperasi** | **Tutup Buku Akhir Tahun** |

Lokasi: **Administrasi & Pengaturan**, di atas item *Laba Bersih / Bagi Hasil* (atau *Distribusi SHU*).

Istilah di halaman ini juga menyesuaikan mode: "Laba Bersih" untuk BUMDes, "Sisa Hasil Usaha (SHU)" untuk Koperasi, dan pos hak pemilik modal dinamai "Bagi Hasil (PADes & Penyerta)" untuk BUMDes atau "Jasa Anggota" untuk Koperasi.

## Apa Tujuan Tutup Buku?

Secara akuntansi, sistem melakukan tiga hal otomatis:
1. **Menolkan (Reset) Saldo Pendapatan & Beban** — Memindahkan semua saldo akun nominal ke akun "Ikhtisar Laba Rugi", sehingga di tanggal 1 Januari tahun berikutnya omset dan biaya mulai dari Rp 0 lagi.
2. **Menghitung Laba Bersih Final** — Selisihnya dipindahkan ke akun Ekuitas "SHU / Laba Belum Dibagi".
3. **Mengalokasikan Laba Bersih** — Laba bersih dipecah dan dijurnal menjadi Utang/Cadangan sesuai persentase hasil Musdes/RAT yang Anda masukkan.

## Langkah-langkah

1. Pastikan seluruh jurnal penyesuaian akhir tahun (penyusutan aset, penyisihan piutang, pajak PPh) sudah selesai dicatat.
2. Dari menu **Administrasi & Pengaturan**, pilih **Tutup Buku / Hitung Laba** (BUMDes) atau **Tutup Buku Akhir Tahun** (Koperasi).
3. Pilih **Tahun Buku** yang akan ditutup pada daftar pilihan, lalu klik **"Hitung Laba Bersih"** / **"Hitung SHU"**.
   > Tombol **"Lihat Histori"** tersedia di halaman yang sama untuk melihat dan membatalkan tutup buku sebelumnya.
4. **Periksa peringatan sistem.** Jika muncul kotak kuning *"Peringatan: Belum Ada Jurnal Penyusutan!"*, berarti belum ada beban penyusutan untuk tahun tersebut. **Tutup bukulah yang akan menghasilkan angka laba yang tidak akurat.** Kembali ke menu Jurnal Penyesuaian Akhir Bulan dan jalankan penyusutan terlebih dahulu.
5. Sistem menampilkan angka **Total SHU / Laba Bersih** final.

## Cara Mengisi Persentase Alokasi

Ada **4 kolom persentase**, totalnya **wajib 100%**. Nilai bawaan sistem adalah 25% / 40% / 10% / 25%.

| Kolom di Layar | Berlaku Untuk | Keterangan |
| --- | --- | --- |
| **Persen Cadangan (%)** | Sama di kedua mode | Masuk ke Modal Cadangan untuk perkuatan modal. |
| **Persen Jasa Anggota (%)** (Koperasi) / **Persen Bagi Hasil (PADes & Penyerta) (%)** (BUMDes) | Bervariasi | Hak pemilik modal utama. Mode BUMDes: bagian ini adalah **PADes** yang wajib disetorkan ke Rekening Kas Desa (RKD). Mode Koperasi: bagian yang akan dibagikan per individu berdasarkan poin. |
| **Persen Dana Pengurus (%)** | Sama di kedua mode | Tantiem / bonus akhir tahun pengelola. |
| **Persen Dana Sosial (%)** | Sama di kedua mode | CSR atau bantuan masyarakat sekitar. |

6. Sesuaikan angka persentase dengan dokumen Notulen Musyawarah atau RAT Anda, lalu klik **"Proses Tutup Buku"**. Sistem akan meminta konfirmasi.
7. Jika tahun tersebut sudah pernah ditutup, halaman akan menampilkan peringatan dan form alokasi **tidak bisa** diisi lagi.

## Membatalkan Tutup Buku

1. Klik **"Lihat Histori"** di halaman Tutup Buku.
2. Klik tombol **"Batalkan"** pada baris tahun yang ingin dibatalkan.
3. Sistem akan memblokir pembatalan jika dana Bagi Hasil PADes (atau SHU Koperasi) sudah telanjur dicairkan/disetorkan pada tahap selanjutnya. Batalkan pencairan tersebut lebih dulu di menu [Pembagian Keuntungan Anggota](administrasi/06-distribusi-shu.md).
