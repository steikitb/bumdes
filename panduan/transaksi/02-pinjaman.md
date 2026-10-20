# Transaksi: Pinjaman (Piutang Simpan Pinjam)

Menu ini adalah pusat pengelolaan semua aktivitas pinjam-meminjam uang (pemberian kredit) oleh organisasi kepada masyarakat atau anggotanya, mulai dari pencairan dana hingga pelunasan angsuran.

## Memberikan Pinjaman Baru (Pencairan)

1.  Dari menu **Pencatatan Transaksi**, pilih **Piutang Simpan Pinjam**.
2.  Klik tombol **"Beri Pinjaman Baru"**.
3.  Isi formulir pinjaman:
    *   **Pilih Debitur / Nasabah:** Ketik dan pilih nama peminjam dari daftar.
    *   **Analisis Skor Kredit (Khusus Koperasi):** Jika dalam Mode Koperasi, sistem menampilkan skor kelayakan kredit otomatis. Jika skor terlalu rendah, sistem akan memblokir pemberian pinjaman. *(Fitur pemblokiran ini dinonaktifkan pada Mode BUMDes).*
    *   **Jenis Pinjaman (Sesuai COA SAK):** Pilih jenis produk kredit. Opsi yang tampil menyesuaikan Mode Entitas:
        *   *Mode BUMDes:* **SPP** (Pinjaman Simpan Pinjam Perempuan) atau **UEP** (Pinjaman Usaha Ekonomi Produktif).
        *   *Mode Koperasi:* **Pinjaman / Piutang Umum**.
    *   **Tanggal Pencairan Dana:** Tanggal saat dana ditransfer atau diberikan tunai.
    *   **Plafon Pinjaman (Rp):** Nominal pokok pinjaman yang diberikan.
    *   **Biaya Administrasi (Rp):** Biaya admin yang dipotong dari pokok saat pencairan.
    *   **Netto Diterima Nasabah (Rp):** Dihitung otomatis = Plafon dikurangi Biaya Administrasi. Inilah uang yang benar-benar diterima debitur.
    *   **Jangka Waktu / Tenor (Bulan):** Lama periode pinjaman.
    *   **Bunga (% per Tahun):** Suku bunga yang dikenakan.
    *   **Metode Pencairan (Uang Keluar):** **Uang Tunai (Kasir)** atau **Transfer / QRIS (Bank)** — pada pilihan Transfer akan muncul daftar rekening bank tujuan.
    *   **Keterangan / Tujuan Pinjaman (Opsional):** bebas, misal "Modal Usaha" atau "Renovasi Rumah".
4.  Klik **"Cairkan Pinjaman"**. Sistem akan otomatis menjurnal uang keluar dan mendebit akun Piutang.

## Mencatat Pembayaran Angsuran

1.  Di halaman daftar pinjaman, cari pinjaman yang akan dibayar angsurannya.
2.  Klik tombol **"Detail & Bayar"**.
3.  Di halaman Buku Pembantu Piutang, Anda akan melihat informasi sisa pokok, tabel riwayat pembayaran, dan form angsuran.
4.  Gunakan formulir **"Catat Angsuran Baru"** di sebelah kanan.
    *   Sistem akan merekomendasikan angka untuk **Pokok Dibayar** dan **Bunga Dibayar** untuk bulan ini (berdasarkan metode *Flat*). Anda **bisa dan harus** menyesuaikan angka ini jika ada denda atau jika peminjam membayar lebih/kurang.
    *   Isi tanggal bayar dan nomor kuitansi pada Keterangan.
5.  Klik **"Simpan Pembayaran Masuk"**.

## Status Piutang
Halaman ini memudahkan Anda memantau status setiap pinjaman:
*   **Aktif (Kuning):** Pinjaman masih berjalan dan memiliki sisa tagihan pokok.
*   **Lunas (Hijau):** Sisa tagihan pokok sudah mencapai Rp 0.