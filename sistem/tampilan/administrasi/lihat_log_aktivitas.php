<?php
$judulHalaman = 'Log Aktivitas Sistem';
cekAkses();

$search = $_GET['search'] ?? '';
$user = $_GET['user'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClauses[] = "(aksi LIKE ? OR keterangan LIKE ? OR ip_address LIKE ?)";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
}
if (!empty($user)) {
    $whereClauses[] = "nama_pengguna LIKE ?";
    $params[] = "%{$user}%";
}
if (!empty($tgl_mulai)) {
    $whereClauses[] = "date(timestamp) >= ?";
    $params[] = $tgl_mulai;
}
if (!empty($tgl_selesai)) {
    $whereClauses[] = "date(timestamp) <= ?";
    $params[] = $tgl_selesai;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$sql_select_from = "SELECT * FROM log_aktivitas";
$paginasi = query_paginasi($sql_select_from, $whereSql . " ORDER BY timestamp DESC", $params, 50);
$logs = $paginasi['data'];
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6">Log Aktivitas Sistem</h1>
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body p-4">
        <form method="GET" action="index.php">
            <input type="hidden" name="halaman" value="administrasi/lihat_log_aktivitas">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <input type="text" name="user" placeholder="Cari nama pengguna..." class="input input-bordered" value="<?= htmlspecialchars($user) ?>">
                <input type="text" name="search" placeholder="Cari aksi/keterangan..." class="input input-bordered" value="<?= htmlspecialchars($search) ?>">
                <input type="date" name="tgl_mulai" class="input input-bordered" value="<?= htmlspecialchars($tgl_mulai) ?>">
                <input type="date" name="tgl_selesai" class="input input-bordered" value="<?= htmlspecialchars($tgl_selesai) ?>">
                <button type="submit" class="btn btn-secondary md:col-span-full">Filter Log</button>
            </div>
        </form>
    </div>
</div>
<form action="<?= BASE_URL ?>sistem/proses/administrasi/proses_hapus_log_massal.php" method="POST" id="form-hapus-massal">
    <?php csrfInput(); ?>
    <div class="flex justify-end mb-4">
        <button type="submit" class="btn btn-error btn-sm" id="btn-hapus-terpilih" disabled onclick="return confirm('Yakin ingin menghapus log yang dipilih secara permanen?')">Hapus Terpilih</button>
    </div>
    <div class="overflow-x-auto">
        <table class="table table-compact w-full">
            <thead>
                <tr>
                    <th><input type="checkbox" id="check-all" class="checkbox checkbox-sm"></th>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center">Data log tidak ditemukan.</td></tr>
                <?php else: foreach ($logs as $log) : ?>
                    <tr>
                        <td><input type="checkbox" name="log_ids[]" value="<?= $log['id'] ?>" class="checkbox checkbox-sm check-item"></td>
                        <td><?= htmlspecialchars(date('d-m-Y H:i:s', strtotime($log['timestamp']))); ?></td>
                        <td><?= htmlspecialchars($log['nama_pengguna']); ?></td>
                        <td><?= htmlspecialchars($log['aksi']); ?></td>
                        <td><?= htmlspecialchars($log['keterangan']); ?></td>
                        <td><?= htmlspecialchars($log['ip_address']); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</form>
<div class="flex justify-center">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>
<?php  ?>