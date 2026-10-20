<?php
cekAkses('admin');
$judulHalaman = 'Manajemen Pengguna';

$users = query("SELECT id, nama_pengguna, nama_lengkap, role, wajib_ganti_sandi FROM pengguna ORDER BY id ASC");
$daftar_role = daftarRolePengguna();
$total_admin = 0;
foreach ($users as $u) { if ($u['role'] === 'admin') { $total_admin++; } }
$current_id = (int)($_SESSION['user_data']['id'] ?? 0);
?>
<div class="flex flex-wrap justify-between items-center gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-bold">Manajemen Pengguna</h1>
        <p class="text-sm opacity-70 mt-1">Kelola akun yang bisa masuk ke aplikasi. Role menentukan menu dan aksi yang boleh diakses.</p>
    </div>
    <a href="index.php?halaman=pengguna/form_pengguna" class="btn btn-primary shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Pengguna
    </a>
</div>

<div class="overflow-x-auto bg-base-100 rounded-lg shadow">
    <table class="table w-full">
        <thead class="bg-base-200">
            <tr>
                <th class="w-14">No</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role / Hak Akses</th>
                <th class="text-center">Status</th>
                <th class="text-center w-40">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="6" class="text-center py-6 text-base-content/60">Belum ada pengguna.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach ($users as $u): ?>
                <tr class="hover border-b border-base-200">
                    <th><?= $no++ ?></th>
                    <td class="font-mono font-bold">
                        <?= htmlspecialchars($u['nama_pengguna']) ?>
                        <?php if ((int)$u['id'] === $current_id): ?>
                            <span class="badge badge-primary badge-sm ml-1">Anda</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] === 'admin' ? 'badge-success' : 'badge-ghost' ?>">
                            <?= htmlspecialchars($daftar_role[$u['role']] ?? $u['role']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ((int)$u['wajib_ganti_sandi'] === 1): ?>
                            <span class="badge badge-warning badge-sm">Wajib ganti sandi</span>
                        <?php else: ?>
                            <span class="badge badge-info badge-sm">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="flex justify-center gap-1">
                            <a href="index.php?halaman=pengguna/form_pengguna&id=<?= (int)$u['id'] ?>" class="btn btn-xs btn-info text-white">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <?php
                                $tidak_bisa_hapus = ((int)$u['id'] === $current_id);
                                $alasan = 'Tidak bisa menghapus akun sendiri.';
                                if (!$tidak_bisa_hapus && $u['role'] === 'admin' && $total_admin <= 1) {
                                    $tidak_bisa_hapus = true;
                                    $alasan = 'Tidak bisa menghapus administrator terakhir.';
                                }
                                if ($u['role'] === 'admin' && $total_admin <= 1 && (int)$u['id'] !== $current_id) {
                                    $tidak_bisa_hapus = true;
                                    $alasan = 'Tidak bisa menghapus administrator terakhir.';
                                }
                            ?>
                            <button type="button" class="btn btn-xs btn-error text-white" <?= $tidak_bisa_hapus ? 'disabled title="' . htmlspecialchars($alasan) . '"' : '' ?> onclick="konfirmasiHapus('sistem/proses/pengguna/hapus_pengguna.php', <?= (int)$u['id'] ?>, 'Yakin ingin menghapus pengguna <?= htmlspecialchars($u['nama_pengguna'], ENT_QUOTES) ?>? Akun ini tidak akan bisa login lagi.')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="alert alert-info mt-4 text-sm">
    <i class="fas fa-circle-info"></i>
    <span>Akun baru wajib mengganti kata sandi saat login pertama. Minimal 8 karakter, kombinasi huruf dan angka.</span>
</div>
