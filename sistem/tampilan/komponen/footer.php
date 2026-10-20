        </main>
    </div>
    <?php if (isset($_SESSION['user_login'])): ?>
        <?php require_once SISTEM_PATH . '/tampilan/komponen/menu_navigasi.php'; ?>
    <?php endif; ?>
</div>
<footer class="footer footer-center p-4 bg-base-300 text-base-content fixed bottom-0 print:hidden">
    <aside>
        <p>Hak Cipta © <?= date('Y') ?> - <?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'Koperasi') ?> | <a href="index.php?halaman=tentang" class="link link-hover text-primary font-semibold">Tentang Aplikasi</a></p>
    </aside>
</footer>
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center hidden">
    <span class="loading loading-spinner loading-lg text-primary"></span>
</div>
<div id="toast-container" class="toast toast-top toast-end z-50"></div>
<dialog id="modal_konfirmasi" class="modal">
  <div class="modal-box">
    <h3 class="font-bold text-lg" id="konfirmasi_judul">Konfirmasi Aksi</h3>
    <p class="py-4" id="konfirmasi_pesan">Apakah Anda yakin ingin melanjutkan?</p>
    <div class="modal-action">
        <button type="button" class="btn" onclick="document.getElementById('modal_konfirmasi').close()">Batal</button>
        <form id="form_lanjutkan" method="POST" class="inline">
            <input type="hidden" name="id" id="konfirmasi_id_input">
            <input type="hidden" name="csrf_token" id="konfirmasi_csrf_token">
            <button type="submit" class="btn btn-error">Lanjutkan</button>
        </form>
    </div>
  </div>
</dialog>
<form id="logout-form" action="<?= BASE_URL ?>sistem/proses/proses_logout.php" method="POST" style="display: none;">
    <?php csrfInput(); ?>
</form>
<?php if (isset($_SESSION['user_login'])): ?>
<a href="index.php?halaman=panduan" title="Buka Panduan" class="btn btn-primary btn-circle fixed bottom-20 right-5 z-40 shadow-lg animate-bounce print:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.546-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
</a>
<?php endif; ?>
<style>
    #loading-overlay.hidden {
        display: none;
    }
</style>
<?php
require_once SISTEM_PATH . '/tampilan/komponen/js/page-scripts.js.php';
require_once SISTEM_PATH . '/tampilan/komponen/js/app.js.php';
?>
</body>
</html>