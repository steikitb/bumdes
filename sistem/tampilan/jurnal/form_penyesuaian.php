<?php
$judulHalaman = 'Catatan Keuangan Akhir Bulan (Penyesuaian)';
cekAkses('proses_penyesuaian_jurnal');
$daftar_akun = query("SELECT kode_akun, nama_akun FROM daftar_akun ORDER BY kode_akun ASC");
$akun_json = [];
foreach ($daftar_akun as &$akun) {
    // Ambil saldo untuk ditampilkan secara real-time
    $akun['saldo'] = getSaldoAkun($akun['kode_akun'], date('Y-m-d'));
    $akun_json[] = $akun;
}

$akun_perlengkapan = $_PENGATURAN['akun_map_perlengkapan'] ?? '1.1.06.01';
$saldo_perlengkapan_saat_ini = getSaldoAkun($akun_perlengkapan, date('Y-m-d'));
?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Halaman ini memuat alat bantu otomatisasi maupun pencatatan manual atas kejadian akuntansi yang belum tercatat di menu operasional harian.</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    
    <!-- Kartu 1: Penyusutan Bulanan -->
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-6 flex flex-col h-full">
            <h2 class="card-title text-primary border-b border-base-200 pb-2"><i class="fas fa-calculator mr-2"></i> Penyusutan & Amortisasi Otomatis</h2>
            <p class="text-sm mt-2 flex-grow">Sistem akan menyusutkan nilai *semua* Aset Tetap (Peralatan, Mesin, Bangunan) dan Aset Tak Berwujud yang terdaftar di Master Data secara serentak untuk bulan yang dipilih.</p>
            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_penyusutan_bulanan.php" method="POST" class="mt-4">
                <?php csrfInput(); ?>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold">Periode Bulan</span></label>
                    <input type="month" name="periode_bulan" class="input input-bordered" value="<?= date('Y-m') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-full shadow" onclick="return confirm('Anda yakin ingin memproses Jurnal Beban Penyusutan untuk seluruh aset pada bulan ini?');">Buat Jurnal Penyusutan</button>
            </form>
        </div>
    </div>

    <!-- Kartu 2: Penyesuaian Perlengkapan ATK -->
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-6 flex flex-col h-full">
            <h2 class="card-title text-secondary border-b border-base-200 pb-2"><i class="fas fa-box-open mr-2"></i> Penyesuaian Pemakaian Perlengkapan</h2>
            <p class="text-sm mt-2 flex-grow">Perlengkapan yang dibeli akan dicatat sebagai Aset. Di akhir bulan, masukkan sisa fisik persediaan ATK untuk dijurnal sebagai Beban pemakaian secara otomatis.</p>
            <div class="text-sm font-bold text-info my-2">Saldo Perlengkapan di Sistem: Rp <?= number_format($saldo_perlengkapan_saat_ini, 0, ',', '.') ?></div>
            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_penyesuaian_perlengkapan.php" method="POST">
                <?php csrfInput(); ?>
                <input type="hidden" name="saldo_buku" value="<?= $saldo_perlengkapan_saat_ini ?>">
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Tanggal Jurnal</span></label>
                        <input type="date" name="tanggal_jurnal" class="input input-bordered" value="<?= date('Y-m-t') ?>" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Sisa Fisik (Rp)</span></label>
                        <input type="text" name="sisa_fisik" class="input input-bordered format-rupiah" placeholder="0" required <?= $saldo_perlengkapan_saat_ini <= 0 ? 'disabled' : '' ?>>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary w-full shadow" <?= $saldo_perlengkapan_saat_ini <= 0 ? 'disabled' : '' ?>>Catat Beban Pemakaian ATK</button>
            </form>
        </div>
    </div>

    <!-- Kartu 3: Penyisihan Piutang -->
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-6 flex flex-col h-full">
            <h2 class="card-title text-accent border-b border-base-200 pb-2"><i class="fas fa-user-shield mr-2"></i> Penyisihan Kerugian Piutang</h2>
            <p class="text-sm mt-2 flex-grow">Sistem akan melakukan Analisis Umur Piutang (Aging) pada kredit anggota yang macet dan membuat jurnal cadangan kerugian sesuai kebijakan BUMDes.</p>
            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_penyisihan_piutang.php" method="POST" class="mt-4">
                <?php csrfInput(); ?>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold">Tanggal Tutup Buku / Jurnal</span></label>
                    <input type="date" name="tanggal_jurnal" class="input input-bordered" value="<?= date('Y')-1 ?>-12-31" required>
                </div>
                <button type="submit" class="btn btn-accent text-white w-full shadow" onclick="return confirm('Anda yakin ingin memproses Jurnal Penyisihan Piutang Tak Tertagih?');">Buat Jurnal Penyisihan</button>
            </form>
        </div>
    </div>

    <!-- Kartu 4: Bunga Simpanan Sukarela (Hanya Koperasi) -->
    <?php if (MODE_ENTITAS !== 'BUMDes'): ?>
    <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body p-6 flex flex-col h-full">
            <h2 class="card-title text-warning border-b border-base-200 pb-2"><i class="fas fa-coins mr-2"></i> Bunga Simpanan Sukarela</h2>
            <p class="text-sm mt-2 flex-grow">Membagikan bunga/jasa langsung ke saldo rekening tabungan sukarela seluruh anggota secara serentak.</p>
            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_bunga_sukarela.php" method="POST" class="mt-4">
                <?php csrfInput(); ?>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Periode Bulan</span></label>
                        <input type="month" name="periode_bulan" class="input input-bordered" value="<?= date('Y-m') ?>" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-bold">Bunga (% Per Thn)</span></label>
                        <input type="number" step="0.1" name="bunga_persen_tahun" class="input input-bordered" placeholder="Misal: 4.5" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning w-full shadow" onclick="return confirm('Yakin ingin mendistribusikan bunga simpanan sukarela bulan ini?');">Bagikan Bunga</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Bagian Jurnal Manual -->
<div class="divider mt-8 mb-8 text-xl font-bold">Pencatatan Jurnal Manual</div>

<form action="<?= BASE_URL ?>sistem/proses/jurnal/simpan_penyesuaian.php" method="POST" id="form-jurnal">
    <?php csrfInput(); ?>
    <div class="card bg-base-100 shadow-xl border-t-4 border-neutral">
        <div class="card-body p-6">
            <p class="text-sm mb-4">Gunakan kolom di bawah ini HANYA untuk penyesuaian akuntansi tingkat lanjut (Contoh: Sewa dibayar dimuka, Koreksi kesalahan, dll).</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 bg-base-200 p-4 rounded-lg">
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Tanggal Bukti Jurnal</span></label>
                    <input type="date" name="tanggal" class="input input-bordered" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Uraian / Keterangan Transaksi</span></label>
                    <input type="text" name="keterangan" placeholder="Contoh: Penyesuaian Sewa Dibayar Dimuka" class="input input-bordered" required>
                </div>
            </div>
            
            <div id="data-akun-container" data-akun='<?= htmlspecialchars(json_encode($akun_json), ENT_QUOTES, 'UTF-8') ?>' class="hidden"></div>
            
            <div class="overflow-x-auto rounded border border-base-200">
                <table class="table w-full">
                    <thead class="bg-neutral text-neutral-content">
                        <tr><th class="w-2/5">Kode & Nama Akun</th><th class="w-1/4 text-right">Debit (Rp)</th><th class="w-1/4 text-right">Kredit (Rp)</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody id="baris-jurnal"></tbody>
                </table>
            </div>
            
            <div class="flex justify-between items-center mt-4">
                <button type="button" id="btn-tambah-baris" class="btn btn-outline btn-sm"><i class="fas fa-plus mr-2"></i>Tambah Baris</button>
            </div>
            
            <div class="bg-base-200 p-4 rounded-lg mt-6 flex flex-col md:flex-row justify-between items-center gap-4 border border-base-300">
                 <div class="text-lg flex gap-6">
                     <div class="flex flex-col items-center">
                         <span class="text-xs uppercase font-bold opacity-70">Total Debit</span>
                         <span class="font-black text-info font-mono" id="total-debit">0</span>
                     </div>
                     <div class="flex flex-col items-center">
                         <span class="text-xs uppercase font-bold opacity-70">Total Kredit</span>
                         <span class="font-black text-error font-mono" id="total-kredit">0</span>
                     </div>
                 </div>
                <button type="submit" class="btn btn-neutral btn-wide shadow-lg text-white">Simpan Jurnal Manual</button>
            </div>
        </div>
    </div>
</form>

<table class="hidden">
    <tbody id="template-baris">
        <tr class="hover">
            <td class="p-2 border-b border-base-200">
                <select name="kode_akun[]" class="select-akun select-bordered w-full" required>
                    <option value="">-- Pilih Akun --</option>
                </select>
            </td>
            <td class="p-2 border-b border-base-200"><input type="text" name="debit[]" class="input-debit input input-bordered w-full text-right font-mono format-rupiah" placeholder="0"></td>
            <td class="p-2 border-b border-base-200"><input type="text" name="kredit[]" class="input-kredit input input-bordered w-full text-right font-mono format-rupiah" placeholder="0"></td>
            <td class="p-2 border-b border-base-200 text-center"><button type="button" class="btn-hapus-baris btn btn-ghost btn-sm text-error"><i class="fas fa-trash pointer-events-none"></i></button></td>
        </tr>
    </tbody>
</table>