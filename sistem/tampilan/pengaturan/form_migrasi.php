<?php
$judulHalaman = 'Setup Awal & Migrasi Saldo';
cekAkses('admin');

$semua_akun = query("SELECT kode_akun, nama_akun, kelompok_akun, saldo_normal FROM daftar_akun ORDER BY kode_akun ASC");

// Ambil data saldo awal yang mungkin sudah tersimpan sebelumnya (Jurnal Trial Balance)
$jurnal_awal = query("SELECT kode_akun, debit, kredit FROM jurnal_umum WHERE nomor_bukti = 'SALDO-AWAL'");
$saldo_existing = [];
foreach($jurnal_awal as $j) {
    $saldo_existing[$j['kode_akun']] = ['debit' => $j['debit'], 'kredit' => $j['kredit']];
}
$tgl_awal = query("SELECT tanggal FROM jurnal_umum WHERE nomor_bukti = 'SALDO-AWAL' LIMIT 1");
if (!empty($tgl_awal) && !empty($tgl_awal[0]['tanggal'])) {
    $tgl_awal_value = $tgl_awal[0]['tanggal'];
} else {
    // Pengaturan "periode_akuntansi_mulai" sudah berupa tanggal lengkap (Y-m-d),
    // jadi JANGAN ditambahkan "-01" lagi -- sebelumnya hasilnya "2026-01-01-01"
    // yang tidak valid, dan <input type="date"> menolaknya sehingga kolom
    // tanggal tampil kosong pada kunjungan pertama.
    $mulai = trim($_PENGATURAN['periode_akuntansi_mulai'] ?? '');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $mulai)) {
        $tgl_awal_value = $mulai;
    } else {
        $tgl_awal_value = date('Y-m-01');
    }
}

// Kalkulasi nilai estimasi dari Master Data yang sudah diinput user
$estimasi_persediaan = query("SELECT SUM(stok * harga_rata_rata) as val FROM produk WHERE jenis_barang = 'Barang Dagangan' AND status_aktif = 1")[0]['val'] ?? 0;
$estimasi_perlengkapan = query("SELECT SUM(stok * harga_rata_rata) as val FROM produk WHERE jenis_barang = 'Perlengkapan' AND status_aktif = 1")[0]['val'] ?? 0;
$estimasi_aset_tetap = query("SELECT SUM(harga_perolehan) as val FROM aset_tetap")[0]['val'] ?? 0;
$estimasi_simpanan_pokok = query("SELECT SUM(jumlah) as val FROM simpanan WHERE jenis_simpanan = 'Pokok'")[0]['val'] ?? 0;
$estimasi_simpanan_wajib = query("SELECT SUM(jumlah) as val FROM simpanan WHERE jenis_simpanan = 'Wajib'")[0]['val'] ?? 0;
// Piutang = total pokok dikurangi pembayaran (jika ada, walau untuk setup awal biasanya belum ada bayar)
$estimasi_piutang = query("SELECT SUM(jumlah_pokok - (SELECT COALESCE(SUM(pokok_dibayar),0) FROM pembayaran_angsuran WHERE pinjaman_anggota_id = pinjaman_anggota.id)) as val FROM pinjaman_anggota")[0]['val'] ?? 0;

$akun_persediaan_map = $_PENGATURAN['akun_map_persediaan'] ?? '1.1.05.01';
$akun_perlengkapan_map = $_PENGATURAN['akun_map_perlengkapan'] ?? '1.1.06.01';
$akun_pokok_map = $_PENGATURAN['akun_map_simpanan_pokok'] ?? '3.1.01.01';
$akun_wajib_map = $_PENGATURAN['akun_map_simpanan_wajib'] ?? '3.1.02.01';
$akun_piutang_map = $_PENGATURAN['akun_map_piutang_pinjaman_anggota'] ?? '1.1.03.04';

// Aset tetap lebih kompleks karena bisa tersebar ke banyak akun (tanah, bangunan, mesin), jadi kita sediakan totalnya saja untuk diinput manual.
?>

<h1 class="text-3xl font-bold mb-6"><?= $judulHalaman ?></h1>
<p class="mb-6 text-base-content/80">Panduan penyusunan pembukuan awal. Sangat disarankan Anda melengkapi Data Master terlebih dahulu sebelum menyusun Neraca Saldo Awal di langkah kedua agar pembukuan selaras dan tidak ada barang/aset yang tertinggal.</p>

<div role="tablist" class="tabs tabs-lifted tabs-lg mb-6 w-full font-bold">
  <a role="tab" class="tab tab-active" id="tab-btn-1" onclick="switchWizardTab(1)">Tahap 1: Evaluasi Data Master</a>
  <a role="tab" class="tab" id="tab-btn-2" onclick="switchWizardTab(2)">Tahap 2: Susun Neraca Saldo Awal</a>
</div>

<!-- STEP 1: Evaluasi Data Master -->
<div id="tab-content-1" class="animate-fade-in">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Card Persediaan -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <h3 class="card-title text-primary"><i class="fas fa-box-open mr-2"></i>Persediaan & Barang</h3>
                <p class="text-sm opacity-80">Nilai total aset persediaan yang terdaftar di Buku Gudang/Sistem saat ini.</p>
                <div class="text-2xl font-black my-2">Rp <?= number_format($estimasi_persediaan, 0, ',', '.') ?></div>
                <div class="text-sm border-t border-base-300 pt-2">Perlengkapan ATK: Rp <?= number_format($estimasi_perlengkapan, 0, ',', '.') ?></div>
                <div class="card-actions justify-end mt-4">
                    <a href="index.php?halaman=produk_stok/form_produk&jenis=Barang%20Dagangan" class="btn btn-sm btn-outline">Input Stok Awal</a>
                </div>
            </div>
        </div>

        <!-- Card Aset Tetap -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <h3 class="card-title text-secondary"><i class="fas fa-building mr-2"></i>Aset Tetap Inventaris</h3>
                <p class="text-sm opacity-80">Nilai akumulasi Harga Perolehan atas aset gedung, mesin, kendaraan dll.</p>
                <div class="text-2xl font-black my-2">Rp <?= number_format($estimasi_aset_tetap, 0, ',', '.') ?></div>
                <div class="text-xs text-warning border-t border-base-300 pt-2">*Nilai penyusutan lampau (akumulasi) harus dihitung manual di tahap Neraca.</div>
                <div class="card-actions justify-end mt-4">
                    <a href="index.php?halaman=aset_tetap/form_aset" class="btn btn-sm btn-outline">Input Aset Baru</a>
                </div>
            </div>
        </div>

        <!-- Card Ekuitas/Modal -->
        <div class="card bg-base-100 shadow-xl border border-base-200">
            <div class="card-body">
                <h3 class="card-title text-accent"><i class="fas fa-hand-holding-usd mr-2"></i>Penyertaan Modal</h3>
                <p class="text-sm opacity-80">Total setoran permanen dari pemerintah desa atau anggota pendiri.</p>
                <div class="text-2xl font-black my-2">Rp <?= number_format($estimasi_simpanan_pokok + $estimasi_simpanan_wajib, 0, ',', '.') ?></div>
                <div class="text-xs border-t border-base-300 pt-2 text-success">*Otomatis ditarik dari riwayat Simpanan/Penyertaan.</div>
                <div class="card-actions justify-end mt-4">
                    <a href="index.php?halaman=simpanan/form_simpanan" class="btn btn-sm btn-outline">Input Setoran Awal</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end border-t border-base-300 pt-6">
        <button class="btn btn-primary btn-lg px-12 shadow-lg" onclick="switchWizardTab(2)">Lanjutkan ke Tahap 2 <i class="fas fa-arrow-right ml-2"></i></button>
    </div>
</div>

<!-- STEP 2: Susun Neraca Saldo -->
<div id="tab-content-2" class="hidden animate-fade-in">
    <div class="alert alert-info shadow-sm mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <h3 class="font-bold">Langkah Final</h3>
            <div class="text-sm">Anda dapat menekan tombol <b>"Tarik Nilai Master Data"</b> di bawah untuk menyalin angka dari Tahap 1 ke dalam formulir Neraca ini secara otomatis. Lengkapi kekurangannya (seperti Uang Kas Tunai, Saldo Bank, dan Utang Masa Lalu) hingga sisi Debit dan Kredit menghasilkan selisih (Rp 0).</div>
        </div>
    </div>

    <form action="<?= BASE_URL ?>sistem/proses/pengaturan/proses_migrasi.php" method="POST" id="form-migrasi">
        <?php csrfInput(); ?>
        
        <div class="card bg-base-100 shadow-xl mb-6 border-t-4 border-warning">
            <div class="card-body">
                <div class="flex flex-wrap justify-between items-end gap-4">
                    <div class="form-control max-w-md w-full">
                        <label class="label"><span class="label-text font-bold">Tanggal Pembukuan Dimulai (Neraca Awal)</span></label>
                        <input type="date" name="tanggal_neraca" class="input input-bordered input-lg bg-base-200 font-bold" required value="<?= htmlspecialchars($tgl_awal_value) ?>">
                    </div>
                    <button type="button" class="btn btn-secondary shadow-md" onclick="tarikDataMasterKeForm()"><i class="fas fa-magic mr-2"></i>Tarik Nilai Master Data ke Form</button>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl mb-32 border border-base-200">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table w-full table-zebra">
                        <thead class="bg-base-200 text-base-content/80">
                            <tr>
                                <th class="w-1/2">Kode & Nama Akun</th>
                                <th class="text-center">Kelompok</th>
                                <th class="text-center">Sifat Dasar</th>
                                <th class="text-right w-48 border-l border-base-300">Debit (Rp)</th>
                                <th class="text-right w-48 border-l border-base-300">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-saldo">
                            <?php foreach ($semua_akun as $akun): 
                                $debit_val = $saldo_existing[$akun['kode_akun']]['debit'] ?? 0;
                                $kredit_val = $saldo_existing[$akun['kode_akun']]['kredit'] ?? 0;
                            ?>
                            <tr class="hover">
                                <td>
                                    <div class="font-bold font-mono text-sm"><?= $akun['kode_akun'] ?></div>
                                    <div class="opacity-90"><?= htmlspecialchars($akun['nama_akun']) ?></div>
                                    <input type="hidden" name="kode_akun[]" value="<?= $akun['kode_akun'] ?>">
                                </td>
                                <td class="text-center text-xs opacity-70"><?= $akun['kelompok_akun'] ?></td>
                                <td class="text-center text-xs">
                                    <span class="badge badge-sm <?= $akun['saldo_normal'] == 'Debit' ? 'badge-info' : 'badge-error text-white' ?>"><?= $akun['saldo_normal'] ?></span>
                                </td>
                                <td class="p-2 border-l border-base-200">
                                    <input type="text" name="debit[]" id="input_debit_<?= str_replace('.', '_', $akun['kode_akun']) ?>" class="input input-sm input-bordered w-full text-right format-rupiah input-calc" value="<?= $debit_val > 0 ? $debit_val : '' ?>" placeholder="0">
                                </td>
                                <td class="p-2 border-l border-base-200">
                                    <input type="text" name="kredit[]" id="input_kredit_<?= str_replace('.', '_', $akun['kode_akun']) ?>" class="input input-sm input-bordered w-full text-right format-rupiah input-calc" value="<?= $kredit_val > 0 ? $kredit_val : '' ?>" placeholder="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Floating Footer for Totals and Submit -->
        <div class="fixed bottom-0 left-0 w-full bg-base-200 border-t border-base-300 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50 print:hidden lg:pl-80">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex gap-4 md:gap-8 flex-wrap justify-center">
                    <div class="bg-base-100 px-4 py-2 rounded shadow-sm border border-base-300">
                        <div class="text-xs font-bold uppercase opacity-70">Total Debit</div>
                        <div class="text-2xl font-black text-info" id="total-debit-display">Rp 0</div>
                    </div>
                    <div class="bg-base-100 px-4 py-2 rounded shadow-sm border border-base-300">
                        <div class="text-xs font-bold uppercase opacity-70">Total Kredit</div>
                        <div class="text-2xl font-black text-error" id="total-kredit-display">Rp 0</div>
                    </div>
                    <div class="bg-base-100 px-4 py-2 rounded shadow-sm border border-base-300">
                        <div class="text-xs font-bold uppercase opacity-70">Selisih (Balance)</div>
                        <div class="text-2xl font-black" id="selisih-display">Rp 0</div>
                    </div>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button type="button" class="btn btn-outline w-1/3 md:w-auto" onclick="switchWizardTab(1)">Kembali</button>
                    <button type="submit" id="btn-submit-migrasi" class="btn btn-primary flex-grow md:btn-wide shadow-lg" disabled>Simpan Ke Sistem</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// JS Logic untuk Wizard Migrasi Saldo
function switchWizardTab(tabNum) {
    const tab1Btn = document.getElementById('tab-btn-1');
    const tab2Btn = document.getElementById('tab-btn-2');
    const content1 = document.getElementById('tab-content-1');
    const content2 = document.getElementById('tab-content-2');

    if(tabNum === 1) {
        tab1Btn.classList.add('tab-active'); tab1Btn.classList.add('font-bold');
        tab2Btn.classList.remove('tab-active'); tab2Btn.classList.remove('font-bold');
        content1.classList.remove('hidden');
        content2.classList.add('hidden');
    } else {
        tab2Btn.classList.add('tab-active'); tab2Btn.classList.add('font-bold');
        tab1Btn.classList.remove('tab-active'); tab1Btn.classList.remove('font-bold');
        content2.classList.remove('hidden');
        content1.classList.add('hidden');
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}

function tarikDataMasterKeForm() {
    const dataEstimasi = {
        '<?= $akun_persediaan_map ?>': {val: <?= $estimasi_persediaan ?>, type: 'debit'},
        '<?= $akun_perlengkapan_map ?>': {val: <?= $estimasi_perlengkapan ?>, type: 'debit'},
        '<?= $akun_piutang_map ?>': {val: <?= $estimasi_piutang ?>, type: 'debit'},
        '<?= $akun_pokok_map ?>': {val: <?= $estimasi_simpanan_pokok ?>, type: 'kredit'},
        '<?= $akun_wajib_map ?>': {val: <?= $estimasi_simpanan_wajib ?>, type: 'kredit'}
    };

    let countFilled = 0;
    for (const [kodeAkun, obj] of Object.entries(dataEstimasi)) {
        if(obj.val > 0) {
            const inputId = `input_${obj.type}_${kodeAkun.replace(/\./g, '_')}`;
            const el = document.getElementById(inputId);
            if (el) {
                // Formatting to string without commas for raw value, event listener handles formatting
                el.value = obj.val;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                countFilled++;
            }
        }
    }
    
    if(countFilled > 0) {
        alert("Berhasil menarik " + countFilled + " data (Persediaan/Modal/Piutang) ke formulir Neraca.\n\nPerhatian: Anda masih perlu memasukkan manual total saldo aset tetap (ke dalam jenis masing-masing aset) dan akumulasi penyusutannya, serta saldo kas, bank, dan utang.");
    } else {
        alert("Tidak ada nilai master data yang bisa ditarik. Pastikan Anda telah menginput nilai di menu Aset/Barang/Penyertaan.");
    }
}
</script>