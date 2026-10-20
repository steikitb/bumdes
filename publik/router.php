<?php
// Router khusus pengganti .htaccess untuk PHP Built-in Server (Electron)
$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

// Handle root directory
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

// Jika yang diakses adalah file fisik asli (seperti gambar logo, css, js), biarkan server melayani secara langsung
if (file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    return false;
}

// Simulasi RewriteRule untuk URL Sistem Ajax
if (preg_match('#^/sistem/ajax/(.*)$#', $uri, $matches)) {
    $_GET['endpoint'] = $matches[1];
    require __DIR__ . '/ajax.php';
    exit;
}

// Simulasi RewriteRule untuk URL Sistem Proses
if (preg_match('#^/sistem/proses/(.*)$#', $uri, $matches)) {
    $_GET['aksi'] = $matches[1];
    require __DIR__ . '/proses.php';
    exit;
}

// Jika file tidak ada dan bukan pola khusus, arahkan ke index.php (seperti rule Rewrite fallback)
require __DIR__ . '/index.php';