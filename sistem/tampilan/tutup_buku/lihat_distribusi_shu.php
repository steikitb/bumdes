<?php
$lbl_judul = MODE_ENTITAS === 'BUMDes' ? 'Histori Penyetoran PADes / Bagi Hasil' : 'Histori Pembagian Keuntungan (SHU)';
$judulHalaman = $lbl_judul;
cekAkses('admin');
$tahun_filter = $_GET['tahun'] ?? date('Y') - 1;

$cek_pembayaran = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ['BYR-SHU-'.$tahun_filter], 's');
$sudah_dibayar = !empty($cek_pembayaran);

$cek_pembayaran_pades = query("SELECT id FROM jurnal_umum WHERE nomor_bukti = ? LIMIT 1", ['BYR-PADES-'.$tahun_filter], 's');
$sudah_disetor_pades = !empty($cek_pembayaran_pades);

$lbl_jasa = MODE_ENTITAS === 'BUMDes' ? 'Laba/Bagi Hasil' : 'SHU';
$lbl_penerima = MODE_ENTITAS === 'BUMDes' ? 'Penyerta Modal' : 'Anggota';

$distribusi = [];
if (MODE_ENTITAS === 'Koperasi') {
    $distribusi = query("
        SELECT hsa.*, a.nama_lengkap 
        FROM histori_shu_anggota hsa
        JOIN anggota a ON hsa.anggota_id = a.id
        WHERE hsa.tahun = ?
        ORDER BY hsa.total_diterima DESC
    ", [$tahun_filter], 'i');
}
?>
<?php  ?>
<style>
@media print {
    .no-print { display: none !important; }
    .printable-area { box-shadow: none; border: none; }
    .hidden-print-header { display: block !important; }
}
.hidden-print-header { display: none; }
</style>
<div class="flex justify-between items-center mb-6 no-print">
    <h1 class="text-3xl font-bold">Laporan Pembagian <?= $lbl_jasa ?> Tahun <?= $tahun_filter ?></h1>
    <div class="flex gap-2">
        <button onclick="window.print()" class="btn btn-secondary">Cetak</button>
        <?php if (MODE_ENTITAS === 'Koperasi'): ?>
            <a href="index.php?halaman=tutup_buku/form_distribusi_shu" class="btn btn-primary">Lakukan Distribusi Baru</a>
        <?php endif; ?>
    </div>
</div>

<div class="card bg-base-100 shadow mb-6 no-print">
    <div class="card-body p-4">
         <form method="GET" action="">
            <input type="hidden" name="halaman" value="tutup_buku/lihat_distribusi_shu">
            <div class="flex items-end gap-4 justify-between flex-wrap">
                <div class="flex gap-4">
                    <div class="form-control"><label class="label"><span class="label-text">Pilih Tahun</span></label><select name="tahun" class="select select-bordered"><?php for ($i = date('Y') - 1; $i >= date('Y') - 5; $i--): ?><option value="<?= $i ?>" <?= ($i == $tahun_filter) ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select></div>
                    <button type="submit" class="btn btn-secondary mt-9">Tampilkan</button>
                </div>

                <?php if(MODE_ENTITAS === 'Koperasi' && !empty($distribusi)): ?>
                <div class="bg-base-200 p-3 rounded-lg flex items-center gap-4">
                    <?php if($sudah_dibayar): ?>
                        <div class="text-success font-bold"><i class="fas fa-check-circle mr-2"></i><?= $lbl_jasa ?> Tahun <?= $tahun_filter ?> Telah Dicairkan</div>
                    <?php else: ?>
                        <div class="text-warning font-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Status: Masih di Utang Bagi Hasil</div>
                        <button type="button" class="btn btn-success" onclick="modal_cairkan_shu.showModal()">Cairkan Secara Massal</button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
         </form>
    </div>
</div>

<?php if (MODE_ENTITAS === 'BUMDes'): ?>
    <!-- BUMDes UI: Hanya Setor PADes -->
    <div class="card bg-base-100 shadow-xl mb-6">
         <div class="card-body">
             <h2 class="card-title text-2xl mb-4">Status Penyetoran PADes Tahun <?= $tahun_filter ?></h2>
             <?php
             $histori_shu_b = query("SELECT alokasi_jasa_anggota FROM histori_shu WHERE tahun = ?", [$tahun_filter]);
             if(empty($histori_shu_b)): ?>
                 <div class="alert alert-warning">Tutup buku dan perhitungan laba tahun <?= $tahun_filter ?> belum dilakukan.</div>
             <?php else: ?>
                 <p class="mb-4">Total Alokasi Bagi Hasil untuk Desa / Pemilik Modal berdasarkan Musdes: <br><strong class="text-3xl text-primary mt-2 block">Rp <?= number_format($histori_shu_b[0]['alokasi_jasa_anggota'], 2, ',', '.') ?></strong></p>
                 <?php if($sudah_disetor_pades): ?>
                     <div class="alert alert-success shadow-lg">
                         <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                         <span>Dana Bagi Hasil PADes tahun <?= $tahun_filter ?> telah sukses disetorkan ke Rekening Kas Desa (RKD).</span>
                     </div>
                 <?php else: ?>
                     <div class="alert alert-info shadow-lg">Dana saat ini masih mengendap di akun Utang Bagi Hasil. Silakan catat penyetoran jika dana sudah ditransfer secara riil ke Rekening Kas Desa.</div>
                     <button onclick="modal_setor_pades.showModal()" class="btn btn-success text-white mt-4 w-full md:w-auto shadow-md">Catat Setor PADes ke RKD</button>
                 <?php endif; ?>
             <?php endif; ?>
         </div>
     </div>
     <dialog id="modal_setor_pades" class="modal no-print">
         <div class="modal-box">
             <h3 class="font-bold text-lg mb-4">Setorkan Bagi Hasil PADes Tahun <?= $tahun_filter ?></h3>
             <form action="<?= BASE_URL ?>sistem/proses/tutup_buku/proses_setor_pades.php" method="POST">
                 <?php csrfInput(); ?>
                 <input type="hidden" name="tahun" value="<?= $tahun_filter ?>">
                 <div class="form-control mb-4">
                     <label class="label"><span class="label-text">Tanggal Penyetoran / Transfer BKU</span></label>
                     <input type="date" name="tanggal_bayar" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                 </div>
                 <div class="modal-action">
                     <button type="button" class="btn" onclick="modal_setor_pades.close()">Batal</button>
                     <button type="submit" class="btn btn-success text-white" onclick="return confirm('Anda yakin? Proses ini akan mendebit Utang Bagi Hasil dan mengkredit Rekening Kas/Bank BUMDes.')">Proses Penyetoran</button>
                 </div>
             </form>
         </div>
     </dialog>
<?php else: ?>
    <!-- Koperasi UI: Detail Per Individu -->
    <div class="overflow-x-auto printable-area">
        <div class="text-center mb-6 hidden-print-header">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($_PENGATURAN['nama_koperasi']) ?></h2>
            <h3 class="text-xl">Laporan Distribusi <?= $lbl_jasa ?> <?= $lbl_penerima ?></h3>
            <p>Tahun Buku: <?= $tahun_filter ?></p>
        </div>
        <table class="table table-compact w-full border border-base-300">
            <thead>
                <tr class="bg-base-200">
                    <th>No</th>
                    <th>Nama <?= $lbl_penerima ?></th>
                    <th class="text-right">Bagian Modal / PADes</th>
                    <th class="text-right">Bagian Usaha</th>
                    <th class="text-right">Total <?= $lbl_jasa ?> Diterima</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distribusi)): ?>
                    <tr><td colspan="5" class="text-center">Belum ada data distribusi untuk tahun ini.</td></tr>
                <?php else: ?>
                    <?php $no = 1; $total = 0; ?>
                    <?php foreach ($distribusi as $item) : $total += $item['total_diterima']; ?>
                    <tr>
                        <th><?= $no++; ?></th>
                        <td><?= htmlspecialchars($item['nama_lengkap']); ?></td>
                        <td class="text-right">Rp <?= number_format($item['jasa_modal_diterima'], 2, ',', '.'); ?></td>
                        <td class="text-right">Rp <?= number_format($item['jasa_usaha_diterima'], 2, ',', '.'); ?></td>
                        <td class="text-right font-bold text-primary">Rp <?= number_format($item['total_diterima'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($distribusi)): ?>
            <tfoot>
                <tr class="bg-base-200 font-bold">
                    <td colspan="4" class="text-right">TOTAL KESELURUHAN</td>
                    <td class="text-right text-lg text-primary">Rp <?= number_format($total, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>

    <dialog id="modal_cairkan_shu" class="modal no-print">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Cairkan <?= $lbl_jasa ?> Tahun <?= $tahun_filter ?></h3>
        <p class="py-4 text-sm">Metode pencairan massal akan memindahkan hak <?= $lbl_jasa ?> ke dalam <strong>Akun Tabungan/Simpanan Sukarela</strong> masing-masing penerima. Mereka dapat menariknya secara tunai kapan saja melalui menu tersebut.</p>
        <form action="<?= BASE_URL ?>sistem/proses/tutup_buku/proses_bayar_shu.php" method="POST">
            <?php csrfInput(); ?>
            <input type="hidden" name="tahun" value="<?= $tahun_filter ?>">
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Tanggal Pencairan / Pemindahan</span></label>
                <input type="date" name="tanggal_bayar" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="modal_cairkan_shu.close()">Batal</button>
                <button type="submit" class="btn btn-success text-white" onclick="return confirm('Anda yakin? Proses ini akan mendebit Utang Bagi Hasil dan mengkredit Simpanan Sukarela.')">Proses Pencairan Massal</button>
            </div>
        </form>
      </div>
    </dialog>
<?php endif; ?>
<?php  ?>