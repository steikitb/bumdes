<?php
$judulHalaman = 'Kelola Kategori Beban';
cekAkses('kelola_pengaturan');
$kategori = query("SELECT kb.*, da.nama_akun FROM kategori_beban kb JOIN daftar_akun da ON kb.kode_akun_terkait = da.kode_akun ORDER BY kb.nama_kategori ASC");
?>
<?php  ?>

<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <h1 class="text-3xl font-bold">Kelola Kategori Beban</h1>
    <a href="index.php?halaman=pengaturan/form_kategori_beban" class="btn btn-primary">Tambah Kategori</a>
</div>
<p class="mb-4">Gunakan halaman ini untuk mengatur kategori yang muncul di form pencatatan biaya dan memetakannya ke akun akuntansi yang sesuai.</p>
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kategori</th>
                <th>Akun Terkait</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kategori)): ?>
                <tr><td colspan="4" class="text-center">Belum ada kategori beban yang dibuat.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($kategori as $item): ?>
                <tr>
                    <th><?= $no++; ?></th>
                    <td><?= htmlspecialchars($item['nama_kategori']); ?></td>
                    <td><?= htmlspecialchars($item['kode_akun_terkait'] . ' - ' . $item['nama_akun']); ?></td>
                    <td class="flex gap-2">
                        <a href="index.php?halaman=pengaturan/form_kategori_beban&id=<?= $item['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <button onclick="konfirmasiHapus('sistem/proses/pengaturan/hapus_kategori_beban.php', '<?= $item['id']; ?>')" class="btn btn-sm btn-error">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php  ?>