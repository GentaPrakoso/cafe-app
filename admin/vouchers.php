<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Voucher</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .voucher-form {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .voucher-form .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .voucher-form input,
        .voucher-form select {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        th,
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>☕ Admin</h2>
            <ul>
                <li><a href="dashboard.php">Beranda</a></li>
                <li><a href="orders.php">Pesanan</a></li>
                <li><a href="menus.php">Menu</a></li>
                <li><a href="vouchers.php" class="active">Voucher</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <h2>Manajemen Voucher</h2>
            <div class="voucher-form" id="form-voucher">
                <h3>Tambah/Edit Voucher</h3>
                <input type="hidden" id="voucher-id">
                <div class="form-row">
                    <input type="text" id="kode" placeholder="Kode Voucher" required>
                    <select id="tipe">
                        <option value="persen">Persen</option>
                        <option value="nominal">Nominal</option>
                    </select>
                    <input type="number" id="nilai" placeholder="Nilai Diskon" required>
                </div>
                <div class="form-row">
                    <input type="date" id="tanggal_mulai">
                    <input type="date" id="tanggal_berakhir">
                    <input type="number" id="min_pembelian" placeholder="Min. Pembelian" value="0">
                </div>
                <button class="btn" id="btn-simpan">Simpan</button>
            </div>

            <h3>Daftar Voucher</h3>
            <table id="tabel-voucher">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Nilai</th>
                        <th>Berlaku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </main>
    </div>

    <script>
        function loadVouchers() {
            fetch('../api/admin/vouchers.php?action=list')
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    data.forEach(v => {
                        html += `<tr>
                            <td>${v.kode}</td>
                            <td>${v.tipe_diskon}</td>
                            <td>${v.nilai}</td>
                            <td>${v.tanggal_mulai ?? '-'} s/d ${v.tanggal_berakhir ?? '-'}</td>
                            <td>
                                <button onclick="editVoucher(${v.id})">Edit</button>
                                <button onclick="hapusVoucher(${v.id})">Hapus</button>
                            </td>
                        </tr>`;
                    });
                    document.querySelector('#tabel-voucher tbody').innerHTML = html;
                });
        }

        function editVoucher(id) {
            fetch(`../api/admin/vouchers.php?action=get&id=${id}`) // Kita butuh endpoint get (tambahkan di api)
                .then(res => res.json())
                .then(v => {
                    document.getElementById('voucher-id').value = v.id;
                    document.getElementById('kode').value = v.kode;
                    document.getElementById('tipe').value = v.tipe_diskon;
                    document.getElementById('nilai').value = v.nilai;
                    document.getElementById('tanggal_mulai').value = v.tanggal_mulai ?? '';
                    document.getElementById('tanggal_berakhir').value = v.tanggal_berakhir ?? '';
                    document.getElementById('min_pembelian').value = v.minimal_pembelian;
                });
        }

        function hapusVoucher(id) {
            Swal.fire('Yakin?', '', 'warning', {
                showCancelButton: true
            }).then(res => {
                if (res.isConfirmed) {
                    fetch('../api/admin/vouchers.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=delete&id=${id}`
                    }).then(() => loadVouchers());
                }
            });
        }

        document.getElementById('btn-simpan').addEventListener('click', function() {
            const id = document.getElementById('voucher-id').value;
            const action = id ? 'update' : 'create';
            const formData = new FormData();
            formData.append('action', action);
            if (id) formData.append('id', id);
            formData.append('kode', document.getElementById('kode').value);
            formData.append('tipe', document.getElementById('tipe').value);
            formData.append('nilai', document.getElementById('nilai').value);
            formData.append('tanggal_mulai', document.getElementById('tanggal_mulai').value);
            formData.append('tanggal_berakhir', document.getElementById('tanggal_berakhir').value);
            formData.append('minimal_pembelian', document.getElementById('min_pembelian').value);

            fetch('../api/admin/vouchers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil', data.message, 'success');
                        document.getElementById('form-voucher').reset();
                        document.getElementById('voucher-id').value = '';
                        loadVouchers();
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
        });

        loadVouchers();
    </script>
</body>

</html>