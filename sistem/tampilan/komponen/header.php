<?php
if (isset($_SESSION['wajib_ganti_sandi']) && $_SESSION['wajib_ganti_sandi']) {
    $halaman_sekarang = $_GET['halaman'] ?? '';
    if ($halaman_sekarang !== 'pengguna/ubah_kata_sandi_sendiri') {
        header('Location: ' . BASE_URL . 'index.php?halaman=pengguna/ubah_kata_sandi_sendiri');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judulHalaman ?? 'Sistem Akuntansi Koperasi' ?> - <?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'Koperasi') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            document.documentElement.setAttribute('data-theme', storedTheme);
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
            /* ==== Tom Select: seragam dengan DaisyUI select-bordered ==== */
            /* Warna dipinjam dari variabel daisyui (--b1/--b2/--bc/--p/--pc) sehingga
               otomatis mengikuti data-theme (dark/light), sama seperti <select class="select-bordered">. */
            .ts-wrapper .ts-control {
                background-color: oklch(var(--b1)) !important;
                background-image: none !important;
                border: 1px solid oklch(var(--b2)) !important;
                border-color: oklch(var(--b2)) !important;
                border-radius: var(--rounded-btn, 0.5rem) !important;
                color: oklch(var(--bc)) !important;
                min-height: 2.8rem;
                padding: 0.5rem 1rem;
                box-shadow: none !important;
            }
            .ts-wrapper .ts-control:hover { border-color: oklch(var(--b3)) !important; }
            .ts-wrapper .ts-control input { color: oklch(var(--bc)) !important; }
            .ts-wrapper .ts-control input::placeholder { color: oklch(var(--bc) / 0.6) !important; }
            .ts-wrapper .ts-control .item { color: oklch(var(--bc)) !important; }
            .ts-wrapper.multi .ts-control .item {
                background-color: oklch(var(--b2)) !important;
                color: oklch(var(--bc)) !important;
                border-radius: var(--rounded-btn, 0.25rem);
            }
            .ts-wrapper.multi .ts-control .item .remove { color: oklch(var(--bc)) !important; }
            .ts-wrapper .ts-control:after {
                border-color: oklch(var(--bc)) transparent transparent transparent !important;
                opacity: 0.6;
            }
            .ts-wrapper.focus .ts-control {
                border-color: oklch(var(--p)) !important;
                box-shadow: 0 0 0 1px oklch(var(--p)) !important;
                outline: none;
            }
            .ts-dropdown {
                background-color: oklch(var(--b1)) !important;
                border: 1px solid oklch(var(--b2)) !important;
                border-radius: var(--rounded-box, 1rem);
                box-shadow: var(--shadow-lg, 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1));
                z-index: 50;
                color: oklch(var(--bc)) !important;
                overflow: hidden;
            }
            .ts-dropdown .ts-dropdown-content { padding: 0.25rem; }
            .ts-dropdown .ts-dropdown-content .option {
                background-color: transparent !important;
                color: oklch(var(--bc)) !important;
                border-radius: var(--rounded-btn, 0.5rem);
                padding: 0.5rem 0.75rem;
                cursor: pointer;
            }
            .ts-dropdown .ts-dropdown-content .option:hover { background-color: oklch(var(--b2)) !important; }
            .ts-dropdown .ts-dropdown-content .option.active { background-color: oklch(var(--p)) !important; color: oklch(var(--pc)) !important; }
            .ts-dropdown .active { background-color: oklch(var(--b2)) !important; }
            .ts-dropdown .spinner { background: oklch(var(--b2)) !important; border-color: oklch(var(--bc)) !important; }

        .details-row > td { padding: 0 !important; border: none; }
        .details-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
            padding: 0 1rem;
        }
        .details-row.is-visible .details-content {
            max-height: 500px; 
            padding: 1rem;
        }
        
        .print-header-container {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body, html {
                background-color: white !important;
                color: black !important;
                margin: 0;
                padding: 0;
            }
            .drawer-side, .navbar, .footer, a[href="index.php?halaman=panduan"] {
                display: none !important;
            }
            .drawer, .drawer-content, main {
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100%;
            }
            .printable-area {
                box-shadow: none !important;
                border: none !important;
                background-color: white !important;
                color: black !important;
            }
            table, th, td {
                border-color: #666 !important;
            }
            tr {
                page-break-inside: avoid;
            }
            .card {
                background-color: transparent !important;
            }
             body * {
                visibility: hidden;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 1.5rem;
            }
            .print-header-container {
                display: flex !important;
                align-items: center;
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .print-logo img {
                max-height: 75px;
            }
            .print-title {
                text-align: center;
                flex-grow: 1;
            }
            .printable-area thead {
                background-color: #ffffffff !important;
                color: #000000ff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .printable-area thead th {
                color: #000000ff !important;
            }
        }
    </style>
</head>
<body class="bg-base-200 min-h-screen">
<div class="drawer <?php if(isset($_SESSION['user_login'])): ?>lg:drawer-open<?php endif; ?>">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col">
        <?php if(isset($_SESSION['user_login'])): ?>
        <div class="navbar bg-base-100 lg:hidden sticky top-0 z-30">
            <div class="flex-none">
                <label for="my-drawer-2" aria-label="open sidebar" class="btn btn-square btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </label>
            </div>
            <div class="flex-1">
                <a href="index.php" class="btn btn-ghost text-xl"><?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'Koperasi') ?></a>
            </div>
            <div class="flex-none gap-2">
                <button onclick="toggleTheme()" class="btn btn-ghost btn-circle">
                    <i class="fas fa-sun text-lg" id="theme-icon-mobile"></i>
                </button>
                <div class="dropdown dropdown-end">
                  <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full bg-neutral-focus text-neutral-content grid place-items-center">
                        <span><?= strtoupper(substr($_SESSION['user_data']['nama_lengkap'], 0, 1)) ?></span>
                    </div>
                  </label>
                  <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                    <li class="p-2">
                        <strong><?= htmlspecialchars($_SESSION['user_data']['nama_lengkap']) ?></strong><br>
                        <span class="badge badge-ghost">Admin</span>
                    </li>
                    <div class="divider my-1"></div>
                    <li><a href="index.php?halaman=pengguna/ubah_kata_sandi_sendiri">Ubah Kata Sandi</a></li>
                    <li>
                        <form action="<?= BASE_URL ?>sistem/proses/proses_logout.php" method="POST">
                            <?php csrfInput(); ?>
                            <button type="submit" class="w-full text-left">Keluar</button>
                        </form>
                    </li>
                  </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <main id="main-content" class="p-4 md:p-8 w-full mb-16">