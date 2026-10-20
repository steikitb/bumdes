<?php
$judulHalaman = 'Panduan Penggunaan Sistem';

function renderSidebarFromMarkdown_v2($filePath) {
     if (!file_exists($filePath)) {
        return '<div class="alert alert-error">File _sidebar.md tidak ditemukan!</div>';
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $html = '';
    $in_submenu = false;

    foreach($lines as $line) {
        if (strpos(trim($line), '* **') === 0) { 
            if ($in_submenu) {
                $html .= '</ul></details></li>';
            }
            preg_match('/\*\s+\*\*(.+?)\*\*/', $line, $matches);
            $html .= '<li><details open><summary><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>' . htmlspecialchars($matches[1]) . '</summary><ul>';
            $in_submenu = true;
        } elseif (strpos(trim($line), '* [') === 0) { 
             preg_match('/\[(.+?)\]\((.+?)\)/', $line, $matches);
             $title = htmlspecialchars($matches[1]);
             $path = htmlspecialchars($matches[2]);
             $html .= '<li><a class="file-link" href="#" data-path="' . $path . '">' . $title . '</a></li>';
        }
    }
    if ($in_submenu) {
        $html .= '</ul></details></li>';
    }
    return '<ul class="menu p-2">' . $html . '</ul>';
}

$sidebarFile = SISTEM_PATH . '/../panduan/_sidebar.md';
?>
<?php  ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold"><?= $judulHalaman ?></h1>
    <button id="fullscreen-toggle" class="btn btn-ghost btn-circle" title="Tampilan Penuh">
        <i class="fas fa-expand text-xl"></i>
    </button>
</div>

<style>
    .file-link.active {
        background-color: hsl(var(--p));
        color: hsl(var(--pc));
        font-weight: bold;
    }
    #file-tree-container {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-full">
    <aside class="lg:col-span-1">
        <div class="sticky top-4">
             <div class="p-4 bg-base-200 rounded-t-lg border-b border-base-300">
                <h2 class="text-xl font-bold mb-3">Daftar Panduan</h2>
                <div class="join w-full">
                    <input type="text" id="input-search-panduan" placeholder="Cari di buku saku..." class="input input-sm input-bordered join-item w-full" autocomplete="off" />
                    <button id="btn-search-panduan" class="btn btn-sm btn-secondary join-item" title="Cari"><i class="fas fa-search"></i></button>
                </div>
            </div>
            <div id="file-tree-container" class="bg-base-100 shadow-md rounded-b-lg">
                <?php echo renderSidebarFromMarkdown_v2($sidebarFile); ?>
            </div>
        </div>
    </aside>

    <main class="lg:col-span-3">
        <div class="card bg-base-100 shadow-xl min-h-[calc(100vh-5rem)]">
            <div id="panduan-content" class="card-body">
                 <div class="prose max-w-none lg:prose-lg text-center m-auto">
                    <h2 class="text-3xl font-bold">Selamat Datang di Panduan Sistem</h2>
                    <p class="text-base-content/70">Pilih topik panduan dari daftar di sebelah kiri atau gunakan kotak pencarian di atas untuk mencari instruksi secara global.</p>
                </div>
            </div>
        </div>
    </main>
</div>