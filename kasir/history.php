<?php 
include '../config/session.php'; 
require_once '../config/database.php'; 
requireRole(['admin', 'kasir']); 
date_default_timezone_set('Asia/Jakarta');
$db = (new Database())->getConnection(); 

$filter_date = $_GET['tanggal'] ?? date('Y-m-d');
$filter_status = $_GET['status'] ?? '';
$filter_search = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pesanan — Café Modern</title>
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
        <a href="dashboard.php" class="nav-item"><span class="nav-icon">📋</span> Pesanan Pending</a>
        <a href="history.php" class="nav-item active"><span class="nav-icon">🕓</span> History Pesanan</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-chip"><div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div><div class="user-info"><div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div><div class="urole">Kasir</div></div></div>
        <button class="logout-link" onclick="showLogoutModal()"><span>🚪</span> Logout</button>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left"><div class="greeting">Kasir Dashboard</div><h1>History <em>Pesanan</em></h1></div>
        <div class="topbar-right"><div class="date-pill" id="real-time-date">📅 -- : -- : --</div></div>
    </div>

    <div class="stats-grid" id="summary-stats">
        <div class="stat-card"><div class="stat-icon">📊</div><div class="stat-label">Total Transaksi</div><div class="stat-value" id="total-transaksi">0</div></div>
        <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-label">Pendapatan</div><div class="stat-value gold" id="total-pendapatan">Rp 0</div></div>
        <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-label">Lunas</div><div class="stat-value" style="color:#6ec97a" id="jml-lunas">0</div></div>
        <div class="stat-card"><div class="stat-icon">❌</div><div class="stat-label">Dibatalkan</div><div class="stat-value" style="color:#e07070" id="jml-batal">0</div></div>
    </div>

    <div class="table-section">
        <div class="table-header"><div class="table-title">Riwayat Pesanan <small>Filter berdasarkan tanggal, status, atau invoice</small></div></div>
        <form class="filter-bar" id="filter-form">
            <div class="filter-group"><label class="filter-label">Tanggal</label><input type="date" name="tanggal" class="filter-input" id="filter-date" value="<?= htmlspecialchars($filter_date) ?>"></div>
            <div class="filter-group"><label class="filter-label">Status</label><select name="status" class="filter-input" id="filter-status">
                <option value="">Semua</option>
                <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Lunas</option>
                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Batal</option>
            </select></div>
            <div class="filter-group" style="flex:1"><label class="filter-label">Cari Invoice / Nama</label><input type="text" name="search" class="filter-input" id="filter-search" placeholder="Cari..." value="<?= htmlspecialchars($filter_search) ?>"></div>
            <button type="submit" class="btn-filter">🔍 Filter</button>
            <button type="button" class="btn-filter-reset" id="reset-filter">✕ Reset</button>
        </form>
        <div class="table-wrap" id="history-table-wrap">
            <div class="loading-state">Memuat data...</div>
        </div>
    </div>
</main>

<div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()"><div class="modal-box" onclick="event.stopPropagation()"><div class="modal-emoji">🚪</div><div class="modal-title">Yakin mau logout?</div><div class="modal-desc">Sesi kamu akan diakhiri.</div><div class="modal-actions"><button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button><a href="../logout.php" class="mbtn mbtn-confirm">Ya, Logout</a></div></div></div>

<script>
// Live Clock
function updateClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', day: '2-digit', month: 'short', year: 'numeric' };
    document.getElementById('real-time-date').innerHTML = '📅 ' + now.toLocaleDateString('id-ID', options);
}
updateClock();
setInterval(updateClock, 1000);

// Fungsi untuk refresh history
async function refreshHistory() {
    const date = document.getElementById('filter-date').value;
    const status = document.getElementById('filter-status').value;
    const search = document.getElementById('filter-search').value;
    
    try {
        const res = await fetch(`../api/admin/orders.php?action=history_stats&date=${date}&status=${status}&search=${encodeURIComponent(search)}`);
        const data = await res.json();
        
        if (data.success) {
            // Update summary stats
            document.getElementById('total-transaksi').innerText = data.summary.total_transaksi;
            document.getElementById('total-pendapatan').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.summary.total_lunas);
            document.getElementById('jml-lunas').innerText = data.summary.jml_lunas;
            document.getElementById('jml-batal').innerText = data.summary.jml_batal;
            
            const wrap = document.getElementById('history-table-wrap');
            
            if (data.orders.length === 0) {
                wrap.innerHTML = '<div class="empty-state"><div class="empty-icon">🗂️</div><div class="empty-title">Tidak ada data</div><div class="empty-sub">Tidak ditemukan pesanan untuk filter yang dipilih.</div></div>';
            } else {
                wrap.innerHTML = `
                    <table id="history-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Pelanggan</th>
                                <th>Meja</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody">
                            ${data.orders.map(order => {
                                let statusClass = '';
                                let statusText = '';
                                
                                if (order.status_pembayaran === 'paid') {
                                    statusClass = 'status-lunas';
                                    statusText = '✓ Lunas';
                                } else if (order.status_pembayaran === 'failed') {
                                    statusClass = 'status-batal';
                                    statusText = '✕ Batal';
                                } else {
                                    statusClass = 'status-pending';
                                    statusText = '⏳ Pending';
                                }
                                
                                return `
                                    <tr id="history-row-${order.id}">
                                        <td><span class="invoice-badge">${order.invoice}</span></td>
                                        <td>${escapeHtml(order.nama_pelanggan)}</td>
                                        <td>${order.nomor_meja || '—'}</td>
                                        <td><span class="amount">Rp ${new Intl.NumberFormat('id-ID').format(order.total)}</span></td>
                                        <td><span class="method-chip ${order.metode_pembayaran === 'cash' ? 'cash' : ''}">${order.metode_pembayaran}</span></td>
                                        <td><span class="status-chip ${statusClass}">${statusText}</span></td>
                                        <td class="time-cell">${new Date(order.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                `;
            }
        }
    } catch(e) {
        console.error('Refresh history failed:', e);
        document.getElementById('history-table-wrap').innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><div class="empty-title">Error</div><div class="empty-sub">Gagal memuat data</div></div>';
    }
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

// Filter form submit
document.getElementById('filter-form').addEventListener('submit', (e) => {
    e.preventDefault();
    refreshHistory();
});

// Reset filter
document.getElementById('reset-filter').addEventListener('click', () => {
    document.getElementById('filter-date').value = '<?= date('Y-m-d') ?>';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-search').value = '';
    refreshHistory();
});

// Logout Modal
function showLogoutModal() { document.getElementById('logout-modal').classList.add('active'); }
function hideLogoutModal() { document.getElementById('logout-modal').classList.remove('active'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') hideLogoutModal(); });

// Auto refresh setiap 5 detik
setInterval(refreshHistory, 5000);

// Load pertama kali
refreshHistory();
</script>
</body>
</html>