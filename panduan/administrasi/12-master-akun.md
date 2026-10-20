# Administrasi: Master Bagan Akun (COA)

Daftar Akun atau *Chart of Accounts (COA)* adalah jantung dari seluruh proses akuntansi. Menu ini digunakan untuk mengelola daftar kode akun yang digunakan dalam pembuatan jurnal umum. 

Saat aplikasi pertama kali dijalankan, sistem sudah menyiapkan puluhan daftar akun standar yang sesuai dengan Kepmendesa 136 Tahun 2022. Anda bebas menambah atau mengubah akun ini sesuai kebutuhan spesifik Anda.

## Menambah Akun Baru Secara Manual

1.  Dari menu **Administrasi & Pengaturan**, pilih **Master Bagan Akun (COA)**.
2.  Klik tombol **"Tambah Akun Baru"**.
3.  Isi detail akun:
    *   **Kode Akun:** Berikan kode unik (Contoh: 1.1.01.03). Untuk BUMDes disarankan mengikuti format 4 level.
    *   **Nama Akun:** Nama yang jelas (Contoh: Kas di Bank BRI).
    *   **Kelompok Akun:** Pilih apakah akun tersebut masuk kelompok Aset, Liabilitas, Ekuitas, Pendapatan, atau Beban.
    *   **Unit Usaha:** Pilih "Konsolidasi" agar akun ini tersedia untuk semua aktivitas.
    *   **Saldo Normal:** Tentukan apakah sifat dasar akun ini bersaldo Debit atau Kredit.
4.  Klik **"Simpan Akun"**.

## Mengimpor Akun Secara Massal (CSV)

Jika Anda memiliki daftar akun yang banyak (misal dari sistem lama), Anda dapat mengimpornya sekaligus menggunakan file Excel/CSV.

1.  Di halaman daftar akun, klik tombol **"Import CSV"**.
2.  Siapkan file spreadsheet yang memiliki **2 kolom**:
    *   **Kolom 1:** Kode Akun diikuti spasi lalu Nama Akun (Contoh: 1.1.01.03 Kas di Bank BSI).
    *   **Kolom 2:** Saldo Normal akun (Ketik **Debit** atau **Kredit**).
3.  Simpan file Excel Anda dengan format **.csv** (Comma Separated Values).
4.  Pilih file tersebut di aplikasi, centang kotak jika file memiliki baris judul (header), lalu klik **"Mulai Import"**.
5.  Sistem akan otomatis mengenali dan memasukkan akun-akun baru tersebut, asalkan kode akunnya belum ada di dalam sistem.