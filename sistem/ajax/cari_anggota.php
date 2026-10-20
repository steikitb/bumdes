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
    SELECT id, nama_lengkap, alamat, telepon
    FROM anggota 
    WHERE 
        (nama_lengkap LIKE ? OR alamat LIKE ? OR telepon LIKE ?)
        AND status_keanggotaan = 'Aktif'
    LIMIT 20
";

$anggota = query($sql, [$searchTerm, $searchTerm, $searchTerm]);

$results = [];
foreach ($anggota as $a) {
    $results[] = [
        'id' => $a['id'],
        'nama_lengkap' => $a['nama_lengkap'],
        'detail' => $a['telepon'] . ' - ' . substr($a['alamat'], 0, 30)
    ];
}

echo json_encode($results);