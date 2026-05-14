<?php 
include '../config/session.php'; 
require_once '../config/database.php'; 
requireRole(['admin', 'kasir']); 
$db = (new Database())->getConnection(); 

// Gunakan waktu server Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');
$selected_date = $_GET['date'] ?? $today;

// Pastikan format tanggal valid
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = $today;
}

$stmt = $db->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_pendapatan FROM orders WHERE DATE(created_at) = ? AND status_pembayaran = 'paid'");
$stmt->execute([$selected_date]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total_menu = $db->query("SELECT COUNT(*) FROM menus")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM orders WHERE status_pembayaran = 'pending'")->fetchColumn();

$avg_order = $stats['total_orders'] > 0 ? number_format($stats['total_pendapatan'] / $stats['total_orders'], 0, ',', '.') : '0';

// Data untuk chart (7 hari terakhir dari HARI INI)
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days", strtotime($today)));
    $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as jumlah FROM orders WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $sales_data[] = [
        'tanggal' => date('d/m', strtotime($date)),
        'revenue' => (int)$row['total'],
        'orders' => (int)$row['jumlah']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Café Modern Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <div class="greeting">Selamat datang kembali</div>
            <h1><?= htmlspecialchars($_SESSION['nama']) ?>, <em>good day!</em></h1>
        </div>
        <div class="topbar-right">
            <div class="date-pill" id="real-time-date">📅 -- : -- : --</div>
            <button class="refresh-btn" onclick="location.reload()">↻ Refresh</button>
        </div>
    </div>

    <div class="filter-date" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label style="color: var(--cream-dim); font-size: 12px;">📅 Filter Tanggal:</label>
        <input type="date" id="filter-date" value="<?= $selected_date ?>" style="background: var(--bg-2); border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; color: var(--cream);">
        <button id="apply-date" style="background: var(--gold); color: #1a1008; border: none; border-radius: 8px; padding: 8px 20px; cursor: pointer; font-weight: 600;">Tampilkan</button>
        <button id="reset-date" style="background: transparent; border: 1px solid var(--border); border-radius: 8px; padding: 8px 20px; color: var(--cream-dim); cursor: pointer;">Hari Ini</button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-label">Order <?= $selected_date == $today ? 'Hari Ini' : date('d/m/Y', strtotime($selected_date)) ?></div>
            <div class="stat-value" id="stat-orders"><?= $stats['total_orders'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Pendapatan</div>
            <div class="stat-value gold sm" id="stat-revenue">Rp <?= number_format($stats['total_pendapatan'], 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🍽</div>
            <div class="stat-label">Total Menu</div>
            <div class="stat-value"><?= $total_menu ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">Pending Payment</div>
            <div class="stat-value <?= $pending > 0 ? 'amber' : 'green' ?>" id="stat-pending"><?= $pending ?></div>
        </div>
    </div>

    <div class="bottom-row">
        <div class="chart-card">
            <div class="card-header">
                <div class="card-title">Grafik Penjualan <small>7 hari terakhir</small></div>
                <div class="live-badge"><div class="pulse-dot"></div> Live</div>
            </div>
            <div class="chart-wrap">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="quick-panel">
            <div class="card-header" style="margin-bottom:0.5rem;">
                <div class="card-title">Ringkasan</div>
            </div>
            <div class="quick-item">
                <div class="quick-dot" style="background:#c9a050;"></div>
                <div class="quick-info">
                    <div class="qlabel">Rata-rata per Order</div>
                    <div class="qval gold">Rp <?= $avg_order ?></div>
                </div>
            </div>
            <div class="quick-item">
                <div class="quick-dot" style="background:#6ec97a;"></div>
                <div class="quick-info">
                    <div class="qlabel">Status Dapur</div>
                    <div class="qval green" id="kitchen-status">Aktif ✓</div>
                </div>
            </div>
            <div class="quick-item">
                <div class="quick-dot" style="background:#e8b84b;"></div>
                <div class="quick-info">
                    <div class="qlabel">Pending Payment</div>
                    <div class="qval amber" id="pending-status"><?= $pending ?> pesanan</div>
                </div>
            </div>
            <a href="orders.php" class="btn-orders">Lihat Semua Pesanan →</a>
        </div>
    </div>
</main>

<script>
const chartData = <?= json_encode($sales_data) ?>;
let salesChart = null;

function initChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const labels = chartData.map(d => d.tanggal);
    const revenue = chartData.map(d => d.revenue);
    const orders = chartData.map(d => d.orders);
    
    if (salesChart) salesChart.destroy();
    
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: revenue,
                    yAxisID: 'y',
                    borderColor: '#c9a050',
                    backgroundColor: 'rgba(201,160,80,0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#c9a050',
                    pointRadius: 4,
                    tension: 0.45,
                    fill: true,
                },
                {
                    label: 'Jumlah Order',
                    data: orders,
                    yAxisID: 'y1',
                    borderColor: '#7eb8d4',
                    borderWidth: 2,
                    pointBackgroundColor: '#7eb8d4',
                    pointRadius: 4,
                    tension: 0.45,
                    fill: false,
                    borderDash: [6, 3],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1410',
                    borderColor: 'rgba(201,160,80,0.35)',
                    callbacks: {
                        label: (ctx) => ctx.datasetIndex === 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw) : ctx.raw + ' order'
                    }
                }
            },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) } },
                y1: { position: 'right', grid: { drawOnChartArea: false } }
            }
        }
    });
}

// Realtime Clock WIB
function updateClock() {
    const now = new Date();
    const options = { timeZone: 'Asia/Jakarta', weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    const formatted = now.toLocaleDateString('id-ID', options);
    document.getElementById('real-time-date').innerHTML = '📅 ' + formatted;
}
updateClock();
setInterval(updateClock, 1000);

// Realtime Refresh Data
let currentFilterDate = '<?= $selected_date ?>';
const todayDate = '<?= $today ?>';

async function refreshDashboardData() {
    try {
        const res = await fetch(`../api/admin/orders.php?action=stats&date=${currentFilterDate}`);
        const data = await res.json();
        if (data.success) {
            document.getElementById('stat-orders').innerText = data.total_orders;
            document.getElementById('stat-revenue').innerHTML = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_pendapatan);
            document.getElementById('stat-pending').innerText = data.pending;
            document.getElementById('pending-status').innerHTML = data.pending + ' pesanan';
            document.getElementById('stat-pending').className = `stat-value ${data.pending > 0 ? 'amber' : 'green'}`;
        }
    } catch(e) { console.error('Refresh failed:', e); }
}
setInterval(refreshDashboardData, 5000);

// Filter buttons
document.getElementById('apply-date').addEventListener('click', () => {
    const date = document.getElementById('filter-date').value;
    if (date) window.location.href = `dashboard.php?date=${date}`;
});
document.getElementById('reset-date').addEventListener('click', () => {
    window.location.href = 'dashboard.php';
});

initChart();
</script>
</body>
</html>