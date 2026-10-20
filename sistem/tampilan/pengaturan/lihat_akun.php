<?php
$judulHalaman = 'Manajemen Daftar Akun';
cekAkses('kelola_daftar_akun');

$search = trim($_GET['search'] ?? '');
$whereClause = '';
$params = [];

if ($search !== '') {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE kode_akun LIKE ? OR nama_akun LIKE ? OR kelompok_akun LIKE ?";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$sql_select_from = "SELECT * FROM daftar_akun";
$paginasi = query_paginasi($sql_select_from, $whereClause . " ORDER BY kode_akun ASC", $params, 25);
$akun_list = $paginasi['data'];
?>

<?php  ?>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Manajemen Daftar Akun (Chart of Accounts)</h1>
    <div class="flex gap-2">
        <button type="button" onclick="modal_import_csv.showModal()" class="btn btn-secondary">
            <i class="fas fa-file-import mr-2"></i> Import CSV
        </button>
        <a href="index.php?halaman=pengaturan/form_akun" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Tambah Akun Baru
        </a>
    </div>
</div>

<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="pengaturan/lihat_akun">
            <div class="join w-full">
                <input type="text" name="search" placeholder="Cari kode, nama, atau kelompok akun..." class="input input-bordered join-item w-full" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-secondary join-item">Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Kelompok</th>
                <th>Unit Usaha</th>
                <th>Saldo Normal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($akun_list)): ?>
                <tr><td colspan="6" class="text-center">Data tidak ditemukan.</td></tr>
            <?php else: ?>
                <?php foreach ($akun_list as $item) : ?>
                <tr>
                    <td class="font-mono font-bold"><?= htmlspecialchars($item['kode_akun']); ?></td>
                    <td><?= htmlspecialchars($item['nama_akun']); ?></td>
                    <td><?= htmlspecialchars($item['kelompok_akun']); ?></td>
                    <td><span class="badge badge-ghost badge-sm"><?= htmlspecialchars($item['unit_usaha']); ?></span></td>
                    <td><?= htmlspecialchars($item['saldo_normal']); ?></td>
                    <td class="flex gap-2">
                        <a href="index.php?halaman=pengaturan/form_akun&id=<?= htmlspecialchars($item['kode_akun']); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <button onclick="konfirmasiHapus('sistem/proses/pengaturan/hapus_akun.php', '<?= htmlspecialchars($item['kode_akun']); ?>', 'Yakin ingin menghapus akun ini? Akun yang sudah memiliki transaksi tidak dapat dihapus.')" class="btn btn-sm btn-error">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="flex justify-center">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>

<dialog id="modal_import_csv" class="modal">
  <div class="modal-box">
    <form method="dialog"><button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button></form>
    <h3 class="font-bold text-lg mb-4">Import Akun dari CSV (2 Kolom)</h3>
    <div class="text-sm mb-4">
        <p class="mb-2">Upload file CSV yang berisi 2 kolom dengan format:</p>
        <ul class="list-disc ml-6 mb-2">
            <li><b>Kolom 1:</b> Kode & Nama Akun (contoh: <i>1.1.01.01 Kas Tunai</i>)</li>
            <li><b>Kolom 2:</b> Saldo Normal (contoh: <i>Debit</i> / <i>Kredit</i>)</li>
        </ul>
        <p>Sistem akan otomatis mendeteksi <i>Kelompok Akun</i> berdasarkan digit pertama kode akun sesuai standar akuntansi.</p>
    </div>
    <form action="<?= BASE_URL ?>sistem/proses/pengaturan/import_akun_csv.php" method="POST" enctype="multipart/form-data">
        <?php csrfInput(); ?>
        <div class="form-control mb-4">
            <label class="label"><span class="label-text">File Spreadsheet (CSV)</span></label>
            <input type="file" name="file_csv" accept=".csv" class="file-input file-input-bordered w-full" required>
        </div>
        <div class="form-control mb-4">
            <label class="label cursor-pointer">
                <span class="label-text font-bold">File memiliki baris judul (header) di baris pertama</span>
                <input type="checkbox" name="has_header" class="checkbox" checked />
            </label>
        </div>
        <div class="modal-action">
            <button type="submit" class="btn btn-primary w-full">Mulai Import</button>
        </div>
    </form>
  </div>
</dialog>

<?php  ?>