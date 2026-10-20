<?php
$judulHalaman = 'Jurnal Pajak Penghasilan Badan';
cekAkses('admin');

$akun_utang_pajak = $_PENGATURAN['akun_map_utang_pajak'] ?? '2141';
$saldo_utang_pajak = getSaldoAkun($akun_utang_pajak, date('Y-m-d'));
?>
<?php  ?>
<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Halaman ini digunakan untuk mengelola administrasi pajak penghasilan koperasi Anda. Terdiri dari pencatatan pengakuan beban pajak dan pencatatan pembayaran pajak ke kas negara.</p>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- FORM 1: MENGAKUI BEBAN PAJAK -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-warning border-b pb-2">1. Pengakuan Beban Pajak (Akhir Tahun)</h2>
            <p class="text-sm">Gunakan form ini di akhir tahun buku setelah SHU bruto diketahui untuk mencatat Beban PPh Badan dan menimbulkan Utang Pajak.</p>
            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_pajak_penghasilan.php" method="POST" class="mt-4">
                 <?php csrfInput(); ?>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Jurnal</span></label>
                        <input type="date" name="tanggal" class="input input-bordered" value="<?= date('Y-12-31', strtotime('-1 year')) ?>" required>
                    </div>
                     <div class="form-control">
                        <label class="label"><span class="label-text">Jumlah Beban Pajak (Rp)</span></label>
                        <input type="text" name="jumlah" placeholder="0" class="input input-bordered format-rupiah" required>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Keterangan</span></label>
                    <input type="text" name="keterangan" placeholder="Contoh: Beban PPh Badan Tahun Buku 2024" class="input input-bordered" required>
                </div>
                <div class="mt-8">
                    <button type="submit" class="btn btn-warning w-full">Simpan Beban Pajak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FORM 2: PELUNASAN / PEMBAYARAN PAJAK -->
    <div class="card bg-base-100 shadow-xl border-t-4 border-success">
        <div class="card-body">
            <h2 class="card-title text-success border-b pb-2">2. Pembayaran Pajak (Pelunasan)</h2>
            <p class="text-sm">Gunakan form ini saat Anda melakukan penyetoran pajak ke kas negara (membayar via Bank/Kantor Pos). Ini akan mengurangi saldo Utang Pajak dan saldo Kas.</p>
            
            <div class="alert alert-info shadow-lg my-4">
                <div>
                    <h3 class="font-bold text-lg">Saldo Utang Pajak Saat Ini:</h3>
                    <div class="text-2xl font-black">Rp <?= number_format($saldo_utang_pajak, 2, ',', '.') ?></div>
                </div>
            </div>

            <form action="<?= BASE_URL ?>sistem/proses/jurnal/proses_bayar_pajak.php" method="POST">
                 <?php csrfInput(); ?>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Setor</span></label>
                        <input type="date" name="tanggal_bayar" class="input input-bordered" value="<?= date('Y-m-d') ?>" required>
                    </div>
                     <div class="form-control">
                        <label class="label"><span class="label-text">Jumlah Disetor (Rp)</span></label>
                        <input type="text" name="jumlah_bayar" placeholder="0" class="input input-bordered format-rupiah" required>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Nomor Bukti Penerimaan Negara (NTPN)</span></label>
                    <input type="text" name="keterangan" placeholder="Contoh: NTPN 1234567890ABCDEF" class="input input-bordered" required>
                </div>
                <div class="mt-8">
                    <button type="submit" class="btn btn-success w-full text-white" <?= ($saldo_utang_pajak <= 0) ? 'disabled' : '' ?>>Catat Penyetoran Pajak</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php  ?>