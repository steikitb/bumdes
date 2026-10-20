<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Produk tidak valid.']);
    exit;
}

$produk_q = query("SELECT id, harga_rata_rata FROM produk WHERE id = ?", [$id], 'i');
$produk = $produk_q[0] ?? null;

if ($produk) {
    echo json_encode(['success' => true, 'produk' => $produk]);
} else {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
}