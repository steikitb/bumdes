<?php
require_once '../sistem/inti/inisialisasi.php';
$aksi = $_GET['aksi'] ?? '';

if (substr(strtolower($aksi), -4) === '.php') {
    $aksi_filename = $aksi;
} else {
    $aksi_filename = $aksi . '.php';
}

// E2. Otomatisasi Routing proses.php (Mencegah Hardcode)
$baseProsesPath = realpath(SISTEM_PATH . '/proses/');
$aksiFilePath = realpath(SISTEM_PATH . '/proses/' . $aksi_filename);

if ($aksiFilePath && strpos($aksiFilePath, $baseProsesPath) === 0 && file_exists($aksiFilePath)) {
    require_once $aksiFilePath;
} else {
    http_response_code(404);
    echo "Aksi tidak ditemukan atau tidak valid.";
    exit;
}