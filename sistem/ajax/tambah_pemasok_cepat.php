<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

verifyCsrfToken();

header('Content-Type: application/json');
$koneksi = getKoneksi();

$nama_pemasok = trim($_POST['nama_pemasok'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($nama_pemasok)) {
    echo json_encode(['success' => false, 'message' => 'Nama Pemasok wajib diisi.']);
    exit;
}

db_begin_transaction($koneksi);
try {
    $sql = "INSERT INTO pemasok (nama_pemasok, keterangan, alamat, telepon, email) VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    
    if (!$stmt->execute([$nama_pemasok, $keterangan, $alamat, $telepon, $email])) {
        throw new Exception("Gagal menyimpan data pemasok ke database.");
    }
    
    $new_id = db_insert_id($koneksi);
    db_commit($koneksi);
    
    catatLog('Tambah Pemasok Cepat', 'Pemasok baru ditambahkan via form pembelian: ' . $nama_pemasok);

    echo json_encode([
        'success' => true,
        'id' => $new_id,
        'nama' => $nama_pemasok . ($keterangan ? ' (' . $keterangan . ')' : '')
    ]);

} catch (Exception $e) {
    db_rollback($koneksi);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;