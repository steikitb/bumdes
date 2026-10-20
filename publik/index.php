<?php
require_once '../sistem/inti/inisialisasi.php';

$is_ajax_request = !empty($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
$is_export = isset($_GET['export']) && $_GET['export'] === 'csv';

$halaman_raw = $_GET['halaman'] ?? 'login'; 

if (!preg_match('/^[a-z0-9_\/]+$/i', $halaman_raw) || strpos($halaman_raw, '..') !== false) {
    $halaman = 'login'; 
} else {
    $halaman = $halaman_raw;
}
$halaman_publik = ['login'];
// Halaman cetak mandiri: dokumen HTML sendiri (struk thermal), jangan dibungkus layout.
$halaman_tanpa_layout = ['penjualan/cetak_struk'];

if (!$is_ajax_request) { 
    if (!in_array($halaman, $halaman_publik)) {
        if (!isset($_SESSION['user_login'])) {
            header('Location: ' . BASE_URL . 'index.php?halaman=login');
            exit;
        }
    }
    if (isset($_SESSION['user_login'])) {
        if ($halaman === 'login') {
            header('Location: ' . BASE_URL . 'index.php?halaman=dasbor');
            exit;
        }
    }
}

$fileTampilan = SISTEM_PATH . '/tampilan/' . $halaman . '.php';

if (!file_exists($fileTampilan)) {
    http_response_code(404);
    $judulHalaman = '404 - Halaman Tidak Ditemukan';
    $fileTampilan = SISTEM_PATH . '/tampilan/404.php';
}

if ($is_ajax_request) {
    // Mulai menahan output
    ob_start();
    
    // Panggil file konten. Outputnya (HTML, script, dll.) akan masuk ke buffer, bukan ke browser.
    // Di dalam file ini, variabel $judulHalaman akan didefinisikan.
    require_once $fileTampilan;
    
    // Ambil konten yang sudah ditahan dari buffer
    $content = ob_get_contents();
    
    // Hentikan dan bersihkan buffer
    ob_end_clean();

    // Sekarang kita aman untuk mengirim header, karena belum ada output yang dikirim.
    // Variabel $judulHalaman juga sudah tersedia.
    header('X-Page-Title: ' . base64_encode($judulHalaman ?? 'Sistem Koperasi'));
    
    // Terakhir, kirimkan konten yang tadi sudah kita tangkap.
    echo $content;

} elseif ($is_export || in_array($halaman, $halaman_tanpa_layout, true)) {
    // Untuk ekspor file (CSV, dll), langsung proses file tanpa memuat header dan footer HTML
    require_once $fileTampilan;
} else {
    // Halaman internal lainnya menggunakan layout standar (header & footer)
    require_once SISTEM_PATH . '/tampilan/komponen/header.php';
    require_once $fileTampilan;
    require_once SISTEM_PATH . '/tampilan/komponen/footer.php';
}