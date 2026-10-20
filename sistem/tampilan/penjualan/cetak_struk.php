<?php
// B3. Penyesuaian Resolusi Cetak Struk (Thermal)
if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID Penjualan tidak valid."); }
$idPenjualan = (int)$_GET['id'];
$penjualan_data = query("SELECT p.*, a.nama_lengkap FROM penjualan p LEFT JOIN anggota a ON p.anggota_id = a.id WHERE p.id = ?", [$idPenjualan]);
if (empty($penjualan_data)) { die("Data tidak ditemukan."); }
$p = $penjualan_data[0];
$detail = query("SELECT dp.*, pr.nama_produk FROM detail_penjualan dp JOIN produk pr ON dp.produk_id = pr.id WHERE dp.penjualan_id = ?", [$idPenjualan]);

$nama_pelanggan = $p['nama_lengkap'] ?? ($p['nama_pelanggan_umum'] ? $p['nama_pelanggan_umum'] . ' (Umum)' : 'UMUM');
$total_bersih = $p['total'] - $p['diskon'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= htmlspecialchars($p['nomor_faktur']) ?></title>
    <style>
        @page { margin: 0; }
        body { font-family: monospace; font-size: 11px; margin: 0 auto; padding: 5px; width: 58mm; color: black; background: white; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 5px; }
        .border-top { border-top: 1px dashed black; padding-top: 3px; margin-top: 3px; }
        .border-bottom { border-bottom: 1px dashed black; padding-bottom: 3px; margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 1px 0; }
        .item-name { display: block; word-wrap: break-word; max-width: 100%; }
    </style>
</head>
<body>
    <div class="text-center font-bold" style="font-size: 13px;">
        <?= htmlspecialchars($_PENGATURAN['nama_koperasi'] ?? 'KOPERASI') ?>
    </div>
    <div class="text-center border-bottom mb-2" style="font-size: 10px;">
        <?= htmlspecialchars($_PENGATURAN['alamat_koperasi'] ?? '') ?>
    </div>
    
    <table>
        <tr><td style="width:25px">No</td><td>: <?= htmlspecialchars($p['nomor_faktur']) ?></td></tr>
        <tr><td>Tgl</td><td>: <?= date('d/m/y H:i', strtotime($p['tanggal_penjualan'])) ?></td></tr>
        <tr><td>Cus</td><td>: <?= htmlspecialchars($nama_pelanggan) ?></td></tr>
    </table>

    <div class="border-top border-bottom">
        <table>
            <?php foreach($detail as $d): ?>
            <tr>
                <td colspan="3"><span class="item-name"><?= htmlspecialchars($d['nama_produk']) ?></span></td>
            </tr>
            <tr>
                <td style="width: 30%"><?= $d['kuantitas'] ?> x</td>
                <td style="width: 35%"><?= number_format($d['harga_saat_transaksi'], 0, '', '.') ?></td>
                <td style="width: 35%" class="text-right"><?= number_format($d['subtotal'], 0, '', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <table>
        <tr class="text-right">
            <td style="width: 50%">Subtotal :</td>
            <td>Rp <?= number_format($p['total'], 0, '', '.') ?></td>
        </tr>
        <?php if ($p['diskon'] > 0): ?>
        <tr class="text-right">
            <td>Diskon :</td>
            <td>-Rp <?= number_format($p['diskon'], 0, '', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr class="font-bold text-right border-top">
            <td>TOTAL :</td>
            <td>Rp <?= number_format($total_bersih, 0, '', '.') ?></td>
        </tr>
        <tr class="text-right">
            <td><?= htmlspecialchars($p['metode_pembayaran']) ?> :</td>
            <td>Rp <?= number_format($total_bersih, 0, '', '.') ?></td>
        </tr>
    </table>
    
    <div class="border-top text-center" style="margin-top: 10px; font-size: 10px;">
        Terima Kasih<br>Brg tidak dapat ditukar
    </div>
    
    <script>
        // B4. Otomatis Print & Close
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        }
    </script>
</body>
</html>