<?php
require_once dirname(__DIR__) . '/inti/inisialisasi.php';
cekLoginAjax();

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (empty($q)) {
    echo json_encode([]);
    exit;
}

$baseDir = realpath(SISTEM_PATH . '/../panduan');
$sidebarFile = $baseDir . '/_sidebar.md';

if (!file_exists($sidebarFile)) {
    echo json_encode([]);
    exit;
}

// 1. Parsing file sidebar untuk memetakan path ke judul artikel
$lines = file($sidebarFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$files_map = [];

foreach ($lines as $line) {
    if (preg_match('/\[(.+?)\]\((.+?\.md)\)/', $line, $matches)) {
        $files_map[$matches[2]] = $matches[1]; // mapping path => title
    }
}

$results = [];

// 2. Loop setiap file yang valid di sidebar dan cari kontennya
foreach ($files_map as $path => $title) {
    $fullPath = realpath($baseDir . '/' . $path);
    
    // Pastikan path valid dan berada dalam folder panduan
    if ($fullPath && strpos($fullPath, $baseDir) === 0 && file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Membersihkan format markdown standar agar pencarian teks bersih
        $plainText = preg_replace('/[#*`>\[\]\(\)]/', ' ', $content);
        $plainText = strip_tags($plainText);
        $plainText = preg_replace('/\s+/', ' ', $plainText); 

        $pos = stripos($plainText, $q);
        
        if ($pos !== false || stripos($title, $q) !== false) {
            $start = max(0, $pos - 50);
            $snippetRaw = substr($plainText, $start, 120);
            
            // Keamanan XSS & Highlight kuning untuk kata kunci
            $snippetEscaped = htmlspecialchars($snippetRaw);
            $qEscaped = htmlspecialchars($q);
            
            $snippetHighlighted = preg_replace('/(' . preg_quote($qEscaped, '/') . ')/i', '<mark class="bg-warning text-warning-content px-1 rounded">$1</mark>', $snippetEscaped);
            
            $results[] = [
                'title' => $title,
                'path' => $path,
                'snippet' => '...' . $snippetHighlighted . '...'
            ];
        }
    }
}

echo json_encode($results);
exit;