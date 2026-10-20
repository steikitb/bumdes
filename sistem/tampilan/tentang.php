<?php
$judulHalaman = 'Tentang Aplikasi & Pengembang';
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
                <!-- Pengembang -->
                <div class="bg-base-200 p-8 rounded-2xl flex flex-col items-center text-center shadow-inner hover:shadow-lg transition-all">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24 h-24 shadow-lg ring ring-primary ring-offset-base-100 ring-offset-2">
                            <span class="text-3xl font-bold">DJ</span>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold mb-1">Dwi Juli Yantono</h2>
                    <div class="badge badge-primary mb-4 p-3 font-semibold">Pengembang / Peneliti</div>
                    
                    <div class="w-full text-sm text-left space-y-2 bg-base-100 p-4 rounded-xl shadow-sm">
                        <div class="flex justify-between border-b border-base-300 pb-1">
                            <span class="opacity-70 font-semibold">Institusi</span>
                            <span class="font-bold text-right">Politeknik Keuangan Negara STAN</span>
                        </div>
                        <div class="flex justify-between border-b border-base-300 pb-1">
                            <span class="opacity-70 font-semibold">Program Studi</span>
                            <span class="font-bold text-right">D-IV Manajemen Aset Publik</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70 font-semibold">Konsentrasi</span>
                            <span class="font-bold text-right">Manajemen & Perencanaan Aset Strategis</span>
                        </div>
                    </div>
                </div>

                <!-- Dosen Pembimbing -->
                <div class="bg-base-200 p-8 rounded-2xl flex flex-col items-center text-center shadow-inner hover:shadow-lg transition-all">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-secondary text-secondary-content rounded-full w-24 h-24 shadow-lg ring ring-secondary ring-offset-base-100 ring-offset-2">
                            <span class="text-3xl font-bold">EW</span>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold mb-1">Eri Wahyudi, S.ST., MPA.</h2>
                    <div class="badge badge-secondary mb-4 p-3 font-semibold">Dosen Pembimbing</div>
                    
                    <div class="w-full text-sm text-left bg-base-100 p-4 rounded-xl shadow-sm mt-auto">
                        <p class="text-base-content/80 italic text-center">
                            "Membimbing dan mengarahkan dalam penyusunan konsep, perancangan sistem, serta memastikan kesesuaian aplikasi dengan standar akuntansi dan tata kelola aset publik yang berlaku."
                        </p>
                    </div>
                </div>
            </div>

            <div class="divider my-8">Dedikasi & Tujuan</div>

            <div class="text-center max-w-2xl mx-auto space-y-4 text-base-content/80">
                <p>
                    Aplikasi ini dirancang dan dikembangkan sebagai wujud nyata penerapan ilmu Manajemen Aset Publik dan Akuntansi dalam rangka penyusunan Tugas Akhir / Skripsi di Politeknik Keuangan Negara STAN.
                </p>
                <p>
                    Tujuan utama dari sistem ini adalah untuk mempermudah digitalisasi, pencatatan transaksi, dan penyusunan pelaporan keuangan Badan Usaha Milik Desa (BUMDes) maupun Koperasi secara luring (offline) yang aman, akurat, dan akuntabel sesuai dengan kebijakan perundang-undangan (Kepmendesa) dan standar akuntansi (SAK EP).
                </p>
            </div>
            
            <div class="mt-8 text-center">
                <a href="index.php?halaman=dasbor" class="btn btn-primary px-8">Kembali ke Dasbor</a>
            </div>
        </div>
    </div>
</div>