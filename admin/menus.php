<?php
// admin/menus.php
include '../config/session.php';
requireRole(['admin', 'kasir']); // hanya admin & kasir yang bisa kelola menu

// Koneksi database (jika diperlukan untuk ambil kategori)
$db = (new Database())->getConnection();
$kategori_stmt = $db->query("SELECT * FROM categories");
$kategoris = $kategori_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome untuk ikon (opsional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Tambahan styling khusus halaman menu */
        .admin-content {
            padding: 2rem;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-primary {
            background: #6f4e37;
            color: white;
        }

        .btn-warning {
            background: #f0ad4e;
            color: white;
        }

        .btn-danger {
            background: #d9534f;
            color: white;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.85rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: var(--primary);
            color: white;
        }

        .table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-content h3 {
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar navigasi (sama seperti dashboard) -->
        <nav class="admin-nav">
            <h2>☕ Admin</h2>
            <ul>
                <li><a href="dashboard.php">Beranda</a></li>
                <li><a href="orders.php">Pesanan</a></li>
                <li><a href="menus.php" class="active">Menu</a></li>
                <li><a href="vouchers.php">Voucher</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Daftar Menu</h2>
                <button class="btn btn-primary" id="btn-tambah"><i class="fas fa-plus"></i> Tambah Menu</button>
            </div>

            <table class="table" id="tabel-menu">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data dimuat via AJAX -->
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Form (Tambah/Edit) -->
    <div class="modal" id="modal-form">
        <div class="modal-content">
            <h3 id="modal-title">Tambah Menu</h3>
            <form id="form-menu" enctype="multipart/form-data">
                <input type="hidden" id="menu-id" name="id">
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" required min="0" step="100">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($kategoris as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= $kat['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="status" name="status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gambar</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <small>Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn" id="btn-batal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Base URL API
        const apiBase = '../api/admin/menus.php';

        // Load data menu
        function loadMenus() {
            fetch(apiBase + '?action=list')
                .then(res => res.json())
                .then(data => {
                    let rows = '';
                    data.forEach(menu => {
                        rows += `
                            <tr>
                                <td><img src="../uploads/${menu.gambar}" alt="${menu.nama}" onerror="this.onerror=null;this.src='../uploads/default.jpg'"></td>
                                <td>${menu.nama}</td>
                                <td>${menu.kategori_nama ?? ''}</td>
                                <td>Rp ${new Intl.NumberFormat('id-ID').format(menu.harga)}</td>
                                <td><span class="status ${menu.status}">${menu.status}</span></td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-edit" data-id="${menu.id}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm btn-hapus" data-id="${menu.id}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                    document.querySelector('#tabel-menu tbody').innerHTML = rows;
                });
        }

        // Tampilkan modal
        function showModal(title = 'Tambah Menu') {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-form').classList.add('active');
            // Reset form
            document.getElementById('form-menu').reset();
            document.getElementById('menu-id').value = '';
        }

        function hideModal() {
            document.getElementById('modal-form').classList.remove('active');
        }

        // Ambil data untuk edit
        function editMenu(id) {
            fetch(apiBase + `?action=get&id=${id}`)
                .then(res => res.json())
                .then(menu => {
                    document.getElementById('menu-id').value = menu.id;
                    document.getElementById('nama').value = menu.nama;
                    document.getElementById('deskripsi').value = menu.deskripsi ?? '';
                    document.getElementById('harga').value = menu.harga;
                    document.getElementById('kategori_id').value = menu.kategori_id;
                    document.getElementById('status').value = menu.status;
                    // Gambar tidak bisa di-set, biarkan kosong
                    showModal('Edit Menu');
                })
                .catch(err => Swal.fire('Error', 'Gagal mengambil data menu', 'error'));
        }

        // Submit form (tambah/update) dengan upload file
        document.getElementById('form-menu').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('menu-id').value;
            const isEdit = id !== '';
            const formData = new FormData(this);
            formData.append('action', isEdit ? 'update' : 'create');

            fetch(apiBase, {
                    method: 'POST',
                    body: formData
                    // Jangan set Content-Type, biarkan browser set multipart/form-data
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        Swal.fire('Berhasil', response.message, 'success');
                        hideModal();
                        loadMenus();
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    console.error(err);
                });
        });

        // Tombol tambah
        document.getElementById('btn-tambah').addEventListener('click', () => showModal());

        // Tombol batal
        document.getElementById('btn-batal').addEventListener('click', hideModal);

        // Delegasi event untuk tombol edit & hapus
        document.querySelector('#tabel-menu tbody').addEventListener('click', function(e) {
            const target = e.target.closest('button');
            if (!target) return;
            const id = target.dataset.id;

            if (target.classList.contains('btn-edit')) {
                editMenu(id);
            } else if (target.classList.contains('btn-hapus')) {
                Swal.fire({
                    title: 'Yakin hapus menu ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('id', id);
                        fetch(apiBase, {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(response => {
                                if (response.success) {
                                    Swal.fire('Terhapus!', 'Menu berhasil dihapus.', 'success');
                                    loadMenus();
                                } else {
                                    Swal.fire('Gagal', response.message, 'error');
                                }
                            });
                    }
                });
            }
        });

        // Inisialisasi
        document.addEventListener('DOMContentLoaded', loadMenus);
    </script>
</body>

</html>