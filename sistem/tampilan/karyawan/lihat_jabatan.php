<?php
$judulHalaman = 'Data Jabatan';
cekAkses('admin');
$jabatan = query("SELECT * FROM mst_jabatan ORDER BY nama_jabatan ASC");
?>
<?php  ?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Data Jabatan</h1>
    <a href="index.php?halaman=karyawan/form_jabatan" class="btn btn-primary">Tambah Jabatan</a>
</div>
<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr><th>No</th><th>Nama Jabatan</th><th class="text-right">Gaji Pokok Dasar</th><th class="text-right">Tunjangan Dasar</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php if(empty($jabatan)): ?>
                        <tr><td colspan="5" class="text-center py-4">Belum ada data jabatan.</td></tr>
                    <?php else: $no=1; foreach($jabatan as $j): ?>
                        <tr>
                            <th><?= $no++ ?></th>
                            <td class="font-bold"><?= htmlspecialchars($j['nama_jabatan']) ?></td>
                            <td class="text-right">Rp <?= number_format($j['gaji_pokok'], 0, ',', '.') ?></td>
                            <td class="text-right">Rp <?= number_format($j['tunjangan_jabatan'], 0, ',', '.') ?></td>
                            <td>
                                <a href="index.php?halaman=karyawan/form_jabatan&id=<?= $j['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <button onclick="konfirmasiHapus('sistem/proses/karyawan/jabatan_hapus.php', '<?= $j['id'] ?>', 'Yakin ingin menghapus jabatan ini?')" class="btn btn-sm btn-error">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php  ?>