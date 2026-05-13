<?php
include '../config/session.php';
requireRole(['admin', 'kasir']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan — Café Modern Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/orders.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <div class="page-heading">Manajemen Transaksi</div>
        <div class="page-title">Daftar <em>Pesanan</em></div>

        <div class="table-card">
            <div class="table-card-header">
                <div class="table-card-title">
                    Semua Pesanan
                    <small>Diperbarui otomatis setiap 10 detik</small>
                </div>
                <div class="live-badge">
                    <div class="pulse-dot"></div> Live
                </div>
            </div>

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
        </div>

    </main>

    <script>
        function payBadge(status) {
            const s = (status ?? '').toLowerCase();
            if (['lunas','paid','confirmed','sukses'].includes(s))
                return `<span class="pay-badge lunas">✓ ${status}</span>`;
            if (['gagal','failed','cancelled'].includes(s))
                return `<span class="pay-badge gagal">✕ ${status}</span>`;
            return `<span class="pay-badge pending">⏳ ${status}</span>`;
        }

        function loadOrders() {
            fetch('../api/admin/orders.php?action=list')
                .then(res => res.json())
                .then(orders => {
                    let html = '';
                    orders.forEach(order => {
                        const isPending = order.status_pembayaran === 'pending';
                        html += `
                            <tr>
                                <td>${order.invoice}</td>
                                <td>${order.nama_pelanggan}</td>
                                <td>${order.nomor_meja ?? '—'}</td>
                                <td>
                                    <select onchange="updateStatus(${order.id}, this.value)">
                                        <option value="menunggu_konfirmasi" ${order.status_pesanan === 'menunggu_konfirmasi' ? 'selected' : ''}>Menunggu Konfirmasi</option>
                                        <option value="diproses"           ${order.status_pesanan === 'diproses'           ? 'selected' : ''}>Diproses</option>
                                        <option value="sedang_dibuat"      ${order.status_pesanan === 'sedang_dibuat'      ? 'selected' : ''}>Sedang Dibuat</option>
                                        <option value="siap_diantar"       ${order.status_pesanan === 'siap_diantar'       ? 'selected' : ''}>Siap Diantar</option>
                                        <option value="selesai"            ${order.status_pesanan === 'selesai'            ? 'selected' : ''}>Selesai</option>
                                    </select>
                                </td>
                                <td>${payBadge(order.status_pembayaran)}</td>
                                <td>
                                    <div class="btn-actions">
                                        <button class="btn-print" onclick="printInvoice(${order.id})">🖨 Cetak</button>
                                        <button class="btn-konfirmasi"
                                                onclick="konfirmasiBayar(${order.id})"
                                                ${!isPending ? 'disabled' : ''}>
                                            ${isPending ? '✓ Konfirmasi' : '✓ Lunas'}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    document.querySelector('#order-table tbody').innerHTML =
                        html || '<tr><td colspan="6" class="empty-cell">☕ Belum ada pesanan.</td></tr>';
                });
        }

        function updateStatus(order_id, status) {
            fetch('../api/admin/orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_status&order_id=${order_id}&status=${status}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Status diperbarui!', icon: 'success',
                        timer: 1500, showConfirmButton: false,
                        background: '#1c1812', color: '#f5ede0', iconColor: '#6ec97a'
                    });
                } else {
                    Swal.fire({ title: 'Gagal', icon: 'error', background: '#1c1812', color: '#f5ede0' });
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
                cancelButtonText: 'Batal',
                background: '#1c1812', color: '#f5ede0',
                iconColor: '#c9a050',
                confirmButtonColor: '#6ec97a',
                cancelButtonColor: '#3d3328'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('../api/admin/orders.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=konfirmasi_pembayaran&order_id=${order_id}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil', text: 'Pembayaran telah dikonfirmasi.',
                                icon: 'success', background: '#1c1812', color: '#f5ede0',
                                iconColor: '#6ec97a', confirmButtonColor: '#c9a050'
                            });
                            loadOrders();
                        } else {
                            Swal.fire({
                                title: 'Gagal', text: data.message || 'Terjadi kesalahan',
                                icon: 'error', background: '#1c1812', color: '#f5ede0'
                            });
                        }
                    });
                }
            });
        }

        function printInvoice(order_id) {
            window.open(`print_invoice.php?id=${order_id}`, '_blank');
        }

        loadOrders();
        setInterval(loadOrders, 10000);
    </script>
</body>
</html>