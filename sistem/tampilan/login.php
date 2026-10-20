<?php $judulHalaman = 'Login Pengguna'; ?>
<?php  ?>

<div class="hero min-h-screen bg-base-200">
    <div class="hero-content flex-col lg:flex-row-reverse">
        <div class="text-center lg:text-left">
            <h1 class="text-5xl font-bold">Sistem Akuntansi</h1>
            <p class="py-6">Silakan masuk untuk melanjutkan ke dasbor pencatatan keuangan Koperasi.</p>
        </div>
        <div class="card shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
                    <?php
                    $status = $_GET['status'] ?? '';
                    if ($status === 'login_gagal'): ?>
                        <div role="alert" class="alert alert-error alert-sm rounded-b-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Nama pengguna atau kata sandi salah.</span>
                        </div>
                    <?php elseif ($status === 'terlalu_banyak_percobaan'): ?>
                        <div role="alert" class="alert alert-warning alert-sm rounded-b-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.1-.833-2.87 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <span>Terlalu banyak percobaan login. Coba lagi beberapa saat.</span>
                        </div>
                    <?php endif; ?>
                    <form class="card-body" action="<?= BASE_URL ?>sistem/proses/proses_login.php" method="POST">
                        <?php csrfInput(); ?>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nama Pengguna</span>
                    </label>
                    <input type="text" name="nama_pengguna" placeholder="nama pengguna" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Kata Sandi</span>
                    </label>
                    <input type="password" name="kata_sandi" placeholder="kata sandi" class="input input-bordered" required />
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">Masuk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php  ?>