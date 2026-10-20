<script>
    let chartInstance = null;
    const loadingOverlay = document.getElementById('loading-overlay');
    const mainContent = document.getElementById('main-content');

    function runPageScripts(halaman) {
        document.querySelectorAll('input.format-rupiah:not([data-rupiah-formatted])').forEach(initRupiahFormatting);
        
        switch (halaman) {
            case 'dasbor': pageScripts.initDasbor(); break;
            case 'produk_stok/form_penurunan_nilai': pageScripts.initFormPenurunanNilai(); break;
            case 'penjualan/form_penjualan': pageScripts.initFormPenjualan(); break;
            case 'pembelian/form_pembelian': pageScripts.initFormPembelian(); break;
            case 'pinjaman_anggota/form_pinjaman': pageScripts.initFormPinjamanAnggota(); break;
            case 'produk_stok/form_produk': pageScripts.initFormProduk(); break;
            case 'produk_stok/lihat_stok': pageScripts.initLihatStok(); break;
            case 'jurnal/form_penyesuaian': 
                pageScripts.initTomSelect('.select-akun'); 
                pageScripts.initFormPenyesuaian(); 
                break;
            case 'jurnal/lihat_jurnal': 
            case 'pengaturan/form_kategori_beban': 
            case 'pendapatan/form_pendapatan': 
            case 'pengaturan/form_pengaturan_kebijakan':
                pageScripts.initTomSelect('.select-akun'); 
                break;
            case 'pengaturan/form_akun':
                pageScripts.initFormAkun();
                break;
            case 'laporan/laporan_utang_usaha': pageScripts.initTomSelect('.select-pemasok'); break;
            case 'simpanan/form_simpanan': 
                pageScripts.initTomSelect('select[name="anggota_id"]'); 
                pageScripts.initTomSelect('select[name="kode_akun_aset"]'); 
                pageScripts.initFormSimpanan(); 
                break;
            case 'administrasi_laporan/laporan_buku_simpanan': pageScripts.initTomSelect('#anggota_id'); break;
            case 'hibah/form_hibah':
                pageScripts.initTomSelect('select[name="kode_akun_aset"]');
                pageScripts.initFormHibah();
                break;
            case 'panduan': pageScripts.initPanduan(); break;
            case 'tutup_buku/laporan_distribusi_shu': pageScripts.initSimulasiShu(); break;
            case 'produk_stok/form_pecah_barang': 
                pageScripts.initTomSelect('select[name="id_induk"]'); 
                pageScripts.initFormPecahBarang();
                break;
            case 'administrasi/lihat_log_aktivitas': pageScripts.initLogAktivitas(); break;
            case 'beban/form_beban': pageScripts.initFormBeban(); break;
            case 'pengaturan/form_migrasi': pageScripts.initFormMigrasi(); break;
            case 'karyawan/form_karyawan': pageScripts.initFormKaryawan(); break;
            case 'karyawan/form_penggajian': pageScripts.initFormPenggajian(); break;
        }
    }

    function initRupiahFormatting(inputElement) {
        if (!inputElement || inputElement.dataset.rupiahFormatted) return;
        inputElement.dataset.rupiahFormatted = 'true';

        const form = inputElement.closest('form');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = inputElement.name;
        inputElement.parentElement.insertBefore(hiddenInput, inputElement.nextSibling);
        
        inputElement.removeAttribute('name');
        inputElement.setAttribute('inputmode', 'numeric');

        const formatValue = (value) => {
            if (!value) return '';
            const numberString = String(value).replace(/[^0-9-]/g, '');
            if (numberString === '' || numberString === '-') return numberString;
            return new Intl.NumberFormat('id-ID').format(numberString);
        };

        const updateValues = (e) => {
            const rawValue = e.target.value.replace(/\./g, '');
            hiddenInput.value = rawValue;
            e.target.value = formatValue(rawValue);
        };

        if (inputElement.value) {
            const numericValue = Math.round(parseFloat(inputElement.value));
            hiddenInput.value = numericValue;
            inputElement.value = formatValue(numericValue);
        }

        inputElement.addEventListener('input', updateValues);

        if (form) {
            form.addEventListener('submit', () => {
                hiddenInput.value = inputElement.value.replace(/\./g, '');
            });
        }
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-error' : 'alert-info');
        const toast = document.createElement('div');
        toast.className = `alert ${alertClass} shadow-lg`;
        toast.innerHTML = `<div><span>${message}</span></div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.5s ease-out'; setTimeout(() => { toast.remove(); }, 500); }, 3000);
    }

    function konfirmasiHapus(actionUrl, id, pesan = 'Yakin ingin menghapus data ini? Aksi ini tidak dapat dibatalkan.') {
        const modal = document.getElementById('modal_konfirmasi');
        document.getElementById('konfirmasi_pesan').innerText = pesan;
        document.getElementById('form_lanjutkan').action = '<?= BASE_URL ?>' + actionUrl;
        document.getElementById('konfirmasi_id_input').value = id;
        document.getElementById('konfirmasi_csrf_token').value = '<?= htmlspecialchars($_SESSION['csrf_token']) ?>';
        modal.showModal();
    }

    function updateThemeIcons(theme) {
        const isDark = theme === 'dark';
        const iconClass = isDark ? 'fas fa-sun text-lg' : 'fas fa-moon text-lg';
        const mobileIcon = document.getElementById('theme-icon-mobile');
        const desktopIcon = document.getElementById('theme-icon-desktop');
        if (mobileIcon) mobileIcon.className = iconClass;
        if (desktopIcon) desktopIcon.className = iconClass;
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons(newTheme);
    }
    
    const showLoader = () => loadingOverlay.classList.remove('hidden');
    const hideLoader = () => loadingOverlay.classList.add('hidden');

    async function loadPage(url, pushState = true) {
        showLoader();
        
        let timeoutId = setTimeout(() => {
            hideLoader();
            showToast('Koneksi lambat, coba muat ulang halaman.', 'warning');
        }, 15000);

        try {
            const fetchUrl = new URL(url);
            fetchUrl.searchParams.set('ajax', '1');
            
            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) { 
                window.location.href = url; 
                return; 
            }
            
            const newContent = await response.text();
            
            if (newContent.includes('<html') || newContent.includes('drawer-side')) {
                console.warn('Terdeteksi respons Full HTML. Memaksa muat ulang halaman penuh.');
                window.location.href = url;
                return;
            }

            const newTitle = atob(response.headers.get('X-Page-Title') || '') || 'Sistem Koperasi';
            mainContent.innerHTML = newContent;
            document.title = newTitle;
            
            updateThemeIcons(localStorage.getItem('theme') || 'dark');
            
            if (pushState) { 
                history.pushState({ path: url }, newTitle, url); 
            }
            
            const halaman = new URL(url).searchParams.get('halaman') || 'dasbor';
            runPageScripts(halaman);
            updateActiveMenu(url);
            window.scrollTo(0, 0);
            
        } catch (error) { 
            console.error('Page load failed:', error); 
            window.location.href = url; 
        } finally { 
            clearTimeout(timeoutId);
            hideLoader(); 
        }
    }

    function updateActiveMenu(url) {
        const urlParams = new URLSearchParams(new URL(url).search);
        const halaman = urlParams.get('halaman') || 'dasbor';
        const jenis = urlParams.get('jenis');

        document.querySelectorAll('.drawer-side .menu a').forEach(link => {
            link.classList.remove('active');
            
            const linkUrl = new URL(link.href, window.location.origin);
            const linkHalaman = linkUrl.searchParams.get('halaman');
            const linkJenis = linkUrl.searchParams.get('jenis');

            if (linkHalaman === halaman || halaman.startsWith(linkHalaman + '/')) {
                if (jenis && linkJenis) {
                    if (jenis === linkJenis) link.classList.add('active');
                } else if (!linkJenis || !jenis) {
                    link.classList.add('active');
                }
            }
            
            if (link.classList.contains('active')) {
                const details = link.closest('details');
                if (details) { details.open = true; }
            }
        });
    }
    
    if (typeof TomSelect !== 'undefined') {
        TomSelect.define('option_balance', function() {
            const self = this;
            self.hook('after', 'setupTemplates', () => {
                self.settings.render = self.settings.render || {};
                const renderOption = self.settings.render.option;
                self.settings.render.option = (data, escape) => {
                    const original = renderOption
                        ? renderOption.call(self, data, escape)
                        : `<div>${escape(data.text)}</div>`;
                    const saldo = data.saldo;
                    if (saldo === undefined || saldo === null || saldo === '') return original;
                    const balanceStr = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(Number(saldo));
                    return `<div class="flex justify-between items-center gap-2">
                        <span>${original}</span>
                        <span class="text-xs opacity-60 whitespace-nowrap">Saldo: ${escape(balanceStr)}</span>
                    </div>`;
                };
            });
        });
    }

    function checkSystemTime() {
        const serverTime = <?= time() * 1000 ?>;
        const clientTime = Date.now();
        if (Math.abs(clientTime - serverTime) > 86400000) {
            showToast('Peringatan: Jam komputer tidak sinkron (>24 Jam). Pembukuan dikunci demi keamanan.', 'error');
            document.querySelectorAll('form').forEach(f => {
                const btn = f.querySelector('button[type="submit"]');
                if(btn && f.id !== 'logout-form') {
                    btn.disabled = true;
                    btn.title = 'Ubah Jam Komputer Anda terlebih dahulu';
                }
            });
        }
    }

    document.addEventListener('click', e => {
        const link = e.target.closest('a');
        if (!link) return;
        
        const fullReloadPages = [
            'administrasi/lihat_log_aktivitas',
            'aset_tetap/lihat_aset',
            'laporan/laporan_utang_usaha',
            'produk_stok/form_pecah_barang',
            'produk_stok/form_produk',
            'beban/form_beban',
            'pengaturan/form_migrasi',
            'jurnal/form_penyesuaian'
        ];

        let isFullReload = false;
        try {
            const urlObj = new URL(link.href, window.location.origin);
            const halamanParam = urlObj.searchParams.get('halaman');
            if (halamanParam && fullReloadPages.includes(halamanParam)) {
                isFullReload = true;
            }
        } catch(err) {}

        if (link.hostname !== window.location.hostname || 
            link.getAttribute('target') === '_blank' || 
            link.href.includes('#') || 
            link.protocol.startsWith('javascript:') || 
            link.hasAttribute('download') ||
            isFullReload ||
            link.classList.contains('no-ajax')) { 
            return; 
        }
        
        e.preventDefault();
        loadPage(link.href);
    });

    window.addEventListener('popstate', e => { if (e.state && e.state.path) { loadPage(e.state.path, false); } });
    
    document.addEventListener('DOMContentLoaded', function () {
        const currentUrl = new URL(window.location);
        const halaman = currentUrl.searchParams.get('halaman') || 'dasbor';
        runPageScripts(halaman);
        
        const currentThemeOnLoad = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', currentThemeOnLoad);
        updateThemeIcons(currentThemeOnLoad);
        
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) { logoutBtn.addEventListener('click', function (e) { e.preventDefault(); document.getElementById('logout-form').submit(); }); }
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if(this.classList.contains('is-submitting')) {
                    e.preventDefault();
                    return;
                }
                this.classList.add('is-submitting');
                const btn = this.querySelector('button[type="submit"]');
                if(btn && !btn.disabled) {
                    const originalText = btn.innerHTML;
                    btn.dataset.originalText = originalText;
                    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';
                }
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const errorMsg = urlParams.get('error');
        
        const messages = { 'sukses': 'Aksi berhasil dilakukan!', 'sukses_tambah_lagi': 'Data berhasil disimpan. Silakan tambah data baru.', 'edit_sukses': 'Data berhasil diperbarui!', 'hapus_sukses': 'Data berhasil dihapus!', 'edit_tidak_ada_perubahan': 'Tidak ada perubahan yang disimpan.', 'gagal': errorMsg || 'Terjadi kesalahan, data gagal diproses.', 'edit_gagal': errorMsg || 'Data gagal diperbarui.', 'hapus_gagal': errorMsg || 'Data gagal dihapus.', 'hapus_gagal_terkait': 'Gagal hapus, anggota/pemasok/akun memiliki transaksi terkait.', 'hapus_gagal_produk_terjual': 'Gagal hapus, produk memiliki riwayat transaksi.', 'akses_ditolak': 'Anda tidak memiliki hak akses.', 'saldo_kurang': 'Saldo simpanan tidak mencukupi untuk penarikan.', 'bayar_sukses': 'Pembayaran berhasil disimpan.', 'bayar_gagal': errorMsg || 'Pembayaran gagal, terjadi kesalahan.', 'login_gagal': 'Username atau kata sandi salah.', 'penyesuaian_sukses': 'Jurnal/stok berhasil disesuaikan.', 'password_weak': 'Kata sandi baru terlalu lemah (min 8 karakter, wajib ada huruf & angka).', 'password_mismatch': 'Konfirmasi kata sandi tidak cocok.', 'old_password_wrong': 'Kata sandi lama yang Anda masukkan salah.', 'password_changed_success': 'Kata sandi berhasil diubah.' };
        if (status && messages[status]) {
            let type = 'info';
            if (status.includes('sukses') || status.includes('changed')) type = 'success';
            if (status.includes('gagal') || status.includes('kurang') || status.includes('ditolak') || status.includes('invalid') || status.includes('wrong') || status.includes('mismatch') || status.includes('weak')) type = 'error';
            showToast(messages[status], type);
            const newUrl = new URL(window.location);
            newUrl.searchParams.delete('status');
            newUrl.searchParams.delete('error');
            window.history.replaceState({ path: newUrl.href }, '', newUrl.href);
        }
        
        if (typeof checkSystemTime === 'function') checkSystemTime();
    });
</script>