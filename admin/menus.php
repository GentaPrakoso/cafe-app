<?php
include '../config/session.php';
require_once '../config/database.php';

requireRole(['admin', 'kasir']);

$db = (new Database())->getConnection();
$kategori_stmt = $db->query("SELECT * FROM categories");
$kategoris = $kategori_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@300;400;600;700&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/menus.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include 'sidebar.php'; ?>
    
    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="greeting">Admin Panel</div>
                <h1>Kelola <em>Menu</em></h1>
            </div>
            <div class="topbar-right">
                <div class="date-pill">📅 <?= date('d M Y') ?></div>
                <button class="btn-tambah" id="btn-tambah">
                    <i class="fas fa-plus"></i> Tambah Menu
                </button>
            </div>
        </div>

        <!-- Filter / Search Bar -->
        <div class="search-bar">
            <div class="search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="input-search" class="search-input" placeholder="Cari nama menu...">
            </div>
            <select id="filter-kategori" class="filter-select">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategoris as $kat): ?>
                    <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter-status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="tersedia">Tersedia</option>
                <option value="habis">Habis</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    Daftar Menu
                    <small>Data dimuat secara otomatis</small>
                </div>
                <span class="badge-count" id="menu-count">— item</span>
            </div>
            <div class="table-wrap">
                <table id="tabel-menu">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6">
                                <div class="loading-state">
                                    <div class="spinner"></div>
                                    <span>Memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ═══ MODAL FORM TAMBAH / EDIT ═══ -->
    <div class="modal-overlay" id="modal-form">
        <div class="modal-box form-box" onclick="event.stopPropagation()">
            <div class="modal-head">
                <div class="modal-head-title" id="modal-title">Tambah Menu</div>
                <button class="modal-close" id="btn-batal"><i class="fas fa-times"></i></button>
            </div>

            <form id="form-menu" enctype="multipart/form-data">
                <input type="hidden" id="menu-id" name="id">

                <div class="form-grid">
                    <div class="form-group full">
                        <label class="form-label">Nama Menu</label>
                        <input type="text" id="nama" name="nama" class="form-input" placeholder="contoh: Kopi Susu Aren" required>
                    </div>

                    <div class="form-group full">
                        <label class="form-label">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-input form-textarea" rows="2" placeholder="Deskripsi singkat menu..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" class="form-input" placeholder="25000" required min="0" step="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select id="kategori_id" name="kategori_id" class="form-input" required>
                            <option value="">— Pilih Kategori —</option>
                            <?php foreach ($kategoris as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select id="status" name="status" class="form-input" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gambar</label>
                        <div class="file-upload-wrap" id="file-drop-zone">
                            <input type="file" id="gambar" name="gambar" accept="image/*" class="file-input">
                            <div class="file-upload-ui" id="file-upload-ui">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Klik atau drag foto di sini</span>
                                <small>PNG, JPG, WEBP — maks 2MB</small>
                            </div>
                            <div class="file-preview" id="file-preview" style="display:none">
                                <img id="preview-img" src="" alt="preview">
                                <button type="button" class="file-remove" id="file-remove">✕</button>
                            </div>
                        </div>
                        <small class="form-hint">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="mbtn mbtn-cancel" id="btn-batal-form">Batal</button>
                    <button type="submit" class="mbtn mbtn-confirm">
                        <i class="fas fa-save"></i> Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ LOGOUT MODAL ═══ -->
    <div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-emoji">🚪</div>
            <div class="modal-title-center">Yakin mau logout?</div>
            <div class="modal-desc">Sesi kamu akan diakhiri.</div>
            <div class="modal-actions">
                <button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button>
                <a href="../logout.php" class="mbtn mbtn-confirm-red">Ya, Logout</a>
            </div>
        </div>
    </div>

    <script>
        const apiBase = '../api/admin/menus.php';
        let allMenus = [];

        /* ── Load & Render ── */
        function loadMenus() {
            fetch(apiBase + '?action=list')
                .then(res => res.json())
                .then(data => {
                    allMenus = data;
                    renderMenus(data);
                })
                .catch(() => {
                    document.querySelector('#tabel-menu tbody').innerHTML =
                        `<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">⚠️</div><div class="empty-title">Gagal memuat data</div></div></td></tr>`;
                });
        }

        function renderMenus(data) {
            document.getElementById('menu-count').textContent = `${data.length} item`;
            const tbody = document.querySelector('#tabel-menu tbody');

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">🍽️</div>
                            <div class="empty-title">Belum ada menu</div>
                            <div class="empty-sub">Klik "Tambah Menu" untuk menambahkan.</div>
                        </div>
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = data.map(menu => `
                <tr data-id="${menu.id}">
                    <td>
                        <div class="menu-img-wrap">
                            <img src="../uploads/${menu.gambar}" alt="${menu.nama}"
                                 onerror="this.src='../uploads/default.jpg'">
                        </div>
                    </td>
                    <td>
                        <div class="menu-name">${menu.nama}</div>
                        ${menu.deskripsi ? `<div class="menu-desc">${menu.deskripsi}</div>` : ''}
                    </td>
                    <td><span class="cat-chip">${menu.kategori_nama ?? '—'}</span></td>
                    <td><span class="amount">Rp ${new Intl.NumberFormat('id-ID').format(menu.harga)}</span></td>
                    <td>
                        <span class="status-chip ${menu.status === 'tersedia' ? 'status-tersedia' : 'status-habis'}">
                            ${menu.status === 'tersedia' ? '✓ Tersedia' : '✕ Habis'}
                        </span>
                    </td>
                    <td>
                        <div class="aksi-group">
                            <button class="btn-edit btn-icon" data-id="${menu.id}" title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-hapus btn-icon" data-id="${menu.id}" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        /* ── Filter / Search ── */
        function applyFilters() {
            const search = document.getElementById('input-search').value.toLowerCase();
            const kat = document.getElementById('filter-kategori').value;
            const stat = document.getElementById('filter-status').value;

            const filtered = allMenus.filter(m => {
                const matchSearch = m.nama.toLowerCase().includes(search);
                const matchKat = kat === '' || String(m.kategori_id) === kat;
                const matchStat = stat === '' || m.status === stat;
                return matchSearch && matchKat && matchStat;
            });
            renderMenus(filtered);
        }

        document.getElementById('input-search').addEventListener('input', applyFilters);
        document.getElementById('filter-kategori').addEventListener('change', applyFilters);
        document.getElementById('filter-status').addEventListener('change', applyFilters);

        /* ── Modal Form ── */
        function showModal(title = 'Tambah Menu') {
    document.getElementById('modal-title').innerText = title;
    document.getElementById('modal-form').classList.add('active');
    document.getElementById('form-menu').reset();
    document.getElementById('menu-id').value = '';
    resetFilePreview();
}

        function hideModal() {
            document.getElementById('modal-form').classList.remove('active');
        }

        document.getElementById('btn-tambah').addEventListener('click', () => showModal());
        document.getElementById('btn-batal').addEventListener('click', hideModal);
        document.getElementById('btn-batal-form').addEventListener('click', hideModal);

/* ── Edit Menu ── */
/* ── Edit Menu ── */
function editMenu(id) {
    console.log('Mengedit menu ID:', id);
    
    // Tampilkan loading
    Swal.fire({
        title: 'Memuat data...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    fetch(apiBase + `?action=get&id=${id}`)
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(menu => {
            Swal.close();
            console.log('Data menu diterima:', menu);
            
            if (menu.error) {
                Swal.fire('Error', menu.error, 'error');
                return;
            }
            
            if (!menu || !menu.id) {
                Swal.fire('Error', 'Gagal mengambil data menu', 'error');
                return;
            }
            
            // Isi form dengan data menu
            document.getElementById('menu-id').value = menu.id;
            document.getElementById('nama').value = menu.nama || '';
            document.getElementById('deskripsi').value = menu.deskripsi || '';
            document.getElementById('harga').value = menu.harga || 0;
            document.getElementById('kategori_id').value = menu.kategori_id || '';
            document.getElementById('status').value = menu.status || 'tersedia';
            
            // Reset preview gambar
            resetFilePreview();
            
            // Tampilkan preview gambar yang sudah ada
            if (menu.gambar && menu.gambar !== '' && menu.gambar !== 'default.jpg') {
                const previewImg = document.getElementById('preview-img');
                const previewWrap = document.getElementById('file-preview');
                const uploadUI = document.getElementById('file-upload-ui');
                
                previewImg.src = '../uploads/' + menu.gambar;
                previewWrap.style.display = 'flex';
                uploadUI.style.display = 'none';
            }
            
            // Tampilkan modal dengan judul Edit
            document.getElementById('modal-title').innerText = 'Edit Menu';
            document.getElementById('modal-form').classList.add('active');
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire('Error', 'Gagal mengambil data menu: ' + error.message, 'error');
        });
}

        /* ── Submit ── */
        document.getElementById('form-menu').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('menu-id').value;
            const isEdit = id !== '';
            const fd = new FormData(this);
            fd.append('action', isEdit ? 'update' : 'create');

            fetch(apiBase, {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: resp.message,
                            icon: 'success',
                            background: '#201c13',
                            color: '#f5ede0',
                            iconColor: '#6ec97a',
                            confirmButtonColor: '#c9a050'
                        });
                        hideModal();
                        loadMenus();
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: resp.message,
                            icon: 'error',
                            background: '#201c13',
                            color: '#f5ede0',
                            confirmButtonColor: '#c9a050'
                        });
                    }
                })
                .catch(() => Swal.fire('Error', 'Terjadi kesalahan', 'error'));
        });

        /* ── Delegation: edit & hapus ── */
        document.querySelector('#tabel-menu tbody').addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            if (!btn) return;
            const id = btn.dataset.id;

            if (btn.classList.contains('btn-edit')) {
                editMenu(id);
            } else if (btn.classList.contains('btn-hapus')) {
                Swal.fire({
                    title: 'Hapus menu ini?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    background: '#201c13',
                    color: '#f5ede0',
                    iconColor: '#e07070',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e07070',
                    cancelButtonColor: '#3e3828',
                }).then(result => {
                    if (result.isConfirmed) {
                        const fd = new FormData();
                        fd.append('action', 'delete');
                        fd.append('id', id);
                        fetch(apiBase, {
                                method: 'POST',
                                body: fd
                            })
                            .then(res => res.json())
                            .then(resp => {
                                if (resp.success) {
                                    Swal.fire({
                                        title: 'Dihapus!',
                                        text: 'Menu berhasil dihapus.',
                                        icon: 'success',
                                        background: '#201c13',
                                        color: '#f5ede0',
                                        confirmButtonColor: '#c9a050'
                                    });
                                    loadMenus();
                                } else {
                                    Swal.fire('Gagal', resp.message, 'error');
                                }
                            });
                    }
                });
            }
        });

        /* ── File Preview ── */
        const fileInput = document.getElementById('gambar');
        const previewWrap = document.getElementById('file-preview');
        const previewImg = document.getElementById('preview-img');
        const uploadUI = document.getElementById('file-upload-ui');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                previewWrap.style.display = 'flex';
                uploadUI.style.display = 'none';
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('file-remove').addEventListener('click', resetFilePreview);

        function resetFilePreview() {
    const fileInput = document.getElementById('gambar');
    if (fileInput) fileInput.value = '';
    const previewImg = document.getElementById('preview-img');
    if (previewImg) previewImg.src = '';
    const previewWrap = document.getElementById('file-preview');
    const uploadUI = document.getElementById('file-upload-ui');
    if (previewWrap) previewWrap.style.display = 'none';
    if (uploadUI) uploadUI.style.display = 'flex';
}

        /* ── Logout Modal ── */
        function showLogoutModal() {
            document.getElementById('logout-modal').classList.add('active');
        }

        function hideLogoutModal() {
            document.getElementById('logout-modal').classList.remove('active');
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                hideModal();
                hideLogoutModal();
            }
        });

        /* ── Init ── */
        document.addEventListener('DOMContentLoaded', loadMenus);
    </script>
</body>

</html>