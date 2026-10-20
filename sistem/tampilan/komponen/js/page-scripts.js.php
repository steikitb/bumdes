<script>
const pageScripts = {
    initTomSelect: (selector) => {
        document.querySelectorAll(selector).forEach(el => {
            if (!el.tomselect) new TomSelect(el, { 
                create: false, 
                sortField: { field: "text", direction: "asc" },
                plugins: ['option_balance']
            });
        });
    },
    initFormAkun: () => {
        const subKategoriSelect = document.getElementById('pilihan_sub_kategori');
        const inputKodeAkun = document.getElementById('input_kode_akun');
        const selectKelompok = document.getElementById('select_kelompok_akun');
        const selectSaldo = document.getElementById('select_saldo_normal');
        const selectArus = document.getElementById('select_arus_kas');
        
        if (subKategoriSelect && inputKodeAkun) {
            subKategoriSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                const prefix = this.value;
                const kelompok = selectedOpt.getAttribute('data-kelompok');
                const saldo = selectedOpt.getAttribute('data-saldo');
                const arus = selectedOpt.getAttribute('data-arus');
                
                if (prefix) {
                    if(selectKelompok) selectKelompok.value = kelompok;
                    if(selectSaldo) selectSaldo.value = saldo;
                    if(selectArus) selectArus.value = arus;

                    fetch(`<?= BASE_URL ?>sistem/ajax/get_next_akun.php?prefix=${encodeURIComponent(prefix)}`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.success && data.code) {
                                inputKodeAkun.value = data.code;
                            }
                        }).catch(e => console.error("Gagal mendapat kode otomatis", e));
                }
            });
        }
    },
    initDasbor: () => {
        const ctx = document.getElementById('grafikKinerja');
        if (!ctx) return;
        const dataGrafikJson = ctx.getAttribute('data-grafik');
        if (!dataGrafikJson) return;
        const dataGrafik = JSON.parse(dataGrafikJson);
        if (typeof chartInstance !== 'undefined' && chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx.getContext('2d'), { 
            type: 'bar', 
            data: { 
                labels: dataGrafik.labels, 
                datasets: [
                    { label: 'Pendapatan Total', data: dataGrafik.pendapatan, backgroundColor: 'rgba(54, 162, 235, 0.8)', borderRadius: 4 }, 
                    { label: 'Beban Operasional', data: dataGrafik.beban, backgroundColor: 'rgba(255, 99, 132, 0.8)', borderRadius: 4 }
                ] 
            }, 
            options: { 
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(150, 150, 150, 0.1)' } }, x: { grid: { display: false } } }, 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            } 
        });
    },
    initFormPenurunanNilai: () => {
        const pilihanProdukEl = document.getElementById('pilihan-produk');
        if (!pilihanProdukEl) return;
        const pilihanProduk = new TomSelect(pilihanProdukEl, { create: false });
        const hargaBeliDisplay = document.getElementById('harga-beli-display');
        const stokDisplay = document.getElementById('stok-display');
        const nrvInput = document.getElementById('nrv-input');
        const totalKerugianDisplay = document.getElementById('total-kerugian-display');
        const totalKerugianInput = document.getElementById('total-kerugian-input');
        const btnSimpan = document.getElementById('btn-simpan');
        const jenisPenyesuaian = document.getElementById('jenis_penyesuaian');
        
        const formatRupiahDisplay = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(angka);
        
        const updateCalculations = () => {
            const selectedValue = pilihanProduk.getValue();
            if (!selectedValue) {
                btnSimpan.disabled = true;
                totalKerugianDisplay.textContent = formatRupiahDisplay(0);
                totalKerugianInput.value = 0;
                return;
            };
            const selectedOriginalOption = pilihanProdukEl.querySelector(`option[value="${selectedValue}"]`);
            if (!selectedOriginalOption) return;
            
            const hargaBeli = parseFloat(selectedOriginalOption.dataset.hargaBeli) || 0;
            const stok = parseInt(stokDisplay.value) || 0;
            const nrv = parseFloat(nrvInput.value.replace(/\./g, '')) || 0;
            const jenis = jenisPenyesuaian ? jenisPenyesuaian.value : 'penurunan';
            
            let totalKerugian = 0;
            if (jenis === 'penurunan' && nrv < hargaBeli) {
                totalKerugian = (hargaBeli - nrv) * stok;
                document.getElementById('judul-total').textContent = 'Total Kerugian Penurunan Nilai';
                document.getElementById('desc-total').textContent = 'Akan dijurnal sebagai Beban';
                totalKerugianDisplay.className = 'stat-value text-error';
            } else if (jenis === 'pemulihan' && nrv > hargaBeli) { 
                totalKerugian = (nrv - hargaBeli) * stok;
                document.getElementById('judul-total').textContent = 'Total Pemulihan Penurunan Nilai';
                document.getElementById('desc-total').textContent = 'Akan dijurnal sebagai Pendapatan Pemulihan';
                totalKerugianDisplay.className = 'stat-value text-success';
            }
            
            totalKerugianDisplay.textContent = formatRupiahDisplay(totalKerugian);
            totalKerugianInput.value = totalKerugian;
            btnSimpan.disabled = totalKerugian <= 0;
        };
        
        pilihanProduk.on('change', function (value) {
            const selectedOriginalOption = pilihanProdukEl.querySelector(`option[value="${value}"]`);
            if (selectedOriginalOption) {
                hargaBeliDisplay.value = formatRupiahDisplay(selectedOriginalOption.dataset.hargaBeli);
                stokDisplay.value = selectedOriginalOption.dataset.stok;
            } else {
                hargaBeliDisplay.value = '';
                stokDisplay.value = '';
            }
            updateCalculations();
        });
        nrvInput.addEventListener('input', updateCalculations);
        if (jenisPenyesuaian) jenisPenyesuaian.addEventListener('change', updateCalculations);
    },
    
    // Helper function untuk dropdown anggota
    initAnggotaTomSelect: (selector) => {
        const el = typeof selector === 'string' ? document.getElementById(selector) : selector;
        if (!el || el.disabled) return null;
        return new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            render: {
                option: function(data, escape) {
                    const optEl = data.$option || data.node;
                    const detail = optEl ? (optEl.getAttribute('data-detail') || '') : '';
                    if (detail) {
                        return `<div><span class="font-semibold">${escape(data.text)}</span><div class="text-xs opacity-60">${escape(detail)}</div></div>`;
                    }
                    return `<div>${escape(data.text)}</div>`;
                },
                item: function(data, escape) { return `<div>${escape(data.text)}</div>`; }
            }
        });
    },

    initFormPenjualan: () => {
        const formPenjualan = document.getElementById('form-penjualan');
        const pilihanAnggotaEl = document.getElementById('pilihan-anggota');
        const pilihanProdukEl = document.getElementById('pilihan-produk');
        if (!pilihanProdukEl || !pilihanAnggotaEl) return;
        
        const tomSelectAnggota = pageScripts.initAnggotaTomSelect(pilihanAnggotaEl);

        if (tomSelectAnggota) {
            tomSelectAnggota.on('change', function(value) {
                const umumDiv = document.getElementById('pelanggan-umum-div');
                if(value) {
                    umumDiv.classList.add('hidden');
                    document.getElementById('nama_pelanggan_umum').value = '';
                } else {
                    umumDiv.classList.remove('hidden');
                }
            });
        }
        
        const metodePembayaranEl = document.getElementById('metode-pembayaran');
        const divRekeningBank = document.getElementById('div-rekening-bank');
        const divJatuhTempo = document.getElementById('div-jatuh-tempo');
        
        if (metodePembayaranEl) {
            metodePembayaranEl.addEventListener('change', function() {
                const val = this.value;
                if (val === 'Kredit') {
                    divJatuhTempo.classList.remove('hidden');
                    divRekeningBank.classList.add('hidden');
                    document.getElementById('uang-dibayar-input').value = '';
                    hitungKembalian(); 
                } else if (val === 'Transfer') {
                    divRekeningBank.classList.remove('hidden');
                    divJatuhTempo.classList.add('hidden');
                } else {
                    divJatuhTempo.classList.add('hidden');
                    divRekeningBank.classList.add('hidden');
                }
            });
        }

        const pilihanProduk = new TomSelect(pilihanProdukEl, { 
            create: false,
            searchField: ['text', 'kode'],
            render: {
                option: function(data, escape) {
                    if (!data.value) return `<div>${escape(data.text)}</div>`;
                    const optEl = data.$option || data.node;
                    if (!optEl) return `<div>${escape(data.text)}</div>`;
                    const kode = optEl.getAttribute('data-kode') || '';
                    const stok = optEl.getAttribute('data-stok') || '0';
                    const satuan = optEl.getAttribute('data-satuan') || '';
                    const harga = optEl.getAttribute('data-harga') || '0';
                    const formatter = new Intl.NumberFormat('id-ID');
                    return `<div>
                                <div class="font-bold">${escape(data.text)}</div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-xs font-mono opacity-60">${escape(kode)}</span>
                                    <span class="badge badge-sm badge-info text-white font-bold">${escape(stok)} ${escape(satuan)}</span>
                                </div>
                                <div class="text-sm text-primary font-bold mt-1">Rp ${formatter.format(harga)} / ${escape(satuan)}</div>
                            </div>`;
                },
                item: function(data, escape) {
                    if (!data.value) return `<div>${escape(data.text)}</div>`;
                    const optEl = data.$option || data.node;
                    const satuan = optEl ? (optEl.getAttribute('data-satuan') || '') : '';
                    return `<div class="font-bold">${escape(data.text)} (${escape(satuan)})</div>`;
                }
            }
        });
        
        const btnTambah = document.getElementById('btn-tambah-item');
        const inputKuantitas = document.getElementById('kuantitas-produk');
        const keranjang = document.getElementById('keranjang-penjualan');
        const totalDisplay = document.getElementById('total-belanja-display');
        const totalInput = document.getElementById('total-belanja-input');
        const uangDibayarInput = document.getElementById('uang-dibayar-input');
        const kembalianDisplay = document.getElementById('kembalian-display');
        const diskonInput = document.getElementById('diskon-input');
        const totalBersihDisplay = document.getElementById('total-bersih-display');
        
        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID').format(angka);
        
        const hitungTotal = () => { 
            let total = 0; 
            keranjang.querySelectorAll('tr').forEach(row => { total += parseFloat(row.dataset.subtotal); }); 
            totalDisplay.textContent = formatRupiah(total); 
            totalInput.value = total; 
            hitungKembalian();
        };

        const hitungKembalian = () => {
            const total = parseFloat(totalInput.value) || 0;
            const diskon = parseFloat(diskonInput.value.replace(/\./g, '')) || 0;
            const totalBersih = total - diskon;
            
            if (totalBersihDisplay) {
                totalBersihDisplay.textContent = formatRupiah(totalBersih);
            }

            const dibayar = parseFloat(uangDibayarInput.value.replace(/\./g, '')) || 0;
            
            if (metodePembayaranEl && metodePembayaranEl.value === 'Kredit') {
                kembalianDisplay.textContent = '0 (Kredit)';
                kembalianDisplay.classList.remove('text-success');
                return;
            }

            if (dibayar > 0 && dibayar >= totalBersih) {
                kembalianDisplay.textContent = formatRupiah(dibayar - totalBersih);
                kembalianDisplay.classList.add('text-success');
            } else {
                kembalianDisplay.textContent = '0';
                kembalianDisplay.classList.remove('text-success');
            }
        };

        if (uangDibayarInput) uangDibayarInput.addEventListener('input', hitungKembalian);
        if (diskonInput) diskonInput.addEventListener('input', hitungKembalian);

        const addItemToCart = () => {
            const produkId = pilihanProduk.getValue(); 
            if (!produkId) { alert('Pilih produk terlebih dahulu.'); return; }
            
            const selectedOpt = pilihanProduk.options[produkId];
            const node = selectedOpt.$option || selectedOpt.node;
            const produkNama = pilihanProduk.options[produkId].text;
            const harga = parseFloat(node.getAttribute('data-harga')) || 0;
            const stokMaks = parseInt(node.getAttribute('data-stok')) || 0;
            const satuan = node.getAttribute('data-satuan') || 'Unit';
            const kuantitas = parseInt(inputKuantitas.value);
            
            if (isNaN(kuantitas) || kuantitas <= 0) { alert('Masukkan kuantitas yang valid.'); return; }
            
            let stokCukup = true; 
            if (stokMaks < kuantitas) { 
                stokCukup = false; 
                const idInduk = node.getAttribute('data-induk'); 
                const stokInduk = parseInt(node.getAttribute('data-stok-induk')) || 0; 
                const konversi = parseInt(node.getAttribute('data-konversi')) || 0; 
                if (idInduk && stokInduk > 0 && konversi > 0) { 
                    const stokDibutuhkan = kuantitas - stokMaks; 
                    const indukDibutuhkan = Math.ceil(stokDibutuhkan / konversi); 
                    if (stokInduk >= indukDibutuhkan) { stokCukup = true; } 
                } 
            }
            
            if (!stokCukup) { alert(`Stok tidak mencukupi, baik di produk ini maupun di produk induknya. Stok tersedia: ${stokMaks}`); return; }
            
            const existingRow = keranjang.querySelector(`input[name="produk_id[]"][value="${produkId}"]`);
            if (existingRow) { 
                const existingQtyInput = existingRow.closest('tr').querySelector('input[name="kuantitas[]"]'); 
                const newQty = parseInt(existingQtyInput.value) + kuantitas; 
                if (!stokCukup && newQty > stokMaks) { alert(`Stok tidak mencukupi. Stok tersedia: ${stokMaks}, Anda sudah menambahkan ${existingQtyInput.value} di keranjang.`); return; } 
                existingQtyInput.value = newQty; 
                const rowElement = existingQtyInput.closest('tr'); 
                rowElement.querySelector('.qty-display').textContent = newQty; 
                const newSubtotal = harga * newQty; 
                rowElement.dataset.subtotal = newSubtotal; 
                rowElement.querySelector('.subtotal-display').textContent = `Rp ${formatRupiah(newSubtotal)}`; 
            } else { 
                const subtotal = harga * kuantitas; 
                const row = document.createElement('tr'); 
                row.dataset.subtotal = subtotal; 
                row.className = 'border-b border-base-200 hover bg-base-100'; 
                row.innerHTML = `<td class="font-bold">${produkNama}<div class="text-xs opacity-60 mt-1">${satuan}</div><input type="hidden" name="produk_id[]" value="${produkId}"><input type="hidden" name="harga[]" value="${harga}"></td><td class="text-center font-bold text-lg"><span class="qty-display">${kuantitas}</span><input type="hidden" name="kuantitas[]" value="${kuantitas}"></td><td class="text-right font-mono">Rp ${formatRupiah(harga)}</td><td class="text-right font-mono font-bold text-primary subtotal-display">Rp ${formatRupiah(subtotal)}</td><td class="text-center"><button type="button" class="btn btn-xs btn-circle btn-error btn-hapus-item text-white"><i class="fas fa-times pointer-events-none"></i></button></td>`; 
                keranjang.appendChild(row); 
            }
            inputKuantitas.value = '1'; pilihanProduk.clear(); hitungTotal();
            const _scanEl = document.getElementById('scan-barcode');
            if (_scanEl) { _scanEl.focus(); _scanEl.select(); } else { pilihanProduk.focus(); }
        };
        
        btnTambah.addEventListener('click', addItemToCart); 
        inputKuantitas.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addItemToCart(); } }); 
        keranjang.addEventListener('click', function (e) { if (e.target.classList.contains('btn-hapus-item') || e.target.closest('.btn-hapus-item')) { e.target.closest('tr').remove(); hitungTotal(); } }); 
        
        const scanInput = document.getElementById('scan-barcode');
        const btnScanFokus = document.getElementById('btn-scan-fokus');
        const fokusScan = () => { if (scanInput) { scanInput.focus(); scanInput.select(); } };
        const cariByBarcode = (raw) => {
            const q = String(raw || '').trim().toLowerCase();
            if (!q) return null;
            const found = Object.values(pilihanProduk.options).find(opt => {
                if (!opt.value) return false;
                const el = opt.$option || opt.node;
                if (!el) return false;
                const bc = (el.getAttribute('data-barcode') || '').trim().toLowerCase();
                const kode = (el.getAttribute('data-kode') || '').trim().toLowerCase();
                return (bc !== '' && bc === q) || (kode !== '' && kode === q);
            });
            return found || null;
        };
        const jumlahQtyKeranjang = () => Array.from(keranjang.querySelectorAll('input[name="kuantitas[]"]')).reduce((a, i) => a + (parseInt(i.value) || 0), 0);
        const prosesScan = () => {
            if (!scanInput) return;
            const val = scanInput.value.trim();
            if (!val) return;
            const opt = cariByBarcode(val);
            if (!opt) {
                showToast(`Barcode/kode "${val}" belum terdaftar di master barang.`, 'warning');
                scanInput.value = ''; fokusScan();
                return;
            }
            if (!inputKuantitas.value || parseInt(inputKuantitas.value) < 1) inputKuantitas.value = '1';
            const sebelum = jumlahQtyKeranjang();
            pilihanProduk.setValue(opt.value);
            addItemToCart();
            if (jumlahQtyKeranjang() > sebelum) showToast(`Scan: ${opt.text} ditambahkan.`, 'success');
            scanInput.value = ''; fokusScan();
        };
        if (scanInput) {
            scanInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); prosesScan(); }
            });
            setTimeout(fokusScan, 50);
        }
        if (btnScanFokus) btnScanFokus.addEventListener('click', fokusScan);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') { e.preventDefault(); if (pilihanProduk) pilihanProduk.focus(); }
            if (e.key === 'F3') { e.preventDefault(); fokusScan(); }
            if (e.key === 'F8') { e.preventDefault(); if (uangDibayarInput) uangDibayarInput.focus(); }
            if (e.key === 'Enter' && e.target === uangDibayarInput) {
                e.preventDefault();
                formPenjualan.dispatchEvent(new Event('submit', { cancelable: true }));
            }
        });
    },
    initFormPembelian: () => {
        const pilihanPemasokEl = document.getElementById('pilihan-pemasok');
        const pilihanProdukEl = document.getElementById('pilihan-produk');
        if (!pilihanPemasokEl || !pilihanProdukEl) return;
        
        const tomSelectPemasok = new TomSelect(pilihanPemasokEl, { create: false, sortField: { field: "text", direction: "asc" } });
        
        const tomSelectProduk = new TomSelect(pilihanProdukEl, { 
            create: false, 
            sortField: { field: "text", direction: "asc" },
            render: {
                option: function(data, escape) {
                    const optEl = data.$option || data.node;
                    const jenis = optEl ? (optEl.getAttribute('data-jenis') || 'Barang Dagangan') : 'Barang Dagangan';
                    return `<div>
                        <div class="font-bold">${escape(data.text)}</div>
                        <div class="text-xs opacity-60 mt-1 badge badge-ghost badge-sm">${escape(jenis)}</div>
                    </div>`;
                },
                item: function(data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                }
            }
        });
        
        const metodePembayaranEl = document.getElementById('metode-pembayaran-beli');
        const divRekeningBank = document.getElementById('div-rekening-bank-beli');
        const divJatuhTempo = document.getElementById('div-jatuh-tempo-beli');
        
        if (metodePembayaranEl) {
            metodePembayaranEl.addEventListener('change', function() {
                const val = this.value;
                if (val === 'Kredit') {
                    divJatuhTempo.classList.remove('hidden');
                    divRekeningBank.classList.add('hidden');
                } else if (val === 'Transfer') {
                    divRekeningBank.classList.remove('hidden');
                    divJatuhTempo.classList.add('hidden');
                } else {
                    divJatuhTempo.classList.add('hidden');
                    divRekeningBank.classList.add('hidden');
                }
            });
        }

        const btnTambah = document.getElementById('btn-tambah-item');
        const inputHarga = document.getElementById('harga-produk');
        const inputKuantitas = document.getElementById('kuantitas-produk');
        const keranjang = document.getElementById('keranjang-pembelian');
        const totalDisplay = document.getElementById('total-pembelian-display');
        const totalInput = document.getElementById('total-pembelian-input');
        const formatRupiah = (angka) => new Intl.NumberFormat('id-ID').format(angka);
        
        tomSelectProduk.on('change', function (value) { 
            if (value) { 
                const selectedOriginalOption = pilihanProdukEl.querySelector(`option[value="${value}"]`); 
                if(selectedOriginalOption) { 
                    const harga = selectedOriginalOption.dataset.harga; 
                    inputHarga.value = formatRupiah(harga); 
                    inputHarga.dispatchEvent(new Event('input')); 
                } 
            } 
        });
        
        const hitungTotal = () => { let total = 0; keranjang.querySelectorAll('tr').forEach(row => { total += parseFloat(row.dataset.subtotal); }); totalDisplay.textContent = formatRupiah(total); totalInput.value = total; };
        
        const scanInputBeli = document.getElementById('scan-barcode-beli');
        const btnScanFokusBeli = document.getElementById('btn-scan-fokus-beli');
        const fokusScanBeli = () => { if (scanInputBeli) { scanInputBeli.focus(); scanInputBeli.select(); } };

        const tambahItemKeKeranjang = function () {
            const produkId = tomSelectProduk.getValue();
            if (!produkId) { alert('Pilih barang terlebih dahulu.'); return; }
            const selectedOption = tomSelectProduk.options[produkId];
            const produkNama = selectedOption.text;
            const harga = parseFloat(inputHarga.value.replace(/\./g, '')) || 0;
            const kuantitas = parseInt(inputKuantitas.value);
            if (isNaN(harga) || harga <= 0 || isNaN(kuantitas) || kuantitas <= 0) { alert('Masukkan harga serta jumlah yang valid (> 0).'); return; }

            const subtotal = harga * kuantitas;
            const row = document.createElement('tr');
            row.dataset.subtotal = subtotal;
            row.innerHTML = `<td>${produkNama}<input type="hidden" name="produk_id[]" value="${produkId}"></td><td class="text-center font-bold">${kuantitas}<input type="hidden" name="kuantitas[]" value="${kuantitas}"></td><td class="text-right font-mono">Rp ${formatRupiah(harga)}<input type="hidden" name="harga[]" value="${harga}"></td><td class="text-right font-bold text-primary font-mono">Rp ${formatRupiah(subtotal)}</td><td class="text-center"><button type="button" class="btn btn-xs btn-circle btn-error btn-hapus-item text-white"><i class="fas fa-times pointer-events-none"></i></button></td>`;
            keranjang.appendChild(row);

            inputKuantitas.value = ''; inputHarga.value = ''; tomSelectProduk.clear(); hitungTotal();
            fokusScanBeli();
        };
        btnTambah.addEventListener('click', tambahItemKeKeranjang);

        const cariByBarcodeBeli = (raw) => {
            const q = String(raw || '').trim().toLowerCase();
            if (!q) return null;
            const found = Array.from(pilihanProdukEl.options).find(o => {
                if (!o.value) return false;
                const bc = (o.dataset.barcode || '').trim().toLowerCase();
                const kd = (o.dataset.kode || '').trim().toLowerCase();
                return (bc !== '' && bc === q) || (kd !== '' && kd === q);
            });
            return found || null;
        };
        const prosesScanBeli = () => {
            if (!scanInputBeli) return;
            const val = scanInputBeli.value.trim();
            if (!val) return;
            const opt = cariByBarcodeBeli(val);
            if (!opt) {
                showToast(`Barcode/kode "${val}" belum terdaftar di master barang.`, 'warning');
                scanInputBeli.value = ''; fokusScanBeli();
                return;
            }
            if (!inputKuantitas.value || parseInt(inputKuantitas.value) < 1) inputKuantitas.value = '1';
            const harga = parseFloat(opt.dataset.harga) || 0;
            if (harga > 0) { inputHarga.value = formatRupiah(harga); inputHarga.dispatchEvent(new Event('input')); }
            tomSelectProduk.setValue(opt.value);
            tambahItemKeKeranjang();
            showToast(`Scan: ${opt.text} ditambahkan.`, 'success');
            scanInputBeli.value = ''; fokusScanBeli();
        };
        if (scanInputBeli) {
            scanInputBeli.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); prosesScanBeli(); }
            });
            setTimeout(fokusScanBeli, 50);
        }
        if (btnScanFokusBeli) btnScanFokusBeli.addEventListener('click', fokusScanBeli);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'F3') { e.preventDefault(); fokusScanBeli(); }
        });

        keranjang.addEventListener('click', function (e) { if (e.target.classList.contains('btn-hapus-item') || e.target.closest('.btn-hapus-item')) { e.target.closest('tr').remove(); hitungTotal(); } });
        
        const formTambahPemasok = document.getElementById('form-tambah-pemasok');
        if (formTambahPemasok) { 
            formTambahPemasok.addEventListener('submit', function (e) { 
                e.preventDefault(); 
                const formData = new FormData(this); 
                formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token']) ?>'); 
                fetch('<?= BASE_URL ?>sistem/ajax/tambah_pemasok_cepat.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => { 
                    if (data.success) { 
                        tomSelectPemasok.addOption({ value: data.id, text: data.nama }); 
                        tomSelectPemasok.setValue(data.id); 
                        modal_tambah_pemasok.close(); 
                        formTambahPemasok.reset(); 
                        showToast('Pemasok baru berhasil ditambahkan!', 'success'); 
                    } else { 
                        showToast(data.message || 'Gagal menambahkan pemasok.', 'error'); 
                    } 
                }).catch(error => { console.error('Error:', error); showToast('Terjadi kesalahan jaringan.', 'error'); }); 
            }); 
        }
    },
    initFormSimpanan: () => {
        pageScripts.initAnggotaTomSelect('pilihan-anggota');
        const bentukSetoran = document.getElementById('bentuk_setoran');
        const divAkunAset = document.getElementById('div_akun_aset');
        
        if (bentukSetoran && divAkunAset) {
            bentukSetoran.addEventListener('change', function() {
                if (this.value === 'Aset') {
                    divAkunAset.classList.remove('hidden');
                    document.querySelector('select[name="kode_akun_aset"]').required = true;
                } else {
                    divAkunAset.classList.add('hidden');
                    document.querySelector('select[name="kode_akun_aset"]').required = false;
                }
            });
        }
    },
    initFormHibah: () => {
        const bentukHibah = document.getElementById('bentuk_hibah');
        const divAkunAset = document.getElementById('div_akun_aset');
        
        if (bentukHibah && divAkunAset) {
            bentukHibah.addEventListener('change', function() {
                if (this.value === 'Aset') {
                    divAkunAset.classList.remove('hidden');
                    document.querySelector('select[name="kode_akun_aset"]').required = true;
                } else {
                    divAkunAset.classList.add('hidden');
                    document.querySelector('select[name="kode_akun_aset"]').required = false;
                }
            });
        }
    },
    initFormKaryawan: () => {
        pageScripts.initAnggotaTomSelect('pilihan-anggota');
    },
    initFormPinjamanAnggota: () => {
        const ts = pageScripts.initAnggotaTomSelect('pilihan-anggota');
        if (!ts) return;
        
        const infoSkorDiv = document.getElementById('info-skor-kredit');
        const kontenRincian = document.getElementById('konten_rincian_skor');
        ts.on('change', function (value) {
            if (!value) { if(infoSkorDiv) infoSkorDiv.classList.add('hidden'); return; }
            if(infoSkorDiv) {
                infoSkorDiv.classList.remove('hidden'); 
                infoSkorDiv.innerHTML = '<div class="alert"><span class="loading loading-spinner"></span> Memuat Skor Kredit...</div>';
                fetch(`<?= BASE_URL ?>sistem/ajax/get_skor_kredit.php?anggota_id=${value}`).then(response => response.json()).then(data => { if (data.error) { infoSkorDiv.innerHTML = `<div class="alert alert-error">${data.error}</div>`; } else { let alertClass = 'alert-info'; if (data.rekomendasi.includes('Sangat Baik')) alertClass = 'alert-success'; if (data.rekomendasi.includes('Perlu Tinjauan')) alertClass = 'alert-warning'; let rincianHtml = '<ul>'; for (const [key, value] of Object.entries(data.rincian)) { rincianHtml += `<li>${key}: <strong>${value} Poin</strong></li>`; } rincianHtml += '</ul>'; if(kontenRincian) kontenRincian.innerHTML = rincianHtml; infoSkorDiv.innerHTML = `<div class="alert ${alertClass}"><div class="flex-1"><h3 class="font-bold">Skor Kredit: ${data.skor}</h3><div class="text-xs">${data.rekomendasi}</div></div><button type="button" class="btn btn-sm" onclick="document.getElementById('modal_rincian_skor').showModal()">Rincian</button></div>`; } }).catch(error => { infoSkorDiv.innerHTML = `<div class="alert alert-error">Gagal memuat skor kredit.</div>`; });
            }
        });
    },
    initFormProduk: () => {
        const btnAutoKode = document.getElementById('btn-auto-kode');
        if (btnAutoKode) {
            const newBtn = btnAutoKode.cloneNode(true);
            btnAutoKode.parentNode.replaceChild(newBtn, btnAutoKode);
            newBtn.addEventListener('click', function() {
                const randomNum = Math.floor(Math.random() * 9000) + 1000;
                document.getElementById('kode_produk').value = 'ITM-' + randomNum;
            });
        }

        const barcodeInput = document.getElementById('barcode');
        const btnFokusBarcode = document.getElementById('btn-fokus-barcode');
        if (barcodeInput && btnFokusBarcode) {
            btnFokusBarcode.addEventListener('click', function() { barcodeInput.focus(); barcodeInput.select(); });
            setTimeout(function() { barcodeInput.focus(); barcodeInput.select(); }, 50);
        }

        const indukSelectEl = document.getElementById('id_produk_induk');
        if (!indukSelectEl) return;
        const konversiInput = document.getElementById('kuantitas_konversi'); 
        const hargaBeliInput = document.getElementById('harga_beli'); 
        const kodeProdukInput = document.getElementById('kode_produk'); 
        const namaProdukInput = document.getElementById('nama_produk'); 
        const satuanInput = document.getElementById('satuan'); 
        const tomSelectInduk = new TomSelect(indukSelectEl, { create: false }); 
        const isEdit = document.querySelector('input[name="id"]').value !== ''; 
        const isTurunan = indukSelectEl.value !== '';
        
        const updateFormFields = () => {
            const indukId = tomSelectInduk.getValue(); 
            if (!indukId) { hargaBeliInput.readOnly = false; return; }
            const konversiQty = parseInt(konversiInput.value);
            
            if (konversiQty > 0) { 
                fetch(`<?= BASE_URL ?>sistem/ajax/get_produk_detail.php?id=${indukId}`).then(response => response.json()).then(data => { 
                    if (data.success && data.produk.harga_rata_rata) { 
                        const hargaBeliTurunan = parseFloat(data.produk.harga_rata_rata) / konversiQty; 
                        hargaBeliInput.value = Math.round(hargaBeliTurunan); 
                        hargaBeliInput.dispatchEvent(new Event('input', { bubbles: true })); 
                        hargaBeliInput.readOnly = true; 
                    } 
                }).catch(error => console.error('Error fetching produk induk:', error)); 
            } else { 
                hargaBeliInput.readOnly = isTurunan; 
            }
        };
        tomSelectInduk.on('change', () => { if (!isEdit && tomSelectInduk.getValue() && !satuanInput.value) { satuanInput.value = "Pcs"; } updateFormFields(); });
        konversiInput.addEventListener('input', updateFormFields); 
        if (!isEdit) { satuanInput.addEventListener('input', updateFormFields); }
    },
    initFormPenyesuaian: () => {
        const container = document.getElementById('baris-jurnal'); if (!container) return;
        const btnTambah = document.getElementById('btn-tambah-baris'); 
        const template = document.getElementById('template-baris').innerHTML;
        
        const dataContainer = document.getElementById('data-akun-container');
        let dataAkun = [];
        if (dataContainer && dataContainer.dataset.akun) {
            try { dataAkun = JSON.parse(dataContainer.dataset.akun); } catch(e) {}
        }

        const formatRupiahDisplay = (angka) => new Intl.NumberFormat('id-ID').format(angka);

        const tambahBaris = () => { 
            const newRow = document.createElement('tr'); 
            newRow.innerHTML = template; 
            container.appendChild(newRow); 
            
            const selectEl = newRow.querySelector('.select-akun');
            if (dataAkun && dataAkun.length > 0) {
                dataAkun.forEach(akun => {
                    const opt = document.createElement('option');
                    opt.value = akun.kode_akun;
                    opt.textContent = `${akun.kode_akun} - ${akun.nama_akun}`;
                    opt.setAttribute('data-saldo', akun.saldo);
                    selectEl.appendChild(opt);
                });
            }
            
            new TomSelect(selectEl, { 
                create: false, 
                sortField: { field: "text", direction: "asc" }, 
                placeholder: 'Pilih Akun...', 
                dropdownParent: 'body',
                plugins: ['option_balance']
            }); 
            newRow.querySelectorAll('.format-rupiah').forEach(initRupiahFormatting); 
        };

        const hitungTotal = () => { 
            let totalDebit = 0, totalKredit = 0; 
            container.querySelectorAll('tr').forEach(row => { 
                totalDebit += parseFloat(row.querySelector('.input-debit').value.replace(/\./g, '')) || 0; 
                totalKredit += parseFloat(row.querySelector('.input-kredit').value.replace(/\./g, '')) || 0; 
            }); 
            document.getElementById('total-debit').textContent = formatRupiahDisplay(totalDebit); 
            document.getElementById('total-kredit').textContent = formatRupiahDisplay(totalKredit); 
        };

        const newBtnTambah = btnTambah.cloneNode(true);
        btnTambah.parentNode.replaceChild(newBtnTambah, btnTambah);
        newBtnTambah.addEventListener('click', tambahBaris);

        container.addEventListener('click', (e) => { if (e.target.classList.contains('btn-hapus-baris')) { e.target.closest('tr').remove(); hitungTotal(); } });
        container.addEventListener('input', (e) => { if (e.target.classList.contains('input-debit') || e.target.classList.contains('input-kredit')) { hitungTotal(); } });
        
        const formJurnal = document.getElementById('form-jurnal');
        if (formJurnal) {
            formJurnal.addEventListener('submit', function (e) { 
                let totalDebit = 0, totalKredit = 0; 
                container.querySelectorAll('tr').forEach(row => { 
                    totalDebit += parseFloat(row.querySelector('.input-debit').value.replace(/\./g, '')) || 0; 
                    totalKredit += parseFloat(row.querySelector('.input-kredit').value.replace(/\./g, '')) || 0; 
                }); 
                if (totalDebit.toFixed(2) !== totalKredit.toFixed(2)) { 
                    e.preventDefault(); 
                    alert('Jurnal tidak seimbang! Total Debit harus sama dengan Total Kredit.'); 
                    this.classList.remove('is-submitting');
                    const btn = this.querySelector('button[type="submit"]');
                    if(btn) btn.innerHTML = btn.dataset.originalText;
                } 
            });
        }
        
        container.innerHTML = '';
        tambahBaris(); tambahBaris();
    },
    initPanduan: () => {
        const fileTreeContainer = document.getElementById('file-tree-container'); 
        const panduanContent = document.getElementById('panduan-content'); 
        if (!fileTreeContainer) return;
        
        function loadPanduanContent(filePath) { 
            fileTreeContainer.querySelectorAll('.file-link.active').forEach(el => el.classList.remove('active')); 
            const newActiveLink = fileTreeContainer.querySelector(`.file-link[data-path="${filePath}"]`); 
            if (newActiveLink) { newActiveLink.classList.add('active'); } 
            
            panduanContent.innerHTML = '<div class="flex justify-center items-center h-full"><span class="loading loading-lg loading-spinner text-primary"></span></div>'; 
            
            fetch(`<?= BASE_URL ?>sistem/ajax/get_panduan_content.php?file=${encodeURIComponent(filePath)}`)
            .then(response => { if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`); return response.text(); })
            .then(html => { panduanContent.innerHTML = `<article class="prose max-w-none lg:prose-lg">${html}</article>`; })
            .catch(error => { console.error('Error fetching panduan:', error); panduanContent.innerHTML = `<div class="alert alert-error">Gagal memuat konten. Silakan coba lagi.</div>`; }); 
        }
        
        fileTreeContainer.addEventListener('click', function (e) { 
            const target = e.target.closest('a.file-link'); 
            if (!target) return; 
            e.preventDefault(); 
            const filePath = target.dataset.path; 
            loadPanduanContent(filePath); 
        });
        
        panduanContent.addEventListener('click', function (e) { 
            const target = e.target.closest('a'); 
            
            // Handle if clicking on global search result links
            const searchResultLink = e.target.closest('.search-result-link');
            if (searchResultLink) {
                e.preventDefault();
                loadPanduanContent(searchResultLink.dataset.path);
                return;
            }

            if (target && target.getAttribute('href') && target.getAttribute('href').endsWith('.md')) { 
                e.preventDefault(); 
                const filePath = target.getAttribute('href'); 
                loadPanduanContent(filePath); 
            } 
        });
        
        const toggleButton = document.getElementById('fullscreen-toggle'); 
        const drawer = document.querySelector('.drawer'); 
        const icon = toggleButton.querySelector('i'); 
        let isFullscreen = false; 
        toggleButton.addEventListener('click', function () { 
            isFullscreen = !isFullscreen; 
            if (isFullscreen) { 
                drawer.classList.remove('lg:drawer-open'); 
                icon.classList.remove('fa-expand'); icon.classList.add('fa-compress'); 
                toggleButton.setAttribute('title', 'Keluar dari Tampilan Penuh'); 
            } else { 
                drawer.classList.add('lg:drawer-open'); 
                icon.classList.remove('fa-compress'); icon.classList.add('fa-expand'); 
                toggleButton.setAttribute('title', 'Tampilan Penuh'); 
            } 
        });

        // Global Search Logic untuk Buku Saku
        const inputSearch = document.getElementById('input-search-panduan');
        const btnSearch = document.getElementById('btn-search-panduan');
        let searchTimer;

        const performSearch = () => {
            const q = inputSearch.value.trim();
            if (q.length === 0) {
                panduanContent.innerHTML = `<div class="prose max-w-none lg:prose-lg text-center m-auto"><h2 class="text-3xl font-bold">Selamat Datang di Panduan Sistem</h2><p class="text-base-content/70">Pilih topik panduan dari daftar di sebelah kiri atau gunakan kotak pencarian di atas untuk mencari instruksi secara global.</p></div>`;
                fileTreeContainer.querySelectorAll('.file-link.active').forEach(el => el.classList.remove('active'));
                return;
            }

            panduanContent.innerHTML = '<div class="flex justify-center items-center h-full"><span class="loading loading-lg loading-spinner text-primary"></span></div>';

            fetch(`<?= BASE_URL ?>sistem/ajax/cari_panduan.php?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                fileTreeContainer.querySelectorAll('.file-link.active').forEach(el => el.classList.remove('active'));
                
                let html = `<h2 class="text-2xl font-bold mb-6 border-b pb-2">Hasil Pencarian: "${q}"</h2>`;
                if (data.length === 0) {
                    html += `<div class="alert alert-warning">Tidak ada hasil yang ditemukan untuk kata kunci tersebut.</div>`;
                } else {
                    html += `<div class="space-y-4">`;
                    data.forEach(item => {
                        html += `
                        <div class="card bg-base-200 hover:bg-primary hover:text-primary-content transition-colors cursor-pointer search-result-link border border-base-300 shadow-sm" data-path="${item.path}">
                            <div class="card-body p-4">
                                <h3 class="card-title text-lg">${item.title}</h3>
                                <p class="text-sm italic">${item.snippet}</p>
                            </div>
                        </div>`;
                    });
                    html += `</div>`;
                }
                panduanContent.innerHTML = html;
            })
            .catch(err => {
                panduanContent.innerHTML = `<div class="alert alert-error">Terjadi kesalahan saat mencari panduan.</div>`;
            });
        };

        if (btnSearch) btnSearch.addEventListener('click', performSearch);
        if (inputSearch) {
            inputSearch.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    clearTimeout(searchTimer);
                    performSearch();
                } else {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(performSearch, 500);
                }
            });
        }
    },
    initLihatStok: () => {
        document.addEventListener('click', function(e) {
            const button = e.target.closest('.btn-buka-modal-penyesuaian');
            if (!button) return;

            const id = button.dataset.id;
            const nama = button.dataset.nama;
            const stok = button.dataset.stok;
            
            document.getElementById('modal_produk_id').value = id;
            document.getElementById('modal_judul_produk').innerText = 'Hitung Ulang Stok: ' + nama;
            document.getElementById('modal_stok_sistem').value = stok;
            modal_penyesuaian_stok.showModal();
        });
    },
    initSimulasiShu: () => {
        const form = document.getElementById('form-simulasi');
        if (!form) return;

        const persenInputs = form.querySelectorAll('.alokasi-persen');
        const warning = document.getElementById('total-persen-warning');

        function checkTotal() {
            let total = 0;
            persenInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            if (total.toFixed(2) !== '100.00') {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        }

        persenInputs.forEach(input => input.addEventListener('input', checkTotal));

        form.addEventListener('submit', function(e) {
            let total = 0;
            persenInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            if (total.toFixed(2) !== '100.00') {
                e.preventDefault();
                alert('Gagal menjalankan simulasi. Total persentase alokasi harus tepat 100%.');
                this.classList.remove('is-submitting');
                const btn = this.querySelector('button[type="submit"]');
                if(btn) btn.innerHTML = btn.dataset.originalText;
            }
        });

        checkTotal();
    },
    initLogAktivitas: () => {
        const checkAll = document.getElementById('check-all');
        const btnHapus = document.getElementById('btn-hapus-terpilih');

        if (!checkAll || !btnHapus) return;

        function toggleBtn() {
            const checkedCount = document.querySelectorAll('.check-item:checked').length;
            btnHapus.disabled = checkedCount === 0;
            btnHapus.innerText = checkedCount > 0 ? `Hapus ${checkedCount} Terpilih` : 'Hapus Terpilih';
        }

        const newCheckAll = checkAll.cloneNode(true);
        checkAll.parentNode.replaceChild(newCheckAll, checkAll);
        
        newCheckAll.addEventListener('change', function() {
            document.querySelectorAll('.check-item').forEach(item => item.checked = newCheckAll.checked);
            toggleBtn();
        });

        document.querySelector('.table').addEventListener('change', function(e) {
            if (e.target.classList.contains('check-item')) {
                const allChecked = Array.from(document.querySelectorAll('.check-item')).every(i => i.checked);
                newCheckAll.checked = allChecked;
                toggleBtn();
            }
        });
        toggleBtn();
    },
    initFormBeban: () => {
        // Logika RAB dan Potongan Pajak telah dinonaktifkan untuk penyederhanaan
    },
    initFormMigrasi: () => {
        const inputs = document.querySelectorAll('.input-calc');
        const displayDebit = document.getElementById('total-debit-display');
        const displayKredit = document.getElementById('total-kredit-display');
        const displaySelisih = document.getElementById('selisih-display');
        const btnSubmit = document.getElementById('btn-submit-migrasi');
        
        if (!displayDebit || !inputs.length) return;

        const formatRp = (angka) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);

        function hitungTotal() {
            let totalDebit = 0;
            let totalKredit = 0;

            document.querySelectorAll('input[name="debit[]"]').forEach(input => {
                const hidden = input.nextElementSibling;
                let val = 0;
                if (hidden && hidden.type === 'hidden') {
                    val = parseFloat(hidden.value) || 0;
                } else {
                    val = parseFloat(input.value.replace(/\./g, '')) || 0;
                }
                totalDebit += val;
            });

            document.querySelectorAll('input[name="kredit[]"]').forEach(input => {
                const hidden = input.nextElementSibling;
                let val = 0;
                if (hidden && hidden.type === 'hidden') {
                    val = parseFloat(hidden.value) || 0;
                } else {
                    val = parseFloat(input.value.replace(/\./g, '')) || 0;
                }
                totalKredit += val;
            });

            if(displayDebit) displayDebit.textContent = formatRp(totalDebit);
            if(displayKredit) displayKredit.textContent = formatRp(totalKredit);
            
            const selisih = Math.abs(totalDebit - totalKredit);
            if(displaySelisih) displaySelisih.textContent = formatRp(selisih);

            if (totalDebit > 0 && selisih === 0) {
                if(displaySelisih) {
                    displaySelisih.classList.remove('text-error');
                    displaySelisih.classList.add('text-success');
                }
                if(btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Simpan Saldo Awal';
                }
            } else {
                if(displaySelisih) {
                    displaySelisih.classList.remove('text-success');
                    displaySelisih.classList.add('text-error');
                }
                if(btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = 'Neraca Belum Seimbang';
                }
            }
        }

        setTimeout(hitungTotal, 500);

        document.getElementById('tabel-saldo').addEventListener('input', function(e) {
            if(e.target.classList.contains('input-calc')) {
                hitungTotal();
            }
        });
    },
    initFormPecahBarang: () => {
        const selectInduk = document.getElementById('pilihan-induk');
        const selectTurunan = document.getElementById('pilihan-turunan');
        const inputJumlah = document.getElementById('jumlah-dipecah');
        const btnProses = document.getElementById('btn-proses');
        const dataContainer = document.getElementById('data-turunan-container');
        
        if (!selectInduk || !selectTurunan || !inputJumlah || !dataContainer) return;

        let dataTurunan = [];
        try { dataTurunan = JSON.parse(dataContainer.dataset.turunan); } catch(e) {}

        let maxStok = 0;
        let selectedKonversi = 0;
        
        const tsInduk = selectInduk.tomselect;

        if (tsInduk) {
            tsInduk.on('change', function(value) {
                selectTurunan.innerHTML = '<option value="">-- Pilih Turunan --</option>';
                
                if (value) {
                    // TomSelect tidak menyalin atribut data-* ke objek option internal,
                    // baca dari elemen <option> asli agar maxStok tidak nol.
                    const nativeOpt = selectInduk.querySelector('option[value="' + value + '"]');
                    maxStok = nativeOpt ? (parseInt(nativeOpt.getAttribute('data-stok')) || 0) : 0;

                    const satuan = nativeOpt ? (nativeOpt.getAttribute('data-satuan') || '') : '';

                    const lbl = document.getElementById('label-satuan-induk');
                    if(lbl) lbl.textContent = satuan;
                    inputJumlah.max = maxStok;

                    const turunanOptions = dataTurunan.filter(t => t.id_produk_induk == value);
                    turunanOptions.forEach(t => {
                        const optionNode = document.createElement('option');
                        optionNode.value = t.id;
                        optionNode.textContent = `${t.nama_produk} (1 Induk = ${t.kuantitas_konversi} ${t.satuan})`;
                        optionNode.setAttribute('data-konversi', t.kuantitas_konversi);
                        selectTurunan.appendChild(optionNode);
                    });
                    
                    if (turunanOptions.length === 1) {
                        selectTurunan.selectedIndex = 1;
                        selectTurunan.dispatchEvent(new Event('change'));
                    }
                } else {
                    maxStok = 0;
                    const lbl = document.getElementById('label-satuan-induk');
                    if(lbl) lbl.textContent = '...';
                }
                updateKalkulasi();
            });
        }

        selectTurunan.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                selectedKonversi = parseInt(opt.getAttribute('data-konversi')) || 0;
                const desc = document.getElementById('konversi-desc-display');
                if (desc) desc.textContent = `Rasio: 1 = ${selectedKonversi}`;
            } else {
                selectedKonversi = 0;
            }
            updateKalkulasi();
        });

        inputJumlah.addEventListener('input', updateKalkulasi);

        function updateKalkulasi() {
            const jumlah = parseInt(inputJumlah.value) || 0;
            
            const indukDisplay = document.getElementById('induk-kurang-display');
            const turunanDisplay = document.getElementById('turunan-tambah-display');
            if(indukDisplay) indukDisplay.textContent = '-' + jumlah;
            if(turunanDisplay) turunanDisplay.textContent = '+' + (jumlah * selectedKonversi);

            if (jumlah > 0 && selectedKonversi > 0 && jumlah <= maxStok) {
                if(btnProses) btnProses.disabled = false;
            } else {
                if(btnProses) btnProses.disabled = true;
            }
        }
    },
    initFormPenggajian: () => {
        const karyawanSelect = document.getElementById('karyawan_id');
        const gapokInput = document.getElementById('gaji_pokok');
        const tunjInput = document.getElementById('tunjangan');
        const potongInput = document.getElementById('potongan');
        const bersihDisplay = document.getElementById('total-bersih-display');
        const bersihInput = document.getElementById('total_bersih');
        
        const formatRupiahDisplay = (angka) => new Intl.NumberFormat('id-ID').format(angka);

        const hitungBersih = () => {
            const gapok = parseFloat(gapokInput.value.replace(/\./g, '')) || 0;
            const tunj = parseFloat(tunjInput.value.replace(/\./g, '')) || 0;
            const pot = parseFloat(potongInput.value.replace(/\./g, '')) || 0;
            const bersih = gapok + tunj - pot;
            
            if(bersihDisplay) bersihDisplay.textContent = formatRupiahDisplay(bersih);
            if(bersihInput) bersihInput.value = bersih;
        };

        if (karyawanSelect) {
            const ts = new TomSelect(karyawanSelect, {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
            ts.on('change', function(value) {
                if(!value) return;
                fetch('<?= BASE_URL ?>sistem/ajax/get_karyawan_detail.php?id=' + value)
                    .then(r => r.json())
                    .then(res => {
                        if(res.success) {
                            gapokInput.value = res.data.gaji_pokok;
                            gapokInput.dispatchEvent(new Event('input', {bubbles: true}));
                            tunjInput.value = res.data.tunjangan_jabatan;
                            tunjInput.dispatchEvent(new Event('input', {bubbles: true}));
                            hitungBersih();
                        }
                    });
            });
        }

        if(gapokInput) gapokInput.addEventListener('input', hitungBersih);
        if(tunjInput) tunjInput.addEventListener('input', hitungBersih);
        if(potongInput) potongInput.addEventListener('input', hitungBersih);
    }
};

window.bukaModalPelepasan = function(id, nama, nilaiBuku) {
    document.getElementById('modal_aset_id').value = id;
    document.getElementById('nama_aset_modal').innerText = nama;
    document.getElementById('modal_nilai_buku').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(nilaiBuku);
    document.getElementById('modal_harga_jual').value = '0';
    document.getElementById('modal_pelepasan_aset').showModal();
};

window.bukaModalBayarUtang = function(id, nama, sisa) {
    document.getElementById('modal_pemasok_id').value = id;
    document.getElementById('modal_nama_pemasok').value = nama;
    document.getElementById('nama_pemasok_modal').innerText = nama;
    document.getElementById('modal_sisa_utang').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(sisa);
    
    const inputBayar = document.getElementById('modal_jumlah_bayar');
    inputBayar.value = sisa;
    inputBayar.dispatchEvent(new Event('input')); 
    
    document.getElementById('modal_bayar_utang').showModal();
};
</script>