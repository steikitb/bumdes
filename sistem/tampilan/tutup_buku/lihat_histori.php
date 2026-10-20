<?php
$judulHalaman = 'Histori Tutup Buku';
$histori = query("SELECT * FROM histori_shu ORDER BY tahun DESC");
?>
<?php  ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Histori Proses Tutup Buku</h1>
    <a href="index.php?halaman=tutup_buku/form_tutup_buku" class="btn btn-primary">Lakukan Tutup Buku</a>
</div>

<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>Tahun</th>
                <th class="text-right">Total SHU</th>
                <th class="text-right">Alokasi Cadangan</th>
                <th class="text-right">Alokasi Jasa Anggota</th>
                <th class="text-right">Alokasi Dana Pengurus</th>
                <th class="text-right">Alokasi Dana Sosial</th>
                <th>Tanggal Proses</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($histori)): ?>
                <tr><td colspan="8" class="text-center">Belum ada histori proses tutup buku.</td></tr>
            <?php else: ?>
                <?php foreach ($histori as $item) : ?>
                <tr>
                    <td class="font-bold"><?= $item['tahun']; ?></td>
                    <td class="text-right">Rp <?= number_format($item['total_shu'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($item['alokasi_cadangan'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($item['alokasi_jasa_anggota'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($item['alokasi_dana_pengurus'], 0, ',', '.'); ?></td>
                    <td class="text-right">Rp <?= number_format($item['alokasi_dana_sosial'], 0, ',', '.'); ?></td>
                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($item['tanggal_proses']))); ?></td>
                    <td>
                        <form action="<?= BASE_URL ?>sistem/proses/tutup_buku/proses_batal_tutup_buku.php" method="POST" onsubmit="return confirm('SANGAT KRITIKAL: Yakin ingin membatalkan Tutup Buku tahun <?= $item['tahun'] ?>? Jurnal penutup akan dihapus dan Anda harus menghitung ulang SHU.')">
                            <?php csrfInput(); ?>
                            <input type="hidden" name="tahun" value="<?= $item['tahun'] ?>">
                            <button type="submit" class="btn btn-xs btn-error">Batalkan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php  ?>