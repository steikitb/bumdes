# Administrasi: Pencatatan Pajak (PPh Badan)

Menu ini digunakan untuk mencatat **Beban Pajak Penghasilan (PPh) Badan** yang harus dibayarkan oleh entitas. Pencatatan ini biasanya dilakukan di akhir tahun buku setelah Sisa Hasil Usaha (SHU) / Laba Bersih sebelum pajak diketahui.

Nama menu: **Administrasi & Pengaturan -> Pencatatan Pajak (PPh Badan)**.

## Bagian 1: Pengakuan Beban Pajak (Akhir Tahun)

Bagian ini membuat jurnal pengakuan beban pajak sekaligus kewajiban utang pajak.

1. Isi formulir:
   * **Tanggal Jurnal** — biasanya tanggal akhir tahun buku (misal 31 Desember).
   * **Jumlah Beban Pajak (Rp)** — total PPh Badan yang terutang untuk tahun tersebut.
   * **Keterangan** — deskripsi yang jelas, contoh: "Beban PPh Badan Tahun Buku 2025".
2. Klik **"Simpan Beban Pajak"**.

Jurnal yang terbentuk:

| Akun | Debit | Kredit |
| --- | --- | --- |
| Beban Pajak Penghasilan | Rp (nilai pajak) | — |
| Utang Pajak Penghasilan | — | Rp (nilai pajak) |

Dengan begitu, SHU final yang dilaporkan sudah merupakan SHU **setelah pajak**.

## Bagian 2: Pembayaran Pajak (Pelunasan)

Bagian ini mencatat pelunasan utang pajak ketika Anda benar-benar menyetor PPh ke kas negara.

1. Perhatikan panel **"Saldo Utang Pajak Saat Ini"** di atas form — ini adalah tagihan yang masih terutang.
2. Lakukan pembayaran PPh secara riil ke kas negara lebih dulu (melalui Bank/Kantor Pos).
3. Isi formulir:
   * **Tanggal Setor**
   * **Jumlah Disetor (Rp)**
   * **Nomor Bukti Penerimaan Negara (NTPN)** — nomor bukti resmi dari negara, berguna saat rekonsiliasi dengan bagian pajak.
4. Klik **"Catat Penyetoran Pajak"**. Sistem akan mengurangi saldo Utang Pajak sekaligus mengurangi saldo Kas/Bank.
   *(Tombol nonaktif jika saldo utang pajak sudah 0.)*

## Tips

* **Pastikan jurnal penyesuaian lain sudah lengkap** sebelum mencatat pajak, agar angka SHU yang menjadi dasar perhitungan sudah final.
* Jangan sampai Bagian 1 tercatat tapi Bagian 2 terlewat — utang pajak akan menggantung di neraca saat menyusun laporan.
