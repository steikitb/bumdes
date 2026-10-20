<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

$karyawan = query("
    SELECT k.*, 
           COALESCE(a.nama_lengkap, k.nama_karyawan) as nama_tampil,
           j.gaji_pokok, 
           j.tunjangan_jabatan 
    FROM mst_karyawan k 
    LEFT JOIN anggota a ON k.anggota_id = a.id 
    LEFT JOIN mst_jabatan j ON k.jabatan_id = j.id 
    WHERE k.id = ?
", [$id]);

if ($karyawan) {
    echo json_encode(['success' => true, 'data' => $karyawan[0]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan.']);
}