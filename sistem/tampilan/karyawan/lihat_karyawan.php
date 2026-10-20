<?php
$judulHalaman = 'Data Karyawan / Pengurus';
cekAkses('admin');

$karyawan = query("
    SELECT k.*, 
           COALESCE(a.nama_lengkap, k.nama_karyawan) as nama_tampil,
           COALESCE(a.telepon, k.telepon) as telp_tampil,
           j.nama_jabatan 
    FROM mst_karyawan k 
    LEFT JOIN anggota a ON k.anggota_id = a.id 
    LEFT JOIN mst_jabatan j ON k.jabatan_id = j.id 
    ORDER BY k.status_aktif DESC, nama_tampil ASC
");
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Data Karyawan & Pengurus</h1>
    <a href="index.php?halaman=karyawan/form_karyawan" class="btn btn-primary shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Karyawan
    </a>
</div>
<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr><th>No</th><th>Nama Karyawan/Pengurus</th><th>Jabatan</th><th>No. Telp</th><th>Tanggal Masuk</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($karyawan)): ?>
                        <tr><td colspan="7" class="text-center py-6 text-base-content/60">Belum ada data SDM karyawan/pengurus.</td></tr>
                    <?php else: $no=1; foreach($karyawan as $k): ?>
                        <tr class="hover">
                            <th><?= $no++ ?></th>
                            <td>
                                <div class="font-bold"><?= htmlspecialchars($k['nama_tampil']) ?></div>
                                <?php if($k['anggota_id']): ?>
                                    <div class="badge badge-info badge-xs opacity-80 mt-1">Terdaftar Anggota</div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-ghost font-semibold"><?= htmlspecialchars($k['nama_jabatan'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($k['telp_tampil'] ?? '-') ?></td>
                            <td><?= date('d M Y', strtotime($k['tanggal_masuk'])) ?></td>
                            <td>
                                <?php if($k['status_aktif']): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="flex gap-2">
                                <a href="index.php?halaman=karyawan/form_karyawan&id=<?= $k['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php if($k['status_aktif']): ?>
                                <button onclick="konfirmasiHapus('sistem/proses/karyawan/karyawan_hapus.php', '<?= $k['id'] ?>', 'Yakin ingin menonaktifkan karyawan ini dari jabatannya?')" class="btn btn-sm btn-error">Nonaktif</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>