<?php
$judulHalaman = 'Form Penggajian & THR';
cekAkses('admin');

$karyawan_list = query("
    SELECT k.id, a.nama_lengkap, j.nama_jabatan 
    FROM mst_karyawan k 
    JOIN anggota a ON k.anggota_id = a.id
    LEFT JOIN mst_jabatan j ON k.jabatan_id = j.id 
    WHERE k.status_aktif = 1 
    ORDER BY a.nama_lengkap ASC
");
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6">Bayar Gaji / THR Baru</h1>
<div class="card bg-base-100 shadow-xl max-w-4xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/karyawan/penggajian_proses.php" method="POST">
            <?php csrfInput(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Pilih Karyawan</span></label>
                    <select name="karyawan_id" id="karyawan_id" required>
                        <option value="">-- Ketik Nama Karyawan --</option>
                        <?php foreach($karyawan_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_lengkap']) ?> (<?= htmlspecialchars($k['nama_jabatan'] ?? '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Jenis Pembayaran</span></label>
                    <select name="jenis_pembayaran" class="select select-bordered" required>
                        <option value="Gaji Bulanan">Gaji Bulanan</option>
                        <option value="THR">Tunjangan Hari Raya (THR)</option>
                        <option value="Bonus">Bonus Prestasi / Lainnya</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Tanggal Pembayaran</span></label>
                    <input type="date" name="tanggal_pembayaran" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Periode Bulan</span></label>
                    <input type="month" name="periode_bulan" class="input input-bordered" required value="<?= date('Y-m') ?>">
                </div>
            </div>

            <div class="divider">Rincian Penerimaan</div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Gaji Pokok (Rp)</span></label>
                    <input type="text" name="gaji_pokok" id="gaji_pokok" class="input input-bordered format-rupiah" placeholder="0" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Tunjangan (Rp)</span></label>
                    <input type="text" name="tunjangan" id="tunjangan" class="input input-bordered format-rupiah" placeholder="0" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text text-error font-bold">Potongan / Kasbon (Rp)</span></label>
                    <input type="text" name="potongan" id="potongan" class="input input-bordered border-error format-rupiah" placeholder="0" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Keterangan (Opsional)</span></label>
                    <input type="text" name="keterangan" class="input input-bordered" placeholder="Catatan tambahan">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Metode Pencairan (Uang Keluar)</span></label>
                    <select name="metode_pembayaran" class="select select-bordered" required>
                        <option value="Tunai">Tunai (Kas)</option>
                        <option value="Transfer">Transfer Bank</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-success shadow-lg mt-4">
                <div>
                    <h3 class="font-bold text-lg">TOTAL BERSIH DITERIMA:</h3>
                    <div class="text-3xl font-black">Rp <span id="total-bersih-display">0</span></div>
                    <input type="hidden" name="total_bersih" id="total_bersih" value="0">
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=karyawan/lihat_penggajian" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg shadow-lg">Proses Pembayaran</button>
            </div>
        </form>
    </div>
</div>
<?php  ?>