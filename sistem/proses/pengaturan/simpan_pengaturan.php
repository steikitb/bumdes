<?php
require_once dirname(dirname(__DIR__)) . '/inti/inisialisasi.php';
cekLogin();
cekAkses('kelola_pengaturan');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrfToken();
    $koneksi = getKoneksi();
    
    // 9. Simpan file konfigurasi path backup JSON
    if (isset($_POST['backup_path'])) {
        $db_dir = getenv('DATABASE_DIR') ?: SISTEM_PATH . '/database';
        $backup_config = ['backup_path' => $_POST['backup_path']];
        file_put_contents($db_dir . '/backup_config.json', json_encode($backup_config));
    }
    
    $sql = "INSERT OR REPLACE INTO pengaturan (pengaturan_key, pengaturan_value) VALUES (?, ?)";
    $stmt = $koneksi->prepare($sql);
    
    foreach ($_POST as $key => $value) {
        if ($key !== 'csrf_token' && $key !== 'backup_path') {
            $stmt->execute([$key, $value]);
        }
    }
    
    if (isset($_FILES['logo_koperasi']) && $_FILES['logo_koperasi']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['logo_koperasi']['tmp_name'];
        $upload_dir = SISTEM_PATH . '/../publik/';
        
        $info = getimagesize($tmp_name);
        if ($info) {
            $img = null;
            if ($info[2] == IMAGETYPE_JPEG) $img = imagecreatefromjpeg($tmp_name);
            elseif ($info[2] == IMAGETYPE_PNG) $img = imagecreatefrompng($tmp_name);
            
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                
                $max_size = 300;
                if ($width > $max_size || $height > $max_size) {
                    $ratio = min($max_size / $width, $max_size / $height);
                    $new_width = floor($width * $ratio);
                    $new_height = floor($height * $ratio);
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }
                
                $tmp_img = imagecreatetruecolor($new_width, $new_height);
                imagealphablending($tmp_img, false);
                imagesavealpha($tmp_img, true);
                imagecopyresampled($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                imagepng($tmp_img, $upload_dir . 'logo.png', 8); // compress level 8
                imagedestroy($img);
                imagedestroy($tmp_img);
            }
        }
    }

    catatLog('Simpan Pengaturan', 'Pengaturan profil Entitas / BUMDes telah diperbarui.');
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, 'form_pengaturan_kebijakan') !== false) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_pengaturan_kebijakan&status=sukses');
    } elseif (strpos($referer, 'form_pengaturan_shu') !== false) {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_pengaturan_shu&status=sukses');
    } else {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengaturan/form_pengaturan&status=sukses');
    }
    exit;
}