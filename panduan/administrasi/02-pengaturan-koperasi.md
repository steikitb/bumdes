# Administrasi: Pengaturan Identitas Entitas

Menu ini **hanya dapat diakses oleh Admin**. Halaman ini berfungsi sebagai pusat kontrol utama untuk menentukan bagaimana sistem ini bekerja, baik dari sisi tampilan laporan maupun logika akuntansi di belakang layar.

## A. Profil Usaha (Sangat Penting)

*   **Mode Entitas Utama:** 
    *   Pilih **Koperasi** jika Anda menjalankan Koperasi. Istilah yang muncul adalah Anggota, SHU, Simpanan Pokok.
    *   Pilih **BUMDes** jika Anda menjalankan Badan Usaha Milik Desa. Sistem akan menyesuaikan istilah menjadi PADes, Laba Bersih, Penyertaan Modal Desa sesuai Kepmendesa 136/2022.
*   **Jenis BUMDes / Koperasi:** Pilih spesifikasi entitas, seperti BUMDes 1 Desa, BUMDes Bersama, atau Eks PNPM (LKD).
*   **Nama & Alamat Lengkap:** Identitas ini akan tertera otomatis sebagai Kop Surat di semua lembar cetak Laporan dan Buku Administrasi.
*   **Logo Entitas:** Anda dapat mengunggah gambar (PNG/JPG) yang akan muncul di layar struk kasir dan halaman masuk.

## B. Pengaturan Penanggung Jawab

Nama-nama yang Anda masukkan di sini (Kepala Desa/Penasihat, Direktur/Ketua, dan Bendahara/Akuntan) akan otomatis tercetak di bagian bawah (kolom tanda tangan) pada dokumen-dokumen resmi seperti Buku Kas Umum (BKU), Laporan Laba Rugi, Neraca, dan Arus Kas.

## C. Periode Akuntansi & Penguncian (Lock Date)

*   **Mulai Tanggal & Berakhir Tahun Berapa:** Rentang waktu tahun buku akuntansi berjalan.
*   **Kunci Transaksi Sampai Bulan (Lock Date):** 
    Fitur keamanan yang sangat krusial. Jika Anda (Admin) memilih bulan tertentu di sini (Misal: Maret), maka seluruh pengguna **tidak akan bisa lagi** menambah, mengubah, atau membatalkan/menghapus transaksi apapun yang terjadi di bulan Januari, Februari, dan Maret. Ini digunakan setelah rekonsiliasi bulanan selesai agar laporan yang sudah disetor tidak berubah angkanya.

## D. Keamanan Data (Folder Cadangan)

Aplikasi ini berjalan **tanpa internet**, dan seluruh datanya hanya ada di dalam komputer atau laptop ini.

Untuk mencegah kehilangan data bila komputer rusak, hilang, atau terkena virus, Anda **sangat diwajibkan** mengatur folder cadangan otomatis ke media penyimpanan lain di luar komputer — misalnya flashdisk (drive F:), harddisk eksternal, atau folder E:\Backup.

Setiap kali aplikasi ditutup, sistem akan otomatis menyalin data keuangan terbaru ke folder tersebut.