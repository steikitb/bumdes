<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

header('Content-Type: application/json');

$prefix = $_GET['prefix'] ?? '';
$mode = MODE_ENTITAS;

if (empty($prefix)) {
    echo json_encode(['success' => false, 'code' => '']);
    exit;
}

// Cari akun terakhir yang menggunakan prefix ini
$sql = "SELECT kode_akun FROM daftar_akun WHERE kode_akun LIKE ? ORDER BY kode_akun DESC LIMIT 1";
$result = query($sql, [$prefix . '%']);

if (empty($result)) {
    // Jika belum ada sama sekali, buat kode pertama sesuai format
    if ($mode === 'BUMDes') {
        // Format Kepmendesa: X.X.XX.XX (Misal prefix 1.1 -> 1.1.01.01)
        $new_code = $prefix . '01.01';
        // Khusus untuk kepala 3, 4, 5, 6, 7 biasanya langsung 3 level di BUMDes, tapi kita sesuaikan
        if (strlen($prefix) > 2) {
            $parts = explode('.', $prefix);
            if (count($parts) == 2) $new_code = $prefix . '01.01'; // 1.1. -> 1.1.01.01
            if (count($parts) == 3) $new_code = $prefix . '01';    // 1.1.01. -> 1.1.01.01
        }
    } else {
        $new_code = rtrim($prefix, '.') . '.01';
    }
} else {
    $last_code = $result[0]['kode_akun'];
    $parts = explode('.', $last_code);
    $last_part = (int)end($parts);
    $next_part = str_pad($last_part + 1, 2, '0', STR_PAD_LEFT);
    
    array_pop($parts); // Hapus elemen terakhir
    $parts[] = $next_part; // Masukkan elemen baru yang sudah ditambah
    
    $new_code = implode('.', $parts);
}

echo json_encode(['success' => true, 'code' => $new_code]);
exit;