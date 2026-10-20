<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax(); 

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%{$query}%";

$sql = "
    SELECT 
        p1.id, 
        p1.kode_produk, 
        p1.nama_produk, 
        p1.harga_jual, 
        p1.stok,
        p1.id_produk_induk,
        p2.stok as stok_induk,
        p1.kuantitas_konversi
    FROM produk p1
    LEFT JOIN produk p2 ON p1.id_produk_induk = p2.id
    WHERE 
        p1.status_aktif = 1 AND
        (p1.nama_produk LIKE ? OR p1.kode_produk LIKE ?) 
        AND (
            p1.stok > 0 
            OR 
            (p1.id_produk_induk IS NOT NULL AND p2.stok > 0)
        )
    LIMIT 10
";

$produk = query($sql, [$searchTerm, $searchTerm]);

echo json_encode($produk);