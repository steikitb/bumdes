<?php
// Mencegah error jika dipanggil melalui publik/proses.php yang sudah me-load inisialisasi.php
if (!defined('BASE_URL')) {
    require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
}
cekLogin();
cekAkses('kelola_daftar_akun');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    
    if (!isset($_FILES['file_csv']) || $_FILES['file_csv']['error'] !== UPLOAD_ERR_OK) {
        $error_msg = isset($_FILES['file_csv']) ? $_FILES['file_csv']['error'] : 'Tidak ada file yang diupload.';
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=Gagal mengupload file. (Kode Error: ' . $error_msg . ')');
        exit;
    }

    $file = $_FILES['file_csv'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'csv') {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=Format file harus CSV.');
        exit;
    }

    $handle = fopen($file['tmp_name'], "r");
    if ($handle !== FALSE) {
        $has_header = isset($_POST['has_header']);
        if ($has_header) {
            fgetcsv($handle, 1000, ","); // Skip baris pertama (header)
        }

        $koneksi = getKoneksi();
        db_begin_transaction($koneksi);
        $berhasil = 0;
        
        try {
            $sql = "INSERT INTO daftar_akun (kode_akun, nama_akun, kelompok_akun, unit_usaha, saldo_normal) VALUES (?, ?, ?, 'Konsolidasi', ?)";
            
            // Gunakan PDO prepare langsung untuk menangkap error dengan lebih baik
            $pdoStmt = $koneksi->prepare($sql);
            if (!$pdoStmt) {
                $errorInfo = $koneksi->errorInfo();
                throw new Exception("Prepare gagal: " . implode(" - ", $errorInfo));
            }
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Antisipasi jika CSV di-save oleh Excel menggunakan delimiter titik koma (;)
                if (count($data) == 1 && strpos($data[0], ';') !== false) {
                    $data = explode(';', $data[0]);
                }
                
                // Antisipasi jika menggunakan tab (\t) dari copy-paste langsung
                if (count($data) == 1 && strpos($data[0], "\t") !== false) {
                    $data = explode("\t", $data[0]);
                }

                if (count($data) >= 2) {
                    $kolom1 = trim($data[0]); // Misal: "1.1.01.01 Kas Tunai"
                    
                    // Menghapus karakter BOM (Byte Order Mark) jika file CSV dari Excel
                    if (strpos($kolom1, "\xEF\xBB\xBF") === 0) {
                        $kolom1 = substr($kolom1, 3);
                    }
                    
                    $kolom2 = trim($data[1]); // Misal: "Debit"
                    
                    if (empty($kolom1)) continue;

                    // Menggunakan Regex untuk memisahkan Kode (angka & titik) dan Nama Akun
                    // Pola: ambil angka+titik di awal, lalu spasi, sisanya adalah nama akun
                    if (preg_match('/^([\d\.]+)\s+(.+)$/', $kolom1, $matches)) {
                        $kode_akun = trim($matches[1]);
                        $nama_akun = trim($matches[2]);
                    } else {
                        // Jika format tidak sesuai (tidak ada spasi pemisah), lewati baris
                        continue; 
                    }

                    // Cek apakah kode akun sudah ada
                    $cek = query("SELECT kode_akun FROM daftar_akun WHERE kode_akun = ?", [$kode_akun]);
                    if (!empty($cek)) continue; // Lewati baris ini jika duplikat
                    
                    // Deteksi Kelompok berdasarkan digit pertama kode akun
                    $prefix = substr($kode_akun, 0, 1);
                    $kelompok_akun = 'Aset';
                    
                    // Ambil saldo normal dari kolom 2 (default ke Debit jika tidak diisi dengan benar)
                    $saldo_normal = (stripos($kolom2, 'kredit') !== false || stripos($kolom2, 'cr') !== false) ? 'Kredit' : 'Debit';
                    
                    if ($prefix === '1') {
                        $kelompok_akun = 'Aset';
                    } elseif ($prefix === '2') {
                        $kelompok_akun = 'Liabilitas';
                    } elseif ($prefix === '3') {
                        $kelompok_akun = 'Ekuitas';
                    } elseif ($prefix === '4') {
                        $kelompok_akun = 'Pendapatan';
                    } elseif ($prefix === '5') {
                        $kelompok_akun = 'Beban';
                    } elseif ($prefix === '6') {
                        $kelompok_akun = 'Beban';
                    } elseif ($prefix === '7') {
                        if (strpos($kode_akun, '7.1') === 0) {
                            $kelompok_akun = 'Pendapatan Lain-lain';
                        } else {
                            $kelompok_akun = 'Beban Lain-lain';
                        }
                    }

                    try {
                        if ($pdoStmt->execute([$kode_akun, $nama_akun, $kelompok_akun, $saldo_normal])) {
                            $berhasil++;
                        }
                    } catch (Exception $e) {
                        // Jika gagal execute pada baris ini, skip dan lanjut ke baris berikutnya
                        continue;
                    }
                }
            }
            db_commit($koneksi);
            fclose($handle);
            
            catatLog('Import Akun CSV', "Berhasil mengimpor $berhasil akun baru secara massal.");
            header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=sukses_import');
            exit;
        } catch (\Throwable $e) {
            db_rollback($koneksi);
            if (is_resource($handle)) fclose($handle);
            header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=' . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun&status=gagal&error=Tidak dapat membaca file CSV.');
        exit;
    }
} else {
    // Jika diakses menggunakan metode GET, langsung arahkan kembali
    header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/lihat_akun');
    exit;
}