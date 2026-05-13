<?php
include '../config/session.php';
require_once '../config/database.php';

requireRole(['admin', 'kasir']);

$db = (new Database())->getConnection();

// Filter params
$filter_date  = $_GET['tanggal']  ?? date('Y-m-d');
$filter_status = $_GET['status']  ?? '';
$filter_search = $_GET['search']  ?? '';

// Build query
$where = ["DATE(o.created_at) = :tanggal"];
$params = [':tanggal' => $filter_date];

if ($filter_status !== '') {
    $where[] = "o.status_pembayaran = :status";
    $params[':status'] = $filter_status;
}

if ($filter_search !== '') {
    $where[] = "(o.invoice LIKE :search OR o.nama_pelanggan LIKE :search)";
    $params[':search'] = '%' . $filter_search . '%';
}

$sql = "SELECT o.* FROM orders o WHERE " . implode(' AND ', $where) . " ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary for selected date
$stmt2 = $db->prepare("
    SELECT
        COUNT(*) as total_transaksi,
        COALESCE(SUM(CASE WHEN status_pembayaran='lunas' THEN total ELSE 0 END), 0) as total_lunas,
        SUM(CASE WHEN status_pembayaran='lunas' THEN 1 ELSE 0 END) as jml_lunas,
        SUM(CASE WHEN status_pembayaran='pending' THEN 1 ELSE 0 END) as jml_pending,
        SUM(CASE WHEN status_pembayaran='batal' THEN 1 ELSE 0 END) as jml_batal
    FROM orders WHERE DATE(created_at) = ?
");
$stmt2->execute([$filter_date]);
$summary = $stmt2->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Pesanan — Café Modern</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=JetBrains+Mono:wght@300;400;600;700&family=Barlow+Condensed:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/kasir.css">
</head>

<body>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="brand">Café Modern</div>
            <span class="tagline">Kasir Panel</span>
        </div>

        <div class="sidebar-role">
            <span>💵</span> Kasir
        </div>

        <div class="sidebar-section">Navigasi</div>

        <nav>
            <a href="dashboard.php" class="nav-item">
                <span class="nav-icon">📋</span> Pesanan Pending
            </a>
            <a href="history.php" class="nav-item active">
                <span class="nav-icon">🕓</span> History Pesanan
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-chip">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="uname"><?= htmlspecialchars($_SESSION['nama']) ?></div>
                    <div class="urole">Kasir</div>
                </div>
            </div>
            <button class="logout-link" onclick="showLogoutModal()">
                <span>🚪</span> Logout
            </button>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="greeting">Kasir Dashboard</div>
                <h1>History <em>Pesanan</em></h1>
            </div>
            <div class="topbar-right">
                <div class="date-pill">📅 <?= date('d M Y') ?></div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= $summary['total_transaksi'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Pendapatan</div>
                <div class="stat-value gold">Rp <?= number_format($summary['total_lunas'], 0, ',', '.') ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-label">Lunas</div>
                <div class="stat-value" style="color:#6ec97a"><?= $summary['jml_lunas'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-label">Dibatalkan</div>
                <div class="stat-value" style="color:#e07070"><?= $summary['jml_batal'] ?></div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="table-section">
            <div class="table-header">
                <div class="table-title">
                    Riwayat Pesanan
                    <small>Filter berdasarkan tanggal, status, atau invoice</small>
                </div>
            </div>

            <form class="filter-bar" method="GET" action="history.php">
                <div class="filter-group">
                    <label class="filter-label">Tanggal</label>
                    <input type="date" name="tanggal" class="filter-input" value="<?= htmlspecialchars($filter_date) ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-input">
                        <option value="" <?= $filter_status === '' ? 'selected' : '' ?>>Semua</option>
                        <option value="lunas" <?= $filter_status === 'lunas'   ? 'selected' : '' ?>>Lunas</option>
                        <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="batal" <?= $filter_status === 'batal'   ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>
                <div class="filter-group" style="flex:1">
                    <label class="filter-label">Cari Invoice / Nama</label>
                    <input type="text" name="search" class="filter-input" placeholder="Cari..." value="<?= htmlspecialchars($filter_search) ?>">
                </div>
                <button type="submit" class="btn-filter">🔍 Filter</button>
                <a href="history.php" class="btn-filter-reset">✕ Reset</a>
            </form>

            <!-- Table -->
            <div class="table-wrap">
                <?php if (count($orders) > 0): ?>
                    <table id="table-history">
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
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $s = strtolower($order['status_pembayaran']);
                                $status_cls = $s === 'lunas' ? 'status-lunas' : ($s === 'batal' ? 'status-batal' : 'status-pending');
                                $status_label = $s === 'lunas' ? '✓ Lunas' : ($s === 'batal' ? '✕ Batal' : '⏳ Pending');
                                $m = strtolower($order['metode_pembayaran']);
                                $method_cls = in_array($m, ['cash', 'tunai']) ? 'cash' : '';
                                ?>
                                <tr>
                                    <td><span class="invoice-badge"><?= htmlspecialchars($order['invoice']) ?></span></td>
                                    <td><?= htmlspecialchars($order['nama_pelanggan']) ?></td>
                                    <td><?= $order['nomor_meja'] ?? '—' ?></td>
                                    <td><span class="amount">Rp <?= number_format($order['total'], 0, ',', '.') ?></span></td>
                                    <td><span class="method-chip <?= $method_cls ?>"><?= htmlspecialchars($order['metode_pembayaran']) ?></span></td>
                                    <td><span class="status-chip <?= $status_cls ?>"><?= $status_label ?></span></td>
                                    <td class="time-cell"><?= date('H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🗂️</div>
                        <div class="empty-title">Tidak ada data</div>
                        <div class="empty-sub">Tidak ditemukan pesanan untuk filter yang dipilih.</div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($orders) > 0): ?>
                <div class="table-footer">
                    Menampilkan <strong><?= count($orders) ?></strong> transaksi pada
                    <strong><?= date('d M Y', strtotime($filter_date)) ?></strong>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <!-- ═══ LOGOUT MODAL ═══ -->
    <div class="modal-overlay" id="logout-modal" onclick="hideLogoutModal()">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-emoji">🚪</div>
            <div class="modal-title">Yakin mau logout?</div>
            <div class="modal-desc">Sesi kamu akan diakhiri. Pastikan semua transaksi sudah selesai diproses.</div>
            <div class="modal-actions">
                <button class="mbtn mbtn-cancel" onclick="hideLogoutModal()">Batal</button>
                <a href="../logout.php" class="mbtn mbtn-confirm">Ya, Logout</a>
            </div>
        </div>
    </div>

    <script>
        function showLogoutModal() {
            document.getElementById('logout-modal').classList.add('active');
        }

        function hideLogoutModal() {
            document.getElementById('logout-modal').classList.remove('active');
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hideLogoutModal();
        });
    </script>
</body>

</html>