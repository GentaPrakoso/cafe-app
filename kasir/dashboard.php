<?php
include '../config/session.php';
require_once '../config/database.php';
requireRole(['admin', 'kasir']);
date_default_timezone_set('Asia/Jakarta');
$db = (new Database())->getConnection();
$today = date('Y-m-d');

$stmt = $db->prepare("SELECT * FROM orders WHERE status_pembayaran = 'pending' ORDER BY created_at DESC");
$stmt->execute();
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT COUNT(*) as total_order, COALESCE(SUM(total),0) as total_pendapatan FROM orders WHERE DATE(created_at) = ? AND status_pembayaran = 'paid'");
$stmt->execute([$today]);
$today_summary = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir — Café Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@300;400;600;700&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/kasir.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <?php include '../admin/sidebar.php'; ?>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="brand">Café Modern</div><span class="tagline">Kasir Panel</span>
        </div>
        <div class="sidebar-role"><span>💵</span> Kasir</div>
        <div class="sidebar-section">Navigasi</div>
        <nav>
            <a href="dashboard.php" class="nav-item active"><span class="nav-icon">📋</span> Pesanan Pending</a>
            <a href="history.php" class="nav-item"><span class="nav-icon">🕓</span> History Pesanan</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="urole">Kasir</div>
                </div>
            </div>
            <button class="logout-link" onclick="showLogoutModal()"><span>🚪</span> Logout</button>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="greeting">Kasir Dashboard</div>
                <h1><?= htmlspecialchars($_SESSION['nama']) ?>, <em>siap bertugas!</em></h1>
            </div>
            <div class="topbar-right">
                <div class="date-pill" id="real-time-date">📅 -- : -- : --</div>
                <div class="live-indicator">
                    <div class="pulse-dot"></div> Live
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-label">Order Hari Ini</div>
                <div class="stat-value" id="today-orders"><?= $today_summary['total_order'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Pendapatan Hari Ini</div>
                <div class="stat-value gold" id="today-revenue">Rp <?= number_format($today_summary['total_pendapatan'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-label">Menunggu Bayar</div>
                <div class="stat-value" style="color: var(--amber)" id="pending-count"><?= count($pending_orders) ?></div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <div class="table-title">Pesanan Menunggu Pembayaran <small>KONFIRMASI SETELAH PEMBAYARAN DITERIMA</small></div>
            </div>
            <div class="table-wrap" id="pending-table-wrap">
                <?php if (count($pending_orders) > 0): ?>
                    <table id="table-pending">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Nama</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pending-tbody">
                            <?php foreach ($pending_orders as $order): ?>
                                <tr id="row-<?= $order['id'] ?>" data-id="<?= $order['id'] ?>">
                                    <td><span class="invoice-badge"><?= htmlspecialchars($order['invoice']) ?></span></td>
                                    <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                                    <td><?= $order['nomor_meja'] ?? '—' ?></td>
                                    <td><span class="amount">Rp <?= number_format($order['total'], 0, ',', '.') ?></span></td>
                                    <td><span class="method-chip <?= strtolower($order['metode_pembayaran']) == 'cash' ? 'cash' : '' ?>"><?= htmlspecialchars($order['metode_pembayaran']) ?></span></td>
                                    <td>
                                        <button class="btn-detail" onclick="showDetailOrder(<?= $order['id'] ?>)">📋 Detail Order</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">☕</div>
                        <div class="empty-title">Semua beres!</div>
                        <div class="empty-sub">Tidak ada pesanan yang menunggu pembayaran.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal Detail Order -->
    <div id="detailModal" class="modal-overlay" onclick="hideDetailModal()">
        <div class="modal-box modal-detail" onclick="event.stopPropagation()">
            <div class="modal-head">
                <div class="modal-head-title">Detail Pesanan</div>
                <button class="modal-close" onclick="hideDetailModal()">✕</button>
            </div>
            <div id="detailContent" class="modal-body-detail">
                <div class="loading-state">Memuat detail...</div>
            </div>
            <div class="modal-actions-detail" id="detailActions" style="display: none;">
                <button class="btn-konfirmasi-modal" id="modalKonfirmasi">✓ Konfirmasi Bayar</button>
                <button class="btn-batalkan-modal" id="modalBatalkan">✕ Batalkan</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-emoji">🚪</div>
            <div class="modal-title">Yakin mau logout?</div>
            <div class="modal-desc">Sesi kamu akan diakhiri.</div>
            <div class="modal-actions"><button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button><a href="../logout.php" class="mbtn mbtn-confirm">Ya, Logout</a></div>
        </div>
    </div>

    <style>
        /* Modal Detail tambahan */
        .modal-detail {
            max-width: 550px;
            width: 90%;
            background: var(--bg-2);
            border-radius: var(--radius);
            border: 1px solid var(--border);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .modal-head-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--cream);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--cream-dim);
        }

        .modal-body-detail {
            padding: 20px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .modal-actions-detail {
            padding: 12px 20px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-konfirmasi-modal,
        .btn-batalkan-modal {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }

        .btn-konfirmasi-modal {
            background: var(--green);
            color: #1a1008;
        }

        .btn-konfirmasi-modal:hover {
            background: #5bbf6a;
        }

        .btn-batalkan-modal {
            background: rgba(224, 112, 112, 0.2);
            color: var(--red);
            border: 1px solid rgba(224, 112, 112, 0.3);
        }

        .btn-batalkan-modal:hover {
            background: rgba(224, 112, 112, 0.3);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .detail-item {
            border-bottom: 1px solid var(--border);
            padding: 8px 0;
        }

        .item-catatan {
            font-size: 0.8rem;
            color: var(--cream-dim);
            margin-top: 4px;
        }

        .loading-state {
            text-align: center;
            padding: 20px;
            color: var(--cream-dim);
        }

        .error-msg {
            color: var(--red);
            text-align: center;
            padding: 20px;
        }
    </style>

    <script>
        let currentOrderId = null;

        function showDetailOrder(orderId) {
            currentOrderId = orderId;
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            const actionsDiv = document.getElementById('detailActions');
            modal.classList.add('active');
            content.innerHTML = '<div class="loading-state">Memuat detail...</div>';
            actionsDiv.style.display = 'none';

            fetch(`../api/admin/orders.php?action=get_detail&id=${orderId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderDetailOrder(data.order, data.items);
                        actionsDiv.style.display = 'flex';
                    } else {
                        content.innerHTML = '<div class="error-msg">Gagal memuat detail pesanan.</div>';
                    }
                })
                .catch(() => {
                    content.innerHTML = '<div class="error-msg">Terjadi kesalahan.</div>';
                });
        }

        function renderDetailOrder(order, items) {
            let itemsHtml = '';
            items.forEach(item => {
                itemsHtml += `
            <div class="detail-item">
                <div class="detail-row">
                    <span>${escapeHtml(item.nama_menu)}</span>
                    <span>${item.quantity} x Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</span>
                </div>
                ${item.catatan ? `<div class="item-catatan">📝 ${escapeHtml(item.catatan)}</div>` : ''}
            </div>
        `;
            });

            const html = `
        <div class="detail-row"><strong>Invoice:</strong> <span>${escapeHtml(order.invoice)}</span></div>
        <div class="detail-row"><strong>Pelanggan:</strong> <span>${escapeHtml(order.nama_pelanggan)}</span></div>
        <div class="detail-row"><strong>Meja:</strong> <span>${escapeHtml(order.nomor_meja ?? '-')}</span></div>
        <div class="detail-row"><strong>Metode:</strong> <span>${escapeHtml(order.metode_pembayaran)}</span></div>
        <div class="detail-row"><strong>Status Pembayaran:</strong> <span>${escapeHtml(order.status_pembayaran)}</span></div>
        <div style="margin: 12px 0 8px;"><strong>Pesanan:</strong></div>
        ${itemsHtml}
        <div style="margin-top: 12px; border-top: 1px solid var(--border); padding-top: 8px;">
            <div class="detail-row"><span>Subtotal</span><span>Rp ${new Intl.NumberFormat('id-ID').format(order.subtotal)}</span></div>
            <div class="detail-row"><span>Pajak (10%)</span><span>Rp ${new Intl.NumberFormat('id-ID').format(order.pajak)}</span></div>
            <div class="detail-row"><span>Service (5%)</span><span>Rp ${new Intl.NumberFormat('id-ID').format(order.service_charge)}</span></div>
            <div class="detail-row"><span>Diskon</span><span>Rp ${new Intl.NumberFormat('id-ID').format(order.diskon)}</span></div>
            <div class="detail-row total-row"><strong>Total</strong><strong>Rp ${new Intl.NumberFormat('id-ID').format(order.total)}</strong></div>
        </div>
    `;
            document.getElementById('detailContent').innerHTML = html;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        function hideDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
            currentOrderId = null;
        }

        // Tombol di dalam modal
        document.getElementById('modalKonfirmasi').addEventListener('click', function() {
            if (currentOrderId) {
                konfirmasiBayar(currentOrderId);
                hideDetailModal();
            }
        });
        document.getElementById('modalBatalkan').addEventListener('click', function() {
            if (currentOrderId) {
                // Ambil invoice dari baris tabel (atau dari data yang sudah di-render, tapi simpelnya gunakan fungsi batalkanPesanan dengan invoice kosong, nanti di dalamnya pakai order_id saja)
                batalkanPesanan(currentOrderId, '');
                hideDetailModal();
            }
        });

        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            };
            document.getElementById('real-time-date').innerHTML = '📅 ' + now.toLocaleDateString('id-ID', options);
        }
        updateClock();
        setInterval(updateClock, 1000);

        async function refreshKasirData() {
            try {
                const res = await fetch('../api/admin/orders.php?action=kasir_stats');
                const data = await res.json();
                if (data.success) {
                    document.getElementById('today-orders').innerText = data.today_orders;
                    document.getElementById('today-revenue').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.today_revenue);
                    document.getElementById('pending-count').innerText = data.pending_count;

                    const tbody = document.getElementById('pending-tbody');
                    const wrap = document.getElementById('pending-table-wrap');

                    if (data.pending_orders.length === 0) {
                        if (wrap) wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">☕</div><div class="empty-title">Semua beres!</div><div class="empty-sub">Tidak ada pesanan yang menunggu pembayaran.</div></div>';
                    } else if (tbody) {
                        tbody.innerHTML = data.pending_orders.map(order => `
                    <tr id="row-${order.id}" data-id="${order.id}">
                        <td><span class="invoice-badge">${order.invoice}</span></td>
                        <td>${order.nama_pelanggan}</td>
                        <td>${order.nomor_meja || '—'}</td>
                        <td><span class="amount">Rp ${new Intl.NumberFormat('id-ID').format(order.total)}</span></td>
                        <td><span class="method-chip ${order.metode_pembayaran === 'cash' ? 'cash' : ''}">${order.metode_pembayaran}</span></td>
                        <td><button class="btn-detail" onclick="showDetailOrder(${order.id})">📋 Detail Order</button></td>
                    </tr>
                `).join('');
                    }
                }
            } catch (e) {
                console.error('Refresh failed:', e);
            }
        }
        setInterval(refreshKasirData, 3000);
        refreshKasirData();

        function showLogoutModal() {
            document.getElementById('logout-modal').classList.add('active');
        }

        function hideLogoutModal() {
            document.getElementById('logout-modal').classList.remove('active');
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideLogoutModal();
        });

        function konfirmasiBayar(order_id) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                text: 'Apakah pembayaran sudah diterima?',
                icon: 'question',
                background: '#1c1812',
                color: '#f5ede0',
                iconColor: '#c9a050',
                showCancelButton: true,
                confirmButtonText: 'Ya, sudah dibayar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#6ec97a',
                cancelButtonColor: '#3a3228',
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
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Pembayaran telah dikonfirmasi.',
                                    icon: 'success',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                refreshKasirData();
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: data.message,
                                    icon: 'error',
                                    background: '#1c1812',
                                    color: '#f5ede0'
                                });
                            }
                        });
                }
            });
        }

        function batalkanPesanan(order_id, invoice) {
            Swal.fire({
                title: 'Batalkan Pesanan?',
                html: `Pesanan <strong>${invoice || '#'+order_id}</strong> akan dibatalkan.`,
                icon: 'warning',
                background: '#1c1812',
                color: '#f5ede0',
                iconColor: '#e07070',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Kembali',
                confirmButtonColor: '#e07070',
                cancelButtonColor: '#3a3228',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../api/admin/orders.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=batalkan_pesanan&order_id=${order_id}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Dibatalkan',
                                    text: 'Pesanan berhasil dibatalkan.',
                                    icon: 'success',
                                    background: '#1c1812',
                                    color: '#f5ede0',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                const row = document.getElementById('row-' + order_id);
                                if (row) row.remove();
                                refreshKasirData();
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: data.message,
                                    icon: 'error',
                                    background: '#1c1812',
                                    color: '#f5ede0'
                                });
                            }
                        });
                }
            });
        }
    </script>

</body>

</html>