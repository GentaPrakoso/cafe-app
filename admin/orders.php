<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan - Café Modern</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--primary);
            color: white;
        }

        select {
            padding: 5px;
            border-radius: 6px;
        }

        .btn-print,
        .btn-konfirmasi {
            background: var(--primary);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-konfirmasi {
            background: #28a745;
        }

        .btn-konfirmasi:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <h2>☕ Admin</h2>
            <ul>
                <li><a href="dashboard.php">Beranda</a></li>
                <li><a href="orders.php" class="active">Pesanan</a></li>
                <li><a href="menus.php">Menu</a></li>
                <li><a href="vouchers.php">Voucher</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
        <main class="admin-content">
            <h2>Daftar Pesanan</h2>
            <table id="order-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Nama</th>
                        <th>Meja</th>
                        <th>Status Pesanan</th>
                        <th>Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </main>
    </div>

    <script>
        function loadOrders() {
            fetch('../api/admin/orders.php?action=list')
                .then(res => res.json())
                .then(orders => {
                    let html = '';
                    orders.forEach(order => {
                        // Cek status pembayaran
                        const isPending = order.status_pembayaran === 'pending';
                        html += `
                            <tr>
                                <td>${order.invoice}</td>
                                <td>${order.nama_pelanggan}</td>
                                <td>${order.nomor_meja ?? '-'}</td>
                                <td>
                                    <select onchange="updateStatus(${order.id}, this.value)">
                                        <option value="menunggu_konfirmasi" ${order.status_pesanan === 'menunggu_konfirmasi' ? 'selected' : ''}>Menunggu Konfirmasi</option>
                                        <option value="diproses" ${order.status_pesanan === 'diproses' ? 'selected' : ''}>Diproses</option>
                                        <option value="sedang_dibuat" ${order.status_pesanan === 'sedang_dibuat' ? 'selected' : ''}>Sedang Dibuat</option>
                                        <option value="siap_diantar" ${order.status_pesanan === 'siap_diantar' ? 'selected' : ''}>Siap Diantar</option>
                                        <option value="selesai" ${order.status_pesanan === 'selesai' ? 'selected' : ''}>Selesai</option>
                                    </select>
                                </td>
                                <td>${order.status_pembayaran}</td>
                                <td>
                                    <button class="btn-print" onclick="printInvoice(${order.id})">Cetak Invoice</button>
                                    <button class="btn-konfirmasi" 
                                            onclick="konfirmasiBayar(${order.id})" 
                                            ${!isPending ? 'disabled' : ''}>
                                        Konfirmasi Bayar
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    document.querySelector('#order-table tbody').innerHTML = html || '<tr><td colspan="6">Belum ada pesanan.</td></tr>';
                });
        }

        function updateStatus(order_id, status) {
            fetch('../api/admin/orders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=update_status&order_id=${order_id}&status=${status}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Status diperbarui!', '', 'success');
                    } else {
                        Swal.fire('Gagal', '', 'error');
                    }
                });
        }

        function konfirmasiBayar(order_id) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: 'Apakah pembayaran sudah diterima?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, sudah dibayar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/admin/orders.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=konfirmasi_pembayaran&order_id=${order_id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil', 'Pembayaran telah dikonfirmasi.', 'success');
                                loadOrders(); // Refresh tabel
                            } else {
                                Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                            }
                        });
                }
            });
        }

        function printInvoice(order_id) {
            window.open(`print_invoice.php?id=${order_id}`, '_blank');
        }

        // Load awal
        loadOrders();
        // Realtime polling tiap 10 detik
        setInterval(loadOrders, 10000);
    </script>
</body>

</html>