<?php
$judulHalaman = 'Ubah Kata Sandi';
cekLogin();
?>
<?php  ?>
<div class="flex justify-center items-center min-h-full">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h1 class="card-title text-2xl mb-4">Ubah Kata Sandi Anda</h1>
            <?php if (isset($_SESSION['wajib_ganti_sandi']) && $_SESSION['wajib_ganti_sandi']): ?>
            <div role="alert" class="alert alert-info mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Untuk keamanan, Anda harus mengubah kata sandi default Anda sebelum melanjutkan.</span>
            </div>
            <?php endif; ?>
            <form action="<?= BASE_URL ?>sistem/proses/pengguna/proses_ubah_kata_sandi_sendiri.php" method="POST">
                <?php csrfInput(); ?>
                <div class="form-control">
                    <label class="label"><span class="label-text">Kata Sandi Lama</span></label>
                    <input type="password" name="kata_sandi_lama" class="input input-bordered" required />
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text">Kata Sandi Baru</span></label>
                    <input type="password" name="kata_sandi_baru" class="input input-bordered" required />
                </div>
                 <div class="form-control">
                    <label class="label"><span class="label-text">Konfirmasi Kata Sandi Baru</span></label>
                    <input type="password" name="konfirmasi_kata_sandi_baru" class="input input-bordered" required />
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">Simpan Kata Sandi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php  ?>