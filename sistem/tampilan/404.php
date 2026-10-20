<?php $judulHalaman = '404 - Halaman Tidak Ditemukan'; ?>
<?php if (isset($_SESSION['user_login'])) : ?>
    <?php  ?>
    <div class="text-center py-20">
        <h1 class="text-6xl font-bold">404</h1>
        <p class="text-xl mt-4">Maaf, halaman yang Anda cari tidak dapat ditemukan.</p>
        <a href="index.php?halaman=dasbor" class="btn btn-primary mt-8">Kembali ke Dasbor</a>
    </div>
    <?php  ?>
<?php else : ?>
    <?php  ?>
    <div class="hero min-h-screen bg-base-200">
        <div class="hero-content text-center">
            <div class="max-w-md">
                <h1 class="text-6xl font-bold">404</h1>
                <p class="py-6 text-xl">Maaf, halaman yang Anda cari tidak dapat ditemukan.</p>
                <a href="index.php?halaman=login" class="btn btn-primary">Kembali ke Halaman Login</a>
            </div>
        </div>
    </div>
    <?php  ?>
<?php endif; ?>