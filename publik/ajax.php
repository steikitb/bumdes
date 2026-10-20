<?php
require_once '../sistem/inti/inisialisasi.php';

$endpoint = $_GET['endpoint'] ?? '';

$allowed_endpoints = [
    'get_panduan_content.php',
    'cari_panduan.php',
    'get_skor_kredit.php',
    'cari_produk.php',
    'cari_anggota.php',
    'get_info_konversi.php',
    'get_produk_detail.php',
    'tambah_pemasok_cepat.php',
    'get_karyawan_detail.php',
    'get_next_akun.php'
];

$endpoint_filename = basename($endpoint);

if (in_array($endpoint_filename, $allowed_endpoints)) {
    $file_path = SISTEM_PATH . '/ajax/' . $endpoint_filename;
    
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        http_response_code(404);
        echo "Endpoint file not found.";
    }
} else {
    http_response_code(403);
    echo "Akses ditolak atau endpoint tidak valid.";
}