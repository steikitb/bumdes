<?php
cekLoginAjax(); 

header('Content-Type: text/html; charset=utf-8');

$baseDir = realpath(SISTEM_PATH . '/../panduan');
$requestedFile = $_GET['file'] ?? '';

// 7. Sanitasi Direktori Menggunakan Regex Ketat (Hanya izinkan folder/file MD)
if (!preg_match('/^[a-zA-Z0-9_\-\/]+\.md$/', $requestedFile)) {
    http_response_code(403);
    echo '<div class="alert alert-error">Format atau jalur file panduan tidak valid/diizinkan.</div>';
    exit;
}

$fullPath = realpath($baseDir . '/' . $requestedFile);

// Pastikan path yang diminta benar-benar berada di dalam direktori panduan
if (!$fullPath || strpos($fullPath, $baseDir) !== 0 || !file_exists($fullPath) || is_dir($fullPath)) {
    http_response_code(404);
    echo '<div class="alert alert-error">File panduan tidak ditemukan.</div>';
    exit;
}

try {
    $content = file_get_contents($fullPath);
    $Parsedown = new Parsedown();
    $html = $Parsedown->text($content);

    if (trim($html) === '') {
        echo '<p class="text-base-content/60 italic">Konten untuk panduan ini belum tersedia.</p>';
    } else {
        echo $html;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo '<div class="alert alert-error">Terjadi kesalahan saat membaca file: ' . htmlspecialchars($e->getMessage()) . '</div>';
}