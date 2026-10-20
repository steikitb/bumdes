<?php
$judulHalaman = 'Laporan Anggota Keluar';
cekAkses('kelola_anggota');

$anggota_keluar = query("
    SELECT 
        a.id, 
        a.nama_lengkap, 
        a.tanggal_keluar,
        COALESCE((SELECT SUM(jumlah) FROM simpanan s WHERE s.anggota_id = a.id AND s.jenis_simpanan = 'Pokok'), 0) as total_pokok,
        COALESCE((SELECT SUM(jumlah) FROM simpanan s WHERE s.anggota_id = a.id AND s.jenis_simpanan = 'Wajib'), 0) as total_wajib
    FROM anggota a
    WHERE a.status_keanggotaan = 'Keluar' AND a.dana_dikembalikan = 'Belum'
    ORDER BY a.tanggal_keluar ASC
");

?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Pengembalian Dana Anggota Keluar</h1>
</div>
<p class="mb-4 text-base-content/80">Halaman ini berisi daftar anggota yang telah keluar namun dananya (simpanan pokok & wajib) belum dikembalikan. Lakukan proses pengembalian dana setelah semua kewajiban anggota lunas.</p>

<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>Tanggal Keluar</th>
                <th class="text-right">Simpanan Pokok</th>
                <th class="text-right">Simpanan Wajib</th>
                <th class="text-right">Total Dana</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($anggota_keluar)): ?>
                <tr><td colspan="7" class="text-center">Tidak ada data anggota keluar yang dananya belum dikembalikan.</td></tr>
            <?php else: ?>
                <?php $no = 1; ?>
                <?php foreach ($anggota_keluar as $item) : 
                    $total_dana = $item['total_pokok'] + $item['total_wajib'];
                ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($item['tanggal_keluar']))); ?></td>
                    <td class="text-right">Rp <?= number_format($item['total_pokok'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($item['total_wajib'], 0, ',', '.'); ?></td>
                    <td class="text-right font-bold">Rp <?= number_format($total_dana, 0, ',', '.'); ?></td>
                    <td>
                        <?php if ($total_dana > 0): ?>
                        <button onclick="konfirmasiHapus('sistem/proses/anggota/proses_pengembalian_dana.php', '<?= $item['id']; ?>', 'Anda yakin ingin memproses pengembalian dana untuk anggota ini? Jurnal pengeluaran kas akan dibuat otomatis.')" class="btn btn-sm btn-success">Proses Pengembalian</button>
                        <?php else: ?>
                        <span class="badge badge-ghost">Tidak ada dana</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>