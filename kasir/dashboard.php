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
    <div class="sidebar-logo"><div class="brand">Café Modern</div><span class="tagline">Kasir Panel</span></div>
    <div class="sidebar-role"><span>💵</span> Kasir</div>
    <div class="sidebar-section">Navigasi</div>
    <nav>
        <a href="dashboard.php" class="nav-item active"><span class="nav-icon">📋</span> Pesanan Pending</a>
        <a href="history.php" class="nav-item"><span class="nav-icon">🕓</span> History Pesanan</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-chip"><div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div><div class="user-info"><div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div><div class="urole">Kasir</div></div></div>
        <button class="logout-link" onclick="showLogoutModal()"><span>🚪</span> Logout</button>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div class="greeting">Kasir Dashboard</div><h1><?= htmlspecialchars($_SESSION['nama']) ?>, <em>siap bertugas!</em></h1></div>
        <div class="topbar-right"><div class="date-pill" id="real-time-date">📅 -- : -- : --</div><div class="live-indicator"><div class="pulse-dot"></div> Live</div></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-label">Order Hari Ini</div><div class="stat-value" id="today-orders"><?= $today_summary['total_order'] ?? 0 ?></div></div>
        <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-label">Pendapatan Hari Ini</div><div class="stat-value gold" id="today-revenue">Rp <?= number_format($today_summary['total_pendapatan'] ?? 0, 0, ',', '.') ?></div></div>
        <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-label">Menunggu Bayar</div><div class="stat-value" style="color: var(--amber)" id="pending-count"><?= count($pending_orders) ?></div></div>
    </div>

    <div class="table-section">
        <div class="table-header"><div class="table-title">Pesanan Menunggu Pembayaran <small>Konfirmasi setelah pembayaran diterima</small></div></div>
        <div class="table-wrap" id="pending-table-wrap">
            <?php if (count($pending_orders) > 0): ?>
            <table id="table-pending">
                <thead><tr><th>Invoice</th><th>Nama</th><th>Meja</th><th>Total</th><th>Metode</th><th>Aksi</th></tr></thead>
                <tbody id="pending-tbody">
                <?php foreach ($pending_orders as $order): ?>
                <tr id="row-<?= $order['id'] ?>" data-id="<?= $order['id'] ?>">
                    <td><span class="invoice-badge"><?= htmlspecialchars($order['invoice']) ?></span></td>
                    <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                    <td><?= $order['nomor_meja'] ?? '—' ?></td>
                    <td><span class="amount">Rp <?= number_format($order['total'], 0, ',', '.') ?></span></td>
                    <td><span class="method-chip <?= strtolower($order['metode_pembayaran']) == 'cash' ? 'cash' : '' ?>"><?= htmlspecialchars($order['metode_pembayaran']) ?></span></td>
                    <td><div class="aksi-group"><button class="btn-konfirmasi" onclick="konfirmasiBayar(<?= $order['id'] ?>)">✓ Konfirmasi Bayar</button><button class="btn-batalkan" onclick="batalkanPesanan(<?= $order['id'] ?>, '<?= htmlspecialchars($order['invoice']) ?>')">✕ Batalkan</button></div></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state"><div class="empty-icon">☕</div><div class="empty-title">Semua beres!</div><div class="empty-sub">Tidak ada pesanan yang menunggu pembayaran.</div></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()"><div class="modal-box" onclick="event.stopPropagation()"><div class="modal-emoji">🚪</div><div class="modal-title">Yakin mau logout?</div><div class="modal-desc">Sesi kamu akan diakhiri.</div><div class="modal-actions"><button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button><a href="../logout.php" class="mbtn mbtn-confirm">Ya, Logout</a></div></div></div>

<script>
function updateClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: 'short', year: 'numeric' };
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
                        <td><div class="aksi-group"><button class="btn-konfirmasi" onclick="konfirmasiBayar(${order.id})">✓ Konfirmasi Bayar</button><button class="btn-batalkan" onclick="batalkanPesanan(${order.id}, '${order.invoice}')">✕ Batalkan</button></div></td>
                    </tr>
                `).join('');
            }
        }
    } catch(e) { console.error('Refresh failed:', e); }
}
setInterval(refreshKasirData, 3000);
refreshKasirData();

function showLogoutModal() { document.getElementById('logout-modal').classList.add('active'); }
function hideLogoutModal() { document.getElementById('logout-modal').classList.remove('active'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideLogoutModal(); });

function konfirmasiBayar(order_id) {
    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        text: 'Apakah pembayaran sudah diterima?',
        icon: 'question',
        background: '#1c1812', color: '#f5ede0', iconColor: '#c9a050',
        showCancelButton: true, confirmButtonText: 'Ya, sudah dibayar', cancelButtonText: 'Batal',
        confirmButtonColor: '#6ec97a', cancelButtonColor: '#3a3228',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/admin/orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=konfirmasi_pembayaran&order_id=${order_id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ title: 'Berhasil!', text: 'Pembayaran telah dikonfirmasi.', icon: 'success', background: '#1c1812', color: '#f5ede0', timer: 1500, showConfirmButton: false });
                    refreshKasirData();
                } else {
                    Swal.fire({ title: 'Gagal', text: data.message, icon: 'error', background: '#1c1812', color: '#f5ede0' });
                }
            });
        }
    });
}

function batalkanPesanan(order_id, invoice) {
    Swal.fire({
        title: 'Batalkan Pesanan?',
        html: `Pesanan <strong>${invoice}</strong> akan dibatalkan.`,
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
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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