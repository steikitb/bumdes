<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

if (!isset($_GET['anggota_id'])) {
    echo json_encode(['error' => 'Anggota ID tidak ditemukan.']);
    exit;
}

$anggota_id = (int)$_GET['anggota_id'];
$hasil = hitungSkorKredit($anggota_id);

header('Content-Type: application/json');
echo json_encode($hasil);