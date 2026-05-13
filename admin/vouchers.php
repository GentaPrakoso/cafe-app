<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher — Café Modern Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/vouchers.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="brand">Café Modern</div>
            <span class="tagline">Admin Panel</span>
        </div>

        <div class="sidebar-section">Navigasi</div>

        <nav>
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">⬛</span> Dashboard
            </a>
            <a href="orders.php" class="nav-item">
                <span class="nav-icon">📋</span> Pesanan
            </a>
            <a href="menus.php" class="nav-item">
                <span class="nav-icon">☕</span> Kelola Menu
            </a>
            <a href="vouchers.php" class="nav-item active">
                <span class="nav-icon">🏷</span> Voucher
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div>
                    <div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="urole"><?= ucfirst($_SESSION['role'] ?? 'Staff') ?></div>
                </div>
            </div>
            <a href="../logout.php" class="logout-link">
                <span>🚪</span> Logout
            </a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <div class="page-heading">Promo & Diskon</div>
        <div class="page-title">Manajemen <em>Voucher</em></div>

        <!-- Form Tambah/Edit -->
        <div class="form-card">
            <div class="form-card-title">Tambah / Edit Voucher</div>
            <input type="hidden" id="voucher-id">
            <div class="form-row">
                <input type="text"   id="kode"         placeholder="Kode Voucher">
                <select id="tipe">
                    <option value="persen">Persen (%)</option>
                    <option value="nominal">Nominal (Rp)</option>
                </select>
                <input type="number" id="nilai"         placeholder="Nilai Diskon">
                <input type="number" id="min_pembelian" placeholder="Min. Pembelian" value="0">
            </div>
            <div class="form-row">
                <input type="date" id="tanggal_mulai">
                <input type="date" id="tanggal_berakhir">
            </div>
            <button class="btn-simpan" id="btn-simpan">💾 Simpan Voucher</button>
        </div>

        <!-- Tabel Voucher -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    Daftar Voucher
                    <small>Semua voucher aktif & nonaktif</small>
                </div>
                <span class="voucher-count" id="voucher-count">— voucher</span>
            </div>

            <table id="tabel-voucher">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Nilai</th>
                        <th>Min. Pembelian</th>
                        <th>Masa Berlaku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </main>

    <script>
        function loadVouchers() {
            fetch('../api/admin/vouchers.php?action=list')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('voucher-count').textContent = data.length + ' voucher';
                    let html = '';
                    data.forEach(v => {
                        const tipeBadge = v.tipe_diskon === 'persen'
                            ? `<span class="badge-tipe badge-persen">% Persen</span>`
                            : `<span class="badge-tipe badge-nominal">Rp Nominal</span>`;

                        const nilaiText = v.tipe_diskon === 'persen'
                            ? `<span class="nilai-text">${v.nilai}%</span>`
                            : `<span class="nilai-text">Rp ${Number(v.nilai).toLocaleString('id-ID')}</span>`;

                        const minBeli = v.minimal_pembelian > 0
                            ? `Rp ${Number(v.minimal_pembelian).toLocaleString('id-ID')}`
                            : `<span style="color:var(--cream-dim)">—</span>`;

                        const berlaku = (v.tanggal_mulai || v.tanggal_berakhir)
                            ? `<span class="berlaku-text">${v.tanggal_mulai ?? '∞'} — ${v.tanggal_berakhir ?? '∞'}</span>`
                            : `<span class="berlaku-text" style="color:var(--cream-dim)">Tidak terbatas</span>`;

                        html += `<tr>
                            <td><strong>${v.kode}</strong></td>
                            <td>${tipeBadge}</td>
                            <td>${nilaiText}</td>
                            <td>${minBeli}</td>
                            <td>${berlaku}</td>
                            <td>
                                <div class="btn-actions">
                                    <button class="btn-edit"  onclick="editVoucher(${v.id})">✏ Edit</button>
                                    <button class="btn-hapus" onclick="hapusVoucher(${v.id})">🗑 Hapus</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                    document.querySelector('#tabel-voucher tbody').innerHTML =
                        html || '<tr><td colspan="6" class="empty-cell">🏷 Belum ada voucher.</td></tr>';
                });
        }

        function editVoucher(id) {
            fetch(`../api/admin/vouchers.php?action=get&id=${id}`)
                .then(res => res.json())
                .then(v => {
                    document.getElementById('voucher-id').value       = v.id;
                    document.getElementById('kode').value             = v.kode;
                    document.getElementById('tipe').value             = v.tipe_diskon;
                    document.getElementById('nilai').value            = v.nilai;
                    document.getElementById('tanggal_mulai').value    = v.tanggal_mulai    ?? '';
                    document.getElementById('tanggal_berakhir').value = v.tanggal_berakhir ?? '';
                    document.getElementById('min_pembelian').value    = v.minimal_pembelian;
                    document.querySelector('.btn-simpan').textContent = '💾 Perbarui Voucher';
                    document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth' });
                });
        }

        function hapusVoucher(id) {
            Swal.fire({
                title: 'Hapus Voucher?',
                text: 'Voucher ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                background: '#1c1812', color: '#f5ede0',
                iconColor: '#e07070',
                confirmButtonColor: '#e07070',
                cancelButtonColor: '#3d3328'
            }).then(res => {
                if (res.isConfirmed) {
                    fetch('../api/admin/vouchers.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${id}`
                    }).then(() => {
                        Swal.fire({
                            title: 'Dihapus!', icon: 'success', timer: 1400,
                            showConfirmButton: false,
                            background: '#1c1812', color: '#f5ede0', iconColor: '#6ec97a'
                        });
                        loadVouchers();
                    });
                }
            });
        }

        document.getElementById('btn-simpan').addEventListener('click', function () {
            const id     = document.getElementById('voucher-id').value;
            const action = id ? 'update' : 'create';

            const formData = new FormData();
            formData.append('action',            action);
            if (id) formData.append('id',        id);
            formData.append('kode',              document.getElementById('kode').value);
            formData.append('tipe',              document.getElementById('tipe').value);
            formData.append('nilai',             document.getElementById('nilai').value);
            formData.append('tanggal_mulai',     document.getElementById('tanggal_mulai').value);
            formData.append('tanggal_berakhir',  document.getElementById('tanggal_berakhir').value);
            formData.append('minimal_pembelian', document.getElementById('min_pembelian').value);

            fetch('../api/admin/vouchers.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!', text: data.message, icon: 'success',
                            background: '#1c1812', color: '#f5ede0',
                            iconColor: '#6ec97a', confirmButtonColor: '#c9a050'
                        });
                        document.getElementById('voucher-id').value       = '';
                        document.getElementById('kode').value             = '';
                        document.getElementById('nilai').value            = '';
                        document.getElementById('tanggal_mulai').value    = '';
                        document.getElementById('tanggal_berakhir').value = '';
                        document.getElementById('min_pembelian').value    = '0';
                        document.querySelector('.btn-simpan').textContent = '💾 Simpan Voucher';
                        loadVouchers();
                    } else {
                        Swal.fire({
                            title: 'Gagal', text: data.message, icon: 'error',
                            background: '#1c1812', color: '#f5ede0'
                        });
                    }
                });
        });

        loadVouchers();
    </script>

</body>
</html>