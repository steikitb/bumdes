<?php
$judulHalaman = 'Formulir Piutang / Pinjaman';

// Ambil data untuk pre-load dropdown agar tampil langsung tanpa AJAX
$anggota = query("SELECT id, nama_lengkap, alamat, telepon FROM anggota WHERE status_keanggotaan = 'Aktif' ORDER BY nama_lengkap ASC");

// Ambil daftar rekening bank dari daftar akun untuk pencairan transfer
$rekening_bank = query("SELECT kode_akun, nama_akun FROM daftar_akun WHERE kode_akun LIKE '1.1.01%' AND kode_akun != ?", [$_PENGATURAN['akun_map_kas'] ?? '1.1.01.01']);
?>
<h1 class="text-3xl font-bold mb-6">Pencairan / Pemberian Pinjaman Baru</h1>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="<?= BASE_URL ?>sistem/proses/pinjaman_anggota/tambah_pinjaman.php" method="POST">
            <?php csrfInput(); ?>
            
            <div class="form-control mb-4 border-b border-base-300 pb-4">
                <label class="label"><span class="label-text">Pilih Debitur / Nasabah</span></label>
                <select name="anggota_id" id="pilihan-anggota" required>
                    <option value="">-- Ketik dan Pilih Nama Debitur --</option>
                    <?php foreach ($anggota as $item): ?>
                        <option value="<?= $item['id'] ?>" data-detail="<?= htmlspecialchars($item['telepon'] . ' - ' . substr($item['alamat'], 0, 30)) ?>"><?= htmlspecialchars($item['nama_lengkap']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (MODE_ENTITAS !== 'BUMDes'): ?>
                <div id="info-skor-kredit" class="mb-4 hidden"></div>
            <?php endif; ?>

             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text text-primary font-bold">Jenis Pinjaman (Sesuai COA SAK)</span></label>
                    <select name="jenis_pinjaman" class="select select-bordered font-bold" required>
                        <?php if (MODE_ENTITAS === 'BUMDes'): ?>
                            <option value="SPP">Pinjaman Simpan Pinjam Perempuan (SPP)</option>
                            <option value="UEP">Pinjaman Usaha Ekonomi Produktif (UEP)</option>
                        <?php endif; ?>
                        <option value="Umum">Pinjaman / Piutang Umum</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Tanggal Pencairan Dana</span></label>
                    <input type="date" name="tanggal_pinjaman" class="input input-bordered" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-bold text-lg">Plafon Pinjaman (Rp)</span></label>
                    <input type="text" name="jumlah_pokok" id="input_plafon" placeholder="0" class="input input-lg input-bordered format-rupiah text-xl font-bold" required>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Biaya Administrasi (Rp)</span></label>
                    <input type="text" name="biaya_admin" id="input_admin" placeholder="0" class="input input-lg input-bordered format-rupiah text-xl font-bold" required value="0">
                    <label class="label"><span class="label-text-alt text-warning">Dipotong langsung dari Plafon.</span></label>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Netto Diterima Nasabah (Rp)</span></label>
                    <input type="text" id="display_netto" class="input input-lg input-bordered text-success text-xl font-bold" readonly placeholder="0">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Jangka Waktu / Tenor (Bulan)</span></label>
                    <input type="number" name="jangka_waktu_bulan" placeholder="Contoh: 12" class="input input-bordered" required min="1">
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Bunga (% per Tahun)</span></label>
                    <input type="number" step="0.1" name="bunga_persen_tahun" placeholder="Contoh: 10.5" class="input input-bordered" required min="0">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 p-4 border border-base-300 rounded-lg bg-base-50">
                <div class="form-control">
                    <label class="label"><span class="label-text font-bold">Metode Pencairan (Uang Keluar)</span></label>
                    <select name="metode_pencairan" id="metode_pencairan" class="select select-bordered" required>
                        <option value="Tunai">Uang Tunai (Kasir)</option>
                        <option value="Transfer">Transfer / QRIS (Bank)</option>
                    </select>
                </div>

                <!-- Div Pilihan Rekening -->
                <div class="form-control hidden" id="div-rekening-bank-pinjam">
                    <label class="label"><span class="label-text text-info font-bold">Pilih Rekening Sumber Dana</span></label>
                    <select name="kode_akun_bank" class="select select-bordered w-full">
                        <?php foreach($rekening_bank as $rb): ?>
                            <option value="<?= $rb['kode_akun'] ?>"><?= htmlspecialchars($rb['nama_akun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-control mt-4">
                <label class="label"><span class="label-text">Keterangan / Tujuan Pinjaman (Opsional)</span></label>
                <textarea name="keterangan" class="textarea textarea-bordered" placeholder="Tujuan pinjaman, nomor SPK, dll."></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <a href="index.php?halaman=pinjaman_anggota/lihat_pinjaman" class="btn">Batal</a>
                <button type="submit" class="btn btn-primary shadow-md btn-lg">Cairkan Pinjaman</button>
            </div>
        </form>
    </div>
</div>

<?php if (MODE_ENTITAS !== 'BUMDes'): ?>
<dialog id="modal_rincian_skor" class="modal">
  <div class="modal-box">
    <h3 class="font-bold text-lg">Rincian Perhitungan Skor Kredit</h3>
    <div id="konten_rincian_skor" class="py-4"></div>
    <div class="modal-action">
      <form method="dialog"><button class="btn">Tutup</button></form>
    </div>
  </div>
</dialog>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const plafonInput = document.getElementById('input_plafon');
        const adminInput = document.getElementById('input_admin');
        const nettoDisplay = document.getElementById('display_netto');
        
        const formatRupiahDisplay = (angka) => new Intl.NumberFormat('id-ID').format(angka);

        const hitungNetto = () => {
            const plafon = parseFloat(plafonInput.value.replace(/\./g, '')) || 0;
            const admin = parseFloat(adminInput.value.replace(/\./g, '')) || 0;
            const netto = plafon - admin;
            nettoDisplay.value = formatRupiahDisplay(netto > 0 ? netto : 0);
        };

        plafonInput.addEventListener('input', hitungNetto);
        adminInput.addEventListener('input', hitungNetto);

        const metodePencairanEl = document.getElementById('metode_pencairan');
        const divRekeningBank = document.getElementById('div-rekening-bank-pinjam');
        const selectBank = divRekeningBank.querySelector('select');
        
        metodePencairanEl.addEventListener('change', function() {
            if (this.value === 'Transfer') {
                divRekeningBank.classList.remove('hidden');
                selectBank.required = true;
            } else {
                divRekeningBank.classList.add('hidden');
                selectBank.required = false;
            }
        });
    });
</script>