<?php
$judulHalaman = 'Riwayat Penggajian & THR';
cekAkses('admin');

$paginasi = query_paginasi("
    SELECT p.*, 
           COALESCE(a.nama_lengkap, k.nama_karyawan) as nama_tampil, 
           j.nama_jabatan 
    FROM trx_penggajian p 
    JOIN mst_karyawan k ON p.karyawan_id = k.id 
    LEFT JOIN anggota a ON k.anggota_id = a.id
    LEFT JOIN mst_jabatan j ON k.jabatan_id = j.id
", "ORDER BY p.tanggal_pembayaran DESC, p.id DESC", [], '', 25);
$gaji = $paginasi['data'];
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Riwayat Pembayaran Gaji / THR</h1>
    <a href="index.php?halaman=karyawan/form_penggajian" class="btn btn-primary shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Bayar Gaji / THR Baru
    </a>
</div>
<div class="card bg-base-100 shadow-xl mb-6 border border-base-200">
    <div class="card-body p-0">
        <div class="overflow-x-auto rounded-lg">
            <table class="table w-full">
                <thead class="bg-base-200">
                    <tr><th>Tanggal</th><th>Periode</th><th>Nama Karyawan</th><th>Jenis</th><th class="text-right">Total Bersih</th><th class="text-center">Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($gaji)): ?>
                        <tr><td colspan="7" class="text-center py-6 text-base-content/60">Belum ada riwayat penggajian.</td></tr>
                    <?php else: foreach($gaji as $g): ?>
                        <tr class="hover <?= $g['status_batal'] == 1 ? 'opacity-50 line-through bg-base-200' : '' ?>">
                            <td><?= date('d M Y', strtotime($g['tanggal_pembayaran'])) ?></td>
                            <td class="font-bold text-primary"><?= htmlspecialchars($g['periode_bulan']) ?></td>
                            <td>
                                <div class="font-bold"><?= htmlspecialchars($g['nama_tampil']) ?></div>
                                <div class="text-xs opacity-70"><?= htmlspecialchars($g['nama_jabatan'] ?? '-') ?></div>
                            </td>
                            <td><span class="badge badge-ghost"><?= htmlspecialchars($g['jenis_pembayaran']) ?></span></td>
                            <td class="text-right font-mono font-bold text-success">Rp <?= number_format($g['total_bersih'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <?php if($g['status_batal'] == 1): ?>
                                    <span class="badge badge-error">Batal</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($g['status_batal'] == 0): ?>
                                    <button onclick="konfirmasiHapus('sistem/proses/karyawan/penggajian_batal.php', '<?= $g['id'] ?>', 'Yakin ingin membatalkan transaksi gaji ini? Jurnal akan di-reverse secara otomatis.')" class="btn btn-xs btn-error">Batalkan</button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="flex justify-center mb-6">
    <?php render_paginasi($paginasi['total_halaman'], $paginasi['halaman_sekarang']); ?>
</div>
<?php  ?>