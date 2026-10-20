<?php
$judulHalaman = 'Tentang Aplikasi';
// Halaman ini dapat diakses oleh semua pengguna yang login
?>
<div class="flex flex-col items-center justify-center py-8 animate-fade-in">
    <div class="card w-full max-w-4xl bg-base-100 shadow-2xl border-t-4 border-primary">
        <div class="card-body">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-base-content mb-2">Sistem Informasi Akuntansi</h1>
                <p class="text-xl text-base-content/70">BUMDes & Koperasi Berbasis Standar Akuntansi Keuangan Entitas Privat (SAK EP)</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-4">
                <div class="bg-base-200 p-8 rounded-2xl shadow-inner">
                    <h2 class="text-xl font-bold mb-3">Tentang Aplikasi</h2>
                    <p class="text-base-content/80">
                        Aplikasi akuntansi dan administrasi untuk Badan Usaha Milik Desa (BUMDes) maupun Koperasi.
                        Berjalan sepenuhnya luring (tanpa internet) di desktop, atau dipasang pada web server internal.
                        Pencatatan transaksi, buku besar, dan laporan keuangan mengikuti Kepmendesa PDTT Nomor 136 Tahun 2022
                        serta Standar Akuntansi Keuangan Entitas Privat (SAK EP).
                    </p>
                </div>

                <div class="bg-base-200 p-8 rounded-2xl shadow-inner">
                    <h2 class="text-xl font-bold mb-3">Cakupan</h2>
                    <ul class="list-disc list-inside space-y-1 text-base-content/80">
                        <li>Kas &amp; bank, penjualan, pembelian, persediaan</li>
                        <li>Keanggotaan, simpanan, pinjaman</li>
                        <li>Aset tetap, beban, pendapatan, hibah</li>
                        <li>Jurnal, tutup buku, laporan keuangan</li>
                        <li>Administrasi organisasi dan penggajian</li>
                    </ul>
                </div>
            </div>

            <div class="divider my-8">Tujuan</div>

            <div class="text-center max-w-2xl mx-auto space-y-4 text-base-content/80">
                <p>
                    Mempermudah digitalisasi pencatatan transaksi dan penyusunan pelaporan keuangan BUMDes maupun
                    Koperasi secara luring yang aman, akurat, dan akuntabel sesuai kebijakan perundang-undangan
                    dan standar akuntansi yang berlaku.
                </p>
                <p class="text-sm">
                    Perangkat lunak ini disediakan apa adanya untuk mendukung tata kelola keuangan entitas desa dan koperasi.
                </p>
            </div>

            <div class="mt-8 text-center">
                <a href="index.php?halaman=dasbor" class="btn btn-primary px-8">Kembali ke Dasbor</a>
            </div>
        </div>
    </div>
</div>
