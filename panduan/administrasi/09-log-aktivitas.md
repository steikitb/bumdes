# Administrasi: Log Aktivitas Sistem

Menu ini **hanya dapat diakses oleh Admin**. Fungsinya adalah untuk menyediakan jejak audit dari semua aktivitas penting yang dilakukan oleh pengguna di dalam sistem.

## Fungsi Utama

*   **Keamanan:** Memantau aktivitas yang mencurigakan, seperti upaya masuk yang gagal berulang kali atau aktivitas di luar jam kerja normal.
*   **Audit:** Menelusuri siapa yang membuat, mengubah, atau menghapus data tertentu dan kapan hal itu terjadi.
*   **Troubleshooting:** Membantu melacak urutan kejadian jika terjadi kesalahan atau ketidaksesuaian data.

## Informasi yang Dicatat

Setiap baris log akan mencatat:
*   **Waktu:** Tanggal dan jam pasti saat aksi dilakukan.
*   **Pengguna:** Nama lengkap pengguna yang melakukan aksi.
*   **Role:** Peran (misal: admin, pengurus) dari pengguna tersebut.
*   **Aksi:** Jenis tindakan yang dilakukan (contoh: "Tambah Anggota", "Hapus Produk", "Gagal Masuk").
*   **Keterangan:** Detail tambahan mengenai aksi tersebut.
*   **IP Address:** Alamat IP dari perangkat yang digunakan pengguna.

## Cara Menggunakan

1.  Dari menu **Administrasi & Pengaturan**, pilih **Log Aktivitas Sistem**.
2.  Gunakan formulir filter di bagian atas untuk mencari data spesifik:
    *   **Cari nama pengguna:** Filter log berdasarkan pengguna tertentu.
    *   **Cari aksi/keterangan:** Cari kata kunci dalam kolom Aksi atau Keterangan (misal: "hapus", "gagal").
    *   **Tanggal Mulai & Selesai:** Tentukan rentang waktu untuk pencarian.
3.  Klik **"Filter Log"** untuk menerapkan filter.