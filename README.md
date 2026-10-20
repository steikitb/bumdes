# Sistem Akuntansi Koperasi / BUMDes

Aplikasi akuntansi dan administrasi untuk koperasi maupun BUMDes, dibangun dengan PHP dan SQLite. Bisa dipasang sebagai aplikasi web di hosting maupun dijalankan sebagai aplikasi desktop offline lewat Electron.

## Fitur

| Modul | Cakupan |
|---|---|
| Kas & Bank | Buku kas umum, arus kas, transaksi tunai/bank |
| Penjualan | Faktur, pelanggan umum & anggota, penjualan kredit, retur, cetak struk |
| Pembelian | Faktur pemasok, pembelian kredit, pembayaran utang, retur |
| Persediaan | Produk, barcode, konversi satuan, stok minimum, barang terlaris |
| Keanggotaan | Anggota/penyerta modal, simpanan pokok & wajib, simpanan sukarela |
| Pinjaman | Pinjaman anggota & angsuran, pinjaman diterima, skor kredit |
| Aset Tetap | Perolehan, penyusutan bulanan, pelepasan aset |
| Beban & Pendapatan | Kategori beban, anggaran, pendapatan lain |
| Hibah | Hibah pemerintah, syarat pencairan, pengakuan |
| Akuntansi | Bagan akun (COA), jurnal umum, jurnal penyesuaian, tutup buku |
| Laporan | Posisi keuangan, perubahan ekuitas, SHU, arus kas, buku kas, umur piutang/utang, CALK, ekspor audit |
| Administrasi | Surat masuk/keluar, buku tamu, rapat, catatan pengawas, saran anggota |
| SDM | Jabatan, karyawan, penggajian |
| Pengguna | Multi-role/RBAC, log aktivitas, kebijakan kata sandi |

Dua mode entitas tersedia: **Koperasi** dan **BUMDes** (istilah serta pemetaan akun mengikuti Kepmendesa PDTT 136/2022 dan SAK EP).

## Teknologi

- PHP 8.2+ dengan PDO SQLite, tanpa framework
- SQLite sebagai basis data tunggal
- Tailwind CSS + DaisyUI (CDN), Alpine.js, Tom Select
- Composer: PHPMailer, Parsedown
- Electron 28 + electron-builder untuk mode desktop
- Routing terpusat (`publik/index.php`, `proses.php`, `ajax.php`) dengan rewrite `.htaccess`

## Struktur

```
.
├── publik/                  # document root web
│   ├── index.php            # router halaman
│   ├── proses.php           # router aksi POST
│   ├── ajax.php             # router endpoint AJAX
│   ├── router.php           # router PHP built-in server
│   ├── aset/                # CSS/JS/gambar
│   └── unggahan/            # unggahan pengguna (tidak di-commit)
├── sistem/
│   ├── inti/                # bootstrap, koneksi, migrasi skema
│   ├── konfigurasi/         # lokasi database
│   ├── fungsi/              # helper database & autentikasi/RBAC
│   ├── proses/              # handler aksi per modul
│   ├── ajax/                # endpoint AJAX
│   ├── tampilan/            # view per modul + komponen layout
│   └── database/            # koperasi.sqlite (tidak di-commit)
├── panduan/                 # dokumentasi pengguna (Markdown)
├── vendor/                  # dependensi Composer
└── main.js                  # entry Electron
```

## Instalasi Web

1. Salin isi repositori ke direktori aplikasi pada web server.
2. Arahkan document root ke `publik/`, atau gunakan `.htaccess` yang disertakan.
3. Aktifkan ekstensi `pdo_sqlite` dan pastikan `sistem/database/` dapat ditulis.
4. Jalankan `composer install` bila `vendor/` belum tersedia.
5. Buka aplikasi di browser. Skema database dibuat otomatis pada akses pertama.

## Mode Desktop

```bash
npm install
npm start          # Electron + PHP built-in server
npm run build:win  # installer Windows (NSIS)
```

Database desktop disimpan di direktori `userData` Electron, dengan backup otomatis saat aplikasi ditutup.

## Keamanan

- Akun administrator awal dibuat otomatis saat instalasi. Ganti kata sandinya sebelum digunakan.
- Direktori `sistem/database/` dan file `.sqlite` tidak disertakan dalam repositori.
- Laporkan masalah keamanan secara privat, bukan lewat issue publik.

## Dokumentasi

Panduan pemakaian tersedia di [`panduan/`](panduan/_sidebar.md) dan dapat dibuka dari menu **Panduan** di aplikasi.
